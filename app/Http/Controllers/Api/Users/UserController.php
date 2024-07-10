<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'device_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], $this->badRequestStatus);
        }

        $result = $this->getToken(request('email'), request('password'), request('device_id'));

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
            'device_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], $this->badRequestStatus);
        }

        $password = $request->password;
        $input = $request->all();
        $input['password'] = bcrypt($password);
        $user = User::create($input);

        if (null !== $user) {
            $result = $this->getToken($user->email, $password, $request->device_id);

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
        $profile = Profile::find($user->id);

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
            $profile = Profile::where(['user_id' => $user->id])->first();

            $data = [
                'user_id' => $user->id,
            ];

            $dataInsert = array_merge($data, $request->all());

            // Detect when an image is uploaded

            if (null !== $profile) {
                $profile = $profile->update($dataInsert);
            } else {
                $profile = Profile::create($dataInsert);
            }


            return response()->json(["success" => true, "message" => 'Perfil actualizado correctamente.'], $this->successStatus);
        } catch (QueryException $e) {
            var_dump($e->getMessage());
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

    private function getToken($email, $password, $deviceID)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return ['success' => false, 'message' => 'Correo electrónico o contraseña incorrectas'];
        }

        return [
            'success' => true,
            'access_token' => $user->createToken($deviceID)->plainTextToken
        ];
    }
}
