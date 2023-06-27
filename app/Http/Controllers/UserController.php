<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Dotenv\Validator;
use Illuminate\Auth\Events\Validated;
use Illuminate\Validation\Validator as ValidationValidator;

class UserController extends Controller
{
    public function selAll()
    {
        return User::all();
    }

    public function selOne($id)
    {
        return User::find($id);
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

    public function update(Request $request)
    {
        $user = $request->user();

    // Validate the form data
    $validatedData = $request->validate([
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
    // Update the user data
                $user->first_name = $validatedData['first_name'];
                $user->last_name = $validatedData['last_name'];
                $user->gender = $validatedData['gender'];
                $user->role = $validatedData['role'];
                $user->status = $validatedData['status'];
                $user->responsibility = $validatedData['responsibility'];
                $user->address = $validatedData['address'];
                $user->phone_number = $validatedData['phone_number'];
                $user->password = $validatedData['password'];
    // Handle the profile image upload
    if ($request->hasFile('profile_image')) {
        $image = $request->file('profile_image');
        $filename = time().'.'.$image->getClientOriginalExtension();
        $image->storeAs('public/images', $filename);
        $user->profile_image = $filename;
    }

    $user->save();

    return response()->json(['message' => 'Profile updated successfully'], 200);

   
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
