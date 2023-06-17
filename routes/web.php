<?php

use Illuminate\Support\Facades\Route;

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
})->name('home');

Route::get('/unauthorized', function(){
    return view ('unauthorized');
})->name('unauthorized');

Route::get('/{anyPath}', function (){
    return view('welcome');
})->where('anyPath', ".*");