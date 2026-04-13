<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // GET /api/services - Get all services
    public function index()
    {
        $services = Service::all();
        return response()->json($services, 200);
    }

    // POST /api/services - Create new service
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price'  => 'required|numeric|min:0',
        ]);

        $service = Service::create($request->all());
        return response()->json($service, 201);
    }

    // GET /api/services/{id} - Get one service
    public function show($id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service, 200);
    }

    // PUT /api/services/{id} - Update service
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $service->update($request->all());
        return response()->json($service, 200);
    }

    // DELETE /api/services/{id} - Delete service
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return response()->json(['message' => 'Service deleted successfully'], 200);
    }

    // GET /api/services/{id}/contracts
    public function getContracts($id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service->contracts, 200);
    }
}