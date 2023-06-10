<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
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


}
