<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SubscriptionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('register',[UserController::class, 'register']);
Route::post('login',[UserController::class, 'login']);
Route::post('logout',[UserController::class, 'logout']);


Route::group(['prefix' => 'Admin'], function () {
    Route::get('/Allparents',[UserController::class, 'index']);
    Route::put('/updateUser',[AdminController::class, 'updateParent']);
    Route::delete('/deleteUser',[AdminController::class, 'deleteParent']);
});


Route::group(['prefix' => 'products'], function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/store', [ProductController::class, 'store']);
    Route::get('/show', [ProductController::class, 'show']);
    Route::put('/update', [ProductController::class, 'update']);
    Route::delete('/delete', [ProductController::class, 'destroy']);

});
Route::group(['prefix' => 'events'], function () {
    Route::get('/', [EventController::class, 'index']); // Get all events
    Route::post('/store', [EventController::class, 'store']); // Store a new event
    Route::get('/show', [EventController::class, 'show']); // Show details of an event
    Route::put('/update', [EventController::class, 'update']); // Update an event
    Route::delete('/delete', [EventController::class, 'destroy']); // Delete an event

});
Route::group(['prefix' => 'vouchers'], function () {
    Route::get('/', [VoucherController::class, 'index']); // Get all vouchers
    Route::post('/store', [VoucherController::class, 'store']); // Store a new voucher
    Route::get('/show', [VoucherController::class, 'show']); // Show details of an voucher
    Route::put('/update', [VoucherController::class, 'update']); // Update an voucher
    Route::delete('/delete', [VoucherController::class, 'destroy']); // Delete an voucher
});

Route::group(['prefix' => 'feedbacks'], function () {
    Route::get('/', [FeedbackController::class, 'index']); // Get all feedbacks
    Route::post('/makeFeedback', [FeedbackController::class, 'makeFeedback']); // make a new feedback
    Route::get('/showFeedback', [FeedbackController::class, 'show']); // show a specific feedback
});
Route::group(['prefix'=>'cart'], function(){
    Route::get('/', [CartController::class, 'index']); // Get all items in cart
    Route::post('/productToCart', [CartController::class, 'productToCart']); //parent can reserve a product
    Route::put('/editCart', [CartController::class, 'editCart']); //parent can edit product quantity in cart
    Route::delete('/deleteProduct', [CartController::class, 'deleteProduct']);
    Route::post('/sessionToCart', [CartController::class, 'sessionToCart']); // parent reserve and add session to cart
    Route::delete('/deleteSession',[CartController::class, 'deleteSession']);
    Route::post('/eventToCart', [CartController::class, 'eventToCart']); // parent Reserve an event
    Route::delete('/deleteEvent',[CartController::class, 'deleteEvent']);
});

Route::group(['prefix' => 'appointments'], function () {
    Route::get('/', [AppointmentController::class, 'index']); // Get all appointments
    Route::post('/store', [AppointmentController::class, 'store']); // store a new appointments
    Route::delete('/deleteAppointment',[AppointmentController::class, 'destroy']);
});
Route::group(['prefix' => 'sessions'], function () {
    Route::get('/', [SessionController::class, 'index']); // Get all session
    Route::post('/store', [SessionController::class, 'store']); // store a new session
});
Route::post('/subscriptionplans',[SubscriptionController::class, 'subscriptionCard']);

Route::post('/subscribe',[SubscriptionController::class, 'subscribe']);

Route::get('/AllOrders',[OrderController::class, 'index']);
Route::get('/MyOrder',[OrderController::class, 'myorder']);
Route::post('/confirmOrder',[OrderController::class, 'confirmOrder']);

Route::get('/AllDoctors',[DoctorController::class,'index']);
Route::post('/reservedSessions',[DoctorController::class,'showReservedParents']);

Route::get('/success/order/payment/{order_id}/{user_id}',[StripeController::class,'successPayment'])->name('successOrderPayment');
Route::get('/cancel/order/payment/{order_id}',[StripeController::class,'cancelPayment'])->name('cancelOrderPayment');

Route::get('/checkout',[StripeController::class,'checkout']);

