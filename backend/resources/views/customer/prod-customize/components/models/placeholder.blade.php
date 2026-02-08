function createPlaceholderProduct() {
// Clear existing model children
while (model_group.children.length > 0) {
model_group.remove(model_group.children[0]);
}

// Main Base
const baseGeom = new THREE.BoxGeometry(2, 0.5, 2);
const baseMat = new THREE.MeshStandardMaterial({
color: 0x1a1a1a,
roughness: 0.1,
metalness: 0.9
});
const base = new THREE.Mesh(baseGeom, baseMat);
model_group.add(base);

// Center Piece
const centerGeom = new THREE.CylinderGeometry(0.8, 1, 1.5, 32);
const centerMat = new THREE.MeshStandardMaterial({
color: getActiveColor(),
roughness: 0.2,
metalness: 0.8
});
const center = new THREE.Mesh(centerGeom, centerMat);
center.position.y = 1;
center.name = 'base';
model_group.add(center);

// Accents
const ringGeom = new THREE.TorusGeometry(1.1, 0.05, 16, 100);
const ringMat = new THREE.MeshStandardMaterial({ color: 0xffffff, emissive: 0xffffff, emissiveIntensity: 0.5 });
const ring = new THREE.Mesh(ringGeom, ringMat);
ring.rotation.x = Math.PI / 2;
ring.position.y = 0.5;
ring.name = 'accents';
model_group.add(ring);
}