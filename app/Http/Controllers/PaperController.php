<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paper;
use App\Models\SurveyResponse; 
use App\Models\User;
use App\Models\Download;
use App\Models\DownloadRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaperStatusNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;

use App\Models\Announcement; 

class PaperController extends BaseController
{
    public function index()
    {
        $user = Auth::user();

        $announcements = Announcement::orderBy(column: 'created_at', direction: 'desc')->paginate(3);  // 3 announcements per page

        // SDG Names
        $allSdgs = [
            'No Poverty', 
            'Zero Hunger', 
            'Good Health and Well-being', 
            'Quality Education',
            'Gender Equality', 
            'Clean Water and Sanitation', 
            'Affordable and Clean Energy',
            'Decent Work and Economic Growth',
            'Industry, Innovation, and Infrastructure',
            'Reduced Inequality', 
            'Sustainable Cities and Communities',
            'Responsible Consumption and Production',
            'Climate Action', 
            'Life Below Water', 
            'Life on Land', 
            'Peace, Justice and Strong Institutions', 
            'Partnerships for the Goals'
        ];

        // Fetch approved papers based on `datetime` for the "Recently Added" section
        $recentApprovedPapers = Paper::where('status', 'Approved')
                                    ->orderBy('datetime', 'desc') // Order by datetime field
                                    ->take(10) 
                                    ->get();

        // Decode SDGs from JSON to array for each paper
        foreach ($recentApprovedPapers as $paper) {
            $paper->sdgs = json_decode($paper->sdgs, true); 
        }

        // $documentTypes = ['Research Paper', 'Institutional Paper', 'Case Study', 'Thesis', 'Report'];
        $documentTypes = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research','Feasibility Studies', 'Report'];
        $sharedData = $this->getSharedData();
        $forms = $this->getForms();
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

        return view('index', [
            'user' => $user,
            'recentApprovedPapers' => $recentApprovedPapers,
            'allSdgs' => $allSdgs,
            'documentTypes'=>$documentTypes,
            'announcements' =>$announcements,
            'sharedData' => $sharedData,
            'forms' => $forms,
            'pendingCount' => $pendingCount,
            'pendingUserCount' => $pendingUserCount,
            'pendingTicketCount' => $pendingTicketCount,
            'pendingDownloadRequestCount' => $pendingDownloadRequestCount
        ]);
    }
    public function formsView()
{
    $forms = $this->getForms();
    $pendingCount = $this->getPendingSubmissionCount();
    $pendingUserCount = $this->getPendingUserCount();
    $pendingTicketCount = $this->getPendingTicketCount();
    $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

    return view('formsview', ['forms' => $forms,'pendingCount' => $pendingCount, 'pendingUserCount' => $pendingTicketCount,'pendingTicketCount'=>$pendingTicketCount,'pendingDownloadRequestCount'=>$pendingDownloadRequestCount]);
}

