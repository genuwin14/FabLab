function createTshirtModel() {
// Clear existing children from model_group
while (model_group.children.length > 0) {
model_group.remove(model_group.children[0]);
}

// Initialize GLTFLoader
const loader = new THREE.GLTFLoader();

console.log("Loading t-shirt.glb...");

loader.load('/gbl/t-shirt.glb', function(gltf) {
const model = gltf.scene;

// Position and Scale for T-Shirt
model.position.set(0, -1, 0); // Adjust position as needed
model.scale.set(3.5, 3.5, 3.5); // Adjust scale as needed

// Traverse to ensure the material responds to the base color logic
model.traverse((child) => {
if (child.isMesh) {
child.name = 'base';
// If it's a T-shirt, we probably want it to look like fabric
if (child.material) {
child.material.color.setHex(getActiveColor());
// Fabric should be less metallic and more rough
child.material.roughness = 0.8;
child.material.metalness = 0.1;
child.material.side = THREE.DoubleSide;
}
}
});

model_group.add(model);
console.log("T-Shirt model loaded successfully.");
},
function (xhr) {
if (xhr.total > 0) {
console.log((xhr.loaded / xhr.total * 100) + '% loaded');
}
},
function(error) {
console.error('An error happened while loading the T-shirt:', error);

// Fallback to Box if T-shirt fails
const geometry = new THREE.BoxGeometry(2, 2.5, 0.5);
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