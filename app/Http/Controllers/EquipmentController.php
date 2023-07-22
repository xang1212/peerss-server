<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
