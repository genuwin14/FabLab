function createMugModel() {
// Clear existing children from model_group
while (model_group.children.length > 0) {
model_group.remove(model_group.children[0]);
}

// Initialize GLTFLoader
const loader = new THREE.GLTFLoader();

console.log("Loading cup.glb...");

loader.load('/cup.glb', function(gltf) {
const model = gltf.scene;

// Position and Scale for Cup
model.position.set(0, -1, 0); // Center the cup
model.scale.set(10, 10, 10); // Adjust scale as needed

// Traverse to ensure the material responds to the base color logic
model.traverse((child) => {
if (child.isMesh) {
child.name = 'base';
// Standard Ceramic look
if (child.material) {
child.material.color.setHex(getActiveColor());
child.material.roughness = 0.1;
child.material.metalness = 0.2;
}
}
});

model_group.add(model);
console.log("Cup model loaded successfully.");
},
function (xhr) {
if (xhr.total > 0) {
console.log((xhr.loaded / xhr.total * 100) + '% loaded');
}
},
function(error) {
console.error('An error happened while loading the cup:', error);

// Fallback to Cylinder if Cup fails
const geometry = new THREE.CylinderGeometry(1, 1, 2.2, 32);
const material = new THREE.MeshStandardMaterial({
color: getActiveColor(),
roughness: 0.1,
metalness: 0.2
});
const mesh = new THREE.Mesh(geometry, material);
mesh.name = 'base';
model_group.add(mesh);
});
}