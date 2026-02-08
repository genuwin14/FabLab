function createBagModel() {
while (model_group.children.length > 0) {
model_group.remove(model_group.children[0]);
}

const bagMat = new THREE.MeshStandardMaterial({
color: getActiveColor(),
roughness: 0.6,
metalness: 0.1
});

const detailMat = new THREE.MeshStandardMaterial({
color: 0x222222,
roughness: 0.3,
metalness: 0.5
});

// Main Body
const bodyGeom = new THREE.BoxGeometry(3.5, 2.5, 0.8);
const body = new THREE.Mesh(bodyGeom, bagMat);
body.position.y = 1.25;
body.name = 'base';
model_group.add(body);

// Front Pocket
const pocketGeom = new THREE.BoxGeometry(3, 1.8, 0.2);
const pocket = new THREE.Mesh(pocketGeom, bagMat);
pocket.position.set(0, 1.1, 0.5);
pocket.name = 'base';
model_group.add(pocket);

// Zipper line
const zipGeom = new THREE.BoxGeometry(3.6, 0.1, 0.85);
const zip = new THREE.Mesh(zipGeom, detailMat);
zip.position.y = 2.4;
zip.name = 'accents';
model_group.add(zip);

// Handle
const handleGeom = new THREE.TorusGeometry(0.5, 0.1, 12, 24, Math.PI);
const handle = new THREE.Mesh(handleGeom, detailMat);
handle.position.set(0, 2.5, 0);
handle.name = 'accents';
model_group.add(handle);

model_group.scale.set(1.2, 1.2, 1.2);
}