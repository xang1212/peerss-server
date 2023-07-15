<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageEquipment;
use App\Models\PackageFood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Package::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{
            //$user = Auth::user(); // get the authenticated user
    
            $validatedData = $request->validate([

                'name' => 'required|string',
                'desc' => 'nullable|string',
                'price' => 'required|numeric',
                'card_qty' => 'required|numeric',
                'images.*' => 'nullable|image',

                'package_equipment' => 'required|array',
                'package_equipment.*.equipment_id' => 'required|exists:equipment,id',
                'package_equipment.*.equipment_name' => 'required',
                'package_equipment.*.package_qty' => 'required',

                'package_food' => 'required|array',
                'package_food.*.food_id' => 'required|exists:food,id',
                'package_food.*.food_name' => 'required',
            ]);
    

            $package = [
                'name' => $validatedData['name'],
                'desc' => $validatedData['desc'],
                'price' => $validatedData['price'],
                'card_qty' => $validatedData['card_qty'],
            ];

            if ($request->hasFile('images')) {
                $imagePaths = [];
        
                foreach ($request->file('images') as $file) {
                    $path = $file->store('images', 'public');
                    $imagePaths[] = $path;
                }
                $imagePathsString = '[' . implode(',', $imagePaths) . ']';
                $package['images'] = $imagePathsString;
            }

            $package = Package::create($package);

            $packageEquipments = $validatedData['package_equipment'];
            foreach ($packageEquipments as $detail) {
                $packageEquipments = new PackageEquipment([
                    'package_id' => $package->id,
                    'equipment_id' => $detail["equipment_id"],
                    'package_qty' => $detail['package_qty'],
                    'equipment_name' => $detail['equipment_name']
                ]);
                $packageEquipments->save();

            }

            $packageFoods = $validatedData['package_food'];
            foreach ($packageFoods as $detail) {
                $packageFoods = new PackageFood([
                    'package_id' => $package->id,
                    'food_id' => $detail["food_id"],
                    'food_name' => $detail['food_name'],
                ]);
                $packageFoods->save();

            }

            DB::commit();
            return response()->json(['message' => 'Order created', 'order' => $package]);
        }catch(Throwable $th){
            DB::rollBack();
            throw $th;
    }
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
