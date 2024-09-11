<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\controllers\UserController;
use App\Http\controllers\MedicinController;
use App\Http\controllers\AuthController;
use App\Http\controllers\OrderController;
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

// مستودع 0
// صيدلي 1
//**********
// 0 غير مدفوع
// 1 مدفوع
//************
// 0 تم الاستلام
// 1 قيد التحضير
// 2 تم الإرسال
//***************
/* Authintication Section */
Route::get('/login',function (){
    return response()->json(['error'=>true, 'message'=> 'please login first!']);
})->name('login');
Route::post('/login',[AuthController::class,'login']);
Route::post('/store/register',[AuthController::class,'registerStore']);
Route::post('/pharmacy/register',[AuthController::class,'registerPharmacy']);
/* End Authintication Section */


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',[AuthController::class,'logout']);
    
    Route::group(['middleware' => 'checkStorehouse'], function () {
       
        Route::get('/orders',[OrderController::class,'storeGetOrders']);
        Route::get('/medicins',[MedicinController::class,'index']);
        Route::post('/medicin/add',[MedicinController::class,'store']);
        
        Route::post('/updateOrderStatus/{order_id}',[OrderController::class,'updateOrderStatus']);
        Route::post('/updatePaymentStatus/{order_id}',[OrderController::class,'updatePaymentStatus']);
    
        Route::post('/searchMedNameStore',[MedicinController::class,'searchMedNameStore']);

        Route::get('/notification',[OrderController::class,'getNotification']);

    
    });

    Route::group(['middleware' => 'checkPhar'], function () {
        Route::get('/orders/{id}',[OrderController::class,'pharGetOrders']);//عرض الطلبات عندالصيدلي
   
        Route::post('/medicins',[MedicinController::class,'show']);//عرض الادوية عند الصيدلي
    
        Route::post('/order/add',[OrderController::class,'store']);// اضافة طلب للصيدلي 
   
   
        Route::get('/storeNames',[UserController::class,'index']);//عرض اسماء المستودعات عند الصيدلي

        Route::post('/searchMedNamePhar',[MedicinController::class,'searchMedNamePhar']);
    });
});