    public function adminIndex()
    {
        // Fetch only papers with status 'Pending'
        // $papers = Paper::where('status', 'Pending')->get();
        $papers = Paper::where('status', 'Pending')
            //->orderBy('datetime', 'desc') // Order strictly by submission datetime
            ->orderBy('id', 'desc')       // Use id as secondary sorting to maintain a stable order
            ->paginate(10);
        $paperId = null; // or set to a default value if needed
        $user = Auth::user();
        $advisers = [
            'Dr. Angelita M. Ruiz',
            'Dr. Aura Rhea Lanaban, MD',
            'Dr. Elizabeth G. Pasion',
            'Dr. Eulalia M. Casili',
            'Dr. Jennylyn D. Suico',
            'Dr. Joan Mae S. Espinosa',
            'Dr. John Paul Matuginas',
            'Dr. John Vianne B. Murcia',
            'Dr. Josefina D. Ortega',
            'Dr. Melchor Q. Bombeo',
            'Dr. Nestor Arce Jr., MD',
            'Dr. Radeline Lu, MD',
            'Dr. Raymond Libongcogon, MD',
            'Dr. Ricardo V. Garcia',
            'Dr. Roland T. Suico',
            'Dr. Susan S. Cruz',
            'Dr. Vicente T. Delos Reyes',
            'Engr. Anastacio Hular',
            'Engr. Nikka Kaye Bolanio',
            'Engr. Patrick L. Canama',
            'Engr. Roldan G. Suazo',
            'Mr. John Paul Ambit',
            'Mr. Jojin Cobrado',
            'Mr. Lyniel Solitario',
            'Prof. Alexis L. Baligod II',
            'Prof. Allan A. Magsipoc',
            'Prof. Candida Apostol',
            'Prof. Dan Guiller II Acero',
            'Prof. Efhrain Louis Pajota',
            'Prof. Ivan Arendain',
            'Prof. Janette M. Claro',
            'Prof. Jeane S. Dagatan',
            'Prof. Jevielyn Peralta Tan-Nery',
            'Prof. Joan Mae S. Espinosa',
            'Prof. Josephine Gallardo',
            'Prof. Mark S. Alemania',
            'Prof. Myrvic Keyoko P. Yanib',
            'Prof. Queenie Froozan Osorio',
            'Prof. Ralph Conrad Panti',
            'Prof. Real Acabado',
            'Prof. Ronnie Mendoza',
            'Prof. Teresita C. Mendoza',
            'Prof. Tom Anthony A. Tonguia'
        ];
        // $documentTypes = ['Research Paper', 'Institutional Paper', 'Case Study', 'Thesis', 'Report']; // Adjust this as needed
        $documentTypes = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research','Feasibility Studies', 'Report'];
        // SDG Names
        $allSdgs = [
            'No Poverty', 
            'Zero Hunger', 
            'Good Health and Well-being', 
            'Quality Education',
            'Gender Equality', 
            'Clean Water and Sanitation', 
            'Affordable and Clean Energy',
            'Decent Work and Economic Growth',
            'Industry, Innovation, and Infrastructure',
            'Reduced Inequality', 
            'Sustainable Cities and Communities',
            'Responsible Consumption and Production',
            'Climate Action', 
            'Life Below Water', 
            'Life on Land', 
            'Peace, Justice and Strong Institutions', 
            'Partnerships for the Goals'
        ];

        

        // Decode SDGs for each paper
        foreach ($papers as $paper) {
            $paper->sdgs = json_decode($paper->sdgs, true);
        }

    // Initialize an array to hold all keywords
    $allKeywords = [];

    // Collect keywords from each paper
    foreach ($papers as $paper) {
        if ($paper->keywords) {
            $keywords = explode(',', $paper->keywords);
            $allKeywords = array_merge($allKeywords, $keywords); // Merge keywords into the allKeywords array
        }
    }
    $pendingCount = $this->getPendingSubmissionCount();
    $pendingUserCount = $this->getPendingUserCount();
    $pendingTicketCount = $this->getPendingTicketCount();
    $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();


    // Remove duplicates and trim whitespace from keywords
    $keywordsArray = array_unique(array_map('trim', $allKeywords));

        
        return view('articles', compact('papers', 'user', 'allSdgs', 'paperId','documentTypes','advisers', 'keywordsArray','pendingCount','pendingUserCount','pendingTicketCount','pendingDownloadRequestCount')); // Return articles.blade.php
    }


    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'title' => 'required|string|max:255',
            'adviser' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'emails' => 'required|array',
            'abstract' => 'required|string',
            'college' => 'required|string',
            'course' => 'required|string',
            'file' => 'required|file|mimes:pdf,doc,docx',
            'sdgs' => 'required|array',
            'document_type' => 'required|string|max:255',
            'date_published' => 'required|date',
            'keywords' => 'nullable|string',
            'survey.q15' => 'nullable|string|max:5000',  // Validate the suggestion text (optional but added for clarity)

        ]);

        $file = $request->file('file');
        
        // Get file format and size
        $fileFormat = $file->getClientOriginalExtension(); // e.g., 'pdf', 'doc'
        $fileSize = round($file->getSize() / 1024, 2) . ' KB'; // Convert to KB

        // Generate a unique file name to avoid collisions
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Temporary file path
        $tempFilePath = 'temp_papers/' . $fileName;

        // Check if the file already exists in the temp folder
        if (Storage::disk('public')->exists($tempFilePath)) {
            return redirect()->back()->with('error', 'A file with the same name already exists. Please choose a different file name.');
        }

        // Store the file in the temporary folder
        $file->storeAs('temp_papers', $fileName, 'public'); // Save the file to temp

        // Save a single entry in the database for the paper (with all SDGs included)
        Paper::create([
            'title' => $request->title,
            'adviser' => $request->adviser,
            'author' => $request->author,
            'emails' => implode(',', $request->emails), // Convert emails array to a string
            'abstract' => $request->abstract,
            'college' => $request->college,
            'course' => $request->course,
            'file_path' => $tempFilePath, // Store the temporary file path
            'sdgs' => json_encode($request->sdgs), // Save all selected SDGs as JSON
            'status' => 'Pending', // Default status set to 'Pending'
            'uploaded_by' => Auth::id(),
            'is_approved' => false, // Default to unapproved
            'document_type' => $request->document_type, // Store document type
            'date_published' => $request->date_published, // Store date published
            'keywords' => $request->input('keywords'),
            'datetime' => now(), // Set datetime only when submitting
            // You can store file format and size here if you want to save them in the database
            // 'file_format' => $fileFormat,
            // 'file_size' => $fileSize,
        ]);

        // Save survey responses
        foreach ($request->survey as $question => $answer) {
            SurveyResponse::create([
                'user_id' => Auth::id(),
                // 'paper_id' => $paper->id,
                'question' => $question,
                'answer' => $answer,
            ]);
        }

        return redirect()->route('user.articles')->with('success', 'Document uploaded successfully.');
    }

    public function edit(Paper $paper)
    {
        dd($paper);
        // $documentTypes = ['Research Paper', 'Institutional Paper', 'Case Study', 'Thesis', 'Report']; // Adjust this as needed
        $documentTypes = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research','Feasibility Studies', 'Report'];
        $allSdgs = [
            'No Poverty',
            'Zero Hunger',
            'Good Health and Well-being',
            'Quality Education',
            'Gender Equality',
            'Clean Water and Sanitation',
            'Affordable and Clean Energy',
            'Decent Work and Economic Growth',
            'Industry, Innovation, and Infrastructure',
            'Reduced Inequality',
            'Sustainable Cities and Communities',
            'Responsible Consumption and Production',
            'Climate Action',
            'Life Below Water',
            'Life on Land',
            'Peace and Justice Strong Institutions',
            'Partnerships to Achieve the Goal',
        ];
        
        // $keywordsArray = $papers->keywords ? explode(',', $papers->keywords) : [];



        return view('articles.edit', compact('paper', 'allSdgs','documentTypes'));
    }

    public function update(Request $request, Paper $paper)
    {
        
        // Validate the incoming request data
        $request->validate([
            'title' => 'required|string|max:255',
            'adviser' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'emails' => 'required|string', // Handle emails as a string or array accordingly
            'abstract' => 'required|string',
            'sdgs' => 'required|array', // Ensure SDGs is an array of selected checkboxes
            // New inputs
            'document_type' => 'required|string|max:255',
            'date_published' => 'required|date',
            'keywords' => 'nullable|string',
        ]);

        // Update the paper fields
        $paper->title = $request->input('title');
        $paper->adviser = $request->input('adviser');
        $paper->author = $request->input('author');
        $paper->emails = $request->input('emails'); // Handle emails properly
        $paper->abstract = $request->input('abstract');
        $paper->sdgs = json_encode($request->input('sdgs')); // Save selected SDGs as JSON // HERE

        $paper->document_type = $request->input('document_type');
        $paper->date_published = $request->input('date_published');
        $paper->keywords = $request->input('keywords');

        // Check if there's a new file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $tempFilePath = 'temp_papers/' . $file->getClientOriginalName(); // Store in temporary folder

            // Check if the file already exists in the temp folder
            if (Storage::disk('public')->exists($tempFilePath)) {
                return redirect()->back()->with('error', 'A file with the same name already exists. Please choose a different file name.');
            }

            // Store the file in the temporary folder
            $file->storeAs('temp_papers', $file->getClientOriginalName(), 'public');
            $paper->file_path = $tempFilePath; // Update with temporary file path
        }

        
        // Save the updated paper
        $paper->save();

        return redirect()->route('admin.articles')->with('success', 'Document updated successfully.');
    }

    public function destroy(Paper $paper)
    {
        // Delete the file from storage
        if ($paper->file_path) {
            Storage::disk('public')->delete($paper->file_path);
        }
        $paper->delete();
        return redirect()->route('admin.articles')->with('success', 'Document deleted successfully.');
    }

    public function download(Request $request, $id)
    {
        $paper = Paper::findOrFail($id);
    
        // Increment the downloads count if coming from paperview
        if ($request->query('source') === 'paperview') {
            $paper->increment('downloads');
        }
    
        // Check if the user is logged in and external
        if (auth()->check()) {
            // Check if the user is external and their download request has not been approved
            if (auth()->user()->is_external == 1) {
                $downloadRequest = DownloadRequest::where('user_id', auth()->id())
                                                  ->where('paper_id', $paper->id)
                                                  ->first();
    
                // If no request or the request is not approved, deny the download
                if (!$downloadRequest || $downloadRequest->is_approved == false) {
                    // Redirect back to paperview with a message indicating the need for approval
                    return redirect()->route('paperview', ['id' => $paper->id])
                                     ->with('message', 'Your download request is pending approval. You cannot download this paper yet.');
                }
            }
    
            // Increment the download log
            Download::create([
                'user_id' => auth()->id(),
                'paper_id' => $paper->id,
            ]);
        }
    
        // Determine the file path based on paper status
        if ($paper->status === 'Pending') {
            $filePath = 'public/' . $paper->file_path; // Temporary folder for pending papers
        } else {
            // Approved papers have a different structure
            $college = $paper->college;
            $course = $paper->course;
            $sdgPath = "papers/{$college}/{$course}/";
            $filePath = $sdgPath . basename($paper->file_path);
    
            // Check SDG path if necessary
            $sdgs = json_decode($paper->sdgs);
            if (!empty($sdgs)) {
                $filePath = "public/papers/{$college}/{$course}/{$sdgs[0]}/" . basename($paper->file_path);
            }
        }
    
        // Check if the file exists
        if (!Storage::exists($filePath)) {
            return redirect()->back()->withErrors(['message' => 'File not found!']);
        }
    
        // Return the file for download
        return Storage::download($filePath, basename($paper->file_path));
    }

    public function show($id)
    {
        $paper = Paper::findOrFail($id);

        // Increment the views count
        // Check if increment parameter is present in the request
        $incrementView = request()->query('increment', 'true') === 'true';

        // Increment the views count only if the paper is approved and increment is true
        if ($incrementView && $paper->status === 'Approved') {
            $paper->increment('views');
        }

        // Decode SDGs for use in the view
        $sdgs = json_decode($paper->sdgs, true); 

        // $documentTypes = ['Research Paper', 'Institutional Paper', 'Case Study', 'Thesis', 'Report'];
        $documentTypes = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research','Feasibility Studies', 'Report'];

        // All SDG Names
        $allSdgs = [
            'No Poverty',
            'Zero Hunger',
            'Good Health and Well-being',
            'Quality Education',
            'Gender Equality',
            'Clean Water and Sanitation',
            'Affordable and Clean Energy',
            'Decent Work and Economic Growth',
            'Industry, Innovation and Infrastructure',
            'Reduced Inequality',
            'Sustainable Cities and Communities',
            'Responsible Consumption and Production',
            'Climate Action',
            'Life Below Water',
            'Life on Land',
            'Peace, Justice and Strong Institutions',
            'Partnerships for the Goals'
        ];
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

        $existingRequest = null;
        if (auth()->check() && auth()->user()->is_external == 1) {
            $existingRequest = DownloadRequest::where('user_id', auth()->id())
                                              ->where('paper_id', $paper->id)
                                              ->first();
        }
        return view('paperview', compact('paper', 'sdgs', 'allSdgs','documentTypes','pendingCount','pendingUserCount','pendingTicketCount','existingRequest','pendingDownloadRequestCount'));
    }

    public function search(Request $request)
{
    try {
        // Validate incoming requests
        $validated = $request->validate([
            'college' => 'nullable|string',
            'course' => 'nullable|string',
            'sdg' => 'nullable|string',
            'search' => 'nullable|string|max:255',
            'filter' => 'nullable|string', // For the popular filters
            'document_type' => 'nullable|string', // For the document type filter
            'keyword' => 'nullable|string|max:255', // For keyword search
        ]);

        // Initialize the query for approved papers
        $query = Paper::where('status', 'Approved');

        // $documentTypes = ['Research Paper', 'Institutional Paper', 'Case Study', 'Thesis', 'Report'];
        $documentTypes = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research','Feasibility Studies', 'Report'];
        // Existing filters
        if ($request->college) {
            $query->where('college', $request->college);
        }
        if ($request->course) {
            $query->where('course', $request->course);
        }
        if ($request->has('sdg') && $request->sdg) {
            $sdgs = explode(',', $request->sdg);
            $query->where(function($q) use ($sdgs) {
                foreach ($sdgs as $sdg) {
                    $q->orWhere('sdgs', 'like', '%' . $sdg . '%'); // Adjust the condition as necessary
                }
            });
        }
        if ($request->search) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }

        // New filter for "Most Popular by Downloads"
        if ($request->filter === 'downloads') {
            $query->orderBy('downloads', 'desc'); // Assuming you have a 'downloads' column
        }

        // New filter for "Most Popular by Views"
        if ($request->filter === 'views') {
            $query->orderBy('views', 'desc'); // Assuming you have a 'views' column
        }

        // New filter for document type
        if ($request->document_type) {
            $query->where('document_type', $request->document_type);
        }

                // Handle keyword search (check if keyword filter exists)
        if ($request->keyword) {
            $query->where('keywords', 'LIKE', '%' . $request->keyword . '%'); // Assuming you have a 'keywords' column
        }
        // Get results
        //$papers = $query->get();
        $papers = $query->paginate(10);

        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

        // Pass the results to the searchResult view
        return view('searchResult', [
            'papers' => $papers,
            'searchTerm' => $request->search,
            'college' => $request->college,
            'course' => $request->course,
            'sdg' => $request->sdg,
            'documentTypes' => $documentTypes, // Make sure to pass document types to the view
            'keyword' => $request->keyword, // Pass the keyword to the view
            'pendingCount' => $pendingCount,
            'pendingUserCount' => $pendingUserCount,
            'pendingTicketCount' => $pendingTicketCount,
            'pendingDownloadRequestCount' => $pendingDownloadRequestCount

        ]);
    } catch (\Exception $e) {
        Log::error('Search error: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while processing your request.'], 500);
    }
}




