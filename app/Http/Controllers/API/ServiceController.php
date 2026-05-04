<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // GET /api/services
    public function index()
    {
        $services = Service::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $services
        ]);
    }

    // GET /api/services/{service}
    public function show(Service $service)
    {
        return response()->json([
            'status' => 'success',
            'data' => $service
        ]);
    }

    // POST /api/services
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price'  => 'required|numeric|min:0',
        ]);

        $service = Service::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $service
        ], 201);
    }

    // PUT /api/services/{service}
    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'base_price'  => 'sometimes|required|numeric|min:0',
        ]);

        $service->update($data);

        return response()->json([
            'status' => 'success',
            'data' => $service
        ]);
    }

    // DELETE /api/services/{service}
    public function destroy(Service $service)
    {
        $service->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Service deleted successfully'
        ]);
    }

    // GET /api/services/{service}/contracts
    public function contracts(Service $service)
    {
        $service->load('contracts');

        return response()->json([
            'status' => 'success',
            'data' => $service->contracts
        ]);
    }
}