<?php

namespace App\Http\Controllers;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;



class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get all users",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     )
     * )
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);

    }
  /**
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Get a single user by ID",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User found", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function getUser($id)
{
    $user = User::find($id);
    if ($user) {
        return response()->json($user);
    } else {
        return response()->json(['message' => 'User not found'], 404);
    }
}



/**
 * @OA\Post(
 *     path="/register",
 *     summary="Register a new user",
 *     tags={"User"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/RegisterUser")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User registered successfully",
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input"
 *     )
 * )
 */
    public function register(Request $request){
        try {
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
                'url' => [
                    'secure' => true
                ]
            ]);
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
                try {
                    // Initialize Cloudinary configuration if not already done
                    $cloudinary = new Cloudinary();
                    $uploadResult = $cloudinary->uploadApi()->upload($uploadedFile->getRealPath());
                    $image_url = $uploadResult['secure_url'];
                } catch (\Exception $e) {
                    return response()->json(['message' => 'Image upload failed', 'error' => $e->getMessage()], 500);
                }
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

     /**
     * @OA\Post(
     *     path="/login",
     *     summary="User login",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginUser")
     *     ),
     *     @OA\Response(response=200, description="Login successful", @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=401, description="Invalid login credentials")
     * )
     */
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
            $token = $user->createToken('AdminToken')->plainTextToken;
            $cookie = cookie('token', $token, 60*24);
            Log::info('Admin logged in: ', ['user' => $user->username]);
            return response()->json([
                'token' => $token,
                'user' => $user,
                'message' => 'Admin Successfully Logged In'
            ])->withCookie($cookie);
        } elseif ($user->role == 'user') {
            $token = $user->createToken('UserToken')->plainTextToken;
            $cookie = cookie('token', $token, 60*24);
            Log::info('User logged in: ', ['user' => $user->username]);
            return response()->json([
                'token' => $token,
                'user' => $user,
                'message' => 'User Successfully Logged In'
            ])->withCookie($cookie);
        } else {
            Log::error('Unauthorized role detected: ', ['role' => $user->role]);
            return response()->json(['message' => 'Unauthorized'], 403);
        }

    }
// Logout
    public function logout()
    {

        if (Auth::check()) {
            $cookie = Cookie::forget('token');
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


    // Admin Create User
    public function createUser(Request $request)
    {
        try {

            Configuration::instance([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
                'url' => [
                    'secure' => true
                ]
            ]);


            $request->validate([
                'username' => 'required|unique:users|min:4',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'role' => 'required|in:user,admin',
                'phone' => 'required',
                'address' => 'required|min:5|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);


            $image_url = null;
            if ($request->hasFile('image')) {
                $uploadedFile = $request->file('image');
                $uploadResult = (new UploadApi())->upload($uploadedFile->getRealPath());
                $image_url = $uploadResult['secure_url'];
            }


            $phone = $request->filled('phone') ? $request->phone : null;
            $address = $request->filled('address') ? $request->address : null;

            // Create the user
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $phone,
                'address' => $address,
                'role' => $request->role,
                'image' => $image_url,
            ]);

            return response()->json(['message' => 'User created successfully', 'user' => $user]);

        } catch (\Illuminate\Validation\ValidationException $exception) {
            return response()->json(['message' => $exception->errors()], 422);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'An error occurred: ' . $exception->getMessage()], 500);
        }
    }

    public function updateOwnUser(Request $request)
{
    $user = Auth::user();

    // Validate input fields
    $request->validate([
        'username' => 'required|min:4|unique:users,username,' . $user->id,
        'email' => 'required|email|unique:users,email,' . $user->id,
        'phone' => 'sometimes',
        'old_password' => 'required',
        'password' => 'sometimes|min:6|confirmed',
        'address' => 'sometimes|nullable|min:5|max:100',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);


    // Check if the old password is correct
    if (!Hash::check($request->old_password, $user->password)) {
        return response()->json(['message' => 'Old password is incorrect.'], 403);
    }

    // Handle image upload
    $image_url = null;
    if ($request->hasFile('image')) {
        try {
            $uploadedFile = $request->file('image');
            $cloudinary = new Cloudinary();
            $uploadResult = $cloudinary->uploadApi()->upload($uploadedFile->getRealPath(), [
                'folder' => 'user_profiles/' . $user->id,
                'transformation' => [
                    'width' => 300,
                    'height' => 300,
                    'crop' => 'fit',
                    'quality' => 'auto',
                    'fetch_format' => 'auto'
                ]
            ]);
            $image_url = $uploadResult['secure_url'];
        } catch (\Exception $e) {
            return response()->json(['message' => 'Image upload failed', 'error' => $e->getMessage()], 500);
        }
    }

    // Update the authenticated user's data
    $user->username = $request->username;
    $user->email = $request->email;

    // Update password only if a new password is provided
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }


    if ($request->has('phone')) {
        $user->phone = $request->phone;
    }

    if ($request->has('address')) {
        $user->address = $request->address;
    }

    if ($request->hasFile('image')) {
        $user->image = $image_url;
    }


    // Save the updated user data
    $user->save();

    return response()->json(['message' => 'User updated successfully', 'user' => $user]);
}


// By Admin
public function updateUser(Request $request, User $user)
{
    $currentUser = auth()->user();

    // Prevent admin from changing their own role to 'user'
    if ($currentUser->id === $user->id && $currentUser->role === 'admin' && $request->input('role') === 'user') {
        return response()->json(['error' => 'You cannot change your own role from admin to user.'], 403);
    }

    // Validate request data
    $request->validate([
        'username' => 'sometimes|required|min:4|unique:users,username,' . $user->id,
        'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
        'phone' => 'sometimes',
        'password' => 'sometimes|required|min:6',
        'address' => 'sometimes|nullable|min:5|max:100',
        'role' => 'sometimes|in:user,admin',
        'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048|nullable'
    ]);

    // Handle image upload
    if ($request->hasFile('image')) {
        try {
            $uploadedFile = $request->file('image');
            $cloudinary = new Cloudinary();
            $uploadResult = $cloudinary->uploadApi()->upload($uploadedFile->getRealPath());

            // Log upload result for debugging
            \Log::info('Cloudinary upload result: ', $uploadResult);

            $user->image = $uploadResult['secure_url'];  // Directly assign the uploaded URL to the user
        } catch (\Exception $e) {
            // Log error
            \Log::error('Cloudinary upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Image upload failed: ' . $e->getMessage()], 500);
        }
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

    // Save updated user data
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