// filtration code start
// Helper function to get colleges and courses
    private function getColleges()
    {
        

        return [
            'College of Agriculture' => ["Bachelor of Science in Agriculture"],
            'College of Allied Health Science Education' => [
                "Bachelor of Science in Pharmacy",
                "Bachelor of Science in Biology",
                "Bachelor of Science in Radiologic Technology",
                "BS in Medical Technology/Medical Laboratory Science"
            ],
            'College of Arts and Science' => [
                "Bachelor of Science in Psychology",
                "Bachelor of Science in Social Work"
            ],
            'College of Business Education' => [
                "Bachelor of Science in Accountancy",
                "Bachelor of Science in Management Accounting",
                "Bachelor of Science in Business Administration",
                "Bachelor of Science in Entrepreneurship",
                "Bachelor of Science in Tourism Management"
            ],
            'College of Criminal Justice Education' => [
                "Bachelor of Science in Criminology"
            ],
            'College of Engineering' => [
                "Bachelor of Science in Civil Engineering"
            ],
            'College of Information Technology Education' => [
                "Bachelor of Science in Information Technology",
                "BS in Entertainment and Multimedia Computing"
            ],
            'College of Nursing' => ["Bachelor of Science in Nursing"],
            'College of Teacher Education' => [
                "Bachelor of Early Childhood Education",
                "Bachelor of Elementary Education",
                "Bachelor of Secondary Education",
                "Bachelor of Technical - Vocational Teacher Education"
            ],
            'College of Medicine' => ["Doctor of Medicine"],
            'College of Law' => ["Juris Doctor"]

        ];
        
    }

    private function getCourses()
{
    $colleges = $this->getColleges();
    return array_merge(...array_values($colleges)); // Flatten the courses into a single array
}

    // Helper function to get SDGs
    private function getSdgs()
    {
        return [
            'No Poverty',
            'Zero Hunger',
            'Good Health and Well-being',
            'Quality Education',
            'Gender Equality',
            'Clean Water and Sanitation',
            'Affordable and Clean Energy',
            'Decent Work and Economic Growth',
            'Industry, Innovation and Infrastructure',
            'Reduced Inequality',
            'Sustainable Cities and Communities',
            'Responsible Consumption and Production',
            'Climate Action',
            'Life Below Water',
            'Life on Land',
            'Peace, Justice and Strong Institutions',
            'Partnerships for the Goals'
        ];
    }
    // ends here for the filtration


    public function userArticles()
    {
        $user = auth()->user();
        $papers = Paper::where('uploaded_by', $user->id)->get();

        $coursesByCollege = [
            'College of Agriculture' => ["Bachelor of Science in Agriculture"],
            'College of Allied Health Science Education' => [
                "Bachelor of Science in Pharmacy",
                "Bachelor of Science in Biology",
                "Bachelor of Science in Radiologic Technology",
                "BS in Medical Technology/Medical Laboratory Science"
            ],
            'College of Arts and Science' => [
                "Bachelor of Science in Psychology",
                "Bachelor of Science in Social Work"
            ],
            'College of Business Education' => [
                "Bachelor of Science in Accountancy",
                "Bachelor of Science in Management Accounting",
                "Bachelor of Science in Business Administration",
                "Bachelor of Science in Entrepreneurship",
                "Bachelor of Science in Tourism Management"
            ],
            'College of Criminal Justice Education' => [
                "Bachelor of Science in Criminology"
            ],
            'College of Engineering' => [
                "Bachelor of Science in Civil Engineering"
            ],
            'College of Information Technology Education' => [
                "Bachelor of Science in Information Technology",
                "BS in Entertainment and Multimedia Computing"
            ],
            'College of Nursing' => ["Bachelor of Science in Nursing"],
            'College of Teacher Education' => [
                "Bachelor of Early Childhood Education",
                "Bachelor of Elementary Education",
                "Bachelor of Secondary Education",
                "Bachelor of Technical - Vocational Teacher Education"
            ],
            'College of Medicine' => ["Doctor of Medicine"],
            'College of Law' => ["Juris Doctor"]

        ];

        $sdgs = [
            'No Poverty',
            'Zero Hunger',
            'Good Health and Well-being',
            'Quality Education',
            'Gender Equality',
            'Clean Water and Sanitation',
            'Affordable and Clean Energy',
            'Decent Work and Economic Growth',
            'Industry, Innovation and Infrastructure',
            'Reduced Inequality',
            'Sustainable Cities and Communities',
            'Responsible Consumption and Production',
            'Climate Action',
            'Life Below Water',
            'Life on Land',
            'Peace, Justice and Strong Institutions',
            'Partnerships for the Goals'
        ];
    
        $papers = Paper::where('uploaded_by', $user->id)
        ->whereIn('status', ['pending', 'approved']) // Filter by status
        ->get();
        
        return view('UserArticles', [
            'papers' => $papers,
            'coursesByCollege' => $coursesByCollege,
            'user' => $user,
            'sdgs' => $sdgs // Pass the SDGs to the view
        ]);
        
    }
    
    public function approve(Paper $paper)
    {
        try {
            $sdgs = json_decode($paper->sdgs);
            $college = $paper->college;
            $course = $paper->course;
            $originalFilePath = $paper->file_path;  // This should point to the temp file
    
            $publishedDate = \Carbon\Carbon::parse($paper->date_published);
            $yearMonth = $publishedDate->format('Y-m');  // Format as '2024-01'
            
            foreach ($sdgs as $sdg) {
                $destinationPath = "papers/{$yearMonth}/{$college}/{$course}/{$sdg}/";
    
                Storage::disk('public')->makeDirectory($destinationPath);
    
                Storage::disk('public')->move($originalFilePath, $destinationPath . basename($originalFilePath));
    
                \Log::info('File moved to:', ['path' => $destinationPath . basename($originalFilePath)]);
            }
    
            $paper->status = 'Approved';
            $paper->file_path = "papers/{$yearMonth}/{$college}/{$course}/" . basename($originalFilePath);
            $paper->save();
    
            // Get the user's email from the users table
            $userEmail = $paper->user->email; // Using the relationship to fetch the email
    
            // Send email notification
            Mail::to($userEmail)->send(new PaperStatusNotification($paper, 'Your paper has been approved!'));
    
            return response()->json(['message' => 'Paper approved successfully.']); // Return success message as JSON
        } catch (\Exception $e) {
            \Log::error('Error approving paper: ' . $e->getMessage());
            return response()->json(['message' => 'Error approving paper.'], 500); // Return error message as JSON with 500 status
        }
    }
    public function reject(Paper $paper, Request $request)
{
    try {
        
        $request->validate([
            'remarks' => 'required|string|max:255',
        ]); 

        
        // Update paper status to rejected and save remarks
        $paper->status = 'Rejected';
        $paper->remarks = $request->remarks;
        /* $paper->remarks = $request->input('remarks'); */
        /* $paper->remarks = null; */
        $paper->save();

        if ($paper->file_path && Storage::disk('public')->exists($paper->file_path)) {
            Storage::disk('public')->delete($paper->file_path);
        }
        // Get the user's email from the users table
        $userEmail = $paper->user->email;

        // Send email notification
        Mail::to($userEmail)->send(new PaperStatusNotification($paper, 'Your paper has been rejected. Please check the submission requirements and resubmit it.',$request->remarks));

        return response()->json(['success' => true, 'message' => 'Paper rejected successfully.'], 200);
    } catch (\Exception $e) {
        \Log::error('Error rejecting paper: ' . $e->getMessage());
        return response()->json(['error' => 'Error rejecting paper.'], 500);
    }
}
    



    public function articles()
    {

        $allSdgs = [
            'No Poverty',
            'Zero Hunger',
            'Good Health and Well-being',
            'Quality Education',
            'Gender Equality',
            'Clean Water and Sanitation',
            'Affordable and Clean Energy',
            'Decent Work and Economic Growth',
            'Industry, Innovation and Infrastructure',
            'Reduced Inequality',
            'Sustainable Cities and Communities',
            'Responsible Consumption and Production',
            'Climate Action',
            'Life Below Water',
            'Life on Land',
            'Peace, Justice and Strong Institutions',
            'Partnerships for the Goals'
        ];

        // Any other logic for fetching papers or other data...

        return view('articles', compact('papers', 'allSdgs')); // Make sure to pass $allSdgs here
    }

    public function fixSdgs()
    {
        $papers = Paper::all();
        foreach ($papers as $paper) {
            // Decode the SDGs if they are double-encoded
            if (is_string($paper->sdgs) && json_decode($paper->sdgs) !== null) {
                $decodedSdgs = json_decode($paper->sdgs);
                
                if (is_array($decodedSdgs)) {
                    // Re-encode the array as a proper JSON array
                    $paper->sdgs = json_encode($decodedSdgs);
                    $paper->save();
                }
            }
        }

        return "SDGs fixed for all papers";
    }

    

    // This handles the chart data for the dashboard
    public function getApprovedChartData(Request $request)
{
    $collegeId = $request->college;
    $courseId = $request->course;
    $sdgId = $request->sdg;

    // Query to get the count of approved papers based on filters
    $query = Paper::where('status', 'approved');

    if ($collegeId) {
        $query->where('college_id', $collegeId);
    }
    if ($courseId) {
        $query->where('course_id', $courseId);
    }
    if ($sdgId) {
        $query->whereHas('sdgs', function($q) use ($sdgId) {
            $q->where('sdg_id', $sdgId);
        });
    }

    $papers = $query->get();

    // 1. Group the data by college and course
    $data = $papers->groupBy('college.name')
                ->map(function($collegeGroup) {
                    return $collegeGroup->groupBy('course.name')->map(function($courseGroup) {
                        return $courseGroup->count(); // Change this to count instead of just returning
                    });
                });

    $labels = [];
    $values = [];

    foreach ($data as $college => $courses) {
        foreach ($courses as $course => $count) {
            $labels[] = $course;
            $values[] = $count;
        }
    }

    // Ensure there is data for charts even if counts are 0
    if (empty($labels) || empty($values)) {
        $labels = ['No Data']; // Placeholder label if no data is found
        $values = [0]; // Placeholder value
    }

    // 2. Group papers by SDG
    $barChartData = array_fill(0, 17, 0);
    $pieChartData = array_fill(0, 17, 0);
    $lineChartData = array_fill(0, 17, 0);

    foreach ($papers as $paper) {
        foreach ($paper->sdgs as $sdg) {
            $sdgIndex = $sdg->id - 1; // Assuming SDG IDs start at 1
            $barChartData[$sdgIndex]++;
            $pieChartData[$sdgIndex]++;
            $lineChartData[$sdgIndex]++;
        }
    }

    // Return the combined response with both datasets
    return response()->json([
        'collegeCourseData' => [
            'labels' => $labels,
            'values' => $values
        ],
        'sdgsData' => [
            'barChartData' => $barChartData,
            'pieChartData' => $pieChartData,    
            'lineChartData' => $lineChartData
        ]
    ]);
}


