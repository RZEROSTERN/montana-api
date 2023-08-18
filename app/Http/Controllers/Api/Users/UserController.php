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
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\App;

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
        $input['password'] = bcrypt($password);
        $user = User::create($input);

        if (null !== $user) {
            $result = $this->getTokenAndRefreshToken($request, $user->email, $password);

            if ($result['success'] == true) {
                return response()->json($result, $this->successStatus);
            } else {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], $this->unauthorizedStatus);
            }
        }
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

    public function refreshToken(Request $request)
    {
        $oClient = OClient::where('password_client', 1)->first();

        $body = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->header('Refreshtoken'),
            'client_id' => $oClient->id,
            'client_secret' => $oClient->secret,
            'scope' => '*',
        ];

        if (App::runningUnitTests()) {
            $body['client_id'] = "5";
            $body['client_secret'] = "T7gtfKg2YvAXaPmFimlY68ktHs5lGxWoDiYbDIvX";
            $client = new HttpClient();

            try {
                $response = $client->post(env('APP_URL') . 'oauth/token', [
                    RequestOptions::JSON => $body,
                ]);

                if ($response->getStatusCode() == 200) {
                    $result = json_decode((string) $response->getBody()->getContents(), true);
                    $result['success'] = true;
                } else {
                    $result['success'] = false;
                }

                return $result;
            } catch (RequestException $e) {
                $result['success'] = false;
                $result['message'] = "Access denied";
                return $result;
            }
        } else {
            $request->request->add($body);
            $proxy = Request::create('oauth/token', 'POST', $body);
            $response = Route::dispatch($proxy);

            $oauthResponse = json_decode($response->getContent(), true);
            return response()->json(['success' => true, 'access_token' => $oauthResponse['access_token'], 'refresh_token' => $oauthResponse['refresh_token']], $this->successStatus);
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

    private function getTokenAndRefreshToken($request, $email, $password)
    {
        $oClient = OClient::where('password_client', 1)->first();

        $body = [
            'grant_type' => 'password',
            'client_id' => $oClient->id,
            'client_secret' => $oClient->secret,
            'username' => $email,
            'password' => $password,
            'scope' => '*',
        ];

        if (App::runningUnitTests()) {
            $body['client_id'] = "5";
            $body['client_secret'] = "T7gtfKg2YvAXaPmFimlY68ktHs5lGxWoDiYbDIvX";
            $client = new HttpClient();

            try {
                $response = $client->post(env('APP_URL') . 'oauth/token', [
                    RequestOptions::JSON => $body,
                ]);

                if ($response->getStatusCode() == 200) {
                    $result = json_decode((string) $response->getBody()->getContents(), true);
                    $result['success'] = true;
                } else {
                    $result['success'] = false;
                }

                return $result;
            } catch (RequestException $e) {
                $result['success'] = false;
                $result['message'] = "Access denied";
                return $result;
            }
        } else {
            $request->request->add($body);
            $proxy = Request::create('oauth/token', 'POST', $body);
            $response = Route::dispatch($proxy);

            $result = json_decode((string) $response->getContent(), true);
            $result['success'] = (isset($result['error'])) ? false : true;

            return $result;
        }
    }
}
