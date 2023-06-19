<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function register(Request $request){
        $fields = $request->validate([
            'first_name'=>'required|string',
            'last_name'=>'required|string',
            'gender'=>'required|string',
            'responsibility'=>'required|string',
            'address'=>'required|string',
            'phone_number'=>'required|string|unique:users,phone_number',
            'profile_image'=>'nullable|string',
            'password'=>'required|string',
        ]);
        

        $user = User::create([
            'first_name'=>$fields['first_name'],
            'last_name'=>$fields['last_name'],
            'gender'=>$fields['gender'],
            'responsibility'=>$fields['responsibility'],
            'address'=>$fields['address'],
            'phone_number'=>$fields['phone_number'],
            'profile_image'=>$fields['profile_image'],
            'password'=>bcrypt($fields['password'])
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response=[
            'user'=>$user,
            'token'=>$token
        ];

        return response($response, 201);
    }

    public function login(Request $request){
        $fields= $request->validate([
            
            'phone_number'=>'required|string',
            'password'=>'required|string',
        ]);

        //check email
        $user=User::where('phone_number',$fields['phone_number'])->first();

        //check password
        if(!$user || !Hash::check($fields['password'], $user->password)){
            
                return response([
                    'message'=>'bad creds'
                ],401);
            
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response=[
            'user'=>$user,
            'token'=>$token
        ];

        return response($response, 201);
    }
    public function logout(Request $request){

        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }

}