private function getSDGData($papers) {
    // Process $papers to extract SDG data
    // Return an array suitable for your charts
}


public function getPapersDataForCharts()
{
    // Logic to count papers per SDG
    $sdgsData = [
        'barChartData' => $this->getApprovedPaperCountsPerSDG(),
        'pieChartData' => $this->getApprovedPaperCountsPerSDG(), // Adjust based on your logic
        'lineChartData' => $this->getApprovedPaperCountsTrend() // Adjust based on your logic
    ];

    return response()->json(['sdgsData' => $sdgsData]);
}

    
    public function showMetadata($id)
    {
        // Retrieve the paper by ID
        $paper = Paper::find($id);

        // Initialize file format and size as unknown
        $fileFormat = 'Unknown';
        $fileSize = 'Unknown';

        // Check if the file exists
        if ($paper->file) {
            // Build the path to the file (adjust based on your storage)
            $filePath = storage_path('app/papers/' . $paper->file); // Adjust this path as needed

            // Debugging: Check if file path is correct
            dd('File Path: ' . $filePath);

            if (file_exists($filePath)) {
                $fileFormat = pathinfo($filePath, PATHINFO_EXTENSION); // Get the file format
                $fileSize = round(filesize($filePath) / 1024, 2) . ' KB'; // Get size in KB
            } else {
                dd('File does not exist: ' . $filePath);
            }
        }

        // Pass the paper data along with file info to the metadata view
        // Retrieve shared data
        $sharedData = $this->getSharedData();
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

        // Pass the paper data, file info, and shared data to the metadata view
        return view('papermetadata', array_merge($sharedData, [
            'paper' => $paper,
            'fileFormat' => $fileFormat,
            'fileSize' => $fileSize,
            'pendingCount' => $pendingCount,
            'pendingUserCount' => $pendingUserCount,
            'pendingTicketCount' => $pendingTicketCount,
            'pendingDownloadRequestCount' => $pendingDownloadRequestCount
        ]));
    }

    public function showPendingDocuments()
    {
        // Get only pending papers
        $papers = Paper::where('status', 'pending')->get();

        return view('articles', compact('papers'));
    }

    public function archive()
{
    $documentTypes = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research','Feasibility Studies', 'Report'];

    // $documentTypes = ['Research Paper', 'Institutional Paper', 'Case Study', 'Thesis', 'Report'];

    $searchTerm = request()->query('search'); // Get the search term if provided
    $papersQuery = Paper::where('status', 'approved'); // Only show approved papers

    
    if ($searchTerm) {
        // Apply search filter
        $papersQuery->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('adviser', 'like', '%' . $searchTerm . '%');
    }
    $papers = $papersQuery->orderBy('datetime', 'desc')->paginate(10);
    $pendingCount = $this->getPendingSubmissionCount();
    $pendingUserCount = $this->getPendingUserCount();
    $pendingTicketCount = $this->getPendingTicketCount();
    $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

    return view('archive', compact('papers', 'searchTerm','documentTypes','pendingCount','pendingUserCount','pendingTicketCount','pendingDownloadRequestCount'));
}


