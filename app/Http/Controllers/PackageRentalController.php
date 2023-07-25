<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentBroken;
use App\Models\Package;
use App\Models\PackageRental;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PackageRentalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rentals = PackageRental::all();
    
        $output = $rentals->map(function ($rental) {
            $customer = User::find($rental->user_id);

            $package = Package::find($rental->package_id);

    
            return [
                'id' => $rental->id,
                'user_id' => $rental->user_id,
                'package_id' => $rental->package_id,
                'customer' => $customer,
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
                'package' => $package,
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
        $user = Auth::user();

        $request->validate([
            'total_price' => 'required|numeric',
            'package_id' => 'required|max:1|exists:packages,id',
            'address' => 'required|string',
            'shipping_date' => 'required',
            'picking_date' => 'required',
            'receipt_half_image' => 'required',
            'total_broken_price' => 'nullable',
        ]);
    
        $packageRental = [
            'user_id' => $user ->id,
            'package_id' => $request ->package_id,
            'total_price' => $request ->total_price,
            'shipping_date' => $request ->shipping_date,
            'picking_date' => $request ->picking_date,
            'address' => $request ->address,
            'total_broken_price' => $request ->total_broken_price,
        ];

        if ($request->receipt_half_image) {
            // Handle the base64 image data
            $base64Image = $request->receipt_half_image;
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));

            $imageName = 'profile_' . time() . '.png'; // Generate a unique image name (you can change the extension based on your requirements)
            $imagePath = 'public/images/' . $imageName;

            // Save the decoded image data as a file in the storage
            Storage::put($imagePath, $imageData);

            // Set the profile image path in the user array
            $packageRental['receipt_half_image'] = 'images/' . $imageName;
        }

        return PackageRental::create($packageRental);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $rental = PackageRental::find($id);
    
        $customer = User::find($rental->user_id);

        $package = Package::find($rental->package_id);
    
        $equipmentBrokens = EquipmentBroken::where('rental_id', $id)->get();

        $formattedEquipmentBrokens = $equipmentBrokens->map(function ($equipmentBroken) {
            $equipment = Equipment::find($equipmentBroken->equipment_id);
    
            return [
                'id' => $equipmentBroken->id,
                'rental_id' => $equipmentBroken->rental_id,
                'equipment_id' => $equipmentBroken->equipment_id,
                'equipment_name' => $equipment->name,
                'equipment_images' => $equipment->images,
                'equipment_unit' => $equipment->unit,
                'broken_qty' => $equipmentBroken->broken_qty,
                'broken_price' => $equipmentBroken->broken_price,
                'created_at' => $equipmentBroken->created_at,
                'updated_at' => $equipmentBroken->updated_at,
            ];
        });
    
        $output = [
            'id' => $rental->id,
            'user_id' => $rental->user_id,
            'customer' => $customer,
            'package_id' => $rental->package_id,
            'package' => $package,
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
            'equipment_brokens' => $formattedEquipmentBrokens,
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
        return PackageRental::destroy($id);
    }
}
