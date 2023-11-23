<?php

namespace App\Http\Controllers\Api\Teams;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Validator;

class TeamMemberController extends Controller
{
    public function getTeamMembers($id)
    {
        $teamMembers = TeamMember::where(['team_id' => $id])->get();
        return response()->json(['success' => true, 'members' => $teamMembers], $this->successStatus);
    }

    public function addUserToTeam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'team_id' => 'required|numeric',
            'user_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], $this->badRequestStatus);
        }

        $data = $request->all();

        $team = TeamMember::create($data);

        if (null !== $team) {
            return response()->json(['success' => true, 'message' => 'Usuario agregado al equipo correctamente.'], $this->successStatus);
        } else {
            return response()->json(['success' => false, 'message' => 'Error al agregar jugador al equipo.'], $this->internalServerErrorStatus);
        }
    }

    public function dropUserFromTeam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'team_id' => 'required|numeric',
            'user_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], $this->badRequestStatus);
        }

        $teamMember = TeamMember::where(['user_id' => $request->post('user_id'), 'team_id' => $request->post('team_id')])->first();

        if ($teamMember->delete()) {
            return response()->json(['success' => true, 'message' => 'Usuario eliminado del equipo correctamente.'], $this->successStatus);
        } else {
            return response()->json(['success' => false, 'message' => 'Error al eliminar jugador del equipo.'], $this->internalServerErrorStatus);
        }
    }
}
