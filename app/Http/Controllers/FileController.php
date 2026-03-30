<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Securely serve a nutritionist's professional ID file to authorized users (e.g., admin).
     */
    public function showProfessionalId($user_id)
    {
        $user = User::findOrFail($user_id);
        // Only allow admins or authorized users
        if (!Auth::user() || Auth::user()->role->role_name !== 'Admin') {
            abort(403, 'Unauthorized');
        }
        $filePath = $user->professional_id_path;
        if (!$filePath || !file_exists(public_path($filePath))) {
            abort(404, 'File not found');
        }
        $mimeType = mime_content_type(public_path($filePath));
        return response()->file(public_path($filePath), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        ]);
    }
}
