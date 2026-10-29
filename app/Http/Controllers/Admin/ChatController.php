<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message as ChatMessage;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $cashiers = User::where('role', 'cashier')->orderBy('name')->get()->map(function($cashier) {
            $cashier->unread_count = ChatMessage::where('sender_id', $cashier->id)
                ->where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->count();
            return $cashier;
        });
        return view('admin.chat.index', compact('cashiers'));
    }

    public function getMessages($cashierId)
    {
        // Mark messages from this cashier to admin as read
        ChatMessage::where('receiver_id', Auth::id())
            ->where('sender_id', $cashierId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ChatMessage::where(function($q) use ($cashierId) {
                $q->where('sender_id', Auth::id())->where('receiver_id', $cashierId);
            })->orWhere(function($q) use ($cashierId) {
                $q->where('sender_id', $cashierId)->where('receiver_id', Auth::id());
            })
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function getOnlineStatus()
    {
        $cashiers = User::where('role', 'cashier')->get(['id', 'name', 'last_seen_at']);
        $statuses = $cashiers->map(function ($cashier) {
            $unreadCount = ChatMessage::where('sender_id', $cashier->id)
                ->where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->count();

            return [
                'id'        => $cashier->id,
                'is_online' => $cashier->isOnline(),
                'unread_count' => $unreadCount,
            ];
        });
        return response()->json($statuses);
    }

    public function getUnreadCount()
    {
        $count = ChatMessage::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message'     => 'required|string|max:1000',
        ]);

        $msg = ChatMessage::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message'     => $request->message,
        ]);

        $msg->load('sender');

        return response()->json($msg);
    }
}
