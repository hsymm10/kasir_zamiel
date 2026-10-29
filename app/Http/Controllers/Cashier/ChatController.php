<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message as ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function getMessages()
    {
        // Cashier chats with the first admin found
        $admin = User::where('role', 'super_admin')->first();
        if (!$admin) return response()->json([]);

        // Mark messages from admin to cashier as read
        ChatMessage::where('receiver_id', Auth::id())
            ->where('sender_id', $admin->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ChatMessage::where(function($q) use ($admin) {
                $q->where('sender_id', Auth::id())->where('receiver_id', $admin->id);
            })->orWhere(function($q) use ($admin) {
                $q->where('sender_id', $admin->id)->where('receiver_id', Auth::id());
            })
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function getUnreadCount()
    {
        $admin = User::where('role', 'super_admin')->first();
        if (!$admin) return response()->json(['unread_count' => 0]);

        $count = ChatMessage::where('sender_id', $admin->id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function sendMessage(Request $request)
    {
        $admin = User::where('role', 'super_admin')->first();
        if (!$admin) return response()->json(['error' => 'Admin tidak ditemukan'], 404);

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $msg = ChatMessage::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $admin->id,
            'message'     => $request->message,
        ]);

        $msg->load('sender');

        return response()->json($msg);
    }
}
