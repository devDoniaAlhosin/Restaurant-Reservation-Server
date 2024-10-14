<?php


namespace App\Http\Controllers;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Notifications\LoginNotification;
use App\Http\Controllers\API\UserSchema;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Bistro Bliss API",
 *     version="1.0.0",
 *     description="A brief description of your API"
 * )
 */
class UserController extends Controller
{

/**
 * @OA\Get(
 *     path="/api/users",
 *     tags={"User Management"},
 *     summary="Get all users",
 *     @OA\Response(
 *         response=200,
 *         description="A list of users",
 *         @OA\JsonContent(type="array", @OA\Items(
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", format="int64", description="User ID"),
 *                 @OA\Property(property="username", type="string", description="Username of the user"),
 *                 @OA\Property(property="email", type="string", description="Email address of the user"),
 *                 @OA\Property(property="address", type="string", description="Address of the user"),
 *                 @OA\Property(property="phone", type="string", description="Phone number of the user"),
 *                 @OA\Property(property="image", type="string", description="URL of the user's profile image", nullable=true),
 *                 @OA\Property(property="role", type="string", enum={"user", "admin"}, description="Role of the user"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp"),
 *             )
 *         ))
 *     ),
 *     @OA\Response(response=500, description="Internal Server Error")
 * )
 */

    public function index()
    {
       $users = User::orderBy('created_at', 'desc')->get();
        return response()->json($users);

    }



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
 *     path="/api/admin/create-user",
 *     tags={"User Management"},
 *     summary="Create a new user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="username", type="string", description="Username of the user"),
 *             @OA\Property(property="email", type="string", description="Email address of the user"),
 *             @OA\Property(property="password", type="string", description="Password for the user"),
 *             @OA\Property(property="role", type="string", enum={"user", "admin"}, description="Role of the user"),
 *             @OA\Property(property="phone", type="string", description="Phone number of the user"),
 *             @OA\Property(property="address", type="string", description="Address of the user"),
 *             @OA\Property(property="image", type="string", format="binary", description="Profile image")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     ),
 *     @OA\Response(response=422, description="Validation Error"),
 *     @OA\Response(response=500, description="Internal Server Error")
 * )
 */
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

        /**
     * @OA\Patch(
     *     path="/api/user/update",
     *     tags={"Profile Management"},
     *     summary="Update authenticated user's details",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=200, description="User updated successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function updateOwnUser(Request $request)
    {
        $user = Auth::user();

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
        $user->save();

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }


    /**
     * @OA\Patch(
     *     path="/api/admin/update-user/{id}",
     *     tags={"User Management"},
     *     summary="Update a user's details",
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the user to update", @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/User")),
     *     @OA\Response(response=200, description="User updated successfully"),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function updateUser(Request $request, User $user)
    {
        Log::info('Form data:', ['data' => $request->all()]);
        Log::info('File upload data:', ['file' => $request->file('image')]);
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
                Log::info('Cloudinary upload result: ', $uploadResult);

                $user->image = $uploadResult['secure_url'];  // Directly assign the uploaded URL to the user
            } catch (\Exception $e) {
                // Log error
                Log::error('Cloudinary upload failed: ' . $e->getMessage());
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


       /**
     * @OA\Delete(
     *     path="/api/admin/delete-user/{id}",
     *     tags={"User Management"},
     *     summary="Delete a user",
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the user to delete", @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="User deleted successfully"),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */

    public function deleteUser(User $user)
    {

        if (Auth::id() === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

}
