<?php

namespace App\Http\Controllers\Api\Leagues;

use App\Http\Controllers\Controller;
use Validator;

class LeaguesController extends Controller
{
    public function createLeague(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'league_name' => 'required',
            'location' => 'required',
            'field' => 'required',
            'field_latitude' => 'required',
            'field_latitude' => 'required',
        ]);
    }

    public function getLeagues()
    {
    }
}
