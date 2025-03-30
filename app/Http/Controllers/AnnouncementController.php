<?php

namespace App\Http\Controllers;
use App\Models\Announcement;
use App\Models\News;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;

use Illuminate\Http\Request;

class AnnouncementController extends BaseController
{
    public function index()
    {

        // Get shared data (SDGs, recent papers, SDG counts, featured advisers)
        $sharedData = $this->getSharedData();
        \Log::info($sharedData['olderAnnouncements']);
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

        //return view('admin_headline', $sharedData); 
        return view('admin_headline', array_merge($sharedData, ['news' => News::all(), 'pendingCount' => $pendingCount, 'pendingUserCount' => $pendingUserCount,'pendingTicketCount' => $pendingTicketCount,'pendingDownloadRequestCount' => $pendingDownloadRequestCount]));

    }
    public function admin_headline()
    {

        // Get shared data (SDGs, recent papers, SDG counts, featured advisers)
        $sharedData = $this->getSharedData();
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        
        // Pass announcements and user data to the view
        return view('admin_headline', array_merge($sharedData, ['pendingCount' => $pendingCount])); // Add pending count here
    }

    public function create()
    {
        $user = Auth::user();
        $sharedData = $this->getSharedData();
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

        // Return the admin view to create an announcement
        return view('admin_headline', array_merge(compact('user', 'sharedData'), ['pendingCount' => $pendingCount, 'pendingUserCount' => $pendingUserCount, 'pendingTicketCount' => $pendingTicketCount,'pendingDownloadRequestCount'=>$pendingDownloadRequestCount]));
    }

    public function store(Request $request)
    {
        // Get raw title and content directly from the request (without encoding)
        $title = $request->input('title');
        $content = $request->input('content');
    
        // Optionally, sanitize title if needed, but allow HTML in content
        $title = $this->sanitizeContent($title); // Only sanitize title if needed
    
        // Save the announcement with raw content
        $announcement = new Announcement();
        $announcement->title = $title;
        $announcement->content = $content; // Save content with HTML (iframe tags)
        $announcement->save();
    
        // Get shared data (SDGs, recent papers, SDG counts, featured advisers)
        $sharedData = $this->getSharedData();
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

        // Return the view with shared data and all announcements
        return view('admin_headline', array_merge($sharedData, ['announcements' => Announcement::all(), 'pendingCount' => $pendingCount,'pendingUserCount' => $pendingUserCount,'pendingTicketCount' => $pendingTicketCount,'pendingDownloadRequestCount' => $pendingDownloadRequestCount]));
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        // Redirect back after deletion
        return redirect()->route('announcements.index')->with('success', 'Announcement deleted successfully.');
    }
}