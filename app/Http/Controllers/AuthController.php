<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'admin-update-user', 'admin-delete-user', 'all-users']]);
    }

    /**
     * Get a JWT via given credentials.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = Auth::attempt($validator->validated())) {
            return response()->json(['error' => 'Incorrect email or password'], 401);
        }

        return $this->successResponse($this->createNewToken($token), "Successfully logged in");
    }

    public function register(Request $request)
    {
        $validator = $this->validateUser();
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->messages(), 422);
        }

        $create = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        return $this->successResponse($create, "User successfully registered.");
    }

    public function validateUser()
    {
        return Validator::make(request()->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    public function getUsers()
    {
        $getAll = User::get();
        return $this->successResponse($getAll, "All users fetched successfully.");
    }


    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        Auth::logout();

        return $this->successResponse(null, 'User successfully signed out');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(Auth::refresh());
    }

    /**
     * Get the authenticated User.
     */
    public function userProfile()
    {
        // To get current user id
        // $findUser = auth()->id();
        // if (!$findUser)
        //     return  response()->json(['error' => 'User does not exist.'], 401);
        // $user = Auth::guard('api')->getToken();
        return $this->successResponse(auth()->user(), "User details");
    }

    public function updateUser(Request $request, $id)
    {
        if (User::where('id', $id)->exists()) {
            $user = User::find($id);

            $user->name = is_null($request->name) ? $user->name : $user->name;
            $user->email = is_null($request->email) ? $user->email : $user->email;
            $user->password = is_null($request->password) ? $user->password : Hash::make($request->password);
            $user->save();

            return $this->successResponse($user, "Records updated successfully.");
        } else {
            return $this->errorResponse(null, "User not found.", 404);
        }
    }

    public function deleteUser($id)
    {
        var_dump($id);
        if (User::where('id', $id)->exists()) {
            $user = User::find($id);
            $user->delete();

            return $this->successResponse($user, "records deleted.");
        } else {
            return $this->errorResponse(null, "User not found.", 404);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     */
    protected function createNewToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => auth()->user()
        ];
    }
}
