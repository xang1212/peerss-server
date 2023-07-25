<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Food;
use App\Models\Package;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FoodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Food::all();
    }

    public function all_index()
    {
        $equipments = Equipment::all();
        $foods = Food::all();
        $rentals = Rental::all();
        $users = User::all();
        $packages = Package::all();
    
        $allData = [
            'equipments' => $equipments->toArray(),
            'foods' => $foods->toArray(),
            'rentals' => $rentals->toArray(),
            'users' => $users->toArray(),
            'packages' => $packages->toArray(),
        ];
    
        return $allData;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'desc' => 'required|string',
            'unit' => 'nullable|string',
            'image' => 'nullable|image',

    
        ]);
    
        $food = [
            'name' => $request ->name,
            'desc' => $request ->desc,
            'unit' => $request ->unit,

        ];

        if($request->image){
            $file = Storage::disk('public')->put('images', $request->image);
            $food['image']= $file;
        }

        return Food::create($food);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Food::find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'desc' => 'nullable|string',
            'unit' => 'nullable|string',
            'image' => 'nullable|image',

    
        ]);
    
        $food = [
            'name' => $request ->name,
            'desc' => $request ->desc,
            'unit' => $request ->unit,

        ];

        if($request->image){
            $file = Storage::disk('public')->put('images', $request->image);
            $food['image']= $file;
        }
       

        $foodInst = Food::find($id);

        if($foodInst->image && $request->image){
            unlink( 'storage/'.$foodInst->image);
        }

        $foodInst->update($food);

        return $food;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $equipment = Food::find($id);

        
        if($equipment->image){
            unlink( 'storage/'.$equipment->image);
        }
        $equipment->delete();

        return response()->json(['message' => 'ລົບອາຫານສຳເລັດ'], 201);
    }
    
}
