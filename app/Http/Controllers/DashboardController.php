<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Import the User model
use App\Models\Paper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; 

class DashboardController extends BaseController
{
    public function adminDashboard()
    {
        $totalApprovedPapers = Paper::where('status', 'approved')->count();

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

        // Fetch daily, weekly, and monthly trends
        $dailyTrends = DB::table('papers')
        ->select(DB::raw('DATE(datetime) as date'), DB::raw('COUNT(*) as count'))
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();

        // Weekly submissions with formatted date range
        /* 
            $weeklyTrends = DB::table('papers')
            ->select(DB::raw('YEAR(datetime) as year'), DB::raw('WEEK(datetime, 1) as week'), DB::raw('COUNT(*) as count'))
            ->groupBy('year', 'week')
            ->orderBy('year', 'asc')
            ->orderBy('week', 'asc')
            ->get()
            ->map(function ($trend) {
                // Ensure week is zero-padded
                $week = str_pad($trend->week, 2, '0', STR_PAD_LEFT);
        
                // Create a date using the proper format
                try {
                    $startDate = Carbon::createFromFormat('o-W', "{$trend->year}-{$week}")->startOfWeek()->format('l j F');
                    $endDate = Carbon::createFromFormat('o-W', "{$trend->year}-{$week}")->endOfWeek()->format('l j F');
                } catch (\Exception $e) {
                    \Log::error("Error creating date for week {$week}, year {$trend->year}: " . $e->getMessage());
                    $startDate = $endDate = 'Invalid Date';
                }
        
                return [
                    'week' => $trend->week,
                    'year' => $trend->year,
                    'date_range' => "{$startDate} - {$endDate}",
                    'count' => $trend->count,
                ];
            }); */
                        $weeklyTrends = DB::table('papers')
            ->select(
                DB::raw('YEAR(datetime) as year'),
                DB::raw('WEEK(datetime, 1) as week'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'week')
            ->orderBy('year', 'asc')
            ->orderBy('week', 'asc')
            ->get()
            ->map(function ($trend) {
                $week = str_pad($trend->week, 2, '0', STR_PAD_LEFT);
                
                \Log::info("Year: {$trend->year}, Week: {$trend->week}");
        
                if ((int)$week < 1 || (int)$week > 53) {
                    \Log::error("Invalid week number {$trend->week} for year {$trend->year}");
                    $startDate = $endDate = 'Invalid Date';
                } else {
                    try {
                        // Use Carbon's setISODate to handle ISO week dates
                        $date = Carbon::now()->setISODate($trend->year, $trend->week);
                        $startDate = $date->startOfWeek()->format('l j F');
                        $endDate = $date->endOfWeek()->format('l j F');
                    } catch (\Exception $e) {
                        \Log::error("Error creating date for week {$week}, year {$trend->year}: " . $e->getMessage());
                        $startDate = $endDate = 'Invalid Date';
                    }
                }
        
                return [
                    'week' => $trend->week,
                    'year' => $trend->year,
                    'date_range' => "{$startDate} - {$endDate}",
                    'count' => $trend->count,
                ];
            });
/*
            $weeklyTrends = DB::table('papers')
            ->select(DB::raw('YEAR(datetime) as year'), DB::raw('WEEK(datetime, 1) as week'), DB::raw('COUNT(*) as count'))
            ->groupBy('year', 'week')
            ->orderBy('year', 'asc')
            ->orderBy('week', 'asc')
            ->get()
            ->map(function ($trend) {
        $week = str_pad($trend->week, 2, '0', STR_PAD_LEFT);
        
        \Log::info("Year: {$trend->year}, Week: {$trend->week}");

        if ((int)$week < 1 || (int)$week > 53) {
            \Log::error("Invalid week number {$trend->week} for year {$trend->year}");
            $startDate = $endDate = 'Invalid Date';
        } else {
            try {
                $startDate = Carbon::createFromFormat('o-W', "{$trend->year}-{$week}")
                                  ->startOfWeek()
                                  ->format('l j F');
                $endDate = Carbon::createFromFormat('o-W', "{$trend->year}-{$week}")
                                ->endOfWeek()
                                ->format('l j F');
            } catch (\Exception $e) {
                \Log::error("Error creating date for week {$week}, year {$trend->year}: " . $e->getMessage());
                $startDate = $endDate = 'Invalid Date';
            }
        }

        return [
            'week' => $trend->week,
            'year' => $trend->year,
            'date_range' => "{$startDate} - {$endDate}",
            'count' => $trend->count,
        ];
    }); */

            
        $monthlyTrends = DB::table('papers')
        ->select(DB::raw('DATE_FORMAT(datetime, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get(); 

        

        // Fetch approved document type counts
        $documentTypeCounts = DB::table('papers')
            ->select('document_type', DB::raw('count(*) as count'))
            ->where('status', 'Approved')
            ->groupBy('document_type')
            ->get();

        // Prepare document type data for the view
        // $documentTypes = ['Research Paper', 'Institutional Paper', 'Case Study', 'Thesis', 'Report']; // Define your document types
        $documentTypes = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research','Feasibility Studies', 'Report'];

        $documentCounts = array_fill_keys($documentTypes, 0); // Initialize all counts to zero

        // Populate the counts from the fetched data
        foreach ($documentTypeCounts as $documentTypeCount) {
            // Check if the document type exists in the predefined types
            if (array_key_exists($documentTypeCount->document_type, $documentCounts)) {
                $documentCounts[$documentTypeCount->document_type] = $documentTypeCount->count; // Assign the actual count
            }
        }

        $user = auth()->user();
        $insights = $this->generateInsights();

    // Run the Python script and capture the output
    $command = 'C:\Users\Kyle\AppData\Local\Programs\Python\Python313\python.exe ' . base_path('scripts/data_analytics2.py');
    $output = shell_exec($command);
    Log::info('Python script output: ' . $output);

    /* $command2 = 'C:\Users\Kyle\AppData\Local\Programs\Python\Python313\python.exe ' . base_path('scripts/data_analytics3.py');
    $output2 = shell_exec($command2); */


    // Read the generated HTML file
    $htmlReport = '';
    $htmlFilePath = public_path('analytics_report.html');
    if (file_exists($htmlFilePath)) {
        $htmlReport = file_get_contents($htmlFilePath);
    } else {
        // Fallback message if file does not exist
        $htmlReport = 'No analytics report generated or found.';
    }


    // Log output for debugging
    Log::info('Python script output: ' . $output);

    // If no output, set a default message
    if (empty($output)) {
        $output = "No output returned or there was an error with the Python script.";
    }

    // Get the user insights
    $user = auth()->user();
    $insights = $this->generateInsights();
    $pendingCount = $this->getPendingSubmissionCount();
    $pendingUserCount = $this->getPendingUserCount();
    $pendingTicketCount = $this->getPendingTicketCount();
    $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();


    $surveyQuestions = [
        'dp1' => 'Demographic Profile - Gender:',
        'dp2' => 'Demographic Profile - Current academic or professional status:',
        'q1' => 'I find the website\'s search function helpful in easily finding the research papers I need.',
        'q2' => 'I find it easy to use the website to filter research papers effectively.',
        'q3' => 'The website displays all necessary details about research papers (e.g., authors, abstract, SDGs).',
        'q4' => 'The system allows me to effectively filter and customize data for specific analytics needs.',
        'q5' => 'I find it easy to navigate through different sections of the website (e.g., Help Page, Archive, Home Page).',
        'q6' => 'The platform provides real-time updates on research papers and activities.',
        'q7' => 'It is easy to report issues using the ticketing service.',
        'q8' => 'I find the website provides sufficient access to research papers aligned with the Sustainable Development Goals (e.g., climate action, quality education, gender equality).',
        'q9' => 'The SDG filtering tools on the website help me efficiently locate research papers relevant to specific SDG goals.',
        'q10' => 'The platform effectively promotes awareness of SDGs through its features.',
        'q11' => 'Users find the system easy to use and are generally satisfied with its performance.',
        'q12' => 'The system’s features function reliably without frequent breakdowns.',
        'q13' => 'The navigation bar or menu is well-organized and intuitive.',
        'q14' => 'I am satisfied with the overall webiste service.',
        'q15' => 'What improvements or features would you like to see implemented in the system to enhance your overall experience and better meet your needs?'
    ];

    // Return the view with all necessary data
    return view('AdminDashboard', compact(
        'user', 
        'coursesByCollege', 
        'totalApprovedPapers', 
        'sdgs', 
        'dailyTrends', 
        'weeklyTrends', 
        'monthlyTrends', 
        'documentTypes', 
        'documentCounts', 
        'insights',
        'htmlReport',
        'output',// Pass the Python output here
        'pendingCount',
        'pendingUserCount',
        'pendingTicketCount',
        'pendingDownloadRequestCount',
        'surveyQuestions',
    ));
    }

    public function userDashboard()
    {
        $user = auth()->user();
        $userId = auth()->id();
    
        $approvedPapersCount = DB::table('papers')
            ->where('status', 'approved')
            ->where('uploaded_by', $userId)
            ->count();
    
        $submissionData = DB::table('papers')
            ->select('document_type', DB::raw('count(*) as count'))
            ->where('uploaded_by', $userId)
            ->where('status', 'approved') // Only approved papers
            ->groupBy('document_type')
            ->pluck('count', 'document_type');
    
        $approvedPapers = DB::table('papers')
            ->select('id', 'title', 'downloads', 'views')
            ->where('status', 'approved')
            ->where('uploaded_by', $userId)
            ->get();
    
        // $submissionLabels = ['Research Paper', 'Institutional Paper', 'Case Study', 'Thesis', 'Report'];
        $submissionLabels = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research','Feasibility Studies', 'Report'];
        $submissionCounts = [];
        foreach ($submissionLabels as $type) {
            $submissionCounts[] = DB::table('papers')
                ->where('uploaded_by', $userId)
                ->where('document_type', $type)
                ->where('status', 'approved') // Only approved papers
                ->count();
        }


    
        return view('UserDashboard', compact('user', 'approvedPapersCount', 'submissionData', 'approvedPapers', 'submissionLabels', 'submissionCounts',));
    }

    public function messages()
    {
        $user = auth()->user();
        return view('messages', compact('user'));
    }

    public function articles()
    {
        $user = auth()->user();
        return view('articles', compact('user'));
    }

    public function UserArticles()
    {
        $user = auth()->user();
        return view('UserArticles', compact('user'));
    }

    public function showUserManagement()
    {
        $status = request()->get('status', 'pending'); // Get the status from the request or default to 'pending'
        $user = auth()->user(); // Get the authenticated user
        $pendingCount = $this->getPendingSubmissionCount();
        $pendingUserCount = $this->getPendingUserCount();
        $pendingTicketCount = $this->getPendingTicketCount();
        $pendingDownloadRequestCount = $this->getPendingDownloadRequestCount();

        // Retrieve users based on status
        $users = User::when($status === 'pending', function ($query) {
                return $query->where('status', 'pending'); // Filter for pending users
            })
            ->when($status === 'approved', function ($query) {
                return $query->where('status', 'approved'); // Filter for approved users
            })
            ->when($status === 'rejected', function ($query) {
                return $query->where('status', 'rejected'); // Filter for rejected users
            })
            ->where('is_verified', 1) // Only include users with is_verified = 1
            ->paginate(15); // Get the filtered users
    
        return view('UserManagement', compact('user', 'status', 'users','pendingCount','pendingUserCount','pendingTicketCount','pendingDownloadRequestCount')); // Pass $user, $status, and $users to the view
    }

    public function getApprovedPapersByCollege(Request $request)
    {
        $college = $request->input('college');
        // Logic to count approved papers by the selected college
        $approvedPapers = Paper::where('status', 'approved')->where('college', $college)->count();
        
        return response()->json(['approvedPapers' => $approvedPapers]);
    }

    public function getTotalApprovedPapers() {
        $totalApprovedPapers = Paper::where('status', 'approved')->count();
        
        return response()->json(['totalApprovedPapers' => $totalApprovedPapers]);
    }

    // TEST TEST TEST TEST
    // CHART FOR DATA FOR PAPERS SDGS
    public function getPapersDataForCharts(Request $request) 
    {
        // Define the SDGs
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

        // Initialize data arrays
        $barChartData = array_fill(0, count($sdgs), 0); // For bar chart
        $pieChartData = array_fill(0, count($sdgs), 0); // For pie chart
        $lineChartData = []; // Initialize this if you have time-series data

        // Fetch approved papers with filters if provided
        $approvedPapers = Paper::where('status', 'approved');

        // Apply filters if college or course is specified
        if ($request->has('college') && $request->input('college')) {
            $approvedPapers->where('college', $request->input('college'));
        }
        if ($request->has('course') && $request->input('course')) {
            $approvedPapers->where('course', $request->input('course'));
        }

        $approvedPapers = $approvedPapers->get();

        // Count papers per SDG
        foreach ($approvedPapers as $paper) {
            // Decode the SDGs associated with the paper
            $paperSdgs = json_decode($paper->sdgs); // Assuming sdgs is a JSON string in the database
            
            // Increment counts for each SDG the paper is associated with
            foreach ($paperSdgs as $sdg) {
                $index = array_search($sdg, $sdgs); // Get the index of the SDG
                if ($index !== false) {
                    $barChartData[$index]++; // Increment for bar chart
                    $pieChartData[$index]++; // Increment for pie chart
                }
            }
        }

        // Populate lineChartData as needed (this part depends on how you want to represent trends)
        // For example, you can count papers per SDG over time if you have a 'created_at' date

        // Return data as JSON
        return response()->json(['sdgsData' => [
            'barChartData' => $barChartData,
            'pieChartData' => $pieChartData,
            'lineChartData' => $lineChartData // Populate this if needed
        ]]);
    }
    // TEST TEST TEST TEST


    public function getApprovedPapersByCourse(Request $request)
    {
        // Fetch approved papers based on the selected course
        $course = $request->get('course');
        $approvedPapers = Paper::where('course', $course)
                            ->where('status', 'approved')
                            ->count(); // Example query

        return response()->json(['approvedPapers' => $approvedPapers]);
    }
    public function getApprovedPapersBySdg(Request $request)
    {
        $sdg = $request->input('sdg');
        // Logic to count approved papers by the selected SDG
        $approvedPapers = Paper::where('status', 'approved')->where('sdg', $sdg)->count(); // Adjust the condition based on your DB structure

        return response()->json(['approvedPapers' => $approvedPapers]);
    }
    public function getFilteredData(Request $request) {
        $college = $request->input('college');
        $course = $request->input('course');
    
        // Fetch the SDG data based on the filters
        $query = Paper::query();
    
        if ($college) {
            $query->where('college', $college);
        }
        if ($course) {
            $query->where('course', $course);
        }
    
        // Assume you have a method to get SDG data
        $data = $this->getSDGData($query->get());
    
        return response()->json($data);
    }

    // data analytical

    public function analyzeSdgs($college = null, $course = null)
{
    // Start the query
    $query = DB::table('papers')
        ->where('status', 'approved'); // Filter only approved papers

    // Modify the query based on the presence of college and course
    if ($college) {
        $query->where('college', $college); // Filter by college
    }

    if ($course) {
        $query->where('course', $course); // Filter by course
    }

    // Get the SDG data for the filtered papers
    $approvedPapers = $query->pluck('sdgs'); // Get only the SDG field for approved papers

    $sdgCounts = [];

    // Process each paper's SDGs
    foreach ($approvedPapers as $sdgs) {
        // Decode the SDG field
        $decodedSdgs = json_decode($sdgs, true);
        
        // Check if the result is a string (indicating double-encoded JSON)
        if (is_string($decodedSdgs)) {
            // Decode again to get the actual array
            $decodedSdgs = json_decode($decodedSdgs, true);
        }
        
        // Skip if decoding still fails or if it's not an array
        if (!is_array($decodedSdgs)) {
            \Log::warning("Invalid SDG format for: {$sdgs}");
            continue;
        }
        
        // Process each SDG in the decoded array
        foreach ($decodedSdgs as $sdg) {
            $sdg = trim($sdg); // Clean up any whitespace
            $sdgCounts[$sdg] = ($sdgCounts[$sdg] ?? 0) + 1;
        }
    }

    // Sort SDGs by count (highest first)
    arsort($sdgCounts);

    return $sdgCounts;
}
private function analyzeDocumentTypesAlt($college, $course)
{
    return DB::table('papers')
        ->where('college', $college)
        ->where('course', $course)
        ->where('status', 'approved') // Ensure only approved papers are considered
        ->groupBy('document_type')
        ->select('document_type', DB::raw('COUNT(*) as count'))
        ->pluck('count', 'document_type')
        ->toArray();
}

    public function analyzeDocumentTypes()
{
    // Assuming you have a papers table and 'document_type' field in your database
    $papers = \DB::table('papers')
    ->select('document_type')
    ->where('status', 'approved')
    ->get();
    $documentTypeCounts = [];

    foreach ($papers as $paper) {
        $documentType = trim($paper->document_type); // Clean up any whitespace
        $documentTypeCounts[$documentType] = ($documentTypeCounts[$documentType] ?? 0) + 1;
    }

    arsort($documentTypeCounts); // Sort document types by count (highest first)

    return $documentTypeCounts;
}
public function generateInsights()
{
    // SDG Data Analysis
    $sdgCounts = $this->analyzeSdgs();
    // Document Type Data Analysis
    $documentTypeCounts = $this->analyzeDocumentTypes();

    // Check if both analyses return some data
    if (empty($sdgCounts) && empty($documentTypeCounts)) {
        return [
            'trends' => 'No data available to generate trends.',
            'comparisons' => 'No SDG or document type comparisons can be made at this time.',
            'highlights' => 'No highlights available due to insufficient data.',
            'document_type_analysis' => 'No document type analysis available.'
        ];
    } 

    // SDG Insights
    $totalPapers = array_sum($sdgCounts); // Total submissions
    $sdgPercentages = [];

    // Calculate percentage for each SDG
    foreach ($sdgCounts as $sdg => $count) {
        if ($sdg === '' || !is_numeric($count) || $count < 0) {
            continue; // Skip invalid SDGs
        }
        $sdgPercentages[$sdg] = round(($count / $totalPapers) * 100, 2);
    }

    // Check if there are valid SDG percentages to process
    if (empty($sdgPercentages)) {
        return [
            'trends' => 'No valid SDG data to analyze.',
            'comparisons' => 'No valid comparisons can be made.',
            'highlights' => 'No highlights available.',
            'document_type_analysis' => 'No document type analysis available.'
        ];
    }

    // Sort SDGs by percentage (descending order)
    arsort($sdgPercentages);

    // Build the comparisons message
    $comparisonsMessage = '';
    $highestPercentage = max($sdgPercentages); // Find the highest percentage

    $mostRepresented = array_key_first($sdgPercentages); // Get the first (most represented) SDG
    if ($mostRepresented !== null) {
        $mostRepresentedPercentage = $sdgPercentages[$mostRepresented];

        foreach ($sdgPercentages as $sdg => $percentage) {
            $comparisonsMessage .= "<br>$sdg accounts for $percentage% of submissions. ";
            if ($percentage === $highestPercentage) {
                $comparisonsMessage .= "Therefore, it is one of the most represented SDGs. ";
            }
        }
    }

    // SDG Highlights
    $allSdgs = [
        "No Poverty", "Zero Hunger", "Good Health and Well-being",
        "Quality Education", "Gender Equality", "Clean Water and Sanitation",
        "Affordable and Clean Energy", "Decent Work and Economic Growth",
        "Industry, Innovation, and Infrastructure", "Reduced Inequality",
        "Sustainable Cities and Communities", "Responsible Consumption and Production",
        "Climate Action", "Life Below Water", "Life on Land", "Peace, Justice, and Strong Institutions",
        "Partnerships for the Goals"
    ];

    // Get missing SDGs
    $missingSdgs = array_diff($allSdgs, array_keys($sdgCounts));

    // SDG with the least submissions
    $lowestSdgsMessage = '';
    if (!empty($sdgCounts)) {
        $minCount = min($sdgCounts);
        $lowestSdgs = array_keys($sdgCounts, $minCount);
        if (count($lowestSdgs) > 0) {
            $lowestSdgsMessage = "SDGs with the least submissions: " . implode(', ', $lowestSdgs) . ". These SDGs need more focus and action.<br>";
        }
    }

    // Document Type Insights
    $documentTypeMessage = '';
    $documentTypeTotal = array_sum($documentTypeCounts); // Total number of document types

    if ($documentTypeTotal > 0) {
        $allDocumentTypes = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research', 'Feasibility Studies', 'Report'];
        $missingDocumentTypes = array_diff($allDocumentTypes, array_keys($documentTypeCounts)); // Document types with no submissions

        // Report document types with no submissions
        if (count($missingDocumentTypes) > 0) {
            $documentTypeMessage .= "No submissions for the following document types: " . implode(', ', $missingDocumentTypes) . ". These document types need more submissions.<br><br>";
        }

        // Find the most common document type(s)
        $maxDocTypeCount = max($documentTypeCounts);
        $topDocumentTypes = array_keys($documentTypeCounts, $maxDocTypeCount);
        $topDocumentTypesList = implode(', ', $topDocumentTypes);

        // Construct message about document types
        $documentTypeMessage .= "Most common document type(s): " . $topDocumentTypesList . " with " . $maxDocTypeCount . " submissions each.<br><br>";

        // Percentage breakdown for each document type
        foreach ($documentTypeCounts as $docType => $count) {
            $percentage = round(($count / $documentTypeTotal) * 100, 2);
            $documentTypeMessage .= "$docType: $percentage% of submissions. ";
        }

        // Find document types with the least submissions
        $minDocTypeCount = min($documentTypeCounts);
        $leastDocumentTypes = array_keys($documentTypeCounts, $minDocTypeCount);

        // If all document types have submissions, report the least submitted types
        if (count($missingDocumentTypes) === 0 && count($leastDocumentTypes) > 0) {
            $documentTypeMessage .= "Least document type(s): " . implode(', ', $leastDocumentTypes) . ". These document types need more submissions.<br>";
        }
    } else {
        $documentTypeMessage = "No document type data available.";
    }

    return [
        'trends' => count($sdgCounts) > 1
            ? "The top trending SDG(s) is/are: '" . implode(', ', array_keys($sdgCounts)) . "', each appearing " . max($sdgCounts) . " times.<br>"
            : "The top trending SDG is '$mostRepresented', appearing $mostRepresentedPercentage% of all submissions.<br>",
        'comparisons' => $comparisonsMessage,
        'highlights' => count($missingSdgs) > 0
            ? "No papers have been submitted for the following SDGs: " . implode(', ', $missingSdgs) . ".<br>"
            : "All SDGs have at least one paper submitted.<br>" . ($lowestSdgsMessage ? ' ' . $lowestSdgsMessage : ''),
        'document_type_analysis' => $documentTypeMessage
    ];
}
/* public function generateInsights()
{
    // SDG Data Analysis
    $sdgCounts = $this->analyzeSdgs();
    // Document Type Data Analysis
    $documentTypeCounts = $this->analyzeDocumentTypes();

    if (empty($sdgCounts) && empty($documentTypeCounts)) {
        return [
            'trends' => 'No data available to generate trends.',
            'comparisons' => 'No SDG or document type comparisons can be made at this time.',
            'highlights' => 'No highlights available due to insufficient data.',
            'document_type_analysis' => 'No document type analysis available.'
        ];
    }

    // SDG Insights
    $totalPapers = array_sum($sdgCounts); // Total submissions
    $sdgPercentages = [];

    // Calculate percentage for each SDG
    foreach ($sdgCounts as $sdg => $count) {
        $sdgPercentages[$sdg] = round(($count / $totalPapers) * 100, 2);
    }

    // Sort SDGs by percentage (descending order)
    arsort($sdgPercentages);

    // Build the comparisons message
    $comparisonsMessage = '';
    $mostRepresented = array_key_first($sdgPercentages); // Get the first (most represented) SDG
    $mostRepresentedPercentage = $sdgPercentages[$mostRepresented];

    foreach ($sdgPercentages as $sdg => $percentage) {
        if ($sdg == $mostRepresented) {
            $comparisonsMessage .= "$sdg accounts for $percentage% of all submissions, making it the most represented SDG. ";
        } else {
            $comparisonsMessage .= "$sdg accounts for $percentage% of submissions. ";
        }
    }

    // SDG Highlights
    $allSdgs = [
        "No Poverty", "Zero Hunger", "Good Health and Well-being",
        "Quality Education", "Gender Equality", "Clean Water and Sanitation",
        "Affordable and Clean Energy", "Decent Work and Economic Growth",
        "Industry, Innovation, and Infrastructure", "Reduced Inequalities",
        "Sustainable Cities and Communities", "Responsible Consumption and Production",
        "Climate Action", "Life Below Water", "Life on Land", "Peace, Justice, and Strong Institutions",
        "Partnerships for the Goals"
    ];
    $missingSdgs = array_diff($allSdgs, array_keys($sdgCounts));

    // SDG with the least submissions
    $minCount = min($sdgCounts);
    $lowestSdgs = array_keys($sdgCounts, $minCount);
    $lowestSdgsMessage = '';
    if (count($lowestSdgs) > 0) {
        $lowestSdgsMessage = "SDGs with the least submissions: " . implode(', ', $lowestSdgs) . ". These SDGs need more focus and action.<br>";
    }

    // Document Type Insights
    $documentTypeTotal = array_sum($documentTypeCounts); // Total number of document types
    $documentTypeMessage = '';

    if ($documentTypeTotal > 0) {
        // Check for zero submissions for any document types
        $allDocumentTypes = ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research','Feasibility Studies', 'Report'];
        $missingDocumentTypes = array_diff($allDocumentTypes, array_keys($documentTypeCounts)); // Document types with no submissions

        // Report document types with no submissions
        if (count($missingDocumentTypes) > 0) {
            $documentTypeMessage .= "No submissions for the following document types: " . implode(', ', $missingDocumentTypes) . ". These document types need more submissions.<br><br>";
        }

        // Find the most common document type(s)
        $maxDocTypeCount = max($documentTypeCounts);
        $topDocumentTypes = array_keys($documentTypeCounts, $maxDocTypeCount);
        $topDocumentTypesList = implode(', ', $topDocumentTypes);

        // Construct message about document types
        $documentTypeMessage .= "Most common document type(s): " . $topDocumentTypesList . " with " . $maxDocTypeCount . " submissions each.<br><br>";

        // Percentage breakdown for each document type
        foreach ($documentTypeCounts as $docType => $count) {
            $percentage = round(($count / $documentTypeTotal) * 100, 2);
            $documentTypeMessage .= " $docType: $percentage% of submissions. ";
        }

        // Find document types with the least submissions
        $minDocTypeCount = min($documentTypeCounts);
        $leastDocumentTypes = array_keys($documentTypeCounts, $minDocTypeCount);

        // If all document types have submissions, report the least submitted types
        if (count($missingDocumentTypes) === 0) {
            if (count($leastDocumentTypes) > 0) {
                $documentTypeMessage .= "Least document type(s): " . implode(', ', $leastDocumentTypes) . ". These document types need more submissions.<br>";
            }
        }
    } else {
        $documentTypeMessage = "No document type data available.";
    }

    return [
        'trends' => count($sdgCounts) > 1
            ? "The top trending SDG(s) is/are: '" . implode(', ', array_keys($sdgCounts)) . "', each appearing " . max($sdgCounts) . " times.<br>"
            : "The top trending SDG is '$mostRepresented', appearing $mostRepresentedPercentage% of all submissions.<br>",
        'comparisons' => $comparisonsMessage,
        'highlights' => count($missingSdgs) > 0
            ? "No papers have been submitted for the following SDGs: " . implode(', ', $missingSdgs) . ".<br>"
            : "All SDGs have at least one paper submitted.<br>" . ($lowestSdgsMessage ? ' ' . $lowestSdgsMessage : ''),
        'document_type_analysis'  => $documentTypeMessage
    ];
} */




    
    /* public function generateInsights()
    {
        $sdgCounts = $this->analyzeSdgs();
    
        if (empty($sdgCounts)) {
            return [
                'trends' => 'No data available to generate trends.',
                'comparisons' => 'No SDG comparisons can be made at this time.',
                'highlights' => 'No highlights available due to insufficient data.'
            ];
        }
    
        $trendingSdg = array_key_first($sdgCounts); 
        $topSdgCount = $sdgCounts[$trendingSdg] ?? 0;
    
        $totalPapers = array_sum($sdgCounts);
        $topSdgPercentage = $totalPapers ? ($topSdgCount / $totalPapers) * 100 : 0;
        // New
        $maxCount = max($sdgCounts); 
        $topSdgs = array_filter($sdgCounts, fn($count) => $count === $maxCount); // Get all SDGs with the highest count
        $topSdgsList = implode(', ', array_keys($topSdgs)); // Convert SDG names to a readable list
    
        // Comparison: Percentage of total papers for the top SDG
        $totalPapers = array_sum($sdgCounts);
        $topSdgPercentage = $totalPapers ? ($maxCount / $totalPapers) * 100 : 0;

        // Highlight: Find SDGs with zero submissions
        $allSdgs = [
            "No Poverty", "Zero Hunger", "Good Health and Well-being",
            "Quality Education", "Gender Equality", "Clean Water and Sanitation",
            "Affordable and Clean Energy", "Decent Work and Economic Growth",
            "Industry, Innovation, and Infrastructure", "Reduced Inequalities",
            "Sustainable Cities and Communities", "Responsible Consumption and Production",
            "Climate Action", "Life Below Water", "Life on Land", "Peace, Justice, and Strong Institutions",
            "Partnerships for the Goals"
        ];


    
        $missingSdgs = array_diff($allSdgs, array_keys($sdgCounts));
        
    
        return [
            // 'trends' => "The top trending SDG is '$trendingSdg', appearing in $topSdgCount papers.",
            'trends' => "The top trending SDG(s) is/are: '$topSdgsList', each appearing $maxCount times.",
            'comparisons' => "$trendingSdg accounts for " . round($topSdgPercentage, 2) . "% of all submissions.",
            'highlights' => count($missingSdgs) > 0
                ? "No papers have been submitted for the following SDGs: " . implode(', ', $missingSdgs) . "."
                : "All SDGs have at least one paper submitted."
        ];*/

        public function updateInsights(Request $request)
        {
            $college = $request->input('college');
            $course = $request->input('course');
        
            // Fetch insights based on provided filters
            if ($college && $course) {
                $insights = $this->generateInsightsForCollegeAndCourse($college, $course);
            } elseif ($college) {
                $insights = $this->generateInsightsForCollege($college);
            } else {
                $insights = $this->generateInsights();
            }
        
            // Return the data as JSON for the frontend
            return response()->json($insights);
        }
        private function generateInsightsForCollegeAndCourse($college, $course)
{
    // Initialize arrays for insights
    $highlights = [];
    $comparisons = [];
    $trends = [];

    // Fetch SDG counts for the specific college and course
    $sdgCounts = $this->analyzeSdgs($college, $course);

    // If no data available for SDGs
    if (empty($sdgCounts)) {
        return [
            'trends' => "No data available to generate trends for $college - $course.",
            'comparisons' => "No SDG comparisons for $college - $course.",
            'highlights' => "No highlights available for $college - $course.",
            'document_type_analysis' => 'No document type analysis available for the selected filters.'
        ];
    }

    // Total papers submitted
    $totalSdgPapers = array_sum($sdgCounts);

    // List of all possible SDGs
    $allSdgs = [
        'No Poverty', 'Quality Education', 'Good Health and Well-being', 'Gender Equality',
        'Clean Water and Sanitation', 'Affordable and Clean Energy', 'Decent Work and Economic Growth',
        'Industry, Innovation and Infrastructure', 'Reduced Inequality', 'Sustainable Cities and Communities',
        'Responsible Consumption and Production', 'Climate Action', 'Life Below Water', 'Life on Land',
        'Peace, Justice and Strong Institutions', 'Partnerships for the Goals'
    ];

    // Identify absent SDGs
    $absentSdgs = array_diff($allSdgs, array_keys($sdgCounts));

    // Identify the SDG with the least submissions (excluding absent ones)
    $nonZeroSdgs = array_filter($sdgCounts, fn($count) => $count > 0);
    $leastSdg = !empty($nonZeroSdgs) ? array_keys($nonZeroSdgs, min($nonZeroSdgs)) : [];
    $leastSdgCount = !empty($leastSdg) ? min($nonZeroSdgs) : 0;

    // Highlights Section
    if ($totalSdgPapers === 0) {
        $highlights[] = "No SDGs have any submissions in $college - $course, indicating a potential gap in research related to sustainable development. These SDGs need immediate focus.";
    } else {
        $submittedSdgs = array_keys($nonZeroSdgs);
        $highlights[] = "The following SDGs have submissions in $college - $course: " . implode(', ', $submittedSdgs) . ". These SDGs are currently the primary focus.";

        if (!empty($absentSdgs)) {
            $highlights[] = "The following SDGs have no submissions: " . implode(', ', $absentSdgs) . ". These SDGs need more attention and focus for future submissions.";
        }

        if (!empty($leastSdg)) {
            $highlights[] = "<br>The SDG with the least submissions among the submissions are '{$leastSdg[0]}' with $leastSdgCount submission(s). This SDG could be prioritized for future research and attention.";
        }
    }

    // Comparisons Section
    foreach ($sdgCounts as $sdg => $count) {
        if ($count > 0) {
            $percentage = round(($count / $totalSdgPapers) * 100, 2);
            $comparisons[] = "$sdg accounts for $percentage% of all submissions in $college - $course.";
        }
    }

    // Trends Section
    foreach ($sdgCounts as $sdg => $count) {
        if ($count > 0) {
            $trends[] = "In $college - $course, the SDG '$sdg' has the highest focus with $count submission(s), indicating an active engagement with this global challenge.";
        }
    }

    // Fetch Document Type Counts for the specific college and course using the alternate function
    $documentTypeCounts = $this->analyzeDocumentTypesAlt($college, $course);

    // Initialize Document Type Analysis Messages
    $documentTypeMessage = '';
    if (!empty($documentTypeCounts)) {
        $documentTypeTotal = array_sum($documentTypeCounts); // Total document submissions
        foreach ($documentTypeCounts as $docType => $count) {
            $percentage = round(($count / $documentTypeTotal) * 100, 2);
            $documentTypeMessage .= "$docType: $percentage% of submissions.<br>";
        }

        // Find the most common document type(s)
        $maxDocTypeCount = max($documentTypeCounts);
        $topDocumentTypes = array_keys($documentTypeCounts, $maxDocTypeCount);
        $documentTypeMessage .= "Most common document type(s): " . implode(', ', $topDocumentTypes) . " with $maxDocTypeCount submission(s).<br>";
    } else {
        $documentTypeMessage = "No document types submitted for $college - $course.";
    }

    // Return insights
    return [
        'trends' => implode('<br>', $trends),
        'comparisons' => implode('<br>', $comparisons),
        'highlights' => implode('<br>', $highlights),
        'document_type_analysis' => $documentTypeMessage // Include document type analysis in the return
    ];
}


        //this is the second code 
/*        private function generateInsightsForCollegeAndCourse($college, $course)
{
    // Initialize arrays for insights
    $highlights = [];
    $comparisons = [];
    $trends = [];

    // Fetch SDG counts for the specific college and course
    $sdgCounts = $this->analyzeSdgs($college, $course);

    // If no data available for SDGs
    if (empty($sdgCounts)) {
        return [
            'trends' => "No data available to generate trends for $college - $course.",
            'comparisons' => "No SDG comparisons for $college - $course.",
            'highlights' => "No highlights available for $college - $course.",
            'document_type_analysis' => 'No document type analysis available for the selected filters.'
        ];
    }

    // Total papers submitted
    $totalSdgPapers = array_sum($sdgCounts);

    // List of all possible SDGs
    $allSdgs = [
        'No Poverty', 'Quality Education', 'Good Health and Well-being', 'Gender Equality',
        'Clean Water and Sanitation', 'Affordable and Clean Energy', 'Decent Work and Economic Growth',
        'Industry, Innovation and Infrastructure', 'Reduced Inequality', 'Sustainable Cities and Communities',
        'Responsible Consumption and Production', 'Climate Action', 'Life Below Water', 'Life on Land',
        'Peace, Justice and Strong Institutions', 'Partnerships for the Goals'
    ];

    // Identify absent SDGs
    $absentSdgs = array_diff($allSdgs, array_keys($sdgCounts));

    // Identify the SDG with the least submissions (excluding absent ones)
    $nonZeroSdgs = array_filter($sdgCounts, fn($count) => $count > 0);
    $leastSdg = !empty($nonZeroSdgs) ? array_keys($nonZeroSdgs, min($nonZeroSdgs)) : [];
    $leastSdgCount = !empty($leastSdg) ? min($nonZeroSdgs) : 0;

    // Highlights Section
    if ($totalSdgPapers === 0) {
        // No SDG submissions at all
        $highlights[] = "No SDGs have any submissions in $college - $course, indicating a potential gap in research related to sustainable development. These SDGs need immediate focus.";
    } else {
        // Some SDGs have submissions
        $submittedSdgs = array_keys($nonZeroSdgs);
        $highlights[] = "The following SDGs have submissions in $college - $course: " . implode(', ', $submittedSdgs) . ". These SDGs are currently the primary focus.";

        if (!empty($absentSdgs)) {
            $highlights[] = "The following SDGs have no submissions: " . implode(', ', $absentSdgs) . ". These SDGs need more attention and focus for future submissions.";
        }

        if (!empty($leastSdg)) {
            $highlights[] = "The SDG with the least submissions is '{$leastSdg[0]}' with $leastSdgCount submission(s). This SDG could be prioritized for future research and attention.";
        }
    }

    // Comparisons Section
    foreach ($sdgCounts as $sdg => $count) {
        if ($count > 0) {
            $percentage = round(($count / $totalSdgPapers) * 100, 2);
            $comparisons[] = "$sdg accounts for $percentage% of all submissions in $college - $course.";
        }
    }

    // Trends Section
    foreach ($sdgCounts as $sdg => $count) {
        if ($count > 0) {
            $trends[] = "In $college - $course, the SDG '$sdg' has the highest focus with $count submission(s), indicating an active engagement with this global challenge.";
        }
    }

    // Fetch Document Type Counts for the specific college and course
    $documentTypeCounts = $this->analyzeDocumentTypes();

    // Initialize Document Type Analysis Messages
    $documentTypeMessage = '';
    if (!empty($documentTypeCounts)) {
        $documentTypeTotal = array_sum($documentTypeCounts); // Total document submissions
        foreach ($documentTypeCounts as $docType => $count) {
            $percentage = round(($count / $documentTypeTotal) * 100, 2);
            $documentTypeMessage .= "$docType: $percentage% of submissions.<br>";
        }

        // Find the most common document type(s)
        $maxDocTypeCount = max($documentTypeCounts);
        $topDocumentTypes = array_keys($documentTypeCounts, $maxDocTypeCount);
        $documentTypeMessage .= "Most common document type(s): " . implode(', ', $topDocumentTypes) . " with $maxDocTypeCount submissiosn(s).<br>";
    } else {
        $documentTypeMessage = "No document types submitted for $college - $course.";
    }

    // Return insights
    return [
        'trends' => implode('<br>', $trends),
        'comparisons' => implode('<br>', $comparisons),
        'highlights' => implode('<br>', $highlights),
        'document_type_analysis' => $documentTypeMessage // Include document type analysis in the return
    ];
}
        
        /* private function generateInsightsForCollegeAndCourse($college, $course)
        {
            // Fetch SDG counts for the specific college and course
            $sdgCounts = $this->analyzeSdgs($college, $course);
        
            // If no data available
            if (empty($sdgCounts)) {
                return [
                    'trends' => "No data available to generate trends for $college - $course.",
                    'comparisons' => "No SDG comparisons for $college - $course.",
                    'highlights' => "No highlights available for $college - $course.",
                ];
            }
        
            // Total papers submitted
            $totalSdgPapers = array_sum($sdgCounts);
        
            // List of all possible SDGs
            $allSdgs = [
                'No Poverty', 'Quality Education', 'Good Health and Well-being', 'Gender Equality', 
                'Clean Water and Sanitation', 'Affordable and Clean Energy', 'Decent Work and Economic Growth', 
                'Industry, Innovation and Infrastructure', 'Reduced Inequality', 'Sustainable Cities and Communities', 
                'Responsible Consumption and Production', 'Climate Action', 'Life Below Water', 'Life on Land', 
                'Peace, Justice and Strong Institutions', 'Partnerships for the Goals'
            ];
        
            // Identify absent SDGs
            $absentSdgs = array_diff($allSdgs, array_keys($sdgCounts));
        
            // Identify the SDG with the least submissions (excluding absent ones)
            $nonZeroSdgs = array_filter($sdgCounts, fn($count) => $count > 0);
            $leastSdg = !empty($nonZeroSdgs) ? array_keys($nonZeroSdgs, min($nonZeroSdgs)) : [];
            $leastSdgCount = !empty($leastSdg) ? min($nonZeroSdgs) : 0;
        
            // Highlights Section
            $highlights = [];
        
            if ($totalSdgPapers === 0) {
                // No SDG submissions at all
                $highlights[] = "No SDGs have any submissions in $college - $course, indicating a potential gap in research related to sustainable development. These SDGs need immediate focus.";
            } else {
                // Some SDGs have submissions
                $submittedSdgs = array_keys($nonZeroSdgs);
                $highlights[] = "The following SDGs have submissions in $college - $course: " . implode(', ', $submittedSdgs) . ". These SDGs are currently the primary focus.";
        
                if (!empty($absentSdgs)) {
                    $highlights[] = "The following SDGs have no submissions: " . implode(', ', $absentSdgs) . ". These SDGs need more attention and focus for future submissions.";
                }
        
                if (!empty($leastSdg)) {
                    $highlights[] = "The SDG with the least submissions is '{$leastSdg[0]}' with $leastSdgCount submission(s). This SDG could be prioritized for future research and attention.";
                }
            }
        
            // Comparisons Section
            $comparisons = [];
            foreach ($sdgCounts as $sdg => $count) {
                if ($count > 0) {
                    $percentage = round(($count / $totalSdgPapers) * 100, 2);
                    $comparisons[] = "$sdg accounts for $percentage% of all submissions in $college - $course.";
                }
            }
        
            // Trends Section
            $trends = [];
            foreach ($sdgCounts as $sdg => $count) {
                if ($count > 0) {
                    $trends[] = "In $college - $course, the SDG '$sdg' has the highest focus with $count submission(s), indicating an active engagement with this global challenge.";
                }
            }
        
            // Return insights
            return [
                'trends' => implode('<br>', $trends),
                'comparisons' => implode('<br>', $comparisons),
                'highlights' => implode('<br>', $highlights),
            ];
        } */
        
        
        
        
        
        
        
        private function generateInsightsForCollege($college)
        {
            // Fetch SDG counts and document type counts for the specific college
            $sdgCounts = $this->analyzeSdgs($college);
            $documentTypeCounts = $this->analyzeDocumentTypes();
        
            return [
                'trends' => "Trends for $college",
                'comparisons' => "$college contributes X% of all submissions.",
                'highlights' => "Highlights for $college",
                'document_type_analysis' => "Most common document type for $college: " . $this->getMostCommonDocumentType($documentTypeCounts),
            ];
        }
        
        private function generateInsightsForAll()
        {
            return [
                'trends' => "Overall trends",
                'comparisons' => "Overall comparisons",
                'highlights' => "Overall highlights",
                'document_type_analysis' => "Overall document type analysis",
            ];
        }
        


        // data python analytics
        public function exportData()
        {
            // Fetch data from the database
            $data = DB::table('papers')
                ->select('document_type', 'downloads', 'views', 'datetime', 'college', 'course', 'sdgs', 'keywords')
                ->get();
        
            // Define the path to save the CSV file
            $csvFile = storage_path('app/analytics_data.csv'); // Ensure it's saved in storage/app
        
            // Log the path to ensure it's correct
            Log::info("CSV file will be saved at: " . $csvFile);
        
            // Open the file for writing
            $file = fopen($csvFile, 'w');
        
            // Write the header row to the CSV
            fputcsv($file, ['document_type', 'downloads', 'views', 'datetime', 'college', 'course', 'sdgs', 'keywords']); // Add all necessary columns
        
            // Loop through data and write each row to the CSV
            foreach ($data as $row) {
                // Convert row to an array
                $rowData = (array) $row;
        
                // Clean up SDGs and keywords
                $rowData['sdgs'] = $this->cleanUpField($rowData['sdgs']);
                $rowData['keywords'] = $this->cleanUpField($rowData['keywords']);
        
                // If row has fewer than 8 fields, pad it with empty fields
                while (count($rowData) < 8) {
                    $rowData[] = ''; // Add empty fields to match the expected column count
                }
        
                // If row has more than 8 fields, truncate it
                if (count($rowData) > 8) {
                    $rowData = array_slice($rowData, 0, 8);
                }
        
                // Write the row to the CSV
                fputcsv($file, $rowData);
            }
        
            // Close the file
            fclose($file);
        
            // Log the completion of CSV file creation
            Log::info("CSV file created successfully: " . $csvFile);
        
            // Return the CSV file as a download
            return response()->download($csvFile);
        }

        public function exportSurveyData()
        {
            // Fetch the survey responses from the database
            $responses = DB::table('survey_responses')
                ->join('users', 'survey_responses.user_id', '=', 'users.id')
                ->select('survey_responses.user_id', 'survey_responses.question', 'survey_responses.answer')
                ->get();
            
            // Define the survey questions (ensure they match the ones in your database)
            // 'dp1' => 'Demographic Profile - Gender:', 'dp2' => 'Demographic Profile - Current academic or professional status:',
            $surveyQuestions = [
                'q1' => 'I find the website\'s search function helpful in easily finding the research papers I need.',
                'q2' => 'I find it easy to use the website to filter research papers effectively.',
                'q3' => 'The website displays all necessary details about research papers (e.g., authors, abstract, SDGs).',
                'q4' => 'The system allows me to effectively filter and customize data for specific analytics needs.',
                'q5' => 'I find it easy to navigate through different sections of the website (e.g., Help Page, Archive, Home Page).',
                'q6' => 'The platform provides real-time updates on research papers and activities.',
                'q7' => 'It is easy to report issues using the ticketing service.',
                'q8' => 'I find the website provides sufficient access to research papers aligned with the Sustainable Development Goals (e.g., climate action, quality education, gender equality).',
                'q9' => 'The SDG filtering tools on the website help me efficiently locate research papers relevant to specific SDG goals.',
                'q10' => 'The platform effectively promotes awareness of SDGs through its features.',
                'q11' => 'Users find the system easy to use and are generally satisfied with its performance.',
                'q12' => 'The system’s features function reliably without frequent breakdowns.',
                'q13' => 'The navigation bar or menu is well-organized and intuitive.',
                'q14' => 'I am satisfied with the overall ticketing service.',
                'q15' => 'What improvements or features would you like to see implemented in the system to enhance your overall experience and better meet your needs?'
            ];
        
            // Define the path to save the CSV file
            $csvFile = storage_path('app/survey_responses.csv'); // Save in storage/app
        
            // Log the path to ensure it's correct
            Log::info("CSV file will be saved at: " . $csvFile);
        
            // Open the file for writing
            $file = fopen($csvFile, 'w');
        
            // Define the header row, starting with user details
            $header = array_merge(['user_id', 'Gender', 'Current academic or professional status'], array_values($surveyQuestions));
            fputcsv($file, $header); // Add the header to the CSV
        
            // Group responses by user_id
            $groupedResponses = $responses->groupBy('user_id');
            
            // Loop through each user's responses and write to the CSV
            foreach ($groupedResponses as $userId => $userResponses) {
                // Start by getting the gender and academic status for this user
                $genderAnswer = $userResponses->firstWhere('question', 'dp1');
                $academicStatusAnswer = $userResponses->firstWhere('question', 'dp2');
            
                // Prepare the base row with user details (user_id, gender, academic status)
                $baseRow = [
                    $userId,  // user_id
                    $genderAnswer ? $genderAnswer->answer : '',  // Gender
                    $academicStatusAnswer ? $academicStatusAnswer->answer : '',  // Academic Status
                ];
            
                // Get all answers for each question
                $questionAnswers = [];
                foreach ($surveyQuestions as $questionKey => $question) {
                    // Get all answers for the current question for this user
                    $answers = $userResponses->where('question', $questionKey)->pluck('answer')->toArray();
            
                    // Add the answers to the array for this question (using implode to join answers)
                    $questionAnswers[$questionKey] = $answers;
                }
            
                // Calculate the max number of answers for a question (to determine how many rows we need)
                $maxAnswers = max(array_map('count', $questionAnswers));
            
                // Now we create a row for each set of answers (one row per set of answers)
                for ($i = 0; $i < $maxAnswers; $i++) {
                    // Start a row with the basic user details
                    $row = $baseRow;
            
                    // For each question, append the appropriate answer (if available) to the row
                    foreach ($questionAnswers as $answers) {
                        // If there's an answer at index $i, add it to the row; otherwise, add an empty string
                        $row[] = isset($answers[$i]) ? $answers[$i] : '';
                    }
            
                    // Write the row to the CSV
                    fputcsv($file, $row);
                }
            }
            
        
            // Close the file
            fclose($file);
        
            // Log the completion of CSV file creation
            Log::info("CSV file created successfully: " . $csvFile);
        
            // Return the CSV file as a download
            return response()->download($csvFile);
        }
        


        // Referesh python csv file 
        public function refreshData()
    {
        // Fetch data from the database
        $data = DB::table('papers')
            ->select('document_type', 'downloads', 'views', 'datetime', 'college', 'course', 'sdgs', 'keywords')
            ->get();

        // Define the path to save the CSV file
        $csvFile = storage_path('app/analytics_data.csv'); // Ensure it's saved in storage/app

        // Log the path to ensure it's correct
        Log::info("CSV file will be saved at: " . $csvFile);

        // Open the file for writing
        $file = fopen($csvFile, 'w');

        // Write the header row to the CSV
        fputcsv($file, ['document_type', 'downloads', 'views', 'datetime', 'college', 'course', 'sdgs', 'keywords']);

        // Loop through data and write each row to the CSV
        foreach ($data as $row) {
            // Convert row to an array
            $rowData = (array) $row;

            // Clean up SDGs and keywords
            $rowData['sdgs'] = $this->cleanUpField($rowData['sdgs']);
            $rowData['keywords'] = $this->cleanUpField($rowData['keywords']);

            // If row has fewer than 8 fields, pad it with empty fields
            while (count($rowData) < 8) {
                $rowData[] = ''; // Add empty fields to match the expected column count
            }

            // If row has more than 8 fields, truncate it
            if (count($rowData) > 8) {
                $rowData = array_slice($rowData, 0, 8);
            }

            // Write the row to the CSV
            fputcsv($file, $rowData);
        }

        // Close the file
        fclose($file);

        // Log the completion of CSV file creation
        Log::info("CSV file created successfully: " . $csvFile);

        // Return a response indicating success (without triggering a download)
        return response()->json(['status' => 'success', 'message' => 'Data refreshed successfully']);
    }
    public function fetchDashboardData()
    {
        $data = $this->fetchDashboardData(); // Fetch the latest data
        return response()->json(['html' => view('dashboard', ['data' => $data])->render()]);
    }
    


        
        // Function to clean up SDGs and keywords fields
        private function cleanUpField($field)
        {
            // Remove unwanted characters (escape sequences and extra quotes)
            // Remove any unwanted spaces and ensure correct quoting of commas
            $field = preg_replace('/[\x00-\x1F\x7F]/', '', $field); // Remove non-printable characters
            $field = str_replace('\"', '"', $field); // Fix escaped quotes
            $field = str_replace('[', '', $field); // Remove opening square bracket
            $field = str_replace(']', '', $field); // Remove closing square bracket
            $field = str_replace('\"', '', $field); // Remove escaped double quotes
        
            return $field;
        }
        
        
// Run predictive analytics
public function runPredictiveAnalytics()
{
    $inputPath = storage_path('app/public/submission_data.csv');
    $outputPath = storage_path('app/public/predicted_submissions.csv');

    $command = "py " . base_path('scripts/predictive.py') . " {$inputPath} {$outputPath}";
    exec($command, $output, $returnCode);

    if ($returnCode !== 0) {
        return response()->json(['error' => 'Analytics failed!'], 500);
    }

    $predictions = array_map('str_getcsv', file($outputPath));
    return view('AdminDashboard', compact('predictions'));
}

// Run Python analytics and pass to Dashboard view
public function showAnalytics()
{
    // Path to Python executable and script
    $pythonPath = 'C:\\Users\\Kyle\\AppData\\Local\\Programs\\Python\\Python313\\python.exe'; // Ensure this is correct
    $scriptPath = base_path('scripts/data_analytics.py'); // Assuming the script is in 'scripts' directory in your project

    // Run the Python script
    $command = "{$pythonPath} {$scriptPath}";
    $output = shell_exec($command);

    // Log the command and output for debugging
    Log::info('Running command: ' . $command);
    Log::info('Python script output: ' . $output);

    // Handle case where there's no output or an error occurs
    if (empty($output)) {
        $output = "No output returned or there was an error with the Python script.";
    }

    // Log final output for debugging
    Log::info('Final Output after script execution: ' . $output);

    // Get user and additional insights (if needed)
    $user = auth()->user();
    $insights = $this->generateInsights(); // Assuming this method exists to generate insights

    // Pass the data to the view
    return view('AdminDashboard', array_merge(
        compact('user', 'insights'), // Passing user data and insights to the view
        ['output' => $output] // Passing the output of the Python script
    ));
}





// Helper method to get dashboard data from parent controller
private function adminDashboardData()
{
    // Call the parent method and store the returned data
    $data = parent::adminDashboard(); 

    // Ensure it's an array before returning it
    if (is_array($data)) {
        return $data;
    }

    return [];  // Return an empty array if something goes wrong
}

public function submitSurvey(Request $request)
{
    $userId = auth()->id(); // Assuming user ID is required.
    
    // Save demographic responses.
    if (isset($request->survey['dp1'])) {
        DB::table('survey_responses')->insert([
            'user_id' => $userId,
            'question' => 'dp1',
            'answer' => $request->survey['dp1'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    if (isset($request->survey['dp2'])) {
        DB::table('survey_responses')->insert([
            'user_id' => $userId,
            'question' => 'dp2',
            'answer' => $request->survey['dp2'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Save survey questions.
    if (isset($request->responses)) {
        foreach ($request->responses as $key => $response) {
            DB::table('survey_responses')->insert([
                'user_id' => $userId,
                'question' => $key,
                'answer' => $response,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    return redirect()->back()->with('success', 'Survey submitted successfully!');
}

}

