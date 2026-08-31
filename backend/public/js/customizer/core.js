/**
 * Core Three.js initialization and main loop
 */
function init(containerId = 'three-container') {
    const container = document.getElementById(containerId);
    if (!container) return;

    // 1. Scene setup
    scene = new THREE.Scene();

    // 2. Camera setup
    camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(4, 3, 8);

    // 3. Renderer setup
    renderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: true,
        preserveDrawingBuffer: true // Required for screenshots
    });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.0;
    renderer.outputEncoding = THREE.sRGBEncoding;
    container.appendChild(renderer.domElement);

    // 4. Controls
    controls = new THREE.OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.autoRotate = false;
    controls.autoRotateSpeed = 2.0;

    // 5. Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
    scene.add(ambientLight);

    const directLight1 = new THREE.DirectionalLight(0xffffff, 1.0);
    directLight1.position.set(5, 5, 5);
    scene.add(directLight1);

    const directLight2 = new THREE.DirectionalLight(0xffffff, 0.5);
    directLight2.position.set(-5, 2, -5);
    scene.add(directLight2);

    const rimLight = new THREE.PointLight(0xffc508, 0.8);
    rimLight.position.set(0, 5, -5);
    scene.add(rimLight);

    // 6. Environment / Objects
    model_group = new THREE.Group();
    scene.add(model_group);

    // Initial model based on product type from config
    const initialShape = (typeof CustomizerConfig !== 'undefined' && CustomizerConfig.initialShape) ? CustomizerConfig.initialShape : 't-shirt';
    
    if (initialShape === 'mug') {
        createMugModel();
        $('.btn-shape[data-shape="mug"]').addClass('active');
    } else if (initialShape === 't-shirt') {
        createTshirtModel();
        $('.btn-shape[data-shape="t-shirt"]').addClass('active');
    } else if (initialShape === 'shorts') {
        createShortsModel();
        $('.btn-shape[data-shape="shorts"]').addClass('active');
    } else if (initialShape === 'umbrella') {
        createUmbrellaModel();
        $('.btn-shape[data-shape="umbrella"]').addClass('active');
    } else if (initialShape === 'bag') {
        createBagModel();
        $('.btn-shape[data-shape="bag"]').addClass('active');
    } else if (initialShape === 'polo') {
        createPoloModel();
        $('.btn-shape[data-shape="polo"]').addClass('active');
    }

    // 7. Event Listeners - Remove old listener if exists before adding new one
    if (window._currentResizeHandler) {
        window.removeEventListener('resize', window._currentResizeHandler);
    }
    window._currentResizeHandler = () => onWindowResize(containerId);
    window.addEventListener('resize', window._currentResizeHandler);

    animate();
}

/**
 * Frame a freshly loaded GLB the same way as every other product.
 *
 * The source files come from different authors at wildly different scales and
 * origins — the bag and the umbrella are each a fraction of a unit across and
 * sit well off to one side of their own origin. Rather than hand-tuning a magic
 * scale per file, measure the model, size its largest dimension to `targetSize`
 * (matching the ~2.5 units the mug and t-shirt end up at), then recentre it so
 * OrbitControls pivots through the middle of the product.
 *
 * Call this BEFORE adding the model to model_group: the group carries the S/M/L
 * size scale, and measuring through it would cancel that out.
 */
function fitModelToView(model, targetSize = 2.5) {
    const size = new THREE.Box3().setFromObject(model).getSize(new THREE.Vector3());
    const largest = Math.max(size.x, size.y, size.z);
    if (!isFinite(largest) || largest <= 0) return;

    model.scale.setScalar(targetSize / largest);

    // Re-measure once scaled, then shift the model so its centre lands on the origin.
    const center = new THREE.Box3().setFromObject(model).getCenter(new THREE.Vector3());
    model.position.sub(center);
}

