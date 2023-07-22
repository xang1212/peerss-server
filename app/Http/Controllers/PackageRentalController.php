<?php

namespace App\Http\Controllers;

use App\Models\PackageRental;
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
        //
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

        if($request->receipt_half_image){
            $file = Storage::disk('public')->put('images', $request->receipt_half_image);
            $packageRental['receipt_half_image']= $file;
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
        return PackageRental::destroy($id);
    }
}
