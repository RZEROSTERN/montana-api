<?php

namespace App\Http\Controllers\Api\Teams;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Validator;

class TeamsController extends Controller
{
    public function createTeam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'team_name' => 'required',
            'foundation_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], $this->badRequestStatus);
        }

        $user = Auth::user();

        $data = [
            'captain_user_id' => $user->id,
        ];

        $data = array_merge($data, $request->all());

        // Workflow for uploading pictures

        $team = Team::create($data);

        if (null !== $team) {
            return response()->json(['success' => true, 'message' => 'Equipo creado correctamente.'], $this->successStatus);
        } else {
            return response()->json(['success' => false, 'message' => 'Error al crear el equipo.'], $this->internalServerErrorStatus);
        }
    }

    public function getTeamsByUser()
    {
        $user = Auth::user();
        $teams = Team::where(['captain_user_id' => $user->id])->get();
        return response()->json(['success' => true, 'data' => $teams], $this->successStatus);
    }

    public function getTeamById($id)
    {
        $team = Team::where(['id' => $id])->get();

        if (null !== $team) {
            return response()->json(['success' => true, 'data' => $team], $this->successStatus);
        } else {
            return response()->json(['success' => false, 'message' => 'No se encontró el equipo.'], $this->notFoundStatus);
        }
    }

    public function updateTeam($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'team_name' => 'required',
            'foundation_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], $this->badRequestStatus);
        }

        $user = Auth::user();
        $team = Team::where(['id' => $id])->first();

        if (null !== $team) {
            if ($team->captain_user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Acceso denegado. No eres el capitán del equipo o un administrador.'], 401);
            }

            $team->team_name = $request->post('team_name');
            $team->foundation_date = $request->post('foundation_date');
            $team->brochure = $request->post('brochure');

            if ($team->save()) {
                return response()->json(['success' => true, 'message' => 'Equipo actualizado correctamente.'], $this->successStatus);
            } else {
                return response()->json(['success' => false, 'message' => 'Error al actualizar el equipo'], $this->internalServerErrorStatus);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No se encontró el equipo.'], $this->notFoundStatus);
        }
    }

    public function deleteTeam($id)
    {
        $user = Auth::user();
        $team = Team::where(['id' => $id])->first();

        if (null !== $team) {
            if ($team->captain_user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Acceso denegado. No eres el capitán del equipo o un administrador.'], 401);
            }

            if ($team->delete()) {
                return response()->json(['success' => true, 'message' => 'Equipo eliminado correctamente.'], $this->successStatus);
            } else {
                return response()->json(['success' => false, 'message' => 'Error al actualizar el equipo'], $this->internalServerErrorStatus);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No se encontró el equipo.'], $this->notFoundStatus);
        }
    }
}
