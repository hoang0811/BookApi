<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Exception;


class AuthController extends BaseController
{
    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['user'] = $user;
    
        return $this->sendResponse($success, 'User registered successfully.');
    }
    
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
    
        $credentials = $request->only('email', 'password');
        
        if (!$token = auth()->attempt($credentials)) {
            return $this->sendError('Unauthorized.', ['error' => 'Unauthorized']);
        }
    
        $user = auth()->user();
        $success['token'] = $this->respondWithToken($token);
        $success['user'] = $user;
        
        return $this->sendResponse($success, 'User login successfully.');
    }
    
    public function refresh() {
        $success = $this->respondWithToken(auth()->refresh());
        return $this->sendResponse($success, 'Profile fetch successfully.');
    }
    
    public function logout() {
        auth()->logout();
        return $this->sendResponse([], 'Successfully logged out.');
    }
    
    
    public function profile() {
        $success = auth()->user();
        return $this->sendResponse($success, 'Profile fetch successfully.');
    }
    protected function respondWithToken($token) {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ];
    }
    public function redirectToGoogle()
{
    $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
    return response()->json(['url' => $url]);
}

public function handleGoogleCallback()
{
    try {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'password' => bcrypt(uniqid()), // Random password for non-Google login
            ]
        );

        $token = auth()->login($user);

        return response()->json([
            'access_token' => $this->respondWithToken($token),
            'user' => $user,
        ], 200);

    } catch (Exception $e) {
        return response()->json(['error' => 'Unable to authenticate.'], 500);
    }
}

    
}
