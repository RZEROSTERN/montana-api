<?php

namespace App\Http\Controllers\Users;

use App\Models\User; 
use Validator;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client as OClient;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

class UserController extends Controller
{
    public $successStatus = 200;

    /**
     * @OA\Post(
     *      path="/login",
     *      operationId="login",
     *      tags={"Auth"},
     *      summary="Login",
     *      description="Login to Montana",
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      ),
     *      @OA\Response(response=400, description="Bad Request. Invalid Data"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function login() { 
        $result = $this->getTokenAndRefreshToken(request(), request('email'), request('password'));
        
        if($result['success'] == true) {
            return response()->json($result, 200);
        } else { 
            return response()->json(['success' => false, 'error'=>'Unauthorized'], 401); 
        }
    }

    /**
     * @OA\Post(
     *      path="/register",
     *      operationId="register",
     *      tags={"Auth"},
     *      summary="Register",
     *      description="Register to Montana",
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="c_password",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      ),
     *      @OA\Response(response=400, description="Bad Request. Invalid Data"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function register(Request $request) { 
        $validator = Validator::make($request->all(), [ 
            'email' => 'required|email|unique:users', 
            'password' => 'required', 
            'c_password' => 'required|same:password', 
        ]);

        if ($validator->fails()) { 
            return response()->json(['success' => false, 'error'=>$validator->errors()], 401);            
        }

        $password = $request->password;
        $input = $request->all(); 
        $input['password'] = bcrypt($input['password']); 
        $user = User::create($input); 

        $result = $this->getTokenAndRefreshToken($request, $user->email, $password);

        if($result['success'] == true) {
            return response()->json($result, 200);
        } else { 
            return response()->json(['success' => false, 'error'=>'Unauthorized'], 401); 
        }
    }

    public function getTokenAndRefreshToken($request, $email, $password) { 
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

    public function refreshToken(Request $request) { 
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

        return response()->json(['success' => true, 'access_token' => $oauthResponse['access_token'], 'refresh_token' => $oauthResponse['refresh_token']], 200);
    }

    /**
     *  @OA\Post(
     *      path="/user/profile",
     *      operationId="user-profile",
     *      tags={"User"},
     *      summary="User Profile",
     *      description="Get user's profile",
     * 
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=400, description="Bad Request. Invalid Data"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function profile() { 
        $user = Auth::user(); 
        return response()->json($user, $this->successStatus); 
    } 

    public function logout(Request $request) {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function unauthorized() { 
        return response()->json("unauthorized", 401); 
    } 
}
