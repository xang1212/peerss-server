<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentBroken;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                // 'total_price' => 'required|numeric',
                // 'address' => 'nullable|string',
                // 'is_shipping' => 'nullable',
                // 'shipping_date' => 'nullable',
                // 'is_picking' => 'nullable',
                // 'picking_date' => 'nullable',
                // 'reciept_half_image' => 'nullable',
                // 'reciept_full_image' => 'nullable',
                // 'total_broken_price' => 'numeric',
                // 'rental_details' => 'required|array',
                // 'rental_details.*.equipment_id' => 'required|exists:equipment,id',
                // 'rental_details.*.rental_qty' => 'required',
                // 'rental_details.*.price' => 'required',

                'total_price' => 'required|numeric',
                'address' => 'required|string',
                'shipping_date' => 'required',
                'picking_date' => 'required',
                'receipt_half_image' => 'required',
                'receipt_full_image' => 'nullable',
                'total_broken_price' => 'numeric',
                'rental_details' => 'required|array',
                'rental_details.*.equipment_id' => 'required|exists:equipment,id',
                'rental_details.*.rental_qty' => 'required',
                'rental_details.*.price' => 'required',
            ]);
    
            // $rental = new Rental([
            //     'user_id' => $user->id,
            //     'total_price' => $validatedData['total_price'],
            //     'address' => $validatedData['address'],
            //     'shipping_date' => $validatedData['shipping_date'],
            //     'picking_date' => $validatedData['picking_date'],
            // ]);

            // $rental->save();

            $rental = [
                'user_id' => $user->id,
                'total_price' => $validatedData['total_price'],
                'address' => $validatedData['address'],
                'shipping_date' => $validatedData['shipping_date'],
                'picking_date' => $validatedData['picking_date'],
            ];

            if($request->receipt_half_image){
                $file = Storage::disk('public')->put('images', $request->receipt_half_image);
                $rental['receipt_half_image']= $file;
            }
           
            $rental = Rental::create($rental);

            $rentalDetails = $validatedData['rental_details'];
            foreach ($rentalDetails as $detail) {
                $rentalDetails = new RentalDetail([
                    'rental_id' => $rental->id,
                    'equipment_id' => $detail["equipment_id"],
                    'rental_qty' => $detail['rental_qty'],
                    'price' => $detail['price']
                ]);
                $rentalDetails->save();

            }

            DB::commit();
            return response()->json(['message' => 'Order created', 'order' => $rental]);
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
                // 'total_price' => 'required|numeric',
                // 'address' => 'nullable|string',
                // 'is_shipping' => 'nullable',
                // 'shipping_date' => 'nullable',
                // 'is_picking' => 'nullable',
                // 'picking_date' => 'nullable',
                // 'reciept_half_image' => 'nullable',
                // 'reciept_full_image' => 'nullable',
                // 'total_broken_price' => 'numeric',
                // 'rental_details' => 'required|array',
                // 'rental_details.*.equipment_id' => 'required|exists:equipment,id',
                // 'rental_details.*.rental_qty' => 'required',
                // 'rental_details.*.price' => 'required',

                'user_id' => 'required|exists:users,id',
                'total_price' => 'required|numeric',
                'address' => 'required|string',
                'shipping_date' => 'required',
                'picking_date' => 'required',
                'receipt_half_image' => 'required',
                'receipt_full_image' => 'nullable',
                'total_broken_price' => 'numeric',
                'rental_details' => 'required|array',
                'rental_details.*.equipment_id' => 'required|exists:equipment,id',
                'rental_details.*.rental_qty' => 'required',
                'rental_details.*.price' => 'required',
            ]);
    
            // $rental = new Rental([
            //     'user_id' => $user->id,
            //     'total_price' => $validatedData['total_price'],
            //     'address' => $validatedData['address'],
            //     'shipping_date' => $validatedData['shipping_date'],
            //     'picking_date' => $validatedData['picking_date'],
            // ]);

            // $rental->save();

            $rental = [
                'user_id' => $validatedData['user_id'],
                'total_price' => $validatedData['total_price'],
                'address' => $validatedData['address'],
                'shipping_date' => $validatedData['shipping_date'],
                'picking_date' => $validatedData['picking_date'],
            ];

            if($request->receipt_half_image){
                $file = Storage::disk('public')->put('images', $request->receipt_half_image);
                $rental['receipt_half_image']= $file;
            }
           
            $rental = Rental::create($rental);

            $rentalDetails = $validatedData['rental_details'];
            foreach ($rentalDetails as $detail) {
                $rentalDetails = new RentalDetail([
                    'rental_id' => $rental->id,
                    'equipment_id' => $detail["equipment_id"],
                    'rental_qty' => $detail['rental_qty'],
                    'price' => $detail['price']
                ]);
                $rentalDetails->save();

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
    
        $customer = User::find($rental->user_id);
    
        $rentalDetails = RentalDetail::where('rental_id', $id)->get();
    
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
    
        $output = [
            'id' => $rental->id,
            'user_id' => $rental->user_id,
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
            'equipments' => $formattedEquipments,
            'created_at' => $rental->created_at,
            'updated_at' => $rental->updated_at,
        ];
    
        return response()->json($output);


    }


    public function getDistrict()
    {
        $district = Rental::with(['rental_detail'])->get();
        return response()->json($district);
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
            'total_price' => 'required|numeric',
            'payment_status' => 'nullable',
            'address' => 'required|string',
            'is_shipping' => 'nullable',
            'shipping_date' => 'required',
            'is_picking' => 'nullable',
            'picking_date' => 'required',
            'type' => 'nullable',
            'total_broken_price' => 'numeric',
            'receipt_half_image' => 'required',
            'receipt_full_image' => 'nullable',
            'total_broken_price' => 'numeric',
            'rental_details' => 'required|array',
            'rental_details.*.equipment_id' => 'required|exists:equipment,id',
            'rental_details.*.rental_qty' => 'required',
            'rental_details.*.price' => 'required',
            'equipment_brokens' => 'nullable|array',
            'equipment_brokens.*.equipment_id' => 'nullable|exists:equipment,id',
            'equipment_brokens.*.equipment_name' => 'nullable',
            'equipment_brokens.*.broken_qty' => 'nullable',
            'equipment_brokens.*.broken_price' => 'nullable',
        ]);

        $rental = Rental::findOrFail($id); // Find the existing rental by ID

        $rental->total_price = $validatedData['total_price'];
        $rental->payment_status = $validatedData['payment_status'];
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

        $rental->save(); // Update the rental information

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
        //
    }
}
