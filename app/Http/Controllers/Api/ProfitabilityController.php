<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitabilityController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'message' => 'Profitability API under development']);
    }
}
