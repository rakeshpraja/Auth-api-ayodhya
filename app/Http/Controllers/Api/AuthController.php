<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;
use App\Models\Otp;
use App\Models\TempUser;
use App\Models\LoginActivity;
use Mail;
use App\Mail\CustomEmail;
use App\Mail\UpdateProfileVerifyEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;
use UAParser\Parser;
use Jenssegers\Agent\Agent;

use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register()
    {
        try {

            $validator = Validator::make(request()->all(), [
                'name' => 'required',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'name.required' => 'Please enter your name.',

                'email.required' => 'An email address is required.',
                'email.string' => 'Email must be a valid string.',
                'email.email' => 'Please enter a valid email address.',
                'email.max' => 'Email cannot exceed 255 characters.',


                'password.required' => 'A password is required.',
                'password.string' => 'Password must be a valid string.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'Password confirmation does not match.'
            ]);


            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }


            $user = new TempUser();
            $user->name = request()->name;
            $user->email = request()->email;
            $user->password = bcrypt(request()->password);
            $user->save();


            $details = [
                'expires_at' => 5,
                "otp" => rand(1111, 9999),
                'user' => $user,
            ];

            Otp::create([
                'user_id' => $user->id,
                'otp' => $details['otp'],
                'expires_at' => Carbon::now()->addMinutes(5),
            ]);

            Mail::to(request()->email)->send(new CustomEmail($details));


            return response()->json([
                'success' => 'Your registration has been completed. Please check your email and verify your registration.',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }


    public function login()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ], [
                'email.required' => 'Please enter your email address.',
                'email.email' => 'The email address must be a valid email format.',

                'password.required' => 'Please enter your password.',
                'password.string' => 'Password must be a valid string.',
                'password.min' => 'Password must be at least 6 characters long.'
            ]);


            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if (! $token = auth()->attempt($validator->validated())) {
                return response()->json([
                    'error' => 'Invalid email or password'
                ], 401);
            }

            $agent = new Agent();
            $user = Auth::user();

            $deviceType = $agent->isMobile() ? 'Mobile' : ($agent->isTablet() ? 'Tablet' : 'Desktop');
            $browserName = $agent->browser() ?: 'Unknown Browser';
            $browserVersion = $agent->version($browserName) ?: 'Unknown Version';
            $operatingSystem = $agent->platform() ?: 'Unknown OS';
            $osVersion = $agent->version($operatingSystem) ?: 'Unknown Version';
            $deviceName = $agent->device() ?: 'Unknown Device';

            LoginActivity::create([
                'Device_Type' => $deviceType,
                'Device_Name' => $deviceName,
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'operating_system' => $operatingSystem,
                'browser_name' => $browserName,
                'login_time' => Carbon::now(),
            ]);


            return $this->createNewToken($token);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updateProfile()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'name' => 'required',
                'email' => 'required|string|email|max:255|unique:users,email',
                'phone_number' => 'required|numeric',
                'pdf_file' => 'required|mimes:pdf|max:5242880',
                'image' => 'required|image|mimes:jpeg,png,jpg|max:5242880',
            ], [
                'name.required' => 'Please enter your name.',

                'email.required' => 'Please enter your email address.',
                'email.string' => 'The email must be a valid string.',
                'email.email' => 'Please enter a valid email address.',
                'email.max' => 'Email cannot exceed 255 characters.',
                'email.unique' => 'you have already registered with this email address.',

                'phone_number.required' => 'Please enter your phone number.',
                'phone_number.numeric' => 'The phone number must be a valid number.',

                'pdf_file.required' => 'Please upload a PDF file.',
                'pdf_file.mimes' => 'The file must be a PDF.',
                'pdf_file.max' => 'The PDF file size must not exceed 5MB.',

                'image.required' => 'Please upload an image.',
                'image.image' => 'The file must be a valid image.',
                'image.mimes' => 'The image must be in jpeg, png, or jpg format.',
                'image.max' => 'The image file size must not exceed 5MB.'
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 400);
            }

            $pdfFileName = null;
            $imageFileName = null;

            if (request()->hasFile('pdf_file')) {
                $pdfFile = request()->file('pdf_file');
                $pdfFileName = time() . '.' . $pdfFile->getClientOriginalExtension();
                $pdfFile->storeAs('pdfs', $pdfFileName, 'public');
            }

            if (request()->hasFile('image')) {
                $imageFile = request()->file('image');
                $imageFileName = time() . '.' . $imageFile->getClientOriginalExtension();
                $imageFile->storeAs('images', $imageFileName, 'public');
            }

            $user = User::where('id', Auth::user()->id)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Invalid Username...'
                ], 200);
            }

            $temp_user = new TempUser();
            $temp_user->user_id = $user->id;
            $temp_user->name = request()->name;
            $temp_user->email = request()->email;
            $temp_user->phone_number = request()->phone_number;
            $temp_user->pdf_file = $pdfFileName;
            $temp_user->image = $imageFileName;
            $temp_user->save();

            $details = [
                'expires_at' => Carbon::now()->addMinutes(5),
                "otp" => rand(1111, 9999),
                "token" => rand(11111, 99999),
                'user' => $user,
            ];

            Otp::create([
                'user_id' => $user->id,
                'otp' => $details['otp'],
                'expires_at' => Carbon::now()->addMinutes(5),
            ]);

            Mail::to($user->email)->send(new UpdateProfileVerifyEmail($details));

            return response()->json([
                'message' => 'An OTP has been sent to your registered email address to update your profile. Please enter the OTP to confirm and proceed with the profile update.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }


    public function logout()
    {
        try {
            $loginActivity = LoginActivity::where('user_id', Auth::user()->id)
                ->latest()
                ->first();

            if ($loginActivity) {
                $loginActivity->update(["logout_time" => Carbon::now()]);
            }
            auth()->guard('api')->logout();
            return response()->json([
                'message' => 'User successfully signed out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }


    protected function createNewToken($token)
    {
        try {
            return response()->json([
                'success' => "You have successfully logged in. Enjoy your session!",
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }


    public function verify()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'otp' => 'required',
                'email' => 'required|string|email|max:255|unique:users,email',

            ], [
                'otp.required' => 'Please enter the OTP.',

                'email.required' => 'Please enter your email address.',
                'email.string' => 'The email must be a valid string.',
                'email.email' => 'Please enter a valid email address.',
                'email.max' => 'Email cannot exceed 255 characters.',
                'email.unique' => 'you have already registered with this email address.',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 400);
            }

            $temp_user = TempUser::where('email', request()->email)->latest()->first();

            if (!$temp_user) {
                return response()->json([
                    'error' => 'Invalid Username...'
                ], 404);
            }

            $otp = Otp::where('otp', request()->otp)
                ->where('user_id', $temp_user->id)
                ->latest()
                ->first();

            if (!$otp) {
                return response()->json([
                    'error' => 'Invalid OTP'
                ], 400);
            }

            if ($otp && $otp->expires_at && Carbon::now()->greaterThan($otp->expires_at)) {
                return response()->json([
                    'error' => 'OTP has expired'
                ], 400);
            }

            Mail::send('mail.verify_register', ['user' => $temp_user], function ($message) use ($temp_user) {
                $message->to($temp_user->email);
                $message->subject('Verified Registration');
            });


            $user = new User();
            $user->name = $temp_user->name;
            $user->email = $temp_user->email;
            $user->password = $temp_user->password;
            $user->save();
            $otpDel = Otp::where('user_id', $temp_user->id)->get();
            foreach ($otpDel as $del) {
                $del->delete();
            }
            $tempDel = TempUser::where('email', request()->email)->get();
            foreach ($tempDel as $del) {
                $del->delete();
            }
            return response()->json([
                'success' => 'your registration verification has been successful'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }


    public function verifyUpdateProfile()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'otp' => 'required',

            ], [
                'otp.required' => 'Please enter the OTP.',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = User::find(Auth::user()->id);
            if (!$user) {
                return response()->json([
                    'error' => 'Invalid Username...'
                ], 404);
            }

            $otp = Otp::where('otp', request()->otp)
                ->where('user_id', Auth::user()->id)
                ->latest()
                ->first();

            if (!$otp) {
                return response()->json([
                    'error' => 'Invalid OTP'
                ], 400);
            }

            if (Carbon::now()->greaterThan($otp->expires_at)) {
                return response()->json([
                    'error' => 'OTP has expired'
                ], 400);
            }

            $temp_user = TempUser::where('user_id', $otp->user_id)->latest()->first();
            if (!$temp_user) {
                return response()->json([
                    'error' => 'Not found'
                ], 400);
            }

            $user->name = $temp_user->name;
            $user->phone_number = $temp_user->phone_number;
            $user->email = $temp_user->email;
            $user->pdf_file = $temp_user->pdf_file;
            $user->image = $temp_user->image;
            $user->save();
            $otpDel = Otp::where('user_id', Auth::user()->id)->get();
            foreach ($otpDel as $del) {
                $del->delete();
            }
            $tempDel = TempUser::where('user_id', Auth::user()->id)->get();
            foreach ($tempDel as $del) {
                $del->delete();
            }
            return response()->json([
                'success' => 'Your profile has been successfully updated'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function forgotPassword()
    {
        try {

            $validator = Validator::make(request()->all(), [
                'email' => 'required|string|email|max:255',
            ], [
                'email.required' => 'Please enter your email address.',
                'email.string' => 'The email must be a valid string.',
                'email.email' => 'Please enter a valid email address.',
                'email.max' => 'Email cannot exceed 255 characters.',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = User::where('email', request()->email)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }


            $otp = rand(1111, 9999);


            DB::table('password_resets')->insert([
                'email' => request()->email,
                'otp' => $otp,
                'created_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addMinutes(5),
            ]);


            Mail::send('mail.password_reset', ['otp' => $otp, 'expires_at' => 5, 'user' => $user], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Reset Password Request');
            });

            return response()->json([
                'message' => 'Reset password email sent.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword()
    {
        try {

            $validator = Validator::make(request()->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6',
                'otp' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = User::where('email', request()->email)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'Invalid Username...'
                ], 404);
            }
            $passwordReset = DB::table('password_resets')
                ->where('email', request()->email)->latest()
                ->first();

            if (Carbon::now()->greaterThan($passwordReset->expires_at)) {
                return response()->json([
                    'error' => 'OTP has expired'
                ], 400);
            }

            if (request()->otp != $passwordReset->otp) {
                return response()->json([
                    'message' => 'Invalid token'
                ], 400);
            }
            $user->password = Hash::make(request()->password);
            $user->save();
            DB::table('password_resets')->where('email', request()->email)->delete();

            return response()->json([
                'message' => 'Password reset successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'status' => 'success',
                'token' => $newToken,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not refresh token',
            ], 400);
        }
    }

    public function getProfile(Request $request)
    {
        try {
            $userGet = Auth::user();

            return response()->json([
                'status' => 'success',
                'Profile' => $userGet,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not refresh token',
            ], 400);
        }
    }
}
