function createTumblerModel() {
while (model_group.children.length > 0) {
model_group.remove(model_group.children[0]);
}

const tumblerMat = new THREE.MeshStandardMaterial({
color: getActiveColor(),
roughness: 0.2,
metalness: 0.8
});

// Tumbler Body (Tapered)
const bodyGeom = new THREE.CylinderGeometry(1.1, 0.8, 4, 32);
const body = new THREE.Mesh(bodyGeom, tumblerMat);
body.name = 'base';
body.position.y = 2;
model_group.add(body);

// Lid
const lidGeom = new THREE.CylinderGeometry(1.2, 1.1, 0.4, 32);
const lidMat = new THREE.MeshStandardMaterial({ color: 0x222222, roughness: 0.5, metalness: 0.2 });
const lid = new THREE.Mesh(lidGeom, lidMat);
lid.position.y = 4.1;
lid.name = 'accents';
model_group.add(lid);

model_group.scale.set(0.8, 0.8, 0.8);
}