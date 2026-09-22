<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MessageAttachmentController extends Controller
{
    public function download(Request $request, Message $message): Response
    {
        $user = $request->user();
        abort_unless($user, 403);

        abort_unless($message->attachment_path, 404);

        $allowed = DB::table('conversation_user')
            ->where('conversation_id', $message->conversation_id)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($allowed, 403);

        $path = (string) $message->attachment_path;
        abort_unless(Storage::disk('local')->exists($path), 404);

        $name = $message->attachment_name ?: 'attachment';

        return Storage::disk('local')->download($path, $name);
    }
}

