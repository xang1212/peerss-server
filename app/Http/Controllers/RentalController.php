<?php

namespace App\Http\Controllers;

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
        dd(Auth::id());
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
                'total_price' => 'required|numeric',
                'address' => 'nullable|string',
                'is_shipping' => 'nullable',
                'shipping_date' => 'nullable',
                'is_picking' => 'nullable',
                'picking_date' => 'nullable',
                'reciept_half_image' => 'nullable',
                'reciept_full_image' => 'nullable',
                'total_broken_price' => 'numeric',
                'rental_details' => 'required|array',
                'rental_details.*.equipment_id' => 'required|exists:equipment,id',
                'rental_details.*.rental_qty' => 'required',
                'rental_details.*.price' => 'required',
            ]);
    
            $rental = new Rental([
                'user_id' => $user->id,
                'total_price' => $validatedData['total_price']
            ]);
    
            $rental->save();
           
            
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

        // $user = Auth::user(); // get the authenticated user
    
        // $validatedData = $request->validate([
        //     'total_price' => 'required|numeric',
        //     'address' => 'nullable|string',
        //     'is_shipping' => 'nullable',
        //     'shipping_date' => 'nullable',
        //     'is_picking' => 'nullable',
        //     'picking_date' => 'nullable',
        //     'is_picking' => 'nullable',
        //     'picking_date' => 'nullable',
        //     'reciept_half_image' => 'nullable',
        //     'reciept_full_image' => 'nullable',
        //     'total_broken_price' => 'numeric',
        //     'rental_details' => 'required|array',
        //     'rental_details.*.equipment_id' => 'required|exists:equipment,id',
        //     'rental_details.*.rental_qty' => 'required',
        //     'rental_details.*.price' => 'required',
        // ]);

        // $rental = new Rental([
        //     'user_id' => $user->id,
        //     'total_price' => $validatedData['total_price']
        // ]);

        // $rental->save();
       
        
        // $rentalDetails = $validatedData['rental_details'];
        // foreach ($rentalDetails as $detail) {
        //     $rentalDetails = new RentalDetail([
        //         'rental_id' => $rental->id,
        //         'equipment_id' => $detail["equipment_id"],
        //         'rental_qty' => $detail['rental_qty'],
        //         'price' => $detail['price']
        //     ]);
        //     $rentalDetails->save();

        // }
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
        $request->validate([
            'address' => 'nullable|string',
            'payment_status' => 'nullable',
            'is_shipping' => 'nullable',
            'shipping_date' => 'nullable',
            'is_picking' => 'nullable',
            'picking_date' => 'nullable',
            'reciept_half_image' => 'nullable|image',
            'reciept_full_image' => 'nullable|image',
            'total_broken_price' => 'numeric',
        ]);

        $equipment = [
            'address' => $request ->address,
            'payment_status' => $request ->payment_status,
            'is_shipping' => $request ->is_shipping,
            'shipping_date' => $request ->shipping_date,
            'is_picking' => $request ->is_picking,
            'picking_date' => $request ->picking_date,
            'total_broken_price' => $request ->total_broken_price,
            
        ];
        if($request->reciept_half_image){
            $file = Storage::disk('public')->put('images', $request->reciept_half_image);
            $equipment['reciept_half_image']= $file;
        }

        if($request->reciept_full_image){
            $file = Storage::disk('public')->put('images', $request->reciept_full_image);
            $equipment['reciept_full_image']= $file;
        }
       

        $equipmentInst = Rental::find($id);

        if($equipmentInst->reciept_half_image && $request->reciept_half_image){
            unlink( 'storage/'.$equipmentInst->reciept_half_image);
        }

        if($equipmentInst->reciept_full_image && $request->reciept_full_image){
            unlink( 'storage/'.$equipmentInst->reciept_full_image);
        }
        $equipmentInst->update($equipment);

        return $equipment;
    }


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
