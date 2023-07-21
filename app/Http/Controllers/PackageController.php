<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Food;
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
        $package = Package::all();

        $package->load('package_equipment', 'package_food');

        return $package;
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

            $package->load('package_equipment', 'package_food');

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
    // public function show($id)
    // {
    //     $package = Package::find($id);

    //     $package->load('package_equipment', 'package_food');

    //     return $package;
    // }

    public function show($id)
    {
        $package = Package::find($id);
    
        $packageEquipments = PackageEquipment::where('package_id', $id)->get();
    
        $equipmentIds = $packageEquipments->pluck('equipment_id');
    
        $equipments = Equipment::whereIn('id', $equipmentIds)->get();

    
        $formattedEquipments = $equipments->map(function ($equipment) use ($packageEquipments) {
            $packageEquipment = $packageEquipments->firstWhere('equipment_id', $equipment->id);
    
            return [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'category' => $equipment->category,
                'package_qty' => $packageEquipment->package_qty,
                'broken_price' => $equipment->broken_price,
                'unit' => $equipment->unit,
                'images' => $equipment->images,
                'created_at' => $equipment->created_at,
                'updated_at' => $equipment->updated_at,
            ];
        });

        $packageFoods = PackageFood::where('package_id', $id)->get();
    
        $foodIds = $packageFoods->pluck('food_id');
    
        $foods = Food::whereIn('id', $foodIds)->get();

        $formattedFoods = $foods->map(function ($food) use ($packageFoods) {
            $packageFoods->firstWhere('food_id', $food->id);
    
            return [
                'id' => $food->id,
                'name' => $food->name,
                'unit' => $food->unit,
                'image' => $food->image,
                'created_at' => $food->created_at,
                'updated_at' => $food->updated_at,
            ];
        });
    
        $output = [
            'id' => $package->id,
            'name' => $package->name,
            'desc' => $package->desc,
            'price' => $package->price,
            'card_qty' => $package->card_qty,
            'images' => $package->images,
            'equipments' => $formattedEquipments,
            'foods' => $formattedFoods,
            'created_at' => $package->created_at,
            'updated_at' => $package->updated_at,
        ];
    
        return response()->json($output);


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
        DB::beginTransaction();
        try {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'desc' => 'nullable|string',
            'price' => 'required|numeric',
            'card_qty' => 'required|numeric',
            'images.*' => 'nullable|image',

            'package_equipment' => 'nullable|array',
            'package_equipment.*.equipment_id' => 'nullable|exists:equipment,id',
            'package_equipment.*.equipment_name' => 'nullable',
            'package_equipment.*.package_qty' => 'nullable',

            'package_food' => 'nullable|array',
            'package_food.*.food_id' => 'nullable|exists:food,id',
            'package_food.*.food_name' => 'nullable',
        ]);

        $package = Package::findOrFail($id); // Find the existing package by ID

        $package->name = $validatedData['name'];
        $package->desc = $validatedData['desc'];
        $package->price = $validatedData['price'];
        $package->card_qty = $validatedData['card_qty'];


        if ($request->hasFile('images')) {
            $imagePaths = [];
    
            foreach ($request->file('images') as $file) {
                $path = $file->store('images', 'public');
                $imagePaths[] = $path;
            }
            $imagePathsString = '[' . implode(',', $imagePaths) . ']';
            $package['images'] = $imagePathsString;
        }

        $package->save(); // Update the rental information


        if (array_key_exists('package_equipment', $validatedData)) {
        // Delete existing rental details for this rental
        PackageEquipment::where('package_id', $package->id)->delete();

        $packageEquipments = $validatedData['package_equipment'];
        foreach ($packageEquipments as $detail) {
            $packageEquipment = new PackageEquipment([
                'package_id' => $package->id,
                'equipment_id' => $detail["equipment_id"],
                'equipment_name' => $detail['equipment_name'],
                'package_qty' => $detail['package_qty'],
            ]);
            $packageEquipment->save();
        }
    }

        if (array_key_exists('package_food', $validatedData)) {
            // Delete existing equipment brokens for this rental
            PackageFood::where('package_id', $package->id)->delete();

            $packageFoods = $validatedData['package_food'];
            foreach ($packageFoods as $detail) {
                $packageFood = new PackageFood([
                    'package_id' => $package->id,
                    'food_id' => $detail["food_id"],
                    'food_name' => $detail["food_name"],
                ]);
                $packageFood->save();
            }
            $package->load('package_equipment', 'package_food');
        }

        else {
            $package->load('package_equipment'); // Only load the updated rental details
        }

        DB::commit();
        return response()->json(['message' => 'rental updated', 'rental' => $package]);
    } catch (Throwable $th) {
        DB::rollBack();
        throw $th;
    }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Package::destroy($id);
    }
}
