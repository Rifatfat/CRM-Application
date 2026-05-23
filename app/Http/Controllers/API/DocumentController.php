<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    private function authorizeDocument(Document $document, Request $request): void
    {
        if ((int) $document->client?->user_id !== (int) $request->user()->id) {
            abort(403, 'Unauthorized');
        }
    }

    public function index(Request $request)
    {
        $documents = Document::with(['client', 'contract.service', 'uploader'])
            ->ownedBy((int) $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $documents
        ]);
    }

    public function show(Request $request, Document $document)
    {
        $document->load(['client', 'contract.service', 'uploader']);
        $this->authorizeDocument($document, $request);

        return response()->json([
            'status' => 'success',
            'data' => $document
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where('user_id', $request->user()->id),
            ],
            'contract_id' => [
                'required',
                Rule::exists('contracts', 'id')->where(function ($query) use ($request) {
                    $query->whereIn('client_id', function ($clientQuery) use ($request) {
                        $clientQuery->select('id')
                            ->from('clients')
                            ->where('user_id', $request->user()->id);
                    });
                }),
            ],
            'file_name' => 'required|string',
            'file_path' => 'required|string',
            'document_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $contract = Contract::ownedBy((int) $request->user()->id)->findOrFail($validated['contract_id']);

        if ((int) $contract->client_id !== (int) $validated['client_id']) {
            return response()->json([
                'status' => 'error',
                'message' => 'The selected contract does not belong to the selected client.'
            ], 422);
        }

        $document = Document::create([
            ...$validated,
            'uploaded_by' => $request->user()->id,
            'uploaded_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Document uploaded',
            'data' => $document
        ], 201);
    }

    public function destroy(Request $request, Document $document)
    {
        $document->load('client');
        $this->authorizeDocument($document, $request);
        $document->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Document deleted'
        ]);
    }
}
