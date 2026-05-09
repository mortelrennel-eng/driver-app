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
            'name'           => 'required|string|max:100',
            'start_year'     => 'required|integer|min:2000|max:2099',
            'end_year'       => 'required|integer|min:2000|max:2099|gte:start_year',
            'regular_rate'   => 'required|numeric|min:1',
            'sat_discount'   => 'required|numeric|min:0',
            'sun_discount'   => 'required|numeric|min:0',
            'coding_rate'    => 'required|numeric|min:0',
            'coding_is_fixed'=> 'required|boolean',
        ]);

        BoundaryRule::create($request->all());

        system_log('Created Boundary Rule', "Rule: {$request->name}\nRange: {$request->start_year}-{$request->end_year}\nRegular Rate: ₱" . number_format($request->regular_rate, 2));

        return redirect()->route('boundary-rules.index')->with('success', 'Pricing bracket added successfully!');
    }

    public function update(Request $request, $id)
    {
        $rule = BoundaryRule::findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:100',
            'start_year'     => 'required|integer|min:2000|max:2099',
            'end_year'       => 'required|integer|min:2000|max:2099|gte:start_year',
            'regular_rate'   => 'required|numeric|min:1',
            'sat_discount'   => 'required|numeric|min:0',
            'sun_discount'   => 'required|numeric|min:0',
            'coding_rate'    => 'required|numeric|min:0',
            'coding_is_fixed'=> 'required|boolean',
        ]);

        $rule->update($request->all());

        system_log('Updated Boundary Rule', "Rule: {$rule->name}\nUpdated pricing configuration.");

        return redirect()->route('boundary-rules.index')->with('success', 'Pricing bracket updated successfully!');
    }

    public function destroy($id)
    {
        $rule = BoundaryRule::findOrFail($id);
        $name = $rule->name;
        $rule->delete(); // soft delete

        system_log('Archived Boundary Rule', "Rule: {$name} was archived.");

        // Stay on the same page
        return redirect()->route('boundary-rules.index')->with('success', "Pricing bracket \"{$name}\" has been archived successfully.");
    }
}
