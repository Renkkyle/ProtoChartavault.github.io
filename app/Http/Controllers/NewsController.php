<?php

namespace App\Http\Controllers;
use App\Models\News;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class NewsController extends BaseController
{
    public function index()
    {
        // Get the latest 3 news items (current)
        $sharedData = $this->getSharedData();
        \Log::info($sharedData['olderNewsItems']);
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();

        //return view('admin_headline', $sharedData); 
        return view('admin_headline', array_merge($sharedData, ['news' => News::all(), 'pendingCount' => $pendingCount, 'pendingUserCount' => $pendingUserCount,'pendingTicketCount' => $pendingTicketCount]));

    }
    public function admin_headline()
    {
        // Get the latest 3 news items (current)
        $sharedData = $this->getSharedData();
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();

        return view('admin_headline', array_merge($sharedData, ['pendingCount' => $pendingCount,'pendingUserCount' => $pendingUserCount,'pendingTicketCount' => $pendingTicketCount]));
    }

    public function create()
    {
        $sharedData = $this->getSharedData();
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();

        return view('admin_headline', array_merge(compact('sharedData'), ['pendingCount' => $pendingCount,'pendingUserCount' => $pendingUserCount,'pendingTicketCount' => $pendingTicketCount]));
    }


    public function store(Request $request)
    {
        // Get the headline and body content without sanitization
        $headline = $request->input('headline');
        $body = $request->input('content'); // Raw content with HTML tags (e.g., iframe)
    
        $headline = $this->sanitizeContent($headline); // Only sanitize title if needed

        // Create and save the news
        $body = html_entity_decode($request->content);

        $news = new News();
        $news->headline = $headline;  // Title/Headline (no need to modify)
        $news->body = $body;  // Store raw HTML content
        $news->save();
    
        // Get shared data (e.g., SDGs, featured advisers)
        $sharedData = $this->getSharedData();
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
    
        // Return the view with the shared data and all news articles
        return view('admin_headline', array_merge($sharedData, ['news' => News::all(), 'pendingCount' => $pendingCount,'pendingUserCount' => $pendingUserCount, 'pendingTicketCount' => $pendingTicketCount]));
    }
    


    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete();

        // Redirect back after deletion
        return redirect()->route('news.index')->with('success', 'News item deleted successfully.');
    }
}
