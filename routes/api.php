<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketTypeController;
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

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/featured', [EventController::class, 'featured']);
Route::get('/events/{event}', [EventController::class, 'show']);

Route::get('/events/{event}/tickets', [TicketTypeController::class, 'index']);

Route::post('/cart/validate', [CartController::class, 'validate']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

    // Events (create for authenticated users)
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);

    // Events belonging to the authenticated user
    Route::get('/user/events', [EventController::class, 'mine']);

    // Ticket Types
    Route::post('/events/{event}/tickets', [TicketTypeController::class, 'store']);
    Route::put('/tickets/{ticketType}', [TicketTypeController::class, 'update']);
    Route::delete('/tickets/{ticketType}', [TicketTypeController::class, 'destroy']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Payments
    Route::post('/orders/{order}/payment-intent', [PaymentController::class, 'createIntent']);
    Route::post('/orders/{order}/confirm-payment', [PaymentController::class, 'confirmPayment']);
    Route::get('/orders/{order}/payment-status', [PaymentController::class, 'status']);

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index']);

    // Scan ticket by code (organizer only)
    Route::get('/tickets/scan/{code}', [TicketController::class, 'scan'])
        ->where('code', '[A-Za-z0-9\-]+');

    // Redeem ticket (organizer only, confirmation required)
    Route::post('/tickets/redeem/{code}', [TicketController::class, 'redeem'])
        ->where('code', '[A-Za-z0-9\-]+');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
