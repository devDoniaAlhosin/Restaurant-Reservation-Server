<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\Return_;
use Illuminate\Support\Facades\Validator;

    class UserController extends Controller
    {
        public function userLogin(Request $request)
        {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Attempt to log the user in
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $token = $user->createToken('YourAppName')->plainTextToken;

                return response()->json([
                    'message' => 'Logged in successfully',
                    'token' => $token,
                    'user' => [
                        'username' => $user->username,
                        'email' => $user->email,
                        'address' => $user->address,
                        'phone' => $user->phone,
                        'image' => $user->image,
                        'role' => $user->role,
                    ],
                ], 200);
            }

            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Additional functions (e.g., registration) can be added here
    }

