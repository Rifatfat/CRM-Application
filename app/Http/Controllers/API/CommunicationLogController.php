<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunicationLogController extends Controller
{
    private function authorizeLog(CommunicationLog $log, Request $request): void
    {
        if ((int) $log->client?->user_id !== (int) $request->user()->id) {
            abort(403, 'Unauthorized');
        }
    }

    // GET /api/communication-logs - Get all logs
    public function index(Request $request)
    {
        $logs = CommunicationLog::with(['client', 'user'])
            ->ownedBy((int) $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }

    // POST /api/communication-logs - Create new log
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'          => [
                'required',
                Rule::exists('clients', 'id')->where('user_id', $request->user()->id),
            ],
            'communication_type' => 'required|string|max:255',
            'notes'              => 'nullable|string',
            'communication_date' => 'required|date',
        ]);

        $log = CommunicationLog::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $log
        ], 201);
    }

    // GET /api/communication-logs/{id} - Get one log
    public function show(Request $request, CommunicationLog $log)
    {
        $log->load(['client', 'user']);
        $this->authorizeLog($log, $request);

        return response()->json([
            'status' => 'success',
            'data' => $log
        ]);
    }

    // PUT /api/communication-logs/{id} - Update log
    public function update(Request $request, CommunicationLog $log)
    {
        $log->load('client');
        $this->authorizeLog($log, $request);

        $data = $request->validate([
            'client_id'          => [
                'sometimes',
                Rule::exists('clients', 'id')->where('user_id', $request->user()->id),
            ],
            'communication_type' => 'sometimes|required|string|max:255',
            'notes'              => 'nullable|string',
            'communication_date' => 'sometimes|required|date',
        ]);

        $log->update($data);

        return response()->json([
            'status' => 'success',
            'data' => $log
        ]);
    }

    // DELETE /api/communication-logs/{id} - Delete log
    public function destroy(Request $request, CommunicationLog $log)
    {
        $log->load('client');
        $this->authorizeLog($log, $request);
        $log->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Communication log deleted successfully'
        ]);
    }

    // GET /api/communication-logs/{id}/client
    public function getClient(Request $request, CommunicationLog $log)
    {
        $log->load('client');
        $this->authorizeLog($log, $request);

        return response()->json([
            'status' => 'success',
            'data' => $log->client
        ]);
    }

    // GET /api/communication-logs/{id}/user
    public function getUser(Request $request, CommunicationLog $log)
    {
        $log->load(['client', 'user']);
        $this->authorizeLog($log, $request);

        return response()->json([
            'status' => 'success',
            'data' => $log->user
        ]);
    }
}
