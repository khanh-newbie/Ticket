<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', [App\Http\Controllers\Auth\AuthController::class, 'test']);
Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register']);
Route::post('/forgot-password', [App\Http\Controllers\Auth\AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [App\Http\Controllers\Auth\AuthController::class, 'resetPassword']);
Route::post('/loginwithgoogle', [App\Http\Controllers\Auth\AuthController::class, 'loginWithGoogle']);
Route::post('/verifyAccount', [App\Http\Controllers\Auth\AuthController::class, 'verifyAccount']);
Route::get('/music-events-index', [App\Http\Controllers\Customers\IndexController::class, 'musicEventsIndex']);
Route::get('/art-events-index', [App\Http\Controllers\Customers\IndexController::class, 'artEventsIndex']);
// Route::get('sport-events-index', [App\Http\Controllers\Customers\IndexController::class, 'sportEventsIndex']);
Route::get('/other-events-index', [App\Http\Controllers\Customers\IndexController::class, 'otherEventsIndex']);
Route::get('/end-of-month-events-index', [App\Http\Controllers\Customers\IndexController::class, 'endOfMonthEventsIndex']);
Route::get('/weekend-events-index', [App\Http\Controllers\Customers\IndexController::class, 'weekendEventsIndex']);
Route::get('/trending-events-index', [App\Http\Controllers\Customers\IndexController::class, 'trendingEventsIndex']);
//Route::get('/search', [App\Http\Controllers\Customers\IndexController::class, 'search']);
 Route::get('/search', [App\Http\Controllers\Customers\SearchController::class, 'searchEvents']);
Route::get('/categories', [App\Http\Controllers\Customers\IndexController::class, 'getCategories']);
Route::get('/event/{id}', [App\Http\Controllers\Customers\EventDetailController::class, 'index']);
Route::post('create-event', [App\Http\Controllers\Organizers\CreateEventController::class, 'createEvent']);
Route::get('organizer/getInfo/{id}', [App\Http\Controllers\Organizers\CreateEventController::class, 'getInfo']);
Route::get('/getinfoorganizer/{id}', [App\Http\Controllers\Customers\EventDetailController::class, 'getInfoOrganizer']);
Route::get('/event-review', [App\Http\Controllers\Customers\EventDetailController::class, 'getEventReviews']);
// Nhung API nen bao mat thi gop vao 1 group va dung middleware
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('add-to-cart', [App\Http\Controllers\Customers\EventDetailController::class, 'addToCart']);
    Route::get('get-cart', [App\Http\Controllers\Customers\EventDetailController::class, 'getCart']);
    Route::post('remove-from-cart', [App\Http\Controllers\Customers\EventDetailController::class, 'removeFromCart']);
    //Route::post('checkout', [App\Http\Controllers\Customers\EventDetailController::class, 'checkout']);

    Route::post('create-order', [App\Http\Controllers\Customers\PaymentController::class, 'createOrder']);

    Route::get('/my-orders', [App\Http\Controllers\Customers\OrderController::class, 'index']);
    Route::post('reviews', [App\Http\Controllers\Customers\ReviewController::class, 'submitReview']);

    Route::get('orders/{orderId}', [App\Http\Controllers\Customers\OrderController::class, 'getOrderDetails']);
    Route::post('update-profile', [App\Http\Controllers\Customers\ProfileController::class, 'updateProfile']);
    Route::post('change-password', [App\Http\Controllers\Customers\ProfileController::class, 'changePassword']);

    Route::get('organizer/my-events', [App\Http\Controllers\Organizers\MyEventsController::class, 'getMyEvents']);
    Route::get('admin/reviews', [App\Http\Controllers\Admin\ReviewController::class, 'getAllReviews']);
    Route::post('admin/reviews/{id}/status', [App\Http\Controllers\Admin\ReviewController::class, 'updateReviewStatus']);
    Route::post('/reports', [App\Http\Controllers\Customers\ReportController::class, 'submitReport']);
    Route::get('admin/reports', [App\Http\Controllers\Admin\ReportController::class, 'index']);
    Route::post('admin/reports/{id}/status', [App\Http\Controllers\Admin\ReportController::class, 'updateReportStatus']);
});
Route::get('/payment/payos/return', [App\Http\Controllers\Customers\PaymentController::class, 'payOSReturn']);
Route::get('/payment/payos/cancel', [App\Http\Controllers\Customers\PaymentController::class, 'payOSCancel']);

Route::get('/admin/dashboard-stats', [App\Http\Controllers\Admin\DashboardController::class, 'stats']);
Route::get('admin/dashboard-revenue', [App\Http\Controllers\Admin\DashboardController::class, 'revenue']);
Route::get('/admin/organizers', [App\Http\Controllers\Admin\OrganizerController::class, 'index']);

Route::get('/admin/approve-events', function () {
    return response()->json([]);
});


Route::get('/admin/events/pending', [App\Http\Controllers\Admin\EventReviewController::class, 'pending']);
Route::post('/admin/events/{id}/approve', [App\Http\Controllers\Admin\EventReviewController::class, 'approve']);
Route::post('/admin/events/{id}/reject', [App\Http\Controllers\Admin\EventReviewController::class, 'reject']);

Route::get('/admin/events/{id}', [App\Http\Controllers\Admin\EventReviewController::class, 'show']);
Route::get('/organizer/event-categories',[App\Http\Controllers\Organizers\CreateEventController::class, 'getEventCategories']);
Route::get('/admin/orders/paid', [App\Http\Controllers\Admin\OrderController::class, 'paidOrders']);