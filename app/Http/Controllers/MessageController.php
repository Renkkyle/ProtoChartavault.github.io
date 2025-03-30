<?php

namespace App\Http\Controllers;

use App\Models\Message; // Ensure you have this model
use App\Models\User; // Include the User model
use Illuminate\Http\Request;

class MessageController extends Controller
{
    // Display the messages and users
    public function index()
    {
        // Fetch all users, including admins
        $users = User::all(); // Or use appropriate conditions if needed

        // Fetch messages related to the logged-in user
        $messages = Message::where('receiver_id', auth()->id())
            ->orWhere('sender_id', auth()->id())
            ->get();

        // Pass the users and messages to the view
        return view('messages', [
            'users' => $users,
            'messages' => $messages,
            'user' => auth()->user()
        ]);
    }

    // Handle sending a new message
    public function send(Request $request)
    {
        // Validate the request
        $request->validate([
            'message' => 'required|string',
            'file' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        // Handle file upload
        $filePath = null;
        if ($request->file('file')) {
            $filePath = $request->file('file')->store('uploads');
        }

        // Create a new message
        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id, // Make sure to pass this from your form
            'content' => $request->message,
            'file_path' => $filePath,
        ]);

        return redirect()->route('user.messages')->with('success', 'Message sent!');
    }
}
