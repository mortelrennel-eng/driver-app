<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = DB::table('office_expenses')->whereNull('deleted_at')->orderByDesc('date')->get();
        return response()->json(['success' => true, 'data' => $expenses]);
    }

    public function store(Request $request)
    {
        $id = DB::table('office_expenses')->insertGetId([
            'title' => $request->title,
            'amount' => $request->amount,
            'date' => $request->date,
            'category' => $request->category,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json(['success' => true, 'id' => $id]);
    }

    public function approve($id)
    {
        DB::table('office_expenses')->where('id', $id)->update(['status' => 'approved', 'updated_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function reject($id)
    {
        DB::table('office_expenses')->where('id', $id)->update(['status' => 'rejected', 'updated_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        DB::table('office_expenses')->where('id', $id)->update(['deleted_at' => now()]);
        return response()->json(['success' => true]);
    }
}
