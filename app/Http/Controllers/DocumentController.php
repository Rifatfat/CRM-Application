<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'contract_id' => 'required|exists:contracts,id',
            'uploaded_by' => 'required|exists:users,id',
            'file_name' => 'required|string',
            'file_path' => 'required|string',
            'document_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $document = Document::create([
            ...$validator->validated(),
            'uploaded_at' => now()
        ]);

        return response()->json([
            'message' => 'Document uploaded',
            'data' => $document
        ], 201);
    }

    public function destroy($id)
    {
        Document::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Document deleted'
        ]);
    }
}