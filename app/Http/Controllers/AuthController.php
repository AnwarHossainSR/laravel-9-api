<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Traits\ApiResponseWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponseWithHttpStatus;

    public function register (StoreUserRequest $request)
    {
        //create user with hash password
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->apiResponse('User created successfully', [
            'user' => $user,
            'token' => $token
        ],
        201);
    }

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->apiResponse('Invalid credentials', null, 401, false);
        }

        $token = auth()->user()->createToken('auth_token')->plainTextToken;

        return $this->apiResponse('Token created', ['token' => $token, 'user' => auth()->user()]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return $this->apiResponse('Token revoked');
    }

    public function user()
    {
        return $this->apiResponse('User data', auth()->user());
    }

    public function refresh()
    {
        return $this->apiResponse('Token refreshed', ['token' => auth()->user()->createToken('auth_token')->plainTextToken]);
    }

    public function me()
    {
        return $this->apiResponse('User data', auth()->user());
    }
}
