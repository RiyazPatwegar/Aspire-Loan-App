<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$version = 'v1';


Route::group(
        [
    'prefix' => $version
        ], function () use ($version) {

    // Customer APIs
    /* Step 1 : Customer create a loan */
    Route::post('/customer-loan/apply', 'CustomerLoan@apply');

    /* Step 2 : Get Customer Loan Details */
    Route::get('/customer-loan/getLoanStatus', 'CustomerLoan@getLoanStatus');
    

    // Admin APIs
    /* Step 3 : Get Loan Applications */
    Route::get('/admin/getLoanApplications', 'Admin@getLoanApplications');
    
    /* Step 4 : Approve Loan Application*/
    Route::post('/admin/approveLoan', 'Admin@approveLoan');

    /* Step 5 : Pay Scheduled Payment*/
    Route::post('/payment/payNow', 'Payment@payNow');
});

// Fallback Route
Route::fallback(function () {
    $response = [
        'code' => 400,
        'status' => 'failed',
        'message' => 'Bad request',
    ];
    return Response::json($response);
});