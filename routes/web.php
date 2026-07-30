<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckoutDiscountController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\PublicCourseController;
use App\Http\Controllers\DiscourseSsoController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FutureModuleController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\DeploymentHealthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProgramOnboardingController;
use App\Http\Controllers\ProgramRegistrationController;
use App\Http\Controllers\ResendWebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/healthz', [DeploymentHealthController::class, 'live'])->name('deployment.live');
Route::get('/readyz', [DeploymentHealthController::class, 'ready'])->name('deployment.ready');

Route::get('/', HomeController::class)->name('home');
Route::get('/about', fn () => Inertia::render('Public/About'))->name('about');
Route::get('/contact', fn () => Inertia::render('Public/Contact'))->name('contact');
Route::get('/privacy', fn () => Inertia::render('Public/Info', ['kind' => 'privacy']))->name('privacy');
Route::get('/terms', fn () => Inertia::render('Public/Info', ['kind' => 'terms']))->name('terms');
Route::get('/cookie-policy', fn () => Inertia::render('Public/Info', ['kind' => 'cookies']))->name('cookie-policy');
Route::get('/careers', fn () => Inertia::render('Public/Info', ['kind' => 'careers']))->name('careers');
Route::get('/community', [\App\Http\Controllers\CommunityController::class, 'index'])->name('community');
Route::get('/corporate', \App\Http\Controllers\CorporateController::class)->name('corporate');
Route::get('/schools', [FutureModuleController::class, 'show'])->defaults('key', 'school_youth_program')->name('future.schools');
Route::get('/career-center', [FutureModuleController::class, 'show'])->defaults('key', 'career_center')->name('future.career-center');
Route::get('/jobs', [FutureModuleController::class, 'show'])->defaults('key', 'job_board')->name('future.jobs');
Route::get('/employer', [FutureModuleController::class, 'show'])->defaults('key', 'employer_portal')->name('future.employer');
Route::get('/alumni', [FutureModuleController::class, 'show'])->defaults('key', 'alumni_directory')->name('future.alumni');
Route::get('/certificates/verify', [CertificateController::class, 'verify'])->name('certificates.verify');
Route::get('/certificates/{serial}', [CertificateController::class, 'show'])->name('certificates.show');
Route::get('/ambassadors', [FutureModuleController::class, 'show'])->defaults('key', 'ambassador_referral_program')->name('future.ambassadors');
Route::get('/courses', [PublicCourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{trackSlug}/{productSlug}', [PublicCourseController::class, 'showProduct'])->name('courses.products.show');
Route::post('/courses/{product:slug}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('courses.reviews.store');
Route::get('/courses/{trackSlug}', [PublicCourseController::class, 'showTrack'])->name('courses.show');
Route::post('/checkout/discount/validate', [CheckoutDiscountController::class, 'validateDiscount'])
    ->middleware('throttle:10,1')
    ->name('checkout.discount.validate');
Route::get('/checkout/failed', [CheckoutController::class, 'failed'])->name('checkout.status.failed');
Route::get('/checkout/paystack/callback', [CheckoutController::class, 'paystackCallback'])->name('checkout.paystack.callback');
Route::get('/checkout/{product:slug}', [CheckoutController::class, 'details'])->name('checkout.details');
Route::post('/checkout/{product:slug}/review', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/orders/{order:uuid}/review', [CheckoutController::class, 'review'])->name('checkout.orders.review');
Route::post('/checkout/orders/{order:uuid}/pay', [CheckoutController::class, 'pay'])->name('checkout.orders.pay');
Route::get('/checkout/orders/{order:uuid}/processing', [CheckoutController::class, 'processing'])->name('checkout.orders.processing');
Route::get('/checkout/orders/{order:uuid}/success', [CheckoutController::class, 'success'])->name('checkout.orders.success');
Route::get('/checkout/orders/{order:uuid}/pending', [CheckoutController::class, 'pending'])->name('checkout.orders.pending');
Route::get('/checkout/orders/{order:uuid}/failed', [CheckoutController::class, 'failed'])->name('checkout.orders.failed');
Route::post('/webhooks/paystack', PaystackWebhookController::class)->name('webhooks.paystack');

Route::get('/discourse/sso', [DiscourseSsoController::class, 'sso'])
    ->middleware(['auth'])
    ->name('discourse.sso');

Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Resources
Route::get('/resources', [ResourceController::class, 'index'])->name('resources.index');
Route::get('/resources/{slug}', [ResourceController::class, 'show'])->name('resources.show');
Route::post('/resources/{slug}/download', [ResourceController::class, 'download'])->name('resources.download');

// Events
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
Route::post('/events/{slug}/register', [EventController::class, 'register'])->name('events.register');

// Annual Programs (Summer AI, etc.)
// Cart (guest session + authenticated; merged on login)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/cart/checkout', [CartController::class, 'placeOrder'])->middleware('throttle:10,1')->name('cart.checkout.store');
Route::post('/cart/{product:slug}', [CartController::class, 'add'])->middleware('throttle:30,1')->name('cart.add');
Route::delete('/cart/{product:slug}', [CartController::class, 'remove'])->name('cart.remove');

Route::redirect('/summer-ai', '/programs/summer-ai', 301);
Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{program:slug}', [ProgramController::class, 'show'])->name('programs.show');
Route::get('/programs/{program:slug}/editions/{editionSlug}', [ProgramController::class, 'showEdition'])->name('programs.editions.show');
Route::post('/programs/{program:slug}/register', [ProgramRegistrationController::class, 'store'])
    ->middleware('throttle:12,1')
    ->name('programs.registrations.store');
Route::get('/program-registrations/{registration:uuid}', [ProgramRegistrationController::class, 'status'])->name('programs.registrations.status');
Route::get('/program-registrations/{registration:uuid}/verify-email', [ProgramRegistrationController::class, 'verifyPage'])->name('programs.registrations.verify.page');
Route::get('/program-registrations/{registration:uuid}/verify/{token}', [ProgramRegistrationController::class, 'verifyByToken'])
    ->middleware('throttle:12,1')
    ->name('programs.registrations.verify');
Route::post('/program-registrations/{registration:uuid}/verify-otp', [ProgramRegistrationController::class, 'verifyByOtp'])
    ->middleware('throttle:8,1')
    ->name('programs.registrations.verify.otp');
Route::post('/program-registrations/{registration:uuid}/resend', [ProgramRegistrationController::class, 'resend'])
    ->middleware('throttle:4,1')
    ->name('programs.registrations.resend');
Route::post('/program-registrations/{registration:uuid}/pay', [ProgramRegistrationController::class, 'pay'])
    ->middleware('throttle:10,1')
    ->name('programs.registrations.pay');
Route::get('/program-onboarding/{token}', [ProgramOnboardingController::class, 'show'])->name('programs.onboarding.show');
Route::post('/program-onboarding/{token}', [ProgramOnboardingController::class, 'store'])
    ->middleware('throttle:12,1')
    ->name('programs.onboarding.store');
Route::post('/webhooks/resend', ResendWebhookController::class)->name('webhooks.resend');

// Lead Capture Forms (throttled to curb spam / enumeration / unbounded row + email creation)
Route::middleware('throttle:8,1')->group(function () {
    Route::post('/leads/newsletter', [LeadController::class, 'storeNewsletter'])->name('leads.newsletter');
    Route::post('/leads/contact', [LeadController::class, 'storeContact'])->name('leads.contact');
    Route::post('/leads/corporate', [LeadController::class, 'storeCorporate'])->name('leads.corporate');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product:slug}', [WishlistController::class, 'toggle'])
        ->middleware('throttle:30,1')
        ->name('wishlist.toggle');
});

require __DIR__.'/auth.php';
