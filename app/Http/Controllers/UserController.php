<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UserController extends Controller
{

    public function register(Request $request){

        

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
            'first_name'=>'required|string',
            'last_name'=>'required|string',
            'gender'=>'required|string',
            'responsibility'=>'required|string',
            'address'=>'required|string',
            'phone_number'=>'required|string|unique:users,phone_number',
            'profile_image'=>'nullable',
            'password'=>'required|string',
        ]);
        //$file = Storage::disk('public')->put('images', $fields['profile_image']);
        $user = User::create([
            'first_name'=>$fields['first_name'],
            'last_name'=>$fields['last_name'],
            'gender'=>$fields['gender'],
            'responsibility'=>$fields['responsibility'],
            'address'=>$fields['address'],
            'phone_number'=>$fields['phone_number'],
            'profile_image'=> $fields['profile_image'],
            'password'=>bcrypt($fields['password'])
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response=[
            'user'=>$user,
            'token'=>$token
        ];

        return response($response, 201);
    }

    public function update(Request $request,User $user)
    {
        // if($request->profile_image){
        //     $file = Storage::disk('public')->put('images', $request->profile_image);
        //     $request->profile_image= $file;
        // }
        // $news = User::find($id);
        // if($news->profile_image && $request->profile_image){
        //     unlink( 'storage/'.$news->profile_image);
        // }
        // $news ->update($request->all());
        // return $news;

        $prepareUser = [
            'first_name' => $request ->first_name,
            'last_name' => $request ->last_name,
            'gender' => $request ->gender,
            'responsibility' => $request ->responsibility,
            'address' => $request ->address,
            'status' => $request ->status,
            'role' => $request ->role,
            'phone_number' => $request ->phone_number,
            'password' => $request ->password,
        ];
        if($request->profile_image){
            $file = Storage::disk('public')->put('images', $request->profile_image);
            $prepareUser['profile_image']= $file;
        }
        // if (isset($prepareUser['profile_image'])) {
        //     $prepareUser['profile_image'] = $prepareUser->file('profile_image')->store(
        //         '/images', 'public'
        //     );
//        $request->profile_image = $prepareUser['profile_image']; //or some uniquely generated image name
//    }
        $userInt = User::find($user->id);

        if($userInt->profile_image && $request->profile_image){
            unlink( 'storage/'.$userInt->profile_image);
        }
        
        $userInt->update($prepareUser);

        return $prepareUser;

        //////////
        // $cont = User::find($id);

        // try {

        //     $cont->fill($request->post())->update();

        //     if ($request->hasFile('image')) {
        //         // remove old image
        //         if ($cont->image) {
        //             $exists = Storage::disk('public')->exists("public/image/{$cont->image}");
        //             if ($exists) {
        //                 Storage::disk('public')->delete("public/image/{$cont->image}");
        //             }
        //         }

        //         $imageName = Str::random() . '.' . $request->image->getClientOriginalExtension();
        //         Storage::disk('public')->put('image', $request->image, $imageName);
        //         $cont->image = $imageName;
        //         $cont->save();

        //         return response()->json([
        //             'message' => 'You content updated successfully!'
        //         ]);
        //     } else {

        //         return response()->json([
        //             'message' => 'Product updated successfully!'
        //         ]);
        //     }

        // } catch(\Exception $e) {
        //     \Log::error($e->getMessage());
        //     return response()->json([
        //         'message' => 'Something goes wrong while updating a product!'
        //     ], 500);
        // }
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