public function adviserPage()
{
    // Default to 'A' when no letter is specified
    $defaultLetter = 'A';

    return $this->filterByAdviserLetter($defaultLetter);
}

// TEST TEST TEST TEST



public function filterByAdviserLetter($letter)
{
    $documentTypes = ['Research Paper', 'Institutional Paper', 'Case Study', 'Thesis', 'Report'];

    // Fetch distinct adviser names from the papers table and load their associated papers
    $advisers = Paper::select('adviser')
        ->distinct()
        ->with('adviserPapers')
        ->get();

    // Strip title from each adviser's name and filter based on the letter
    $filteredAdvisers = $advisers->filter(function ($adviser) use ($letter) {
        // Strip the title from the adviser's name
        $strippedName = $this->stripTitle($adviser->adviser);
        
        // Check if the first letter of the stripped name matches the selected letter
        return strtoupper(substr($strippedName, 0, 1)) === strtoupper($letter);
    });
    

    /* foreach ($filteredAdvisers as $adviser) {
        $adviser->adviserPapers = Paper::where('adviser', $adviser->adviser)
            ->paginate(5);  
    } */
    $pendingCount = $this->getPendingSubmissionCount();
    $pendingUserCount = $this->getPendingUserCount();
    $pendingTicketCount = $this->getPendingTicketCount();
    $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

    return view('adviser', [
        'filteredAdvisers' => $filteredAdvisers,
        'selectedLetter' => $letter,
        'documentTypes' => $documentTypes,
        'pendingCount' => $pendingCount,
        'pendingUserCount' => $pendingUserCount,
        'pendingTicketCount' => $pendingTicketCount,
        'pendingDownloadRequestCount' => $pendingDownloadRequestCount
    ]);
}

