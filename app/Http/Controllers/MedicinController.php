<?php

namespace App\Http\Controllers;

use App\Models\Medicin;
use App\Models\Category;
use App\Models\Favourit;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\MedicinResource;
use App\Http\Resources\CategoryResourceStore;
use App\Http\Resources\CategoryResourcePhar;
use Carbon\Carbon;
class MedicinController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {  

        $data = Category::get();

        return response()->json([
            'error' => false,
            'data' => CategoryResourceStore::collection($data),
        ]);
    }


    public function show(Request $request)
    {   
        $data = Category::get();

        return response()->json([
            'error' =>false,
            'data' => CategoryResourcePhar::collection($data),
        ]);
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
        //في قصة انو ما لازم الصيدلي يقدر يضيف دواء من هون
        $validator = Validator::make($request->all(), [
            'sc_name' => ['required', 'string', 'max:255'],
            'trad_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'finish_date' => ['required', 'date'],
            'quantity' => ['required', 'integer'],
            'price' => ['required', 'integer'],
            'manufacturer' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        
        $data=Category::where('name',$request['category'])->first();

        if(!$data){
            $crt=Category::create([
                'name'=> $request['category'],
            ]);
        }
       
        $data=Category::where('name',$request['category'])->first()->id;

        
        $num_sc_name=Medicin::where('sc_name',$request['sc_name'])->where('category_id',$data)->first();

        if($num_sc_name){
            $medicin = Medicin::where('sc_name',$request['sc_name'])->where('category_id',$data)->update([
                'quantity'=>$request['quantity']+$num_sc_name['quantity'],
            ]);
        }
        else{
            $currentDate = Carbon::now();

            if($request['finish_date'] <= $currentDate){
                return response()->json([
                    'error' => false,
                    'data' => $currentDate,
                    'message' => "التاريخ غير صالح",
                ]);   
            }

            $medicin=Medicin::create([
                'sc_name' =>$request['sc_name'],
                'trad_name' =>$request['trad_name'],
                'category' =>$request['category'] ,
                'finish_date'=> $request['finish_date'],
                'quantity' => $request['quantity'],
                'user_id'=>Auth::user()->id,
                'category_id'=>$data,
                'price' => $request['price'],
                'manufacturer' => $request['manufacturer']
            ]);
        }
        return response()->json([
            'error' => false,
            'data' => $medicin,
        ]);    
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(medicin $medicin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, medicin $medicin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(medicin $medicin)
    {
        //
    }

    public function searchMedNameStore(Request $request)
    {
        $categoris = Category::all();
        $ret=[];
    
        foreach($categoris as $category){
            $data = Medicin::where('user_id', Auth::user()->id)->where('trad_name','LIKE','%'. $request['trad_name'] .'%')->where('category_id', $category->id)->get();

            array_push($ret, [
                'category' => $category->name,
                'medicins' => MedicinResource::collection($data)
            ]);
        }

        return response()->json([
            'error' => false,
            'data' => $ret,
        ]);
    }

    public function searchMedNamePhar(Request $request)
    {
        $categoris = Category::all();
        $ret=[];
    
        foreach($categoris as $category){
            $data = Medicin::where('user_id', $request['store_id'])->where('trad_name','LIKE','%'. $request['trad_name'] .'%')->where('category_id', $category->id)->get();

            array_push($ret, [
                'category' => $category->name,
                'medicins' => MedicinResource::collection($data)
            ]);
        }

        return response()->json([
            'error' => false,
            'data' => $ret,
        ]);
    }
}
