function createShortsModel() {
// Clear existing children from model_group
while (model_group.children.length > 0) {
model_group.remove(model_group.children[0]);
}

// Initialize GLTFLoader
const loader = new THREE.GLTFLoader();

console.log("Loading shorts.glb...");

loader.load('/shorts.glb', function(gltf) {
const model = gltf.scene;

// Position and Scale for Shorts
model.position.set(0, -1, 0);
model.scale.set(100, 100, 100); // Scale might need adjustment based on model size

// Traverse to ensure the material responds to the base color logic
model.traverse((child) => {
if (child.isMesh) {
child.name = 'base';
// Cloth properties
if (child.material) {
child.material.color.setHex(getActiveColor());
child.material.roughness = 0.8;
child.material.metalness = 0.1;
child.material.side = THREE.DoubleSide;
}
}
});

model_group.add(model);
console.log("Shorts model loaded successfully.");
},
function (xhr) {
if (xhr.total > 0) {
console.log((xhr.loaded / xhr.total * 100) + '% loaded');
}
},
function(error) {
console.error('An error happened while loading the shorts:', error);

// Fallback to Box if Shorts fails
const geometry = new THREE.BoxGeometry(2, 1.5, 0.8);
const material = new THREE.MeshStandardMaterial({
color: getActiveColor(),
roughness: 0.8,
metalness: 0.1
});
const mesh = new THREE.Mesh(geometry, material);
mesh.name = 'base';
model_group.add(mesh);
});
}