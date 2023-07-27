<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Food;
use App\Models\Package;
use App\Models\Rental;
use App\Models\User;

use App\Models\EquipmentBroken;
use App\Models\PackageEquipment;
use App\Models\PackageFood;
use App\Models\RentalDetail;

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
        $users = User::all();
        $packages = Package::all();

        $rentals = Rental::all();
    
        $output = $rentals->map(function ($rental) {
            $customer = User::find($rental->user_id);
            $package = Package::find($rental->package_id);
    
            // Fetch associated package equipments and package foods
            $packageEquipments = PackageEquipment::where('package_id', $rental->package_id)->get();
            $packageFoods = PackageFood::where('package_id', $rental->package_id)->get();
    
            $formattedPackageEquipments = $packageEquipments->map(function ($packageEquipment) {
                return [
                    'id' => $packageEquipment->id,
                    'package_id' => $packageEquipment->package_id,
                    'equipment_id' => $packageEquipment->equipment_id,
                    'equipment_unit' =>  Equipment::find($packageEquipment->equipment_id)->unit,
                    'equipment_category' =>  Equipment::find($packageEquipment->equipment_id)->category,
                    'equipment_name' => Equipment::find($packageEquipment->equipment_id)->name,
                    'equipment_images' => Equipment::find($packageEquipment->equipment_id)->images,
                    'package_qty' => $packageEquipment->package_qty,
                ];
            });
    
            $formattedPackageFoods = $packageFoods->map(function ($packageFood) {
                return [
                    'id' => $packageFood->id,
                    'package_id' => $packageFood->package_id,
                    'food_id' => $packageFood->food_id,
                    'food_name' => Food::find($packageFood->food_id)->name,
                    'food_image' => Food::find($packageFood->food_id)->image,
                ];
            });
    
            $rentalDetails = RentalDetail::where('rental_id', $rental->id)->get();
            $equipmentIds = $rentalDetails->pluck('equipment_id');
            $equipments = Equipment::whereIn('id', $equipmentIds)->get();
    
            $formattedEquipments = $equipments->map(function ($equipment) use ($rentalDetails) {
                $rentalDetail = $rentalDetails->firstWhere('equipment_id', $equipment->id);
                return [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'category' => $equipment->category,
                    'description' => $equipment->description,
                    'qty' => $rentalDetail->rental_qty,
                    'price' => $rentalDetail->price,
                    'broken_price' => $equipment->broken_price,
                    'unit' => $equipment->unit,
                    'images' => $equipment->images,
                    'created_at' => $equipment->created_at,
                    'updated_at' => $equipment->updated_at,
                ];
            });
    
            return [
                'id' => $rental->id,
                'user_id' => $rental->user_id,
                'customer' => $customer,
                'package_id' => $rental->package_id,
                'package' => $package,
                'package_equipments' => $formattedPackageEquipments,
                'package_foods' => $formattedPackageFoods,
                'payment_status' => $rental->payment_status,
                'status' => $rental->status,
                'address' => $rental->address,
                'is_shipping' => $rental->is_shipping,
                'shipping_date' => $rental->shipping_date,
                'is_picking' => $rental->is_picking,
                'picking_date' => $rental->picking_date,
                'type' => $rental->type,
                'total_price' => floatval($rental->total_price),
                'total_broken_price' => $rental->total_broken_price,
                'receipt_half_image' => $rental->receipt_half_image,
                'receipt_full_image' => $rental->receipt_full_image,
                'equipments' => $formattedEquipments,
                'created_at' => $rental->created_at,
                'updated_at' => $rental->updated_at,
            ];
        });
    
        $allData = [
            'equipments' => $equipments->toArray(),
            'foods' => $foods->toArray(),
            'rentals' => $output,
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
