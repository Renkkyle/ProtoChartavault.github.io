<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket; // Assuming you have a Ticket model for tickets
use App\Models\Faq;    // Assuming you have a Faq model for FAQs
use Illuminate\Support\Facades\Auth;

class HelpController extends BaseController
{
    
    public function showHelpPage()
    {
        $user = auth()->user(); // Get the currently authenticated user
        $tickets = Ticket::where('user_id', auth()->id())->get() ?: collect();
        $faqs = Faq::all(); // Get all FAQs

        return view('help',compact('user','tickets','faqs')); // Display the help page
    }

    // Handle FAQ Search
    public function search(Request $request)
    {
        $user = Auth::user(); // Get the authenticated user
        $query = $request->input('query');
        $tickets = Ticket::where('user_id', $user->id)->get(); // Retrieve tickets for the user

        // Search for matching FAQs by question or answer
        $faqs = Faq::where('question', 'LIKE', "%{$query}%")
                    ->orWhere('answer', 'LIKE', "%{$query}%")
                    ->get();

        // Return the help center page with search results
        return view('help', compact('user', 'faqs','tickets')); // Pass $user and $faqs to the view
    }

    // Handle ticket submission
    public function submitTicket(Request $request)
    {
        // Validate the input fields
        $request->validate([
            'category' => 'required|string',
            'question' => 'required|string',
            'issue' => 'required|string',
        ]);
    
        // Create a new ticket
        $ticket = new Ticket();
        $ticket->user_id = auth()->id();
        $ticket->category = $request->category;
        $ticket->issue = $request->issue;
        $ticket->status = 'pending';
        $ticket->question = $request->question;
        $ticket->save();
    
        // Use the auto-generated ID as the ticket number
        return redirect()->route('help')->with('success', 'Your ticket has been submitted. Ticket Number: ' . $ticket->id);
    }

    public function respond(Request $request, $id)
    {
        // Temporarily dump and log the incoming request data to debug
        \Log::info('Request data: ', $request->all());
    
        try {
            $ticket = Ticket::findOrFail($id); // Using 'id' to find the ticket
    
            \Log::info("Found ticket with ID: $id");
    
            $ticket->response = $request->response;
            $ticket->status = 'responded';
            $ticket->save();
    
            \Log::info("Successfully updated ticket with ID: $id");
    
            return response()->json(['message' => 'Response saved successfully']);
        } catch (\Exception $e) {
            \Log::error('Error saving ticket response: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while saving the response.'], 500);
        }
    }

    public function showAdminHelpTickets()
    {
        $user = auth()->user(); // Get the currently authenticated user
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

        // Retrieve all tickets or filter by status if needed
        $tickets = Ticket::where('status', 'pending')->get();

        return view('adminHelp', compact('user','tickets','pendingCount','pendingUserCount','pendingTicketCount','pendingDownloadRequestCount'));
    }

    public function getTicketDetails($id)
    {
        $tickets = Ticket::findOrFail($id);
        return response()->json($tickets); // Returns ticket data in JSON format
    }
}