/**
 * Put `object` in the model group as the only thing in it.
 *
 * Every model loads asynchronously but clears the group synchronously, before
 * its load starts. Two loads that overlap therefore both clear an already-empty
 * group and then both add, leaving two models stacked in the same place — a mug
 * with a handle on each side. Overlapping loads happen whenever init() runs
 * twice before the first GLB arrives, which a preview being opened, closed and
 * reopened does easily.
 *
 * Clearing here instead makes the last load to arrive the one that wins,
 * whatever order they were started in.
 */
function setModel(object) {
    if (!model_group) return;

    while (model_group.children.length > 0) {
        model_group.remove(model_group.children[0]);
    }

    model_group.add(object);
}

/**
 * Grow `target` to cover a mesh's vertices, with the mesh's own transform
 * applied. Measured against the loaded model's root, so call it before the
 * model joins model_group.
 */
function getMeshBounds(mesh, target) {
    const box = target || new THREE.Box3();
    const position = mesh.geometry ? mesh.geometry.attributes.position : null;
    if (!position) return box;

    mesh.updateWorldMatrix(true, false);
    const point = new THREE.Vector3();
    for (let i = 0; i < position.count; i++) {
        box.expandByPoint(point.fromBufferAttribute(position, i).applyMatrix4(mesh.matrixWorld));
    }
    return box;
}

/**
 * Re-unwrap a mesh by projecting it straight down onto the XZ plane.
 *
 * Every design is painted onto one 1024x1024 canvas, which only reaches the
 * model if the print surface is unwrapped across that tile. Not every GLB is:
 * the umbrella canopy ships unwrapped at u 2.18..3.20 — wholly outside the tile,
 * so with three's default clamped wrapping the canopy sampled a single column of
 * edge pixels and nothing the customer added ever showed up. Its island also
 * splits in two with a dead band through the middle, so simply rescaling it into
 * the tile would drop anything centred straight into the gap.
 *
 * A top-down projection gives one continuous print area instead, with the canvas
 * centre on the canopy — which is also how a design would really be printed onto
 * a dome. Pass `bounds` to project several meshes through one shared box so a
 * design lines up across them.
 *
 * Returns false if the mesh has no footprint to project onto.
 */
function applyPlanarUVs(mesh, bounds) {
    const position = mesh.geometry ? mesh.geometry.attributes.position : null;
    if (!position) return false;

    const box = bounds || getMeshBounds(mesh);
    const spanX = box.max.x - box.min.x;
    const spanZ = box.max.z - box.min.z;
    if (!(spanX > 0) || !(spanZ > 0)) return false;

    mesh.updateWorldMatrix(true, false);
    const point = new THREE.Vector3();
    const uv = new Float32Array(position.count * 2);

    for (let i = 0; i < position.count; i++) {
        point.fromBufferAttribute(position, i).applyMatrix4(mesh.matrixWorld);
        uv[i * 2] = (point.x - box.min.x) / spanX;
        uv[i * 2 + 1] = (point.z - box.min.z) / spanZ;
    }

    mesh.geometry.setAttribute('uv', new THREE.BufferAttribute(uv, 2));
    return true;
}

/**
 * Re-unwrap a garment by projecting its front half and back half into opposite
 * halves of the design tile.
 *
 * applyPlanarUVs() flattens one dome from above, which suits an umbrella but not
 * a shirt: a single projection gives the front and the back the same UVs, so a
 * logo placed on the chest also prints on the back, reversed. This splits them
 * on the sign of the vertex normal instead — front-facing geometry lands in
 * u 0..0.5, back-facing in u 0.5..1 — which is what a real garment atlas does
 * and what lets a model offer separate front and back panels.
 *
 * The two halves are mapped to meet at the +x side seam and part at the -x one,
 * so a triangle straddling the silhouette stays continuous everywhere except
 * that single seam, rather than being stretched across the whole tile at both.
 *
 * Both halves are scaled by the SAME factor, so the weave of a texture stays
 * square instead of being stretched to whatever aspect the model happens to
 * have. The scale normally comes from the width, since each half only has half
 * the tile to fit into; a model more than twice as tall as it is wide would run
 * off the top and bottom, so the height caps it.
 *
 * Pass `bounds` to project several meshes through one shared box — a garment
 * exported in chunks needs that, or the design steps at every chunk boundary.
 *
 * Returns false if the mesh has no normals or no width to project.
 */
