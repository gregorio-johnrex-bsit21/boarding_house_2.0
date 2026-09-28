<?php
namespace App\Http\Controllers;

use App\Models\Bed;

class MapApiController extends Controller
{
    /** { "R15_Bed1_A": "occupied", ... }  — same keys the 3D map uses. */
    public function statuses()
    {
        return response()->json(Bed::pluck('status', 'mesh_id'));
    }
}
