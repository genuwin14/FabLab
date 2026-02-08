function createMugModel() {
while (model_group.children.length > 0) {
model_group.remove(model_group.children[0]);
}

// Mug Body
const mugGeom = new THREE.CylinderGeometry(1, 1, 2.2, 32, 1, true);
const bottomGeom = new THREE.CircleGeometry(1, 32);

const mugMat = new THREE.MeshStandardMaterial({
color: getActiveColor(),
roughness: 0.1,
metalness: 0.2,
side: THREE.DoubleSide
});

const body = new THREE.Mesh(mugGeom, mugMat);
const bottom = new THREE.Mesh(bottomGeom, mugMat);
bottom.rotation.x = -Math.PI / 2;
bottom.position.y = -1.1;

const mugGroup = new THREE.Group();
mugGroup.add(body);
mugGroup.add(bottom);
mugGroup.name = 'base';

// Handle
const handleGeom = new THREE.TorusGeometry(0.6, 0.15, 16, 32, Math.PI);
const handle = new THREE.Mesh(handleGeom, mugMat);
handle.position.set(0.9, 0, 0);
handle.rotation.z = -Math.PI / 2;
mugGroup.add(handle);

mugGroup.position.y = 1.1;
model_group.add(mugGroup);
}