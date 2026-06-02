<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    private function extractDocumentId(string $value): ?int
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (preg_match('/\/documents\/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    public function show($id)
    {
        $document = Document::findOrFail($id);
        /** @var User $user */
        $user = Auth::user();
        
        return view('documents.show', compact('document', 'user'));
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'document_ref' => 'required|string|max:255',
        ]);

        $documentId = $this->extractDocumentId($validated['document_ref']);

        if (! $documentId || ! Document::whereKey($documentId)->exists()) {
            return back()->withErrors([
                'document_ref' => 'ID atau link dokumen tidak valid.',
            ]);
        }

        return redirect()->route('documents.show', $documentId);
    }
    
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $document = $user->documents()->create([
            'title' => $request->title ?: 'Untitled Document'
        ]);
        
        return redirect()->route('documents.show', $document->id);
    }

    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $document->update([
            'title' => $request->title,
        ]);

        return response()->json([
            'status' => 'updated',
            'title' => $document->title,
        ]);
    }

    public function versions($id)
    {
        // Ambil 10 versi terakhir
        $versions = \App\Models\DocumentVersion::where('document_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'created_at', 'user_id']);

        return response()->json($versions);
    }

    public function restore($id, $versionId)
    {
        $version = \App\Models\DocumentVersion::findOrFail($versionId);

        return response()->json([
            'state' => json_decode($version->state, true),
            'message' => 'Version restored',
        ]);
    }
}