<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageController extends Controller
{
    /**
     * Serve files from storage/app/public
     */
    public function serve(Request $request, string $path): BinaryFileResponse
    {
        $filePath = storage_path('app/public/' . $path);

        if (!file_exists($filePath) || !is_file($filePath)) {
            abort(404, 'File not found');
        }

        // Get mime type
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        // Return file with proper headers
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
