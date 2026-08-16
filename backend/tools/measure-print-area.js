/**
 * Measure the UV bounds of a GLB's camera-facing panel.
 *
 * The customizer paints designs onto a 1024px texture sheet and the model's UV
 * map decides where that lands. `designPrintArea` tells it which slice of the
 * sheet is the panel a customer actually sees, so the X/Y sliders stay on it.
 * Only bag.js has ever had that measured; this works the numbers out for the
 * rest.
 *
 * Method: transform every triangle to world space, keep the ones whose normal
 * points at the camera, cluster those into UV islands, and report each island's
 * UV bounding box. The chest/front panel is the biggest island centred on the
 * model's midline.
 *
 * Pick the island that is largest, centred on the model's midline, and nearest
 * the camera (highest centroid z) — an inner surface can face the camera too,
 * and on the bag the inside of the back panel outranks the real front by area.
 * Then paste the SUGGESTED line into the model's loader and eyeball it in the
 * studio: push all four sliders to their extremes and check the design stays on
 * the panel.
 *
 * Validated against bag.js, whose values were measured by hand: this reproduces
 * them to within 0.005 UV.
 *
 * Usage:  cd public/gbl && node ../../tools/measure-print-area.js t-shirt.glb
 */

const fs = require('fs');

// ── GLB container ────────────────────────────────────────────────────────

function readGlb(path) {
    const buf = fs.readFileSync(path);
    if (buf.readUInt32LE(0) !== 0x46546c67) throw new Error(`${path}: not a GLB`);

    let offset = 12;
    let json = null;
    let bin = null;

    while (offset < buf.length) {
        const chunkLength = buf.readUInt32LE(offset);
        const chunkType = buf.readUInt32LE(offset + 4);
        const start = offset + 8;

        if (chunkType === 0x4e4f534a) json = JSON.parse(buf.slice(start, start + chunkLength).toString('utf8'));
        else if (chunkType === 0x004e4942) bin = buf.slice(start, start + chunkLength);

        offset = start + chunkLength + ((4 - (chunkLength % 4)) % 4);
    }

    if (!json) throw new Error(`${path}: no JSON chunk`);
    return { json, bin };
}

const COMPONENT = {
    5120: { size: 1, get: (b, o) => b.readInt8(o) },
    5121: { size: 1, get: (b, o) => b.readUInt8(o) },
    5122: { size: 2, get: (b, o) => b.readInt16LE(o) },
    5123: { size: 2, get: (b, o) => b.readUInt16LE(o) },
    5125: { size: 4, get: (b, o) => b.readUInt32LE(o) },
    5126: { size: 4, get: (b, o) => b.readFloatLE(o) },
};

const NUM_COMPONENTS = { SCALAR: 1, VEC2: 2, VEC3: 3, VEC4: 4, MAT4: 16 };

function readAccessor(gltf, bin, index) {
    const accessor = gltf.json.accessors[index];
    const comp = COMPONENT[accessor.componentType];
    const n = NUM_COMPONENTS[accessor.type];
    const out = [];

    if (accessor.bufferView === undefined) {
        for (let i = 0; i < accessor.count * n; i++) out.push(0);
        return { data: out, n };
    }

    const view = gltf.json.bufferViews[accessor.bufferView];
    const base = (view.byteOffset || 0) + (accessor.byteOffset || 0);
    const stride = view.byteStride || comp.size * n;

    for (let i = 0; i < accessor.count; i++) {
        for (let c = 0; c < n; c++) out.push(comp.get(bin, base + i * stride + c * comp.size));
    }

    return { data: out, n };
}

// ── Matrices ─────────────────────────────────────────────────────────────

const IDENTITY = [1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1];

function multiply(a, b) {
    const out = new Array(16).fill(0);
    for (let c = 0; c < 4; c++) {
        for (let r = 0; r < 4; r++) {
            let sum = 0;
            for (let k = 0; k < 4; k++) sum += a[k * 4 + r] * b[c * 4 + k];
            out[c * 4 + r] = sum;
        }
    }
    return out;
}

function composeTrs(node) {
    if (node.matrix) return node.matrix.slice();

    const [tx, ty, tz] = node.translation || [0, 0, 0];
    const [qx, qy, qz, qw] = node.rotation || [0, 0, 0, 1];
    const [sx, sy, sz] = node.scale || [1, 1, 1];

    const x2 = qx + qx, y2 = qy + qy, z2 = qz + qz;
    const xx = qx * x2, xy = qx * y2, xz = qx * z2;
    const yy = qy * y2, yz = qy * z2, zz = qz * z2;
    const wx = qw * x2, wy = qw * y2, wz = qw * z2;

    return [
        (1 - (yy + zz)) * sx, (xy + wz) * sx, (xz - wy) * sx, 0,
        (xy - wz) * sy, (1 - (xx + zz)) * sy, (yz + wx) * sy, 0,
        (xz + wy) * sz, (yz - wx) * sz, (1 - (xx + yy)) * sz, 0,
        tx, ty, tz, 1,
    ];
}

