<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function register(Request $request){

        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:50',
            'lastName' => 'required|string|max:50',
            'cellPhone' => 'required|string|max:25',
            'email'=>'required|string|email|unique:users',
//            'password'=>'required|min:8'
        ]);

        if($validator->fails()) {
            return response()->json(["status" => "failed", "validation_errors" => $validator->errors(), 400]);
        }

        $pass = Str::random(16);

        $user = User::create([
            'firstName' =>  $request->firstName,
            'lastName' =>  $request->lastName,
            'cellPhone' =>  $request->cellPhone,
            'story' =>  $request->story,
            'email' =>  $request->email,
            'optIn' =>  $request->optIn,
            'password' => Hash::make($pass),
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'success' => 'User created successfully.',
            'token_type' => 'Bearer',
            'token' => $token,
            'password' => $pass,
        ],200);
    }

    public function login(Request $request){

        $validator = Validator::make($request->all(), [
            "email" =>  "required|email",
            "password" =>  "required",
        ]);

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Login information is invalid.'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message'=> "You are successfully Logged In" ,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

}

