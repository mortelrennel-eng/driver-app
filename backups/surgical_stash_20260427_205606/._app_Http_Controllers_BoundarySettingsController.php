<?php

namespace App\Http\Controllers;

use App\Models\BoundaryRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoundarySettingsController extends Controller
{
    public function index()
    {
        $rules = BoundaryRule::orderBy('start_year', 'asc')->get();
        return view('settings.boundary-rules', compact('rules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'start_year' => 'required|integer|min:2000|max:2099',
            'end_year' => 'required|integer|min:2000|max:2099|gte:start_year',
            'regular_rate' => 'required|numeric|min:0',
            'sat_discount' => 'required|numeric|min:0',
            'sun_discount' => 'required|numeric|min:0',
            'coding_rate' => 'required|numeric|min:0',
            'coding_is_fixed' => 'required|boolean',
        ]);

        BoundaryRule::create($request->all());

        return redirect()->route('boundary-rules.index')->with('success', 'Boundary rule added successfully!');
    }

    public function update(Request $request, $id)
    {
        $rule = BoundaryRule::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'start_year' => 'required|integer|min:2000|max:2099',
            'end_year' => 'required|integer|min:2000|max:2099|gte:start_year',
            'regular_rate' => 'required|numeric|min:0',
            'sat_discount' => 'required|numeric|min:0',
            'sun_discount' => 'required|numeric|min:0',
            'coding_rate' => 'required|numeric|min:0',
            'coding_is_fixed' => 'required|boolean',
        ]);

        $rule->update($request->all());

        return redirect()->route('boundary-rules.index')->with('success', 'Boundary rule updated successfully!');
    }

    public function destroy($id)
    {
        $rule = BoundaryRule::findOrFail($id);
        $rule->delete();

        return redirect()->route('boundary-rules.index')->with('success', 'Boundary rule deleted successfully!');
    }
}