public function showAddSubmission()
{
    $user = Auth::user(); // Get the currently authenticated user

    // Define your SDGs and courses by college
    $sdgs = [
        'No Poverty',
        'Zero Hunger',
        'Good Health and Well-being',
        'Quality Education',
        'Gender Equality',
        'Clean Water and Sanitation',
        'Affordable and Clean Energy',
        'Decent Work and Economic Growth',
        'Industry, Innovation and Infrastructure',
        'Reduced Inequality',
        'Sustainable Cities and Communities',
        'Responsible Consumption and Production',
        'Climate Action',
        'Life Below Water',
        'Life on Land',
        'Peace, Justice and Strong Institutions',
        'Partnerships for the Goals'
    ];

    $coursesByCollege = [
        'College of Agriculture' => ["Bachelor of Science in Agriculture"],
        'College of Allied Health Science Education' => [
            "Bachelor of Science in Pharmacy",
            "Bachelor of Science in Biology",
            "Bachelor of Science in Radiologic Technology",
            "BS in Medical Technology/Medical Laboratory Science"
        ],
        'College of Arts and Science' => [
            "Bachelor of Science in Psychology",
            "Bachelor of Science in Social Work"
        ],
        'College of Business Education' => [
            "Bachelor of Science in Accountancy",
            "Bachelor of Science in Management Accounting",
            "Bachelor of Science in Business Administration",
            "Bachelor of Science in Entrepreneurship",
            "Bachelor of Science in Tourism Management"
        ],
        'College of Criminal Justice Education' => [
            "Bachelor of Science in Criminology"
        ],
        'College of Engineering' => [
            "Bachelor of Science in Civil Engineering"
        ],
        'College of Information Technology Education' => [
            "Bachelor of Science in Information Technology",
            "BS in Entertainment and Multimedia Computing"
        ],
        'College of Nursing' => ["Bachelor of Science in Nursing"],
        'College of Teacher Education' => [
            "Bachelor of Early Childhood Education",
            "Bachelor of Elementary Education",
            "Bachelor of Secondary Education",
            "Bachelor of Technical - Vocational Teacher Education"
        ],
        'College of Medicine' => ["Doctor of Medicine"],
        'College of Law' => ["Juris Doctor"]


  
    ];

    $advisers = [
        'Dr. Angelita M. Ruiz',
        'Dr. Aura Rhea Lanaban, MD',
        'Dr. Elizabeth G. Pasion',
        'Dr. Eulalia M. Casili',
        'Dr. Jennylyn D. Suico',
        'Dr. Joan Mae S. Espinosa',
        'Dr. John Paul Matuginas',
        'Dr. John Vianne B. Murcia',
        'Dr. Josefina D. Ortega',
        'Dr. Melchor Q. Bombeo',
        'Dr. Nestor Arce Jr., MD',
        'Dr. Radeline Lu, MD',
        'Dr. Raymond Libongcogon, MD',
        'Dr. Ricardo V. Garcia',
        'Dr. Roland T. Suico',
        'Dr. Susan S. Cruz',
        'Dr. Vicente T. Delos Reyes',
        'Engr. Anastacio Hular',
        'Engr. Nikka Kaye Bolanio',
        'Engr. Patrick L. Canama',
        'Engr. Roldan G. Suazo',
        'Mr. John Paul Ambit',
        'Mr. Jojin Cobrado',
        'Mr. Lyniel Solitario',
        'Prof. Alexis L. Baligod II',
        'Prof. Allan A. Magsipoc',
        'Prof. Candida Apostol',
        'Prof. Dan Guiller II Acero',
        'Prof. Efhrain Louis Pajota',
        'Prof. Ivan Arendain',
        'Prof. Janette M. Claro',
        'Prof. Jeane S. Dagatan',
        'Prof. Jevielyn Peralta Tan-Nery',
        'Prof. Joan Mae S. Espinosa',
        'Prof. Josephine Gallardo',
        'Prof. Mark S. Alemania',
        'Prof. Myrvic Keyoko P. Yanib',
        'Prof. Queenie Froozan Osorio',
        'Prof. Ralph Conrad Panti',
        'Prof. Real Acabado',
        'Prof. Ronnie Mendoza',
        'Prof. Teresita C. Mendoza',
        'Prof. Tom Anthony A. Tonguia'
    ];

    // $documentTypes = ['Research Paper', 'Institutional Paper', 'Case Study', 'Thesis', 'Report'];
    $documentTypes = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research','Feasibility Studies', 'Report'];

    // Pass user, allSdgs, and coursesByCollege variables to the view
    return view('addSubmission', compact('user', 'sdgs', 'coursesByCollege','advisers', 'documentTypes'));
}

