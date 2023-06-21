<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CashierAuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionProductController;
use App\Http\Controllers\UserManagementController;

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

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

Route::controller(CashierAuthController::class)->group(function (){
    Route::post('cashier/login', 'login');
    Route::post('cashier/register', 'register'); //Only Admin Can Create New Cashier
    Route::post('cashier/logout', 'logout');
    Route::post('cashier/refresh', 'refresh');
    Route::get('cashier/self', 'self');
});

Route::controller(AdminAuthController::class)->group(function (){
    Route::post('admin/login', 'login');
    Route::post('admin/register', 'register');
    Route::post('admin/logout', 'logout');
    Route::post('admin/refresh', 'refresh');
});

Route::controller(InventoryController::class)->group(function(){
    Route::post('inventory/add_inventory', 'add_inventory');
    Route::get('inventory/getAll', 'getAll');
    Route::post('inventory/deduct_stock/{product_id}', 'deduct_stock');
    Route::post('inventory/update/{product_id}', 'update');
    Route::post('inventory/delete/{product_id}', 'delete');
    Route::get('inventory/get/{product_id}', 'get');
});

Route::controller(UserManagementController::class)->group(function (){
    Route::get('user_management/get_as_admin', 'get_as_admin');
    Route::get('user_management/get_as_cashier', 'get_as_cashier');
});

Route::controller(TransactionController::class)->group(function (){
    Route::post('transaction/add_transaction', 'add_transaction');
});

Route::controller(TransactionProductController::class)->group(function (){
    Route::post('transaction/add_transaction_product', 'add_transaction_product');
});