const applyPoint = (m, [x, y, z]) => [
    m[0] * x + m[4] * y + m[8] * z + m[12],
    m[1] * x + m[5] * y + m[9] * z + m[13],
    m[2] * x + m[6] * y + m[10] * z + m[14],
];

const applyDir = (m, [x, y, z]) => [
    m[0] * x + m[4] * y + m[8] * z,
    m[1] * x + m[5] * y + m[9] * z,
    m[2] * x + m[6] * y + m[10] * z,
];

// ── Geometry gathering ───────────────────────────────────────────────────

/** Every triangle in the file, in world space, with its UVs. */
function collectTriangles(gltf) {
    const { json, bin } = gltf;
    const triangles = [];

    const walk = (nodeIndex, parentMatrix) => {
        const node = json.nodes[nodeIndex];
        const world = multiply(parentMatrix, composeTrs(node));

        if (node.mesh !== undefined) {
            for (const prim of json.meshes[node.mesh].primitives) {
                if (prim.attributes.TEXCOORD_0 === undefined) continue;
                if (prim.mode !== undefined && prim.mode !== 4) continue;

                const pos = readAccessor(gltf, bin, prim.attributes.POSITION);
                const uv = readAccessor(gltf, bin, prim.attributes.TEXCOORD_0);
                const nrm = prim.attributes.NORMAL !== undefined
                    ? readAccessor(gltf, bin, prim.attributes.NORMAL)
                    : null;

                const indices = prim.indices !== undefined
                    ? readAccessor(gltf, bin, prim.indices).data
                    : Array.from({ length: pos.data.length / 3 }, (_, i) => i);

                for (let i = 0; i < indices.length; i += 3) {
                    const verts = [indices[i], indices[i + 1], indices[i + 2]].map(vi => ({
                        vi,
                        p: applyPoint(world, [pos.data[vi * 3], pos.data[vi * 3 + 1], pos.data[vi * 3 + 2]]),
                        uv: [uv.data[vi * 2], uv.data[vi * 2 + 1]],
                        n: nrm ? applyDir(world, [nrm.data[vi * 3], nrm.data[vi * 3 + 1], nrm.data[vi * 3 + 2]]) : null,
                    }));

                    // Face normal from the geometry when the file omits one.
                    let normal = verts[0].n
                        ? [0, 1, 2].reduce((acc, c) => (acc.push((verts[0].n[c] + verts[1].n[c] + verts[2].n[c]) / 3), acc), [])
                        : faceNormal(verts[0].p, verts[1].p, verts[2].p);

                    const len = Math.hypot(...normal) || 1;
                    normal = normal.map(v => v / len);

                    triangles.push({ verts, normal, key: `${nodeIndex}:${node.mesh}` });
                }
            }
        }

        for (const child of node.children || []) walk(child, world);
    };

    const scene = json.scenes[json.scene || 0];
    for (const root of scene.nodes) walk(root, IDENTITY);

    return triangles;
}

function faceNormal(a, b, c) {
    const u = [b[0] - a[0], b[1] - a[1], b[2] - a[2]];
    const v = [c[0] - a[0], c[1] - a[1], c[2] - a[2]];
    return [u[1] * v[2] - u[2] * v[1], u[2] * v[0] - u[0] * v[2], u[0] * v[1] - u[1] * v[0]];
}

// ── UV islands ───────────────────────────────────────────────────────────

/**
 * Cluster triangles into UV islands. Two triangles sharing a vertex index share
 * that vertex's UV, so union-find over indices gives the islands directly —
 * glTF duplicates vertices wherever a UV seam splits them.
 */
function clusterIslands(triangles) {
    const parent = new Map();
    const find = (x) => {
        while (parent.get(x) !== x) {
            parent.set(x, parent.get(parent.get(x)));
            x = parent.get(x);
        }
        return x;
    };
    const union = (a, b) => {
        const [ra, rb] = [find(a), find(b)];
        if (ra !== rb) parent.set(ra, rb);
    };

    for (const tri of triangles) {
        for (const v of tri.verts) {
            const id = `${tri.key}:${v.vi}`;
            if (!parent.has(id)) parent.set(id, id);
        }
    }
    for (const tri of triangles) {
        const ids = tri.verts.map(v => `${tri.key}:${v.vi}`);
        union(ids[0], ids[1]);
        union(ids[1], ids[2]);
    }

    const islands = new Map();
    for (const tri of triangles) {
        const root = find(`${tri.key}:${tri.verts[0].vi}`);
        if (!islands.has(root)) islands.set(root, []);
        islands.get(root).push(tri);
    }

    return [...islands.values()];
}

