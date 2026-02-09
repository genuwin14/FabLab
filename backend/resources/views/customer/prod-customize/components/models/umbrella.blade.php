function createUmbrellaModel() {
// Clear existing children from model_group
while (model_group.children.length > 0) {
model_group.remove(model_group.children[0]);
}

// Initialize GLTFLoader
const loader = new THREE.GLTFLoader();

console.log("Loading umbrella.glb...");

loader.load('/gbl/umbrella.glb', function(gltf) {
const model = gltf.scene;

// Position and Scale for Umbrella
model.position.set(0, -1, 0);
model.scale.set(0.01, 0.01, 0.01); // Many models need small scale if they are large

// Traverse to ensure the material responds to the base color logic
model.traverse((child) => {
if (child.isMesh) {
child.name = 'base';
// Standard look
if (child.material) {
child.material.color.setHex(getActiveColor());
child.material.roughness = 0.4;
child.material.metalness = 0.3;
child.material.side = THREE.DoubleSide;
}
}
});

model_group.add(model);
console.log("Umbrella model loaded successfully.");
},
function (xhr) {
if (xhr.total > 0) {
console.log((xhr.loaded / xhr.total * 100) + '% loaded');
}
},
function(error) {
console.error('An error happened while loading the umbrella:', error);

// Fallback if load fails
const geometry = new THREE.ConeGeometry(2, 2, 32);
const material = new THREE.MeshStandardMaterial({
color: getActiveColor(),
roughness: 0.5,
metalness: 0.5
});
const mesh = new THREE.Mesh(geometry, material);
mesh.name = 'base';
mesh.position.y = 1;
model_group.add(mesh);
});
}