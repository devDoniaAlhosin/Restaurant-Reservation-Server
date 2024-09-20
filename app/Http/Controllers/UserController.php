<?php

namespace App\Http\Controllers;
use Cloudinary\Cloudinary;

use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Register User
    public function register(Request $request){
        try {
            // Validate
            $request->validate([
                'username' => 'required|unique:users|min:3',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'phone' => 'required',
                'address' => 'nullable',
                "image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
            ]);

            // Handle image upload
            $image_url = null;
            if ($request->hasFile('image')) {
                $uploadedFile = $request->file('image');
                $cloudinary = new Cloudinary();
                $uploadResult = $cloudinary->uploadApi()->upload($uploadedFile->getRealPath());
                $image_url = $uploadResult['secure_url'];
            }
            // Create
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'phone' => $request->phone,
                'image' => $image_url,
            ]);
            //  token
            $token = $user->createToken('auth_token')->plainTextToken;

            //  cookie
            $cookie = cookie('token', $token, 60 * 24, null, null, true, true);

            // Return success response
            return response()->json([
                'message' => 'Registration successful',
                'token' => $token,
                'user' => $user
            ]);
        }catch (\Exception $exception){
            return response()->json(['message' => 'Validation Error : User Already Exists'], 400);
        }

    }

    public function login(Request $request){
        $login = $request->input('login'); // 'username' or 'email'
        $password = $request->input('password');
        $credentials =    filter_var($login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $login, 'password' => $password]
            : ['username' => $login, 'password' => $password];

        //    $credentials = $request->only('username', 'password');
        if (!Auth::attempt($credentials)){
            return response()->json(['message' => 'Invalid Login Credentials'], 401);
        }

        $user = Auth::user();
        // Check user role
        if ($user->role == 'admin') {
            // return response for admin
            $token = $user->createToken('AdminToken')->plainTextToken;
            $cookie = cookie('token', $token, 60*24);
            return response()->json(['token' => $token, 'message' => 'Successfully Login'])->withCookie($cookie);
        } else {
            // return response for normal user
            $token = $user->createToken('UserToken')->plainTextToken;
            $cookie = cookie('token', $token, 60*24);
            return response()->json(['token' => $token, 'message' => 'Successfully Login'])->withCookie($cookie);
        }

    }
// Logout
    public function logout()
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            $cookie = Cookie::forget('jwt');
            Auth::user()->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully'])->withCookie($cookie);
        } else {
            return response()->json(['error' => 'User is not authenticated'], 401);
        }
    }


//    // Get Authenticated User
    public function user()
    {
        if(!Auth::check()){
            return response()->json(['message' => 'User is not authenticated'], 401);
        }

        return response()->json(['user' => Auth::user()]);

    }


}
