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
            'name' => 'required|string|max:255|unique:suppliers,name,' . ($request->id ?? 'NULL'),
            'contact_person' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        if (isset($data['id'])) {
            $supplier = Supplier::find($data['id']);
            $supplier->update($data);
        } else {
            $supplier = Supplier::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Supplier saved successfully',
            'data' => $supplier
        ]);
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier archived successfully'
        ]);
    }
}
