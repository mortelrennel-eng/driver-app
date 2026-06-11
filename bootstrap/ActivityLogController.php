<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer|exists:suppliers,id',
            'name' => 'required|string|max:35|unique:suppliers,name,' . ($request->id ?? 'NULL'),
            'contact_person' => 'nullable|string|max:25',
            'phone_number' => 'nullable|string|regex:/^09\d{9}$/',
            'address' => 'nullable|string',
        ]);

        if (isset($data['id'])) {
            $supplier = Supplier::find($data['id']);
            $supplier->update($data);
        } else {
            $supplier = Supplier::create($data);
        }

        system_log((isset($data['id']) ? 'Updated Supplier' : 'Created Supplier'), "Supplier: {$supplier->name}\nContact: {$supplier->contact_person}");

        return response()->json([
            'success' => true,
            'message' => 'Supplier saved successfully',
            'data' => $supplier
        ]);
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $name = $supplier->name;
        $supplier->delete();

        system_log('Archived Supplier', "Supplier: {$name} moved to archive.");

        return response()->json([
            'success' => true,
            'message' => 'Supplier archived successfully'
        ]);
    }
}