function applyFrontBackUVs(mesh, bounds, sleeves, sides) {
    const position = mesh.geometry ? mesh.geometry.attributes.position : null;
    const normal = mesh.geometry ? mesh.geometry.attributes.normal : null;
    if (!position || !normal) return false;

    const box = bounds || getMeshBounds(mesh);
    const spanX = box.max.x - box.min.x;
    const spanY = box.max.y - box.min.y;
    if (!(spanX > 0) || !(spanY > 0)) return false;

    const scale = Math.min(0.5 / spanX, 1 / spanY);
    const centerY = (box.min.y + box.max.y) / 2;

    mesh.updateWorldMatrix(true, false);
    const normalMatrix = new THREE.Matrix3().getNormalMatrix(mesh.matrixWorld);
    const point = new THREE.Vector3();
    const facing = new THREE.Vector3();
    const uv = new Float32Array(position.count * 2);

    for (let i = 0; i < position.count; i++) {
        point.fromBufferAttribute(position, i).applyMatrix4(mesh.matrixWorld);
        facing.fromBufferAttribute(normal, i).applyMatrix3(normalMatrix);

        // Read the side off splitGarmentSeam()'s verdict where there is one.
        // A vertex it duplicated sits on the body but belongs to a sleeve
        // triangle, or the reverse, and asking its position again here would
        // hand the triangle back the split it was just rescued from.
        const onSleeve = sides
            ? sides[i] === 1
            : sleeves && Math.abs(point.x - sleeves.centerX) > sleeves.splitX;

        if (sleeves && onSleeve) {
            // Flattened along x instead: see planSleeveUVs().
            const left = point.x < sleeves.centerX;
            const round = left ? point.z - sleeves.zMin : sleeves.zMax - point.z;
            uv[i * 2] = (left ? sleeves.leftU : sleeves.rightU) + round * sleeves.scale;
            uv[i * 2 + 1] = sleeves.topV + (sleeves.yMax - point.y) * sleeves.scale;
            continue;
        }

        // 0 at the -x seam, 0.5 at the +x seam, for both halves.
        const across = (point.x - box.min.x) * scale;

        uv[i * 2] = facing.z >= 0 ? across : 1 - across;
        // Canvas v grows downward while world y grows up, so this subtraction is
        // what keeps artwork the right way up without a flipV on every zone.
        uv[i * 2 + 1] = 0.5 + (centerY - point.y) * scale;
    }

    mesh.geometry.setAttribute('uv', new THREE.BufferAttribute(uv, 2));
    return true;
}

/**
 * Work out where a garment's sleeves go in the tile, for applyFrontBackUVs()
 * to unwrap them apart from the body.
 *
 * The front/back projection flattens the garment along z, which a torso can
 * afford: its two faces are broadly flat, so throwing depth away costs almost
 * nothing. A sleeve is a tube, and it cannot. Flattened along z, every point
 * on the front of the tube at a given x collapses onto ONE u — so a design put
 * there is smeared around the arm, pinching where the surface turns. No zone
 * rectangle can fix that; the projection has to change.
 *
 * So sleeves are flattened along x instead: u from z, v from y. That is the
 * view from the side, which is both where a sleeve print is looked at and the
 * one direction a sleeve does not wrap. The outer surface — the half anyone
 * sees — comes through undistorted, and what compresses instead is the front
 * and back of the arm, which is the right thing to give up.
 *
 * They are packed into the strip above the torso. The body projection maps
 * y to v about the model's middle at a scale set by its full width, so on a
 * garment wider than half its height it never reaches the top of the tile —
 * on the polo it stops at v 0.179, and that empty strip is what these two
 * rectangles sit in, side by side with a gap between them.
 *
 * Both sleeves share one scale, so the weave of a texture stays the same size
 * across the garment, and both are unmirrored by construction: u grows with +z
 * on the left sleeve and -z on the right, which is screen-right from a camera
 * on that side.
 *
 * Triangles that straddle the split would get one end of each projection and
 * stretch between them, which draws a torn line round the armhole rather than
 * the hairline it sounds like: a triangle spanning the two rectangles sweeps
 * the canvas between them and drags whatever it crosses along the seam.
 * splitGarmentSeam() below gives those triangles their own vertices first, so
 * every one of them lands wholly on one side and none of them sweeps anything.
 *
 * Pass the same meshes and bounds as applyFrontBackUVs(). Returns null when the
 * model has nothing past the split, which leaves the caller unwrapping a plain
 * front and back.
 */
