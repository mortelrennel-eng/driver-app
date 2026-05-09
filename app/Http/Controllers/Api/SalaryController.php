<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryController extends Controller
{
    public function index()
    {
        $salaries = DB::table('salaries as s')
            ->leftJoin('staff as st', 's.staff_id', '=', 'st.id')
            ->whereNull('s.deleted_at')
            ->select('s.*', 'st.full_name as staff_name')
            ->get();
        return response()->json(['success' => true, 'data' => $salaries]);
    }
}
