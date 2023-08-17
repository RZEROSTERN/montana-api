<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\User;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client as OClient;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    public function login()
    {
        $result = $this->getTokenAndRefreshToken(request(), request('email'), request('password'));

        if ($result['success'] == true) {
            return response()->json($result, $this->successStatus);
        } else {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], $this->unauthorizedStatus);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], $this->badRequestStatus);
        }

        $password = $request->password;
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $result = $this->getTokenAndRefreshToken($request, $user->email, $password);

        if ($result['success'] == true) {
            return response()->json($result, $this->successStatus);
        } else {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], $this->unauthorizedStatus);
        }
    }

    public function getTokenAndRefreshToken($request, $email, $password)
    {
        $oClient = OClient::where('password_client', env('DEFAULT_PASSWORD_CLIENT_ID', 1))->first();

        $request->request->add([
            'grant_type' => 'password',
            'client_id' => $oClient->id,
            'client_secret' => $oClient->secret,
            'username' => $email,
            'password' => $password,
            'scope' => '*',
        ]);

        $response = Route::dispatch(Request::create('oauth/token', 'POST'));

        $result = json_decode((string) $response->getContent(), true);
        $result['success'] = (isset($result['error'])) ? false : true;

        return $result;
    }

    public function refreshToken(Request $request)
    {
        $oClient = OClient::where('password_client', env('DEFAULT_PASSWORD_CLIENT_ID', 1))->first();

        $request->request->add([
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->header('Refreshtoken'),
            'client_id' => $oClient->id,
            'client_secret' => $oClient->secret,
            'scope' => '*',
        ]);

        $response = Route::dispatch(Request::create('oauth/token', 'POST'));
        $oauthResponse = json_decode($response->getContent(), true);

        return response()->json(['success' => true, 'access_token' => $oauthResponse['access_token'], 'refresh_token' => $oauthResponse['refresh_token']], $this->successStatus);
    }

    public function profile()
    {
        $user = Auth::user();
        $profile = Profile::id($user->id);

        if (null !== $profile) {
            return response()->json(["success" => true, "data" => $user], $this->successStatus);
        } else {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], $this->notFoundStatus);
        }
    }

    public function createProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'birth_date' => 'required|date',
                'bloodtype' => 'required|numeric',
                'phone' => 'required',
                'gender' => 'required|numeric',
                'country' => 'required|numeric',
                'state' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->errors()], $this->badRequestStatus);
            }

            $user = Auth::user();
            $profile = Profile::where(['user_id' => $user->id])->get();

            $data = $request->all();

            // Detect when an image is uploaded

            if (null !== $profile) {
                $profile = $profile->update($data);
            } else {
                $profile = Profile::create($data);
            }


            return response()->json(["success" => true, "message" => 'Perfil actualizado correctamente.'], $this->successStatus);
        } catch (QueryException $e) {
            return response()->json(["success" => false, "message" => 'Error al actualizar el perfil.'], $this->internalServerErrorStatus);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function unauthorized()
    {
        return response()->json(["Success" => false, "message" => "Unauthorized"], 401);
    }
}
