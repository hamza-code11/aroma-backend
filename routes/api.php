<?php

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Mail\PasswordResetOtpMail;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ContactCmsController;
use App\Http\Controllers\AboutCmsController;
use App\Http\Controllers\ShopCmsController;
use App\Http\Controllers\BannerCmsController;
use App\Http\Controllers\HeroCmsController;
use App\Http\Controllers\TaxIdController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FedExController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminDashboardController;




// Route::get('/create-symplink', function () {
//     symlink(storage_path('app/public'), public_path('storage'));
//     return 'Storage linked successfully';
// });

// Route::get('/test-api', function () {
//     return response()->json([
//         'status' => true,
//         'message' => 'API is working '
//     ]);
// });




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::post('/verify-password', [AuthController::class, 'verifyPassword']);
    Route::put('/change-password', [AuthController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/auth/google/onetap', [AuthController::class, 'handleGoogleOneTap']);



// Public
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/testimonials', [TestimonialController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store']);
Route::get('/gallery', [GalleryController::class, 'index']);
Route::post('/newsletter', [NewsletterController::class, 'store']);
Route::get('/contact-cms', [ContactCmsController::class, 'show']);
Route::get('/about-cms', [AboutCmsController::class, 'show']);
Route::get('/shop-cms', [ShopCmsController::class, 'show']);
Route::get('/banner-cms', [BannerCmsController::class, 'show']);
Route::get('/hero-cms', [HeroCmsController::class, 'show']);


    
Route::middleware(['auth:sanctum'])->group(function () {
    // ==========================================
    // CHECKOUT ROUTES (Yahan add karein)
    // ==========================================
    Route::post('/checkout/address', [CheckoutController::class, 'saveAddress']);
    Route::post('/checkout/shipping', [CheckoutController::class, 'saveShipping']);
    Route::post('/checkout/payment', [PaymentController::class, 'processPayment']);
    Route::post('/fedex/rates', [FedExController::class, 'getRates']);
    Route::get('/orders/{id}', [CheckoutController::class, 'getOrder']);

    Route::get('/user/orders', [CheckoutController::class, 'userOrders']);
    Route::get('/user/orders/{id}', [CheckoutController::class, 'userOrderShow']);

    Route::post('/fedex/track', [FedExController::class, 'trackShipment']);
});






// auth required 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/testimonials', [TestimonialController::class,'store']);

    Route::post('/tax-id', [TaxIdController::class, 'store']);
    Route::get('/tax-id', [TaxIdController::class, 'myTaxId']);
});



Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

Route::get('/dashboard-stats', [AdminDashboardController::class, 'getStats']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus']);


    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);


    Route::get('/testimonials', [TestimonialController::class,'adminIndex']);
    Route::patch('/testimonials/{testimonial}/verification', [TestimonialController::class,'toggleVerification']);

    Route::get('/contacts', [ContactController::class, 'index']);
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy']);


    Route::post('/gallery', [GalleryController::class, 'store']);
    Route::post('/gallery/{gallery}', [GalleryController::class, 'update']);
    Route::delete('/gallery/{gallery}', [GalleryController::class, 'destroy']);


    Route::get('/newsletters', [NewsletterController::class, 'index']);
    Route::delete('/newsletters/{newsletter}', [NewsletterController::class,'destroy']);


    Route::get('/customers', [AuthController::class, 'getCustomers']);
    Route::delete('/customers/{user}', [AuthController::class, 'deleteCustomer']);

    Route::post('/contact-cms', [ContactCmsController::class, 'update']);
    Route::post('/about-cms', [AboutCmsController::class, 'update']);
    Route::post('/shop-cms', [ShopCmsController::class, 'update']);
    Route::post('/banner-cms', [BannerCmsController::class, 'update']);
    Route::post('/hero-cms', [HeroCmsController::class, 'update']);


    Route::get('/tax-ids', [TaxIdController::class, 'index']);
    Route::patch('/tax-ids/{taxId}/status', [TaxIdController::class, 'updateStatus']);


    Route::get('/orders', [CheckoutController::class, 'adminIndex']);
    Route::get('/orders/{id}', [CheckoutController::class, 'adminShow']);
    Route::post('/orders/{id}/ship', [FedExController::class, 'createShipment']);
    
});




// Route::get('/test-email', function () {
//     Mail::to('ahmedmalik30600@gmail.com')->send(
//         new PasswordResetOtpMail('583214')
//     );

//     return response()->json([
//         'message' => 'Test email sent successfully.',
//     ]);
// });

