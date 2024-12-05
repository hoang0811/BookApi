<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class AuthController extends BaseController
{
    public function register(Request $request)
    {

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
        $user->sendEmailVerificationNotification();

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


    public function refresh()
    {
        $success = $this->respondWithToken(auth()->refresh());
        return $this->sendResponse($success, 'Profile fetch successfully.');
    }

    public function logout()
    {
        auth()->logout();
        return $this->sendResponse([], 'Successfully logged out.');
    }


    public function profile()
    {
        $success = auth()->user();
        return $this->sendResponse($success, 'Profile fetch successfully.');
    }
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ];
    }
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? $this->sendResponse([], 'Reset link sent successfully.')
            : $this->sendError('Error', ['error' => __($status)]);
    }
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return $this->sendResponse($user, 'Profile updated successfully.');
    }


    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->sendResponse([], 'Password reset successfully.')
            : $this->sendError('Error', ['error' => __($status)]);
    }

    public function redirectToGoogle()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url]);
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
                ->user();

            $user = User::updateOrCreate(
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
    public function redirectToFacebook()
    {
        $url = Socialite::driver('facebook')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url]);
    }


    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')
                ->stateless()
                ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
                ->user();  // Use `user()` instead of `redirect()`, as `redirect()` is used for the initial redirect

            // Check if email is available
            $email = $facebookUser->getEmail();
            if (!$email) {
                // Handle the case where email is not provided (e.g., request the user to enter it manually)
                return response()->json(['error' => 'Email is required, but not provided by Facebook.'], 400);
            }

            // Create or update the user
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $facebookUser->getName(),
                    'facebook_id' => $facebookUser->getId(),
                    'password' => bcrypt(uniqid()), // Random password for non-Facebook login
                ]
            );

            // Login the user and create a token
            $token = auth()->login($user);

            return response()->json([
                'access_token' => $this->respondWithToken($token),
                'user' => $user,
            ], 200);
        } catch (Exception $e) {
            Log::error('Facebook Auth Error: ' . $e->getMessage());

            return response()->json(['error' => 'Unable to authenticate.'], 500);
        }
    }
}
