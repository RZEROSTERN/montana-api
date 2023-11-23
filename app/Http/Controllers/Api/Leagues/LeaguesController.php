<?php

namespace App\Http\Controllers\Api\Leagues;

use App\Http\Controllers\Controller;
use App\Models\League;
use Validator;

class LeaguesController extends Controller
{
    public function createLeague(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'responsible_user_id' => 'required|numeric',
            'league_name' => 'required',
            'location' => 'required',
            'field' => 'required',
            'field_latitude' => 'required',
            'field_latitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], $this->badRequestStatus);
        }

        $data = $request->all();

        $league = League::create($data);

        if (null !== $league) {
            return response()->json(['success' => true, 'message' => 'Liga creada correctamente'], $this->successStatus);
        } else {
            return response()->json(['success' => false, 'message' => 'Error al crear la liga.'], $this->internalServerErrorStatus);
        }
    }

    public function getAllLeagues()
    {
        $leagues = League::all();

        return response()->json(['success' => true, 'data' => $leagues], $this->successStatus);
    }

    public function deleteLeague($id)
    {
        $league = League::where(['id' => $id])->first();

        if ($league->delete()) {
            return response()->json(['success' => true, 'message' => 'Liga eliminada correctamente'], $this->successStatus);
        } else {
            return response()->json(['success' => false, 'message' => 'Error al eliminar la liga'], $this->internalServerErrorStatus);
        }
    }

    public function updateLeague($id, Request $request)
    {
        $league = League::where(['id' => $id])->first();

        if (null === $league) {
            return response()->json(['success' => true, 'message' => 'Liga no encontrada']);
        }

        $validator = Validator::make($request->all(), [
            'responsible_user_id' => 'required|numeric',
            'league_name' => 'required',
            'location' => 'required',
            'field' => 'required',
            'field_latitude' => 'required',
            'field_latitude' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], $this->badRequestStatus);
        }

        $data = $request->all();
        if ($league->update($data)) {
            return response()->json(['success' => true, 'message' => 'Liga actualizada correctamente'], $this->successStatus);
        } else {
            return response()->json(['success' => false, 'message' => 'Error al actualizar la liga'], $this->internalServerErrorStatus);
        }
    }
}
