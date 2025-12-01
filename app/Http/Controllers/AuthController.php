<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterStoreRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        try {
            if (! Auth::guard('web')->attempt($request->only('email', 'password'))) {
                return \response()->json([
                    'message' => 'Unauthorized',
                    'data' => 'null',
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return \response()->json([
                'message' => 'Login Success',
                'data' => [
                    'token' => $token,
                    'user' => new UserResource($user),
                ],
            ], 200);
        } catch (Exception $th) {
            // throw $th;
            return \response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function me()
    {
        try {
            $user = Auth::user();
            return \response()->json([
                'message' => 'Profile User Berhasil Diambil',
                'data' => new UserResource(($user))
            ], 200);
        } catch (\Exception $th) {
            //throw $th;
            return \response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        try {
            $user = Auth::user();
            $user->currentAccessToken()->delete();

            return \response()->json([
                'message' => 'Log Out Berhasil',
                'data' => null
            ], 200);
        } catch (\Exception $th) {
            //throw $th;
            return \response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function register(RegisterStoreRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();
            return response()->json([
                'message' => 'Registerasi berhasil',
                'data' => [
                    'token' => $token,
                    'user' => new UserResource($user)
                ]
            ]);
        } catch (Exception $th) {
            //throw $th;
            return \response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