/**
 * Give every triangle that straddles the sleeve split its own vertices, so
 * each one lands wholly on the body side or wholly on the sleeve side.
 *
 * The two projections send neighbouring points to opposite ends of the tile.
 * A triangle with a vertex in each therefore does not merely stretch — it
 * sweeps the canvas between them, picking up every design it crosses and
 * smearing them along the seam. On the polo that read as a torn white line
 * round the armhole.
 *
 * The fix is the one a modelling tool would use: a UV seam needs duplicate
 * vertices, one set for each side, and a projected unwrap has to make its own
 * because the mesh was never split there. Only the triangles actually spanning
 * the boundary need them — a single ring around each armhole, a few hundred
 * out of hundreds of thousands — so this costs almost nothing, unlike
 * un-indexing the whole mesh to get the same guarantee.
 *
 * A duplicated vertex keeps its position. What changes is which projection
 * reads it: the whole triangle is assigned to the side that already holds two
 * of its corners, and the odd corner is unwrapped there too, a fraction past
 * where that projection was measured. Better a triangle slightly overshooting
 * its own panel than one straddling both.
 *
 * Returns the per-vertex side for applyFrontBackUVs() to use in place of
 * re-reading position, or null if the mesh is unindexed — every triangle
 * already owns its vertices then, and there is nothing to split.
 */
function splitGarmentSeam(mesh, sleeves) {
    const geometry = mesh.geometry;
    const position = geometry && geometry.attributes.position;
    if (!position || !sleeves) return null;

    mesh.updateWorldMatrix(true, false);
    const point = new THREE.Vector3();
    const sideOf = (i) => {
        point.fromBufferAttribute(position, i).applyMatrix4(mesh.matrixWorld);
        return Math.abs(point.x - sleeves.centerX) > sleeves.splitX ? 1 : 0;
    };

    let sides = new Uint8Array(position.count);
    for (let i = 0; i < position.count; i++) sides[i] = sideOf(i);

    const index = geometry.index;
    if (!index) return sides;

    // Pass one: which vertices need a copy, and on which side.
    const indices = index.array;
    const copies = new Map();   // original index * 2 + side -> new index
    const source = [];          // original index for each copy, in order
    let next = position.count;

    for (let t = 0; t < indices.length; t += 3) {
        const a = indices[t], b = indices[t + 1], c = indices[t + 2];
        const total = sides[a] + sides[b] + sides[c];
        if (total === 0 || total === 3) continue;

        const want = total >= 2 ? 1 : 0;
        for (const v of [a, b, c]) {
            if (sides[v] === want) continue;
            const key = v * 2 + want;
            if (copies.has(key)) continue;
            copies.set(key, next++);
            source.push(v);
        }
    }

    if (!copies.size) return sides;

    // Pass two: grow every attribute by the copies, then point the straddling
    // triangles at them.
    const grown = next;
    for (const name of Object.keys(geometry.attributes)) {
        const attribute = geometry.attributes[name];
        const size = attribute.itemSize;
        const array = new attribute.array.constructor(grown * size);
        array.set(attribute.array.subarray(0, position.count * size));
        source.forEach((from, n) => {
            const to = position.count + n;
            for (let c = 0; c < size; c++) array[to * size + c] = attribute.array[from * size + c];
        });
        geometry.setAttribute(name, new THREE.BufferAttribute(array, size, attribute.normalized));
    }

    const sidesGrown = new Uint8Array(grown);
    sidesGrown.set(sides);
    source.forEach((from, n) => {
        // The copy exists precisely to sit on the other side from its original.
        sidesGrown[position.count + n] = sides[from] === 1 ? 0 : 1;
    });

    for (let t = 0; t < indices.length; t += 3) {
        const a = indices[t], b = indices[t + 1], c = indices[t + 2];
        const total = sides[a] + sides[b] + sides[c];
        if (total === 0 || total === 3) continue;

        const want = total >= 2 ? 1 : 0;
        [a, b, c].forEach((v, n) => {
            if (sides[v] !== want) indices[t + n] = copies.get(v * 2 + want);
        });
    }
    index.needsUpdate = true;

    return sidesGrown;
}

