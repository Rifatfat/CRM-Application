<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use Illuminate\Http\Request;

class CommunicationLogController extends Controller
{
    // GET /api/communication-logs - Get all logs
    public function index()
    {
        $logs = CommunicationLog::all();
        return response()->json($logs, 200);
    }

    // POST /api/communication-logs - Create new log
    public function store(Request $request)
    {
        $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'user_id'            => 'required|exists:users,id',
            'communication_type' => 'required|string|max:255',
            'notes'              => 'nullable|string',
            'communication_date' => 'required|date',
        ]);

        $log = CommunicationLog::create($request->all());
        return response()->json($log, 201);
    }

    // GET /api/communication-logs/{id} - Get one log
    public function show($id)
    {
        $log = CommunicationLog::findOrFail($id);
        return response()->json($log, 200);
    }

    // PUT /api/communication-logs/{id} - Update log
    public function update(Request $request, $id)
    {
        $log = CommunicationLog::findOrFail($id);
        $log->update($request->all());
        return response()->json($log, 200);
    }

    // DELETE /api/communication-logs/{id} - Delete log
    public function destroy($id)
    {
        $log = CommunicationLog::findOrFail($id);
        $log->delete();
        return response()->json(['message' => 'Communication log deleted successfully'], 200);
    }

    // GET /api/communication-logs/{id}/client
    public function getClient($id)
    {
        $log = CommunicationLog::findOrFail($id);
        return response()->json($log->client, 200);
    }

    // GET /api/communication-logs/{id}/user
    public function getUser($id)
    {
        $log = CommunicationLog::findOrFail($id);
        return response()->json($log->user, 200);
    }
}