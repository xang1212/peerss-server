<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function selAll()
    {
        return User::all();
    }

    public function selEmplyeeOwner()
    { 
        return User::where('role','EMPLOYEE')->orWhere('role','OWNER')->get();
    }

    public function selOne($id)
    {
        return User::find($id);
    }

    public function employeeRegister(Request $request){

        dd($request);
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'role' => 'required|string',
            'gender' => 'required|string',
            'responsibility' => 'required|string',
            'address' => 'required|string',
            'phone_number' => 'required|string|unique:users,phone_number',
            'profile_image' => 'nullable',
            'password' => 'required|string',
    
        ]);
    
    
        $user = [
            'first_name' => $request ->first_name,
            'last_name' => $request ->last_name,
            'role' => $request ->role,
            'gender' => $request ->gender,
            'responsibility' => $request ->responsibility,
            'address' => $request ->address,
            'phone_number' => $request ->phone_number,
            'profile_image' => $request ->profile_image,
            'password' => $request ->password,
        ];
        if($request->profile_image){
            $file = Storage::disk('public')->put('images', $request->profile_image);
            $user['profile_image']= $file;
        }


        return User::create($user);
    }


    public function register(Request $request)
    {
        // $prepareUser = [
        //     'first_name' => $request ->first_name,
        //     'last_name' => $request ->last_name,
        //     'gender' => $request ->gender,
        //     'responsibility' => $request ->responsibility,
        //     'address' => $request ->address,
        //     'phone_number' => $request ->phone_number,
        //     'profile_image' => $file,
        //     'password'=>bcrypt($request->password),
        // ];

        // $user = User::create($prepareUser);

        $fields = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'required|string',
            'responsibility' => 'required|string',
            'address' => 'required|string',
            'phone_number' => 'required|string|unique:users,phone_number',
            'profile_image' => 'nullable',
            'password' => 'required|string',
        ]);
        //$file = Storage::disk('public')->put('images', $fields['profile_image']);
        $user = User::create([
            'first_name' => $fields['first_name'],
            'last_name' => $fields['last_name'],
            'gender' => $fields['gender'],
            'responsibility' => $fields['responsibility'],
            'address' => $fields['address'],
            'phone_number' => $fields['phone_number'],
            'profile_image' => $fields['profile_image'],
            'password' => bcrypt($fields['password'])
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function update(Request $request, $id)
    {
    //     $user = $request->user();

    // // Validate the form data
    // $validatedData = $request->validate([
    //     'first_name' => 'required|string',
    //     'last_name' => 'required|string',
    //     'gender' => 'required|string',
    //     'responsibility' => 'required|string',
    //     'address' => 'required|string',
    //     'role' => 'required|string',
    //     'status' => 'required|string',
    //     'phone_number' => 'required|string',
    //     'profile_image' => 'nullable',
    //     'password' => 'required|string',
    // ]);
    // // Update the user data
    //             $user->first_name = $validatedData['first_name'];
    //             $user->last_name = $validatedData['last_name'];
    //             $user->gender = $validatedData['gender'];
    //             $user->role = $validatedData['role'];
    //             $user->status = $validatedData['status'];
    //             $user->responsibility = $validatedData['responsibility'];
    //             $user->address = $validatedData['address'];
    //             $user->phone_number = $validatedData['phone_number'];
    //             $user->password = bcrypt($validatedData['password']);
    // // Handle the profile image upload
    // if ($request->hasFile('profile_image')) {
    //     $image = $request->file('profile_image');
    //     $filename = time().'.'.$image->getClientOriginalExtension();
    //     $image->storeAs('public/images', $filename);
    //     $user->profile_image = $filename;
    // }

    // $user->save();

    // return response()->json(['message' => 'Profile updated successfully'], 200);

    $request->validate([
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'gender' => 'required|string',
        'responsibility' => 'required|string',
        'address' => 'required|string',
        'role' => 'required|string',
        'status' => 'required|string',
        'phone_number' => 'required|string',
        'profile_image' => 'nullable',
        'password' => 'required|string',
    ]);

    $user = [
        'first_name' => $request ->first_name,
        'last_name' => $request ->last_name,
        'gender' => $request ->gender,
        'responsibility' => $request ->responsibility,
        'address' => $request ->address,
        'role' => $request ->role,
        'status' => $request ->status,
        'phone_number' => $request ->phone_number,
        'profile_image' => $request ->profile_image,
        'password' => bcrypt($request ->password),
        
    ];
    if($request->profile_image){
        $file = Storage::disk('public')->put('images', $request->profile_image);
        $user['profile_image']= $file;
    }


    $userInst = User::find($id);

    

    if($userInst->profile_image && $request->profile_image){
        unlink( 'storage/'.$userInst->profile_image);
    }
    $userInst->update($user);

    return $user;
    }


    public function login(Request $request)
    {
        $fields = $request->validate([

            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        //check email
        $user = User::where('phone_number', $fields['phone_number'])->first();

        //check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {

            return response([
                'message' => 'bad creds'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }
    public function logout(Request $request)
    {

        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }
}
