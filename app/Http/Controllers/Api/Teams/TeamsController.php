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
            'team_name' => 'required|unique:teams,team_name',
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
}
