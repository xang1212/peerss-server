<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentBroken;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Equipment::all();
    }

    public function selOne($id)
    {
        return Equipment::find($id);
    }

    public function sel_equipment_broken()
    {
        $equipmentBroken = EquipmentBroken::all();

        $formattedBrokenEquipments = $equipmentBroken->map(function ($brokenEquipment) {
            return [
                'id' => $brokenEquipment->id,
                'rental_id' => $brokenEquipment->rental_id,
                'package_rental_id' => $brokenEquipment->package_rental_id,
                'equipment_id' => $brokenEquipment->equipment_id,
                'equipment_name' => $brokenEquipment->equipment_name,
                'images' => Equipment::find($brokenEquipment->equipment_id)->images,
                'broken_qty' => $brokenEquipment->broken_qty,
                'broken_price' => $brokenEquipment->broken_price,
                'created_at' => $brokenEquipment->created_at,
                'updated_at' => $brokenEquipment->updated_at,
            ];
        });

        return response()->json($formattedBrokenEquipments);
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
        'category' => 'required|string',
        'desc' => 'nullable|string',
        'qty' => 'nullable|numeric',
        'price' => 'required|numeric',
        'broken_price' => 'required|numeric',
        'unit' => 'nullable|string',
        'images.*' => 'nullable|image',

    ]);


    $equipment = [
        'name' => $request ->name,
        'category' => $request ->category,
        'desc' => $request ->desc,
        'qty' => $request ->qty,
        'price' => $request ->price,
        'broken_price' => $request ->broken_price,
        'unit' => $request ->unit,
    ];

    if ($request->hasFile('images')) {
        $imagePaths = [];

        foreach ($request->file('images') as $file) {
            $path = $file->store('images', 'public');
            $imagePaths[] = $path;
        }
        $imagePathsString = '[' . implode(',', $imagePaths) . ']';
        $equipment['images'] = $imagePathsString;
    }

    return Equipment::create($equipment);
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
            'name' => 'required|string',
            'category' => 'required|string',
            'desc' => 'nullable|string',
            'qty' => 'nullable|numeric',
            'price' => 'required|numeric',
            'broken_price' => 'required|numeric',
            'unit' => 'nullable|string',
            'images.*' => 'nullable|image',
        ]);

        $equipment = [
            'name' => $request ->name,
            'category' => $request ->category,
            'desc' => $request ->desc,
            'qty' => $request ->qty,
            'price' => $request ->price,
            'broken_price' => $request ->broken_price,
            'unit' => $request ->unit,
            
        ];

        if ($request->hasFile('images')) {
            $imagePaths = [];
    
            foreach ($request->file('images') as $file) {
                $path = $file->store('images', 'public');
                $imagePaths[] = $path;
            }
            $imagePathsString = '[' . implode(',', $imagePaths) . ']';
            $equipment['images'] = $imagePathsString;
        }
        // if($request->images){
        //     $file = Storage::disk('public')->put('images', $request->images);
        //     $food['images']= $file;
        // }

        $equipmentInst = Equipment::find($id);

        // if($equipmentInst->images && $request->images){
        //     unlink( 'storage/'.$equipmentInst->images);
        // }

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
        return Equipment::destroy($id);
    }
}