// SDG COUNT
public function showSdgCounts()
    {
        // Log method call
        \Log::info('showSdgCounts method called');
        
        // Fetch all approved papers
        $papers = Paper::where('status', 'Approved')->get();
        \Log::info('Approved Papers:', $papers->toArray());


        // Define all SDGs
        $sdgs = [
            "No Poverty",
            "Zero Hunger",
            "Good Health and Well-being",
            "Quality Education",
            "Gender Equality",
            "Clean Water and Sanitation",
            "Affordable and Clean Energy",
            "Decent Work and Economic Growth",
            "Industry, Innovation and Infrastructure",
            "Reduced Inequality",
            "Sustainable Cities and Communities",
            "Responsible Consumption and Production",
            "Climate Action",
            "Life Below Water",
            "Life on Land",
            "Peace, Justice and Strong Institutions",
            "Partnerships for the Goals"
        ];

        // Initialize counts for each SDG
        $sdgCounts = array_fill_keys($sdgs, 0);
        \Log::info('Initialized SDG Counts:', $sdgCounts);

        // Loop through each paper and count SDG occurrences
        foreach ($papers as $paper) {
            \Log::info('Raw SDGs:', [$paper->sdgs]);
            
            // Attempt to decode JSON
            $sdgsArray = json_decode($paper->sdgs, true);

            // Check for JSON errors and log them
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('JSON decoding error:', [json_last_error_msg()]);
                continue;
            }

            // Increment counts for each SDG if decoded successfully
            if (is_array($sdgsArray)) {
                foreach ($sdgsArray as $sdg) {
                    $sdg = trim($sdg);
                    if (isset($sdgCounts[$sdg])) {
                        $sdgCounts[$sdg]++;
                    } else {
                        \Log::warning('SDG not found in predefined list:', [$sdg]);
                    }
                }
            }
        }

        \Log::info('Final SDG Counts:', $sdgCounts);
        
        // Pass the SDG counts to the view
        return view('index', compact('sdgCounts'));
    }
    
    public function getSdgCounts()
{
    // Initialize an array to hold the counts
    $sdgCounts = [];
    
    // Define all SDGs
    $allSdgs = [
        "No Poverty",
        "Zero Hunger",
        "Good Health and Well-being",
        "Quality Education",
        "Gender Equality",
        "Clean Water and Sanitation",
        "Affordable and Clean Energy",
        "Decent Work and Economic Growth",
        "Industry, Innovation and Infrastructure",
        "Reduced Inequality",
        "Sustainable Cities and Communities",
        "Responsible Consumption and Production",
        "Climate Action",
        "Life Below Water",
        "Life on Land",
        "Peace, Justice and Strong Institutions",
        "Partnerships for the Goals"
    ];

    // Loop through each SDG to count occurrences
    foreach ($allSdgs as $sdg) {
        // Count the number of approved papers for each SDG
        $count = Paper::where('status', 'Approved')
                       ->whereRaw("sdgs LIKE '%{$sdg}%'") // Check if the SDG is contained in the sdgs string
                       ->count();

        // Store the count in the array
        $sdgCounts[$sdg] = $count;
    }

    return $sdgCounts;
}
public function showFeatured()
{
    $user = Auth::user();
    $announcements = Announcement::orderBy('created_at', 'desc')->paginate(3);  // 3 announcements per page

    // SDG Names
    $allSdgs = [
        'No Poverty', 
        'Zero Hunger', 
        'Good Health and Well-being', 
        'Quality Education',
        'Gender Equality', 
        'Clean Water and Sanitation', 
        'Affordable and Clean Energy',
        'Decent Work and Economic Growth',
        'Industry, Innovation, and Infrastructure',
        'Reduced Inequality', 
        'Sustainable Cities and Communities',
        'Responsible Consumption and Production',
        'Climate Action', 
        'Life Below Water', 
        'Life on Land', 
        'Peace, Justice and Strong Institutions', 
        'Partnerships for the Goals'
    ];

    // Fetch recently approved papers
    // Return here for the order soething happening here 
    $recentApprovedPapers = Paper::where('status', 'Approved')
                                //->orderBy('datetime', 'desc')
                                ->orderBy('id','desc')
                                ->take(12)
                                ->get();

    foreach ($recentApprovedPapers as $paper) {
        $paper->sdgs = json_decode($paper->sdgs, true); 
    }

    // Featured advisers by download totals
    $featuredAdvisers = Paper::select('adviser')
        ->selectRaw('COUNT(*) as paper_count, SUM(downloads) as total_downloads')
        ->where('status', 'Approved') 
        ->groupBy('adviser')
        ->orderByDesc('total_downloads')
        ->take(3)
        ->get();

    // Fetch papers for each featured adviser
    foreach ($featuredAdvisers as $featuredAdviser) {
        $featuredAdviser->papers = Paper::where('adviser', $featuredAdviser->adviser)->get();
    }

    // Top 3 documents by downloads/views
    $featuredDocuments = Paper::orderByDesc('downloads')
        ->orderByDesc('views')
        ->where('status', 'Approved')
        ->take(3)
        ->get();


    $sdgCounts = $this->getSdgCounts(); // Call your SDG counting method
    // $documentTypes = ['Research Paper', 'Institutional Paper', 'Case Study', 'Thesis', 'Report'];
    $documentTypes = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research','Feasibility Studies', 'Report'];
    $sharedData = $this->getSharedData();
    $forms = $this->getForms();
    $pendingCount = $this->getPendingSubmissionCount();
    $pendingUserCount = $this->getPendingUserCount();
    $pendingTicketCount = $this->getPendingTicketCount();
    $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

    return view('index', compact(
        'user', 'recentApprovedPapers', 'allSdgs', 'featuredAdvisers', 'featuredDocuments', 'sdgCounts','documentTypes','announcements','sharedData','forms','pendingCount','pendingUserCount','pendingTicketCount','pendingDownloadRequestCount'
    ));
}