const triUvArea = (t) => {
    const [a, b, c] = t.verts.map(v => v.uv);
    return Math.abs((b[0] - a[0]) * (c[1] - a[1]) - (c[0] - a[0]) * (b[1] - a[1])) / 2;
};

function correlation(xs, ys) {
    const n = xs.length;
    if (n < 2) return 0;
    const mx = xs.reduce((a, b) => a + b, 0) / n;
    const my = ys.reduce((a, b) => a + b, 0) / n;
    let num = 0, dx = 0, dy = 0;
    for (let i = 0; i < n; i++) {
        num += (xs[i] - mx) * (ys[i] - my);
        dx += (xs[i] - mx) ** 2;
        dy += (ys[i] - my) ** 2;
    }
    return (dx && dy) ? num / Math.sqrt(dx * dy) : 0;
}

const quantile = (sorted, q) => sorted[Math.min(sorted.length - 1, Math.max(0, Math.floor(q * (sorted.length - 1))))];

function describe(island) {
    const us = [], vs = [], xs = [], ys = [], zs = [];
    for (const tri of island) {
        for (const v of tri.verts) {
            us.push(v.uv[0]); vs.push(v.uv[1]);
            xs.push(v.p[0]); ys.push(v.p[1]); zs.push(v.p[2]);
        }
    }
    const su = [...us].sort((a, b) => a - b);
    const sv = [...vs].sort((a, b) => a - b);
    const mean = (a) => a.reduce((x, y) => x + y, 0) / a.length;

    return {
        triangles: island.length,
        uvArea: island.reduce((sum, t) => sum + triUvArea(t), 0),
        raw: { u0: su[0], u1: su[su.length - 1], v0: sv[0], v1: sv[sv.length - 1] },
        // Trimmed bounds ignore the few stray vertices that a tapered panel
        // throws out past the body of the island.
        p02: { u0: quantile(su, 0.02), u1: quantile(su, 0.98), v0: quantile(sv, 0.02), v1: quantile(sv, 0.98) },
        centroid: [mean(xs), mean(ys), mean(zs)],
        // Canvas v grows downward while world Y grows up, so an un-mirrored
        // panel wants corr(v, worldY) < 0 and corr(u, worldX) > 0.
        corrUX: correlation(us, xs),
        corrVY: correlation(vs, ys),
    };
}

// ── Report ───────────────────────────────────────────────────────────────

const FACING = 0.5; // normal.z above this = pointing at the camera at (4,3,8)

for (const path of process.argv.slice(2)) {
    const gltf = readGlb(path);
    const all = collectTriangles(gltf);
    const facing = all.filter(t => t.normal[2] > FACING);

    console.log(`\n${'='.repeat(72)}\n${path}`);
    console.log(`${all.length} triangles, ${facing.length} facing the camera (normal.z > ${FACING})`);

    if (!facing.length) {
        console.log('  No camera-facing geometry found — check the model orientation.');
        continue;
    }

    const islands = clusterIslands(facing)
        .map(describe)
        .sort((a, b) => b.uvArea - a.uvArea)
        .slice(0, 6);

    islands.forEach((island, i) => {
        const { raw, p02 } = island;
        const inset = (b) => ({
            u0: b.u0 + (b.u1 - b.u0) * 0.08,
            u1: b.u1 - (b.u1 - b.u0) * 0.08,
            v0: b.v0 + (b.v1 - b.v0) * 0.08,
            v1: b.v1 - (b.v1 - b.v0) * 0.08,
        });
        const s = inset(p02);
        const f = (n) => n.toFixed(3);

        console.log(`\n  Island ${i + 1}: ${island.triangles} tris, UV area ${island.uvArea.toFixed(4)}`);
        console.log(`    world centroid  x ${island.centroid[0].toFixed(2)}  y ${island.centroid[1].toFixed(2)}  z ${island.centroid[2].toFixed(2)}`);
        console.log(`    raw bounds      u ${f(raw.u0)}..${f(raw.u1)}   v ${f(raw.v0)}..${f(raw.v1)}`);
        console.log(`    trimmed (2-98%) u ${f(p02.u0)}..${f(p02.u1)}   v ${f(p02.v0)}..${f(p02.v1)}`);
        console.log(`    corr(u,worldX) ${island.corrUX.toFixed(2)}   corr(v,worldY) ${island.corrVY.toFixed(2)}`);
        console.log(`    flipU ${island.corrUX < 0}   flipV ${island.corrVY > 0}`);
        console.log(`    SUGGESTED { u0: ${f(s.u0)}, v0: ${f(s.v0)}, u1: ${f(s.u1)}, v1: ${f(s.v1)}`
            + `${island.corrUX < 0 ? ', flipU: true' : ''}${island.corrVY > 0 ? ', flipV: true' : ''} }`);
    });
}
