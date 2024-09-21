<?php

namespace App\Http\Controllers;
use Cloudinary\Cloudinary;

use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // Get  all users
    public function index()
    {
        $users = User::all();
        return response()->json($users);

    }


    // Register User
    public function register(Request $request){
        try {
            // Validate
            $request->validate([
                'username' => 'required|unique:users|min:4',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'phone' => 'required',
                'address' => 'nullable|min:5|max:100',
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
            ])->withCookie($cookie);
        }catch (\Illuminate\Validation\ValidationException  $exception){
            return response()->json(['message' => $exception->errors()], 422);
        }catch (\Exception $exception) {
            return response()->json(['message' => 'An error occurred: ' . $exception->getMessage()], 500);
        }

    }

    public function login(Request $request){
        $login = $request->input('login'); // 'username' or 'email'
        $password = $request->input('password');
        $credentials =    filter_var($login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $login, 'password' => $password]
            : ['username' => $login, 'password' => $password];

        //    $credentials = $request->only('username', 'password');
        Log::info('Login Attempt: ', $credentials);
        if (!Auth::attempt($credentials)){
            Log::error('Login failed for: ', $credentials);
            return response()->json(['message' => 'Invalid Login Credentials'], 401);
        }

        $user = Auth::user();
        // Check user role
        if ($user->role == 'admin') {
            // return response for admin
            $token = $user->createToken('AdminToken')->plainTextToken;
            $cookie = cookie('token', $token, 60*24);
            return response()->json(['token' => $token,  'user' => $user ,'message' => 'Successfully Login'])->withCookie($cookie);
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

    public function createUser(Request $request)
    {

        // Only admins can create a new user
        $request->validate([
            'username' => 'required|unique:users|min:4',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:user,admin',  //  role is 'user' or 'admin'
            'phone' => 'required',
            'address' => 'nullable|min:5|max:100',
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

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $request->role,
            'image' => $image_url,
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    // For UserProfile
    public function updateOwnUser(Request $request)
    {
        $user = Auth::user();

        // Validate input fields
        $request->validate([
            'username' => 'sometimes|min:4|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|numeric',
            'password' => 'sometimes|min:6',
            'address' => 'sometimes|nullable|min:5|max:100',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048|nullable'
        ]);
        // Handle image upload
        $image_url = null;
        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            $cloudinary = new Cloudinary();
            $uploadResult = $cloudinary->uploadApi()->upload($uploadedFile->getRealPath());
            $image_url = $uploadResult['secure_url'];
        }

        // Update the authenticated user's data
        if ($request->has('username')) {
            $user->username = $request->username;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        if ($request->has('address')) {
            $user->address = $request->address;
        }
        if($request->hasFile('image')){
            $user->image = $image_url;
        }

        // Save the updated user data
        $user->save();

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function updateUser(Request $request, User $user)
    {
        // Only admin can update any user's data
        $request->validate([
            'username' => 'sometimes|min:4|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|numeric',
            'password' => 'sometimes|min:6',
            'address' => 'sometimes|nullable|min:5|max:100',
            'role' => 'sometimes|in:user,admin',
            'image'=>'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048|nullable'
        ]);
        // Handle image upload
        $image_url = null;
        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            $cloudinary = new Cloudinary();
            $uploadResult = $cloudinary->uploadApi()->upload($uploadedFile->getRealPath());
            $image_url = $uploadResult['secure_url'];
        }

        // Update the user's data
        if ($request->has('username')) {
            $user->username = $request->username;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        if ($request->has('address')) {
            $user->address = $request->address;
        }

        if ($request->has('role')) {
            $user->role = $request->role;
        }
        if($request->hasFile('image')){
            $user->image =   $image_url;
        }

        $user->save();

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function deleteUser(User $user)
    {

        if (Auth::id() === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

}