// Download Log 
public function showDownloadLogs()
{
    $sharedData = $this->getSharedData();
    $pendingCount = $this->getPendingSubmissionCount();

    // Fetch download logs with pagination
    $logs = Download::with('user', 'paper')->latest()->paginate(10);
    
    // Pass logs to the view
    return view('downloadlogs', array_merge($sharedData, ['logs' => $logs,  'pendingCount' => $pendingCount]));
}

public function submitDownloadRequest(Request $request, $paper_id)
{
    $paper = Paper::findOrFail($paper_id);

    // Create a new download request for the paper
    DownloadRequest::create([
        'user_id' => auth()->id(),
        'paper_id' => $paper->id,
        'reason' => $request->input('reason'),
        'is_approved' => false, // Default is pending approval
    ]);

    return redirect()->route('paperview', ['id' => $paper_id])->with('message', 'Your download request has been submitted.');
}

public function approveDownloadRequest($requestId)
{
    $request = DownloadRequest::findOrFail($requestId);

    // Set the status to 'approved' instead of setting is_approved to true
    $request->status = 'approved';
    $request->is_approved = true;  // You can still keep is_approved for internal tracking if needed
    $request->save();

    $user = $request->user;
    $user->save();

    return back()->with('success', 'Download request approved successfully.');
}


public function rejectDownloadRequest($requestId)
{
    \Log::info('Rejecting download request for ID: ' . $requestId);

    $request = DownloadRequest::findOrFail($requestId);

    // Set the status to 'rejected' instead of setting is_approved to false
    $request->status = 'rejected';
    $request->is_approved = false;  // You can keep is_approved if needed for internal tracking
    $request->save();

    // Optionally, you can send a notification or email to the user
    // You could also log the rejection reason here if needed

    return back()->with('error', 'Download request rejected.');
}
public function showDownloadRequests()
{
    $user = Auth::user(); // Get the currently authenticated user
    $pendingCount = $this->getPendingSubmissionCount();
    $pendingUserCount = $this->getPendingUserCount();
    $pendingTicketCount = $this->getPendingTicketCount();
    $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();
    $requests = DownloadRequest::where('status', 'pending')->paginate(10);

    return view('downloadrequest', compact('requests','user','pendingCount','pendingUserCount','pendingTicketCount','pendingDownloadRequestCount'));
}


}







