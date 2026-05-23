<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    private function authorizeService(Service $service, Request $request): void
    {
        if ((int) $service->user_id !== (int) $request->user()->id) {
            abort(403, 'Unauthorized');
        }
    }

    // GET /api/services
    public function index(Request $request)
    {
        $services = Service::ownedBy((int) $request->user()->id)->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $services
        ]);
    }

    // GET /api/services/{service}
    public function show(Request $request, Service $service)
    {
        $this->authorizeService($service, $request);

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

        $data['user_id'] = $request->user()->id;

        $service = Service::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $service
        ], 201);
    }

    // PUT /api/services/{service}
    public function update(Request $request, Service $service)
    {
        $this->authorizeService($service, $request);

        if ($service->contracts()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service cannot be changed while contracts are using it.'
            ], 409);
        }

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
    public function destroy(Request $request, Service $service)
    {
        $this->authorizeService($service, $request);

        if ($service->contracts()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service cannot be deleted while contracts are using it.'
            ], 409);
        }

        $service->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Service deleted successfully'
        ]);
    }

    // GET /api/services/{service}/contracts
    public function contracts(Request $request, Service $service)
    {
        $this->authorizeService($service, $request);

        $contracts = $service->contracts()
            ->with(['client', 'service'])
            ->ownedBy((int) $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $contracts
        ]);
    }
}
