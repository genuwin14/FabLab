<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;

Route::get('/', [LandingController::class, 'index'])->name('landing');

// Login Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Register Routes
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Logout Routes
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Socialite Routes
Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/login/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Forgot Password Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'checkUserForReset'])->name('password.email.check');
Route::post('/forgot-password/send', [AuthController::class, 'sendResetCode'])->name('password.code.send');
Route::get('/forgot-password/verify', [AuthController::class, 'showResetVerificationForm'])->name('password.verify.show');
Route::post('/forgot-password/verify', [AuthController::class, 'verifyResetCode'])->name('password.verify.submit');

// Reset Password Routes
Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.form');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Protected Routes (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {

    // Phone Verification
    Route::get('/verify-code', [\App\Http\Controllers\Auth\OtpController::class, 'show'])->name('verify.code');
    Route::post('/verify-code', [\App\Http\Controllers\Auth\OtpController::class, 'verify'])->name('verify.code.submit');
    Route::post('/verify-code/resend', [\App\Http\Controllers\Auth\OtpController::class, 'resend'])->name('verify.code.resend');

    // Contact number prompt for Google sign-ups (skippable)
    Route::get('/complete-profile', [AuthController::class, 'showCompleteProfile'])->name('profile.complete');
    Route::post('/complete-profile', [AuthController::class, 'storeCompleteProfile'])->name('profile.complete.store');

    // Notifications (shared across roles)
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/poll', [\App\Http\Controllers\NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');

    /*
    |--------------------------------------------------------------------------
    | Admin only
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {

    // Admin Routes/Dashboard
    Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    // Admin Routes/Products
    Route::get('/admin/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products.index');
    Route::post('/admin/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('admin.products.store');
    Route::put('/admin/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/admin/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('admin.products.destroy');
    // Admin Routes/Product Suppliers (Phase 2)
    Route::get('/admin/products/{id}/suppliers', [\App\Http\Controllers\Admin\ProductController::class, 'assignSuppliers'])->name('admin.products.suppliers.assign');
    Route::post('/admin/products/{id}/suppliers', [\App\Http\Controllers\Admin\ProductController::class, 'storeSuppliers'])->name('admin.products.suppliers.store');
    Route::get('/admin/products/{id}/textures', [\App\Http\Controllers\Admin\ProductController::class, 'assignTextures'])->name('admin.products.textures.assign');
    Route::post('/admin/products/{id}/textures', [\App\Http\Controllers\Admin\ProductController::class, 'storeTextures'])->name('admin.products.textures.store');
    Route::get('/admin/products/{id}/colors', [\App\Http\Controllers\Admin\ProductController::class, 'assignColors'])->name('admin.products.colors.assign');
    Route::post('/admin/products/{id}/colors', [\App\Http\Controllers\Admin\ProductController::class, 'storeColors'])->name('admin.products.colors.store');
    // Admin Routes/Categories
    Route::get('/admin/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/admin/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/admin/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/admin/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    // Admin Routes/Suppliers
    Route::get('/admin/suppliers', [\App\Http\Controllers\Admin\SupplierController::class, 'index'])->name('admin.suppliers.index');
    Route::post('/admin/suppliers', [\App\Http\Controllers\Admin\SupplierController::class, 'store'])->name('admin.suppliers.store');
    Route::put('/admin/suppliers/{id}', [\App\Http\Controllers\Admin\SupplierController::class, 'update'])->name('admin.suppliers.update');
    Route::delete('/admin/suppliers/{id}', [\App\Http\Controllers\Admin\SupplierController::class, 'destroy'])->name('admin.suppliers.destroy');
    // Admin Routes/Raw Materials
    Route::get('/admin/raw-materials', [\App\Http\Controllers\Admin\RawMaterialController::class, 'index'])->name('admin.raw-materials.index');
    Route::post('/admin/raw-materials', [\App\Http\Controllers\Admin\RawMaterialController::class, 'store'])->name('admin.raw-materials.store');
    Route::put('/admin/raw-materials/{id}', [\App\Http\Controllers\Admin\RawMaterialController::class, 'update'])->name('admin.raw-materials.update');
    Route::delete('/admin/raw-materials/{id}', [\App\Http\Controllers\Admin\RawMaterialController::class, 'destroy'])->name('admin.raw-materials.destroy');
    // Stock ledger: the only path that moves a material's stock by hand.
    Route::post('/admin/raw-materials/{id}/usage', [\App\Http\Controllers\Admin\RawMaterialController::class, 'storeUsage'])->name('admin.raw-materials.usage.store');
    Route::post('/admin/raw-material-movements/{movement}/reverse', [\App\Http\Controllers\Admin\RawMaterialController::class, 'reverseUsage'])->name('admin.raw-materials.usage.reverse');
    // Admin Routes/Colors (plain finishes — the alternative to a texture)
    Route::get('/admin/colors', [\App\Http\Controllers\Admin\ColorController::class, 'index'])->name('admin.colors.index');
    Route::post('/admin/colors', [\App\Http\Controllers\Admin\ColorController::class, 'store'])->name('admin.colors.store');
    Route::put('/admin/colors/{id}', [\App\Http\Controllers\Admin\ColorController::class, 'update'])->name('admin.colors.update');
    Route::delete('/admin/colors/{id}', [\App\Http\Controllers\Admin\ColorController::class, 'destroy'])->name('admin.colors.destroy');
    // Admin Routes/Textures
    Route::get('/admin/textures', [\App\Http\Controllers\Admin\TextureController::class, 'index'])->name('admin.textures.index');
    Route::post('/admin/textures', [\App\Http\Controllers\Admin\TextureController::class, 'store'])->name('admin.textures.store');
    Route::put('/admin/textures/{id}', [\App\Http\Controllers\Admin\TextureController::class, 'update'])->name('admin.textures.update');
    Route::delete('/admin/textures/{id}', [\App\Http\Controllers\Admin\TextureController::class, 'destroy'])->name('admin.textures.destroy');

    // Admin Routes/Inventory
    Route::get('/admin/inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('admin.inventory.index');
    Route::post('/admin/inventory/assign-supplier', [\App\Http\Controllers\Admin\InventoryController::class, 'assignSupplier'])->name('admin.inventory.assignSupplier');

    // Admin Routes/Equipment
    Route::get('/admin/equipment', [\App\Http\Controllers\Admin\EquipmentController::class, 'index'])->name('admin.equipment.index');
    Route::post('/admin/equipment', [\App\Http\Controllers\Admin\EquipmentController::class, 'store'])->name('admin.equipment.store');
    Route::put('/admin/equipment/{id}', [\App\Http\Controllers\Admin\EquipmentController::class, 'update'])->name('admin.equipment.update');
    Route::delete('/admin/equipment/{id}', [\App\Http\Controllers\Admin\EquipmentController::class, 'destroy'])->name('admin.equipment.destroy');

    // Admin Routes/Reports
    Route::get('/admin/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/reports/materials', [\App\Http\Controllers\Admin\ReportController::class, 'materials'])->name('admin.reports.materials');
    Route::get('/admin/reports/materials/preview', [\App\Http\Controllers\Admin\ReportController::class, 'previewMaterials'])->name('admin.reports.materials.preview');
    Route::get('/admin/reports/materials/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportMaterialsPdf'])->name('admin.reports.materials.pdf');
    Route::get('/admin/reports/materials/docx', [\App\Http\Controllers\Admin\ReportController::class, 'exportMaterialsDocx'])->name('admin.reports.materials.docx');
    Route::get('/admin/reports/equipment', [\App\Http\Controllers\Admin\ReportController::class, 'equipment'])->name('admin.reports.equipment');
    Route::get('/admin/reports/equipment/preview', [\App\Http\Controllers\Admin\ReportController::class, 'previewEquipment'])->name('admin.reports.equipment.preview');
    Route::get('/admin/reports/equipment/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportEquipmentPdf'])->name('admin.reports.equipment.pdf');
    Route::get('/admin/reports/equipment/docx', [\App\Http\Controllers\Admin\ReportController::class, 'exportEquipmentDocx'])->name('admin.reports.equipment.docx');

    // Admin Routes/Purchase Orders
    Route::get('/admin/purchase', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'index'])->name('admin.purchase.index');
    Route::get('/admin/purchase/create', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'create'])->name('admin.purchase.create');
    Route::post('/admin/purchase', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'store'])->name('admin.purchase.store');
    Route::get('/admin/purchase/{id}', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'show'])->name('admin.purchase.show');
    Route::put('/admin/purchase/{id}/status', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'updateStatus'])->name('admin.purchase.updateStatus');

    // Admin Routes/Orders
    Route::get('/admin/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('admin.orders.index');
    Route::post('/admin/orders/{id}/review', [\App\Http\Controllers\Admin\OrderController::class, 'review'])->name('admin.orders.review');
    Route::post('/admin/orders/{id}/cancel', [\App\Http\Controllers\Admin\OrderController::class, 'cancel'])->name('admin.orders.cancel');
    // Purchase Request paperwork: the NOA releases production, the PO delivery.
    Route::post('/admin/orders/{id}/documents/{type}', [\App\Http\Controllers\Admin\OrderController::class, 'uploadDocument'])->name('admin.orders.documents.upload');
    Route::get('/admin/orders/{id}/documents/{type}', [\App\Http\Controllers\Admin\OrderController::class, 'document'])->name('admin.orders.documents.show');
    Route::post('/admin/orders/{id}/close-pr', [\App\Http\Controllers\Admin\OrderController::class, 'closePurchaseRequest'])->name('admin.orders.closePr');
    // Admin Routes/Sales
    Route::get('/admin/sales', [\App\Http\Controllers\Admin\SalesController::class, 'index'])->name('admin.sales.index');
    Route::get('/admin/sales/preview', [\App\Http\Controllers\Admin\SalesController::class, 'preview'])->name('admin.sales.preview');
    Route::get('/admin/sales/pdf', [\App\Http\Controllers\Admin\SalesController::class, 'exportPdf'])->name('admin.sales.pdf');
    Route::get('/admin/sales/docx', [\App\Http\Controllers\Admin\SalesController::class, 'exportDocx'])->name('admin.sales.docx');
    // Admin Routes/Profile
    Route::put('/admin/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('admin.profile.update');
    // Admin Routes/Users
    Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users/{id}/status', [\App\Http\Controllers\Admin\UserController::class, 'updateStatus'])->name('admin.users.updateStatus');
    // Admin Routes/Customization Pricing (what the 3D customizer charges per element)
    Route::get('/admin/customization-pricing', [\App\Http\Controllers\Admin\CustomizationPricingController::class, 'index'])->name('admin.customization-pricing.index');
    Route::put('/admin/customization-pricing', [\App\Http\Controllers\Admin\CustomizationPricingController::class, 'update'])->name('admin.customization-pricing.update');
    // Admin Routes/Settings
    Route::get('/admin/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('/admin/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('admin.settings.update');

    });

    /*
    |--------------------------------------------------------------------------
    | Staff area — admins allowed too
    |
    | Order fulfilment (processing → ready → completed) exists only under
    | /staff, so locking admins out would leave a shop with no staff account
    | unable to finish an order.
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:staff,admin')->group(function () {

    // Staff Routes/Dashboard
    Route::get('/staff/dashboard', [\App\Http\Controllers\Staff\DashboardController::class, 'index'])->name('staff.dashboard');
    // Staff Routes/Orders
    Route::get('/staff/orders', [\App\Http\Controllers\Staff\OrderController::class, 'index'])->name('staff.orders.index');
    Route::post('/staff/orders/{id}/update-status', [\App\Http\Controllers\Staff\OrderController::class, 'updateStatus'])->name('staff.orders.updateStatus');
    Route::get('/staff/orders/{id}/receipt', [\App\Http\Controllers\Staff\OrderController::class, 'receipt'])->name('staff.orders.receipt');
    // Staff Routes/Sales
    Route::get('/staff/sales', [\App\Http\Controllers\Staff\SalesController::class, 'index'])->name('staff.sales.index');
    // Staff Routes/Products (read + edit only)
    Route::get('/staff/products', [\App\Http\Controllers\Staff\ProductController::class, 'index'])->name('staff.products.index');
    Route::put('/staff/products/{id}', [\App\Http\Controllers\Staff\ProductController::class, 'update'])->name('staff.products.update');
    // Staff Routes/Inventory (read only)
    Route::get('/staff/inventory', [\App\Http\Controllers\Staff\InventoryController::class, 'index'])->name('staff.inventory.index');
    // Staff Routes/Raw Materials (read + edit only)
    Route::get('/staff/raw-materials', [\App\Http\Controllers\Staff\RawMaterialController::class, 'index'])->name('staff.raw-materials.index');
    Route::put('/staff/raw-materials/{id}', [\App\Http\Controllers\Staff\RawMaterialController::class, 'update'])->name('staff.raw-materials.update');
    // Stock ledger: staff record usage but cannot correct a count.
    Route::post('/staff/raw-materials/{id}/usage', [\App\Http\Controllers\Staff\RawMaterialController::class, 'storeUsage'])->name('staff.raw-materials.usage.store');
    Route::post('/staff/raw-material-movements/{movement}/reverse', [\App\Http\Controllers\Staff\RawMaterialController::class, 'reverseUsage'])->name('staff.raw-materials.usage.reverse');
    // Staff Routes/Textures (read + edit only)
    Route::get('/staff/textures', [\App\Http\Controllers\Staff\TextureController::class, 'index'])->name('staff.textures.index');
    Route::put('/staff/textures/{id}', [\App\Http\Controllers\Staff\TextureController::class, 'update'])->name('staff.textures.update');
    // Staff Routes/Colors (read + edit only). Adding or retiring a finish
    // changes what every product can offer, so there is no staff route for it.
    Route::get('/staff/colors', [\App\Http\Controllers\Staff\ColorController::class, 'index'])->name('staff.colors.index');
    Route::put('/staff/colors/{id}', [\App\Http\Controllers\Staff\ColorController::class, 'update'])->name('staff.colors.update');
    // Staff Routes/Customization Pricing (read only — staff look a rate up for
    // a customer, but repricing hits every live quote, so that stays with admins)
    Route::get('/staff/customization-pricing', [\App\Http\Controllers\Staff\CustomizationPricingController::class, 'index'])->name('staff.customization-pricing.index');
    // Staff Routes/Purchase Orders (full procurement workflow)
    Route::get('/staff/purchase', [\App\Http\Controllers\Staff\PurchaseOrderController::class, 'index'])->name('staff.purchase.index');
    Route::get('/staff/purchase/create', [\App\Http\Controllers\Staff\PurchaseOrderController::class, 'create'])->name('staff.purchase.create');
    Route::post('/staff/purchase', [\App\Http\Controllers\Staff\PurchaseOrderController::class, 'store'])->name('staff.purchase.store');
    Route::get('/staff/purchase/{id}', [\App\Http\Controllers\Staff\PurchaseOrderController::class, 'show'])->name('staff.purchase.show');
    Route::put('/staff/purchase/{id}/status', [\App\Http\Controllers\Staff\PurchaseOrderController::class, 'updateStatus'])->name('staff.purchase.updateStatus');
    // Staff Routes/Profile
    Route::put('/staff/profile', [\App\Http\Controllers\Staff\ProfileController::class, 'update'])->name('staff.profile.update');
    // Staff Routes/Settings
    Route::get('/staff/settings', [\App\Http\Controllers\Staff\SettingsController::class, 'index'])->name('staff.settings.index');
    Route::put('/staff/settings', [\App\Http\Controllers\Staff\SettingsController::class, 'update'])->name('staff.settings.update');

    });

    /*
    |--------------------------------------------------------------------------
    | Customer only
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:customer')->group(function () {

    // Customer Routes/Shop
    Route::get('/customer/shop', [\App\Http\Controllers\Customer\ShopController::class, 'index'])->name('customer.shop');

    // Customer Routes/Cart
    Route::get('/customer/cart', [\App\Http\Controllers\Customer\CartController::class, 'index'])->name('customer.cart.index');
    Route::post('/customer/cart/add', [\App\Http\Controllers\Customer\CartController::class, 'add'])->name('customer.cart.add');
    Route::post('/customer/cart/update', [\App\Http\Controllers\Customer\CartController::class, 'update'])->name('customer.cart.update');
    Route::post('/customer/cart/remove', [\App\Http\Controllers\Customer\CartController::class, 'remove'])->name('customer.cart.remove');
    Route::post('/customer/cart/checkout', [\App\Http\Controllers\Customer\CartController::class, 'checkout'])->name('customer.cart.checkout');
    Route::get('/customer/cart/count', [\App\Http\Controllers\Customer\CartController::class, 'count'])->name('customer.cart.count');

    // Customer Routes/Orders
    Route::get('/customer/orders', [\App\Http\Controllers\Customer\OrderController::class, 'index'])->name('customer.orders.index');
    Route::post('/customer/orders/{id}/cancel', [\App\Http\Controllers\Customer\OrderController::class, 'cancel'])->name('customer.orders.cancel');
    // Procurement's PR number is what releases a held order for review.
    Route::post('/customer/orders/{id}/pr-number', [\App\Http\Controllers\Customer\OrderController::class, 'submitPrNumber'])->name('customer.orders.prNumber');
    Route::get('/customer/orders/{id}/receipt', [\App\Http\Controllers\Customer\OrderController::class, 'receipt'])->name('customer.orders.receipt');
    // Customer Routes/Customize
    Route::get('/customer/customize', [\App\Http\Controllers\Customer\CustomizeController::class, 'index'])->name('customer.customize.index');
    Route::post('/customer/customize/save', [\App\Http\Controllers\Customer\CustomizeController::class, 'save'])->name('customer.customize.save');
    Route::delete('/customer/customize/{id}', [\App\Http\Controllers\Customer\CustomizeController::class, 'destroy'])->name('customer.customize.destroy');
    Route::get('/customer/my-designs', [\App\Http\Controllers\Customer\CustomizeController::class, 'myDesigns'])->name('customer.customize.my-designs');

    // Customer Routes/Profile
    Route::put('/customer/profile', [\App\Http\Controllers\Customer\ProfileController::class, 'update'])->name('customer.profile.update');

    // Customer Routes/Settings
    Route::get('/customer/settings', [\App\Http\Controllers\Customer\SettingsController::class, 'index'])->name('customer.settings.index');
    Route::put('/customer/settings', [\App\Http\Controllers\Customer\SettingsController::class, 'update'])->name('customer.settings.update');

    });

});
