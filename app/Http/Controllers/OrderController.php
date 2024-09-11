<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Medicin;
use App\Models\Category;
use App\Models\Order_medicin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\OrdersResource;
use Carbon\Carbon;
use Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //'date' => ['required', 'date'],
            'medicins' => ['required', 'array'],
            'medicins.*.medicin_id' => ['required', 'integer'],
            'medicins.*.quantity' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $currentDate = Carbon::now();
        
        $order = Order::create([
            'order_date' =>$currentDate,
            'user_id' => Auth::user()->id,
            'store_id'=> $request['store_id'],
            'order_status' => 0,
            'payment_status' => 0,
            'seenNotifi' => 0,
       ]);

       $ret = [];
        foreach($request['medicins'] as $element){

            $medicin=Medicin::where('id',$element['medicin_id'])->where('user_id', $request['store_id'])->first();
            
            if(!$medicin){
                return response()->json([
                    'error' => true,
                    'message' => "ليس لديك صلاحية أن تأخذ الدواء من أكثر من مستودع",
                ]);
            }
            if($element['quantity']>$medicin['quantity']){
            
                array_push($ret, [
                    'error' => true,
                    'message' =>$medicin['quantity']." غير متوفرة يتوفر لدينا ". $medicin['sc_name']." الكمية المطلوبة من دواء " ,
                ]);

            }
            else{
                Order_medicin::create([
                        'order_id' => $order['id'],
                        'medicin_id' => $element['medicin_id'],
                        'quantity' => $element['quantity'],
                ]);
            }
        }

        if(count($ret) >0){
            return response()->json([
                'error' => true,
                'QuantitiesNotAvailable' =>$ret,
                'message' => "الكمية لهذه الأدوية غير متوفرة",
            ]);
        }
        else{
            return response()->json([
                'error' => false,
                'message' => "تمت الإضافة بنجاح",
            ]);
            
        }
    }

   
    /**
     * Display the specified resource.
     */
    public function show()
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(order $order)
    {
        //
    }

    public function PharGetOrders($id)
    {
        $orders = Order::where('user_id', Auth::user()->id)->where('store_id', $id)->get();
        
        if(count($orders) > 0){
            return response()->json([
                'error' => false,
                'data' => OrdersResource::collection($orders),
            ]);
        }
        else{
            return response()->json([
                'error' => true,
                'message' => "لا يوجد طلبيات لحضرتك في هذا المستودع",    
            ]);    
        }
    }

    public function storeGetOrders()
    {
       $orders = Order::where('store_id', Auth::user()->id)->get();

        if(count($orders) > 0){
            return response()->json([
                'error' => false,
                'data' => OrdersResource::collection($orders),
            ]);
        }
        else{
            return response()->json([
                'error' => true,
                'message' => "ليس لديك طلبات من أي صيدلي",    
            ]);    
        }
    }


    //************
    // 0 تم الاستلام
    // 1 قيد التحضير
    // 2 تم الإرسال
    //***************
    public function updateOrderStatus($order_id,Request $request)
    {
        if($request['order_status']!=0 && $request['order_status']!=1 && $request['order_status']!=2){
            return response()->json([
                'error' => true,
                'message' => "يجب أن تدخل 0 أو 1 أو 2",
            ]); 
        }

        $data=Order::where('id',$order_id)->where('store_id',Auth::user()->id)->update([
            'order_status' => $request['order_status'],
        ]);


        if($data){

            if($request['order_status']==2){
                $orderMed=Order_medicin::where('order_id',$order_id)->get();

                foreach($orderMed as $element){
                    $medicin=Medicin::where('id',$element['medicin_id'])->first();

                        $up=Medicin::where('id',$element['medicin_id'])->update([
                            'quantity'=>$medicin['quantity']-$element['quantity'],
                        ]);           
                }

                return response()->json([
                    'error' => false,
                    'data' => $data,
                    'message' => "الطلبية تم إرسالها",
                ]);
            }
            else if($request['order_status']==1){
                return response()->json([
                    'error' => false,
                    'data' => $data,
                    'message' =>"الطلبية قيد التحضير"
                ]);
            }
            else if($request['order_status']==0){
                return response()->json([
                    'error' => false,
                    'data' => $data,
                    'message' => "الطلبية تم استلامها",
                ]);
            }
        }

        return response()->json([
            'error' => true,
            'data' => $data,
            'message' =>"هذه الطلبية لا توجد في مستودعك",
        ]);
        
    }

    //**********
    // 0 غير مدفوع
    // 1 مدفوع
    //************

    public function updatePaymentStatus($order_id,Request $request)
    {
        if($request['payment_status']!=0 && $request['payment_status']!=1){
            return response()->json([
                'error' => true,
                'message' => "يجب أن تدخل 0 أو 1",
            ]); 
        }

        $data=Order::where('id',$order_id)->where('store_id',Auth::user()->id)->update([
            'payment_status' => $request['payment_status'],
        ]);


        if($data){

            if($request['payment_status']==0){
                return response()->json([
                    'error' => false,
                    'data' => $data,
                    'message' => "الطلبية لم تُدفع بعد",
                ]);
            }
            else if($request['payment_status']==1){
                return response()->json([
                    'error' => false,
                    'data' => $data,
                    'message' =>"الطلبية دُفعت"
                ]);
            }
        }

        return response()->json([
            'error' => true,
            'data' => $data,
            'message' =>"هذه الطلبية لا توجد في مستودعك",
        ]);
        
    }


    //1 رأيتها
    //0 لم تراها
    public function getNotification()
    {
        $ret=[];
        $orders = Order::where('store_id',  Auth::user()->id)->where('seenNotifi',0)->get();
        $ret=$orders;
        $seen = Order::where('store_id',  Auth::user()->id)->where('seenNotifi',0)->update([
            'seenNotifi' => 1,
        ]);
        return response()->json([
            'error' => false,
            'data' => $ret,
            'message' => "تم عرض الإشعارات",
        ]);

    }
}