function planSleeveUVs(meshes, bounds, splitX, band) {
    const centerX = (bounds.min.x + bounds.max.x) / 2;
    const top = band && band.top !== undefined ? band.top : 0.005;
    const height = band && band.height !== undefined ? band.height : 0.169;
    const width = band && band.width !== undefined ? band.width : 0.24;

    let zMin = Infinity, zMax = -Infinity, yMin = Infinity, yMax = -Infinity;
    const point = new THREE.Vector3();

    for (const mesh of meshes) {
        const position = mesh.geometry && mesh.geometry.attributes.position;
        if (!position) continue;
        mesh.updateWorldMatrix(true, false);
        for (let i = 0; i < position.count; i++) {
            point.fromBufferAttribute(position, i).applyMatrix4(mesh.matrixWorld);
            if (Math.abs(point.x - centerX) <= splitX) continue;
            if (point.z < zMin) zMin = point.z;
            if (point.z > zMax) zMax = point.z;
            if (point.y < yMin) yMin = point.y;
            if (point.y > yMax) yMax = point.y;
        }
    }

    const zSpan = zMax - zMin;
    const ySpan = yMax - yMin;
    if (!(zSpan > 0) || !(ySpan > 0)) return null;

    const scale = Math.min(width / zSpan, height / ySpan);
    const leftU = 0.02;

    return {
        centerX, splitX, scale, zMin, zMax, yMax,
        topV: top,
        leftU,
        rightU: leftU + zSpan * scale + 0.06,
    };
}

/**
 * Utility to hide loading screens across both editor and preview modal
 */
function hideLoadingScreen() {
    // Target both potential loader IDs to avoid conflicts
    const $loaders = $('#loader, #preview-loader');
    if ($loaders.length > 0) {
        $loaders.stop(true, true).fadeOut(400);
    }
}

function animate() {
    requestAnimationFrame(animate);
    if (controls) controls.update();
    if (renderer && scene && camera) renderer.render(scene, camera);
}

function onWindowResize(containerId = 'three-container') {
    const container = document.getElementById(containerId);
    if (!container) return;
    camera.aspect = container.clientWidth / container.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
}

function resetCamera() {
    camera.position.set(4, 3, 8);
    if(controls) controls.target.set(0, 0, 0);
}

/**
 * Swing the viewer round to look at a panel head-on.
 *
 * Editing the back of a shirt while staring at its front is guesswork, so
 * picking a panel moves the camera to it. All four sit the same distance out,
 * so switching panels reframes rather than zooms.
 */
function focusZoneCamera(zone) {
    if (!zone || !zone.camera || !camera) return;

    camera.position.set(zone.camera.x, zone.camera.y, zone.camera.z);
    if (controls) {
        controls.target.set(0, 0, 0);
        controls.update();
    }
}

function toggleAutoRotate() {
    isRotating = !isRotating;
    if(controls) controls.autoRotate = isRotating;
    const btnRotate = document.getElementById('btn-rotate');
    if (btnRotate) btnRotate.classList.toggle('active');
}

function downloadImage() {
    const link = document.createElement('a');
    link.download = 'custom-product-design.png';
    link.href = renderer.domElement.toDataURL('image/png');
    link.click();
}
