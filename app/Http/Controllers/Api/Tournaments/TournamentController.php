<?php

namespace App\Http\Controllers\Api\Tournaments;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tournament;

class TournamentController extends Controller
{
    public function getTournamentsByLeague($leagueID)
    {
        $tournaments = Tournament::where(['league_id' => $leagueID])->get();

        return response()->json(['success' => true, 'data' => $tournaments], 200);
    }
}
