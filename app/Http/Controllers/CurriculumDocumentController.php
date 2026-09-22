<?php

namespace App\Http\Controllers;

use App\Models\CurriculumDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CurriculumDocumentController extends Controller
{
    public function download(Request $request, CurriculumDocument $document): Response
    {
        $user = $request->user();
        abort_unless($user, 403, 'Unauthorized.');

        // Tenant boundary check
        if (! $user->is_super_admin && $user->tenant_id && $document->tenant_id !== $user->tenant_id) {
            abort(403, 'Unauthorized access to curriculum document.');
        }

        // Parent enrollment check
        if ($user->role === 'parent') {
            $hasChildInClass = $user->students()
                ->where('class_id', $document->class_id)
                ->exists();
            abort_unless($hasChildInClass, 403, 'You do not have a child enrolled in this class.');
        }

        abort_unless(Storage::disk('public')->exists($document->file_path), 404, 'Curriculum document file not found.');

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
