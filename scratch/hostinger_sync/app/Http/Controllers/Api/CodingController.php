<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CodingController extends Controller
{
    public function index()
    {
        $units = DB::table('units')->whereNull('deleted_at')->get();
        return response()->json(['success' => true, 'units' => $units]);
    }
}
