<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogApiController extends Controller
{
    public function index()
    {
        $logs = DB::table('activity_logs as al')
            ->leftJoin('users as u', 'al.user_id', '=', 'u.id')
            ->select('al.*', 'u.full_name as user_name')
            ->orderByDesc('al.created_at')
            ->limit(50)
            ->get();
        return response()->json(['success' => true, 'data' => $logs]);
    }
}
