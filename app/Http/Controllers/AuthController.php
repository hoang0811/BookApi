<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
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
    
}
