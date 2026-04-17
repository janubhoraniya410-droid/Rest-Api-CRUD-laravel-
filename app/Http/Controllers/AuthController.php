<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class AuthController extends Controller
{
    public function reg(Request $req)
    {
        $validateUser = validator::make(
            $req->all(),
            [
                'name' => 'required',
                'password' => 'required',
                'email' => 'required|email',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validator Error',
                'error' => $validateUser->errors()->all()
            ], 401);
        }

        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => $req->password,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'user created successfullly',
            'user' => $user
        ], 200);
    }

    public function login(Request $req)
    {
        $validateUser = validator::make(
            $req->all(),
            [
                'password' => 'required',
                'email' => 'required|email',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'please enter valid password and email',
                'error' => $validateUser->errors()->all()
            ], 401);
        }

        $data = $req->only('email', 'password');

        if (Auth::attempt($data)) {

            $AuthUser = Auth::user();

            return response()->json([
                'status' => true,
                'message' => 'user login successfullly',
                'token' => $AuthUser->createToken("Api Token")->plainTextToken,
                'token_type' => 'bearer',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'email & password does not match',
                'error' => $validateUser->errors()->all()
            ], 401);
        }
    }

    public function logout(Request $req)
    {
        $user = $req->user();
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'user' => $user,
            'message' => 'user logout successfullly',
        ], 200);
    }
}
