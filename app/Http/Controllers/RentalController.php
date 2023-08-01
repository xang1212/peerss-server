<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentBroken;
use App\Models\Food;
use App\Models\Package;
use App\Models\PackageEquipment;
use App\Models\PackageFood;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use Throwable;

class RentalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
    
        return response()->json($output);
    }
    

    public function getRentalsByUserId($user_id)
    {
        try {
            // Retrieve rentals based on the provided user_id
            $rentals = Rental::where('user_id', $user_id)->get();

            // If there are no rentals, return an empty array
            if ($rentals->isEmpty()) {
                return response()->json([]);
            }

            
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
        
            return response()->json($output);
        } catch (Throwable $th) {
            return response()->json(['message' => 'Error retrieving rentals.', 'error' => $th->getMessage()], 500);
        }
    }

    public function sel_pending()
    {
        $rentals = Rental::where('status','PENDING')->get();
            
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
    
        return response()->json($output);
    }

    public function sel_shipping()
    {
        $rentals = Rental::where('status', 'APPROVED')
        ->where('is_shipping', 'NO')
        ->get();

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
    
        return response()->json($output);
    }

    public function sel_picking()
    {
        $rentals = Rental::where('status', 'APPROVED')
        ->where('is_shipping', 'YES')
        ->where('is_picking', 'NO')
        ->get();

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
    
        return response()->json($output);
    }

    public function sel_shipping_date($shipping_date)
    {
        $rentals = Rental::where('shipping_date',$shipping_date)->get();
            
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
    
        return response()->json($output);
    }


    public function sel_picking_date($picking_date)
    {
        $rentals = Rental::where('picking_date',$picking_date)->get();
            
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
    
        return response()->json($output);
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
            $user = Auth::user(); // get the authenticated user
    
            $validatedData = $request->validate([
                'package_id' => 'nullable|exists:packages,id',
                'total_price' => 'required|numeric',
                'address' => 'required|string',
                'type' => 'nullable|string',
                'shipping_date' => 'required',
                'picking_date' => 'required',
                'receipt_half_image' => 'required',
                'receipt_full_image' => 'nullable',
                'total_broken_price' => 'numeric|nullable',
                'rental_details' => 'nullable|array',
                'rental_details.*.equipment_id' => 'nullable|exists:equipment,id',
                'rental_details.*.rental_qty' => 'nullable',
                'rental_details.*.price' => 'nullable',
            ]);
    


            $rental = [
                'user_id' => $user->id,
                'package_id' => optional($validatedData)['package_id'],
                'total_price' => $validatedData['total_price'],
                'address' => $validatedData['address'],
                'type' => $validatedData['type'],
                'shipping_date' => $validatedData['shipping_date'],
                'picking_date' => $validatedData['picking_date'],
            ];
            if($request->package_id){
            $package = Package::findOrFail($request->package_id);
            $package->package_equipment()->each(function ($packageEquipment) {
                $equipment = $packageEquipment->equipment;
                if ($equipment) {
                    $equipment->decrement('qty', $packageEquipment->package_qty);
                }
            });}


            if($request->receipt_half_image){
                $file = Storage::disk('public')->put('images', $request->receipt_half_image);
                $rental['receipt_half_image']= $file;
            }
           
            $rental = Rental::create($rental);

            if (array_key_exists('rental_details', $validatedData)) {
            $rentalDetails = $validatedData['rental_details'];
            foreach ($rentalDetails as $detail) {
                $rentalDetails = new RentalDetail([
                    'rental_id' => $rental->id,
                    'equipment_id' => $detail["equipment_id"],
                    'rental_qty' => $detail['rental_qty'],
                    'price' => $detail['price']
                ]);
                $rentalDetails->save();
                Equipment::where('id', $detail['equipment_id'])->decrement('qty', $detail['rental_qty']);
                }
            }

            DB::commit();
            return response()->json(['message' => 'ການເຊົ່າສຳເລັດ ກະລຸນາລໍຖ້າພະນັກງານອະນຸມັດ', 'rental' => $rental]);
        }catch(Throwable $th){
            DB::rollBack();
            throw $th;
        }
    }

    public function rental_app(Request $request)
    {
        DB::beginTransaction();
        try{
            $user = Auth::user(); // get the authenticated user
    
            $validatedData = $request->validate([
                'package_id' => 'nullable|exists:packages,id',
                'total_price' => 'required|numeric',
                'address' => 'required|string',
                'type' => 'nullable|string',
                'shipping_date' => 'required',
                'picking_date' => 'required',
                'receipt_half_image' => 'required',
                'receipt_full_image' => 'nullable',
                'total_broken_price' => 'numeric|nullable',
                'rental_details' => 'nullable|array',
                'rental_details.*.equipment_id' => 'nullable|exists:equipment,id',
                'rental_details.*.rental_qty' => 'nullable',
                'rental_details.*.price' => 'nullable',
            ]);
    

            $rental = [
                'user_id' => $user->id,
                'package_id' => optional($validatedData)['package_id'],
                'total_price' => $validatedData['total_price'],
                'address' => $validatedData['address'],
                'type' => $validatedData['type'],
                'shipping_date' => $validatedData['shipping_date'],
                'picking_date' => $validatedData['picking_date'],
            ];
            if($request->package_id){
                $package = Package::findOrFail($request->package_id);
                $package->package_equipment()->each(function ($packageEquipment) {
                    $equipment = $packageEquipment->equipment;
                    if ($equipment) {
                        $equipment->decrement('qty', $packageEquipment->package_qty);
                    }
                });}

            if ($request->receipt_half_image) {
                // Handle the base64 image data
                $base64Image = $request->receipt_half_image;
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));

                $imageName = 'profile_' . time() . '.png'; // Generate a unique image name (you can change the extension based on your requirements)
                $imagePath = 'public/images/' . $imageName;

                // Save the decoded image data as a file in the storage
                Storage::put($imagePath, $imageData);

                // Set the profile image path in the user array
                $rental['receipt_half_image'] = 'images/' . $imageName;
            }
           
            $rental = Rental::create($rental);

             // Now you can access the user's properties, for example, 'phone_number':
            $customerPhoneNumber = '+85620' . $user->phone_number;
            $customerName =  $user->first_name . " " . $user->last_name;
            $ownerPhoneNumber = '+8562078339191';
            $id = $rental->id;
            $format_status = GetRentalStatusInLao("PENDING");

            $jsonPayload = [
                'contacts' => [$customerPhoneNumber, $ownerPhoneNumber], // Assuming you have only one contact for each rental
                'messages' => [
                    "🏪 ຂໍ້ຄວາມຈາກຮ້ານປະລະມີ\n🥰 ເຖິງລູກຄ້າ: $customerName \nອໍເດີ້ຂອງທ່ານ: $format_status\nເລກທີ່ອ້າງອີງການເຊົ່າ: $id",
                    "📢 ມີອໍເດີ້ເຂົ້າໃໝ່\nຈາກລູກຄ້າ: $customerName\nເບີໂທ: $customerPhoneNumber\nເລກທີ່ອ້າງອີງການເຊົ່າ: $id\nກະລຸນາກວດສອບຂໍ້ມູນການເຊົ່າ!",
                ],
            ];

            // Send the HTTP POST request using the Http facade (Guzzle)
            $responseSendNoti = Http::post('http://127.0.0.1:3000/send-message', $jsonPayload);


            if (array_key_exists('rental_details', $validatedData)) {
            $rentalDetails = $validatedData['rental_details'];
            foreach ($rentalDetails as $detail) {
                $rentalDetails = new RentalDetail([
                    'rental_id' => $rental->id,
                    'equipment_id' => $detail["equipment_id"],
                    'rental_qty' => $detail['rental_qty'],
                    'price' => $detail['price']
                ]);
                $rentalDetails->save();
                Equipment::where('id', $detail['equipment_id'])->decrement('qty', $detail['rental_qty']);
                }
            }

            DB::commit();
            return response()->json(['message' => 'ການເຊົ່າສຳເລັດໝ ກະລຸນາລໍຖ້າພະນັກງານອະນຸມັດ', 'rental' => $rental]);
        }catch(Throwable $th){
            DB::rollBack();
            throw $th;
        }
    }

    public function walk_in(Request $request)
    {
        DB::beginTransaction();
        try{
            //$user = Auth::user(); // get the authenticated user
    
            $validatedData = $request->validate([

                'user_id' => 'required|exists:users,id',
                'package_id' => 'nullable|exists:packages,id',
                'total_price' => 'required|numeric',
                'address' => 'required|string',
                'type' => 'nullable|string',
                'shipping_date' => 'required',
                'picking_date' => 'required',
                'receipt_half_image' => 'required',
                'receipt_full_image' => 'nullable',
                'total_broken_price' => 'numeric',
                'rental_details' => 'nullable|array',
                'rental_details.*.equipment_id' => 'nullable|exists:equipment,id',
                'rental_details.*.rental_qty' => 'nullable',
                'rental_details.*.price' => 'nullable',
            ]);
    

            $rental = [
                'user_id' => $validatedData['user_id'],
                'package_id' => optional($validatedData)['package_id'],
                'total_price' => $validatedData['total_price'],
                'address' => $validatedData['address'],
                'type' => $validatedData['type'],
                'shipping_date' => $validatedData['shipping_date'],
                'picking_date' => $validatedData['picking_date'],
            ];
            if($request->package_id){
                $package = Package::findOrFail($request->package_id);
                $package->package_equipment()->each(function ($packageEquipment) {
                    $equipment = $packageEquipment->equipment;
                    if ($equipment) {
                        $equipment->decrement('qty', $packageEquipment->package_qty);
                    }
                });}

            if($request->receipt_half_image){
                $file = Storage::disk('public')->put('images', $request->receipt_half_image);
                $rental['receipt_half_image']= $file;
            }
           
            $rental = Rental::create($rental);

            $user = User::where('id', $request->user_id)->first();
            $customerPhoneNumber = '+85620' . $user->phone_number;
            $customerName =  $user->first_name . " " . $user->last_name;
            $ownerPhoneNumber = '+8562078339191';
            $id = $rental->id;
            $format_status = GetRentalStatusInLao("PENDING");

            $jsonPayload = [
                'contacts' => [$customerPhoneNumber, $ownerPhoneNumber], // Assuming you have only one contact for each rental
                'messages' => [
                    "🏪 ຂໍ້ຄວາມຈາກຮ້ານປະລະມີ\n🥰 ເຖິງລູກຄ້າ: $customerName \nອໍເດີ້ຂອງທ່ານ: $format_status\nເລກທີ່ອ້າງອີງການເຊົ່າ: $id",
                    "📢 ມີອໍເດີ້ເຂົ້າໃໝ່\nຈາກລູກຄ້າ: $customerName\nເບີໂທ: $customerPhoneNumber\nເລກທີ່ອ້າງອີງການເຊົ່າ: $id\nກະລຸນາກວດສອບຂໍ້ມູນການເຊົ່າ!",
                ],
            ];

            // Send the HTTP POST request using the Http facade (Guzzle)
            $responseSendNoti = Http::post('http://127.0.0.1:3000/send-message', $jsonPayload);

            if (array_key_exists('rental_details', $validatedData)) {
            $rentalDetails = $validatedData['rental_details'];
            foreach ($rentalDetails as $detail) {
                $rentalDetails = new RentalDetail([
                    'rental_id' => $rental->id,
                    'equipment_id' => $detail["equipment_id"],
                    'rental_qty' => $detail['rental_qty'],
                    'price' => $detail['price']
                ]);
                $rentalDetails->save();
                Equipment::where('id', $detail['equipment_id'])->decrement('qty', $detail['rental_qty']);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Order created', 'order' => $rental]);
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
        $rental = Rental::find($id);
    
        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }
    
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
                'equipment_name' => Equipment::find($packageEquipment->equipment_id)->name,
                'equipment_category' =>  Equipment::find($packageEquipment->equipment_id)->category,
                'unit' => Equipment::find($packageEquipment->equipment_id)->unit,
                'images' => Equipment::find($packageEquipment->equipment_id)->images,
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
    
        // Fetch all rental details and equipment information
        $rentalDetails = RentalDetail::where('rental_id', $rental->id)->get();
        $equipmentIds = $rentalDetails->pluck('equipment_id');
        $equipments = Equipment::whereIn('id', $equipmentIds)->get();
    
        // Map all rented equipment
        $formattedEquipments = $equipments->map(function ($equipment) use ($rentalDetails) {
            $rentalDetail = $rentalDetails->where('equipment_id', $equipment->id)->first();
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
    
        // Fetch all broken equipment information
        $equipmentBrokens = EquipmentBroken::where('rental_id', $rental->id)->get();
        $brokenEquipmentIds = $equipmentBrokens->pluck('equipment_id');
        $brokenEquipments = Equipment::whereIn('id', $brokenEquipmentIds)->get();
    
        // Map all broken equipment
        $formattedBrokenEquipments = $brokenEquipments->map(function ($equipment) use ($equipmentBrokens) {
            $equipmentBroken = $equipmentBrokens->where('equipment_id', $equipment->id)->first();
            return [
                'id' => $equipmentBroken->id,
                'equipment_id' => $equipment->id,
                'equipment_name' => $equipment->name,
                'category' => $equipment->category,
                'desc' => $equipment->desc,
                'broken_qty' => $equipmentBroken->broken_qty,
                'broken_price' => $equipment->broken_price,
                'unit' => $equipment->unit,
                'images' => $equipment->images,
                'created_at' => $equipment->created_at,
                'updated_at' => $equipment->updated_at,
            ];
        });
    
        $output = [
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
            'broken_equipments' => $formattedBrokenEquipments,
            'created_at' => $rental->created_at,
            'updated_at' => $rental->updated_at,
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
//     public function update_eq_broken(Request $request, $id)
// {
//     DB::beginTransaction();
//     try {
//         //$user = Auth::user(); // get the authenticated user

//         $validatedData = $request->validate([
//             'total_price' => 'required|numeric',
//             'address' => 'required|string',
//             'shipping_date' => 'required',
//             'picking_date' => 'required',
//             'receipt_half_image' => 'required',
//             'receipt_full_image' => 'nullable',
//             'total_broken_price' => 'numeric',
//             'rental_details' => 'required|array',
//             'rental_details.*.equipment_id' => 'required|exists:equipment,id',
//             'rental_details.*.rental_qty' => 'required',
//             'rental_details.*.price' => 'required',
//         ]);

//         $rental = Rental::findOrFail($id); // Find the existing rental by ID

//         $rental->total_price = $validatedData['total_price'];
//         $rental->address = $validatedData['address'];
//         $rental->shipping_date = $validatedData['shipping_date'];
//         $rental->picking_date = $validatedData['picking_date'];

//         if ($request->hasFile('receipt_half_image')) {
//             $file = Storage::disk('public')->put('images', $request->file('receipt_half_image'));
//             $rental->receipt_half_image = $file;
//         }

//         $rental->save(); // Update the rental information

//         // Delete existing rental details for this rental
//         RentalDetail::where('rental_id', $rental->id)->delete();

//         $rentalDetails = $validatedData['rental_details'];
//         foreach ($rentalDetails as $detail) {
//             $rentalDetail = new RentalDetail([
//                 'rental_id' => $rental->id,
//                 'equipment_id' => $detail["equipment_id"],
//                 'rental_qty' => $detail['rental_qty'],
//                 'price' => $detail['price']
//             ]);
//             $rentalDetail->save();
//         }

//         $rental->load('rental_detail');

//         DB::commit();
//         return response()->json(['message' => 'Order updated', 'order' => $rental]);
//     } catch (Throwable $th) {
//         DB::rollBack();
//         throw $th;
//     }
// }

    public function update(Request $request, $id)
{
    DB::beginTransaction();
    try {
        //$user = Auth::user(); // get the authenticated user

        $validatedData = $request->validate([
            'package_id' => 'nullable|exists:packages,id',
            'total_price' => 'required|numeric',
            'payment_status' => 'nullable',
            'status' => 'nullable',
            'address' => 'required|string',
            'is_shipping' => 'nullable',
            'shipping_date' => 'required',
            'is_picking' => 'nullable',
            'picking_date' => 'required',
            'type' => 'nullable',
            'receipt_half_image' => 'nullable',
            'receipt_full_image' => 'nullable',
            'total_broken_price' => 'numeric|nullable',
            'rental_details' => 'nullable|array',
            'rental_details.*.equipment_id' => 'nullable|exists:equipment,id',
            'rental_details.*.rental_qty' => 'nullable',
            'rental_details.*.price' => 'nullable',
            'equipment_brokens' => 'nullable|array',
            'equipment_brokens.*.equipment_id' => 'nullable|exists:equipment,id',
            'equipment_brokens.*.equipment_name' => 'nullable',
            'equipment_brokens.*.broken_qty' => 'nullable',
            'equipment_brokens.*.broken_price' => 'nullable',
        ]);

        $rental = Rental::findOrFail($id); // Find the existing rental by ID

        $rental->package_id = optional($validatedData)['package_id'];
        $rental->total_price = $validatedData['total_price'];
        $rental->payment_status = $validatedData['payment_status'];
        $rental->status = $validatedData['status'];
        $rental->address = $validatedData['address'];
        $rental->is_shipping = $validatedData['is_shipping'];
        $rental->shipping_date = $validatedData['shipping_date'];
        $rental->is_picking = $validatedData['is_picking'];
        $rental->picking_date = $validatedData['picking_date'];
        $rental->type = $validatedData['type'];
        $rental->total_broken_price = $validatedData['total_broken_price'];

        if ($request->hasFile('receipt_half_image')) {
            $file = Storage::disk('public')->put('images', $request->file('receipt_half_image'));
            $rental->receipt_half_image = $file;
        }

        if ($request->hasFile('receipt_full_image')) {
            $file = Storage::disk('public')->put('images', $request->file('receipt_full_image'));
            $rental->receipt_full_image = $file;
        }

        if ($request['package_id']) {
            // Code to handle package rental
            $package = Package::findOrFail($request->package_id);
            $package->package_equipment()->each(function ($packageEquipment) {
                $equipment = $packageEquipment->equipment;
                if ($equipment) {
                    $equipment->increment('qty', $packageEquipment->package_qty);
                }
            });
        }

        $rental->save(); // Update the rental information


        if (array_key_exists('rental_details', $validatedData)) {
        // Delete existing rental details for this rental
        RentalDetail::where('rental_id', $rental->id)->delete();

        $rentalDetails = $validatedData['rental_details'];
        foreach ($rentalDetails as $detail) {
            $rentalDetail = new RentalDetail([
                'rental_id' => $rental->id,
                'equipment_id' => $detail["equipment_id"],
                'rental_qty' => $detail['rental_qty'],
                'price' => $detail['price']
            ]);
            $rentalDetail->save();
            Equipment::where('id', $detail['equipment_id'])->increment('qty', $detail['rental_qty']);
        }
    }

        if (array_key_exists('equipment_brokens', $validatedData)) {
            // Delete existing equipment brokens for this rental
            EquipmentBroken::where('rental_id', $rental->id)->delete();

            $EquipmentBrokens = $validatedData['equipment_brokens'];
            foreach ($EquipmentBrokens as $detail) {
                $EquipmentBroken = new EquipmentBroken([
                    'rental_id' => $rental->id,
                    'equipment_id' => $detail["equipment_id"],
                    'equipment_name' => $detail["equipment_name"],
                    'broken_qty' => $detail['broken_qty'],
                    'broken_price' => $detail['broken_price']
                ]);
                $EquipmentBroken->save();
                Equipment::where('id', $detail['equipment_id'])->decrement('qty', $detail['rental_qty']);
            }
            $rental->load('rental_detail', 'equipment_broken');
        }

        else {
            $rental->load('rental_detail'); // Only load the updated rental details
        }

        DB::commit();
        return response()->json(['message' => 'rental updated', 'rental' => $rental]);
    } catch (Throwable $th) {
        DB::rollBack();
        throw $th;
    }
}


public function update_status(Request $request, $id)
{
    $request->validate([
        'status' => 'required|string',
    ]);

    $status = [
        'status' => $request->status,
    ];

    $statusInst = Rental::find($id);
    $statusInst->update($status);

    $user = User::find($statusInst->user_id);

    if (!$user) {
        return response()->json(['error' => 'User not found for the rental'], 404);
    }
// Assuming you have defined the 'user' relationship in the Rental model

    // Now you can access the user's properties, for example, 'phone_number':
    $customerPhoneNumber = '+85620' . $user->phone_number;
    $customerName =  $user->first_name . " " . $user->last_name;
    $ownerPhoneNumber = '+8562078339191';
    $id = $statusInst->id;
    $total_price = number_format($statusInst->total_price);
    $format_status = GetRentalStatusInLao($request->status);
    $outtro = "";
    if($request->status == "APPROVED"){
        $outtro = "\nແລ້ວພວກຈະຕິດຕໍ່ກັບເພື່ອສົ່ງເຄື່ອງໄວໆນີ້!";
    }

    $jsonPayload = [
        'contacts' => [$customerPhoneNumber], // Assuming you have only one contact for each rental
        'messages' => [
            "🏪 ຂໍ້ຄວາມຈາກຮ້ານປະລະມີ\n🥰 ເຖິງລູກຄ້າ: $customerName \nອໍເດີ້ຂອງທ່ານ: $format_status\nເລກທີ່ອ້າງອີງການເຊົ່າ: $id $outtro",
        ],
    ];

    // Send the HTTP POST request using the Http facade (Guzzle)
    $response = Http::post('http://127.0.0.1:3000/send-message', $jsonPayload);

    // Check for errors in the response
    if ($response->failed()) {
        // Handle the error appropriately
        return response()->json(['error' => $response->clientError()], $response->status());
    }

    // Get the response body as an array (assuming the server returns JSON)
    $responseData = $response->json();
    // You can handle the response data as needed

    // Return a success response if needed
    return response()->json(['message' => 'Messages sent successfully']);
}

public function update_shipping(Request $request, $id)
{
    $request->validate([
        'is_shipping' => 'required|string',
    ]);

    $shipping = [
        'is_shipping' => $request ->is_shipping,
    ];
    $shippingInst = Rental::find($id);

    $shippingInst->update($shipping);
    return $shipping;
}

public function update_picking(Request $request, $id)
{
    $request->validate([
        'is_picking' => 'required|string',
    ]);

    $picking = [
        'is_picking' => $request ->is_picking,
    ];
    $pickingInst = Rental::find($id);

    $pickingInst->update($picking);
    return $picking;
}

    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'address' => 'nullable|string',
    //         'payment_status' => 'nullable',
    //         'is_shipping' => 'nullable',
    //         'shipping_date' => 'nullable',
    //         'is_picking' => 'nullable',
    //         'picking_date' => 'nullable',
    //         'type' => 'nullable',
    //         'receipt_half_image' => 'nullable|image',
    //         'receipt_full_image' => 'nullable|image',
    //         'total_broken_price' => 'numeric',
    //     ]);

    //     $rental = [
    //         'address' => $request ->address,
    //         'payment_status' => $request ->payment_status,
    //         'is_shipping' => $request ->is_shipping,
    //         'shipping_date' => $request ->shipping_date,
    //         'is_picking' => $request ->is_picking,
    //         'picking_date' => $request ->picking_date,
    //         'type' => $request ->type,
    //         'total_broken_price' => $request ->total_broken_price,
            
    //     ];
    //     if($request->receipt_half_image){
    //         $file = Storage::disk('public')->put('images', $request->receipt_half_image);
    //         $rental['receipt_half_image']= $file;
    //     }

    //     if($request->receipt_full_image){
    //         $file = Storage::disk('public')->put('images', $request->receipt_full_image);
    //         $rental['receipt_full_image']= $file;
    //     }
       

    //     $rentalInst = Rental::find($id);

    //     if($rentalInst->receipt_half_image && $request->receipt_half_image){
    //         unlink( 'storage/'.$rentalInst->receipt_half_image);
    //     }

    //     if($rentalInst->receipt_full_image && $request->receipt_full_image){
    //         unlink( 'storage/'.$rentalInst->receipt_full_image);
    //     }
    //     $rentalInst->update($rental);

    //     return $rental;
    // }


    public function updateAddress(Request $request, $id)
    {
        // $request->validate([
        //     'shipping_date' => 'required|date',
        //     'picking_date' => 'required|date',
        //     'address' => 'required|string',
        // ]);
        // $rental = Rental::find($id);
        // $rental ->update($request->all());
        // return $rental;

        $request->validate([
            'shipping_date' => 'required|date',
            'picking_date' => 'required|date',
            'address' => 'required|string',
        ]);

        $equipment = [
            'shipping_date' => $request ->shipping_date,
            'picking_date' => $request ->picking_date,
            'address' => $request ->address,
            
        ];

        $equipmentInst = Rental::find($id);

        $equipmentInst->update($equipment);

        return $equipment;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Rental::destroy($id);
    }

    public function eqm_bk_delete($id)
    {
        return EquipmentBroken::destroy($id);
    }
}

function GetRentalStatusInLao($status) {
    $rentalStatusEnum = [
        [
            'status_en' => 'PENDING',
            'status_lo' => 'ລໍຖ້າກວດສອບ ⌛',
        ],
        [
            'status_en' => 'APPROVED',
            'status_lo' => 'ອະນຸມັດແລ້ວ ✅',
        ],
        [
            'status_en' => 'DENIED',
            'status_lo' => 'ຖືກປະຕິເສດ ❌',
        ],
        [
            'status_en' => 'CANCEL',
            'status_lo' => 'ໄດ້ຍົກເລີກແລ້ວ ✖️',
        ],
    ];

    $matchingStatus = array_filter($rentalStatusEnum, function ($enum) use ($status) {
        return strtoupper($enum['status_en']) === strtoupper($status);
    });

    if (count($matchingStatus) > 0) {
        return reset($matchingStatus)['status_lo'];
    }

    return "";
}
 