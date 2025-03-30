<?php

namespace App\Http\Controllers;
use App\Models\Paper;
use App\Models\Ticket;
use App\Models\User;
use App\Models\News;
use App\Models\DownloadRequest;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected function getAllSdgs()
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
            'Reduced Inequalities', 
            'Sustainable Cities and Communities',
            'Responsible Consumption and Production',
            'Climate Action', 
            'Life Below Water', 
            'Life on Land', 
            'Peace, Justice and Strong Institutions', 
            'Partnerships for the Goals'
        ];
    }

    // Shared method to fetch recent approved papers
    protected function getRecentApprovedPapers($limit = 10)
    {
        $papers = Paper::where('status', 'Approved')
                       ->orderBy('datetime', 'desc')
                       ->take($limit)
                       ->get();

        // Decode SDGs for each paper
        foreach ($papers as $paper) {
            $paper->sdgs = json_decode($paper->sdgs, true);
        }

        return $papers;
    }

    // Shared method to fetch document types
    protected function getDocumentTypes()
    {
        return ['Student Thesis', 'Capstone', 'Institutional Paper', 'Action Research', 'Feasibility Studies', 'Report'];
    }

    // Shared method to get SDG counts
    protected function getSdgCounts()
    {
        $sdgs = $this->getAllSdgs();  // Reuse SDG names
        $sdgCounts = array_fill_keys($sdgs, 0);

        $papers = Paper::where('status', 'Approved')->get();

        foreach ($papers as $paper) {
            $sdgsArray = json_decode($paper->sdgs, true);
            if (is_array($sdgsArray)) {
                foreach ($sdgsArray as $sdg) {
                    $sdg = trim($sdg);
                    if (isset($sdgCounts[$sdg])) {
                        $sdgCounts[$sdg]++;
                    }
                }
            }
        }

        return $sdgCounts;
    }

    // Method to get featured advisers by download totals
    protected function getFeaturedAdvisers()
    {
        $featuredAdvisers = Paper::select('adviser')
            ->selectRaw('COUNT(*) as paper_count, SUM(downloads) as total_downloads')
            ->groupBy('adviser')
            ->orderByDesc('total_downloads')
            ->take(3)
            ->get();

        // Fetch papers for each featured adviser
        foreach ($featuredAdvisers as $featuredAdviser) {
            $featuredAdviser->papers = Paper::where('adviser', $featuredAdviser->adviser)->get();
        }

        return $featuredAdvisers;
    }

    protected function getFeaturedDocument()
    {
        // Top 3 documents by downloads/views
        $featuredDocuments = Paper::orderByDesc('downloads')
        ->orderByDesc('views')
        ->take(3)
        ->get();

    return $featuredDocuments;
    }

    protected function getRecentApproved()
    {
    // Return here for the order soething happening here 
    $recentApprovedPapers = Paper::where('status', 'Approved')
                                //->orderBy('datetime', 'desc')
                                ->orderBy('id','desc')
                                ->take(12)
                                ->get();

    foreach ($recentApprovedPapers as $paper) {
        $paper->sdgs = json_decode($paper->sdgs, true); 
    }
    return $recentApprovedPapers;
    }

    protected function getAnnouncement()
    {
        $announcements = Announcement::orderBy(column: 'created_at', direction: 'desc')->paginate(3);  // 3 announcements per page

        //return $announcements;
        return Announcement::paginate(10); // Assuming you are using pagination

    }
    
    protected function getOlderAnnouncements()
    {
        // Get older announcements
        $olderAnnouncements = Announcement::orderBy('created_at', 'desc')->get();
        \Log::info($olderAnnouncements); // Log data to check

        return $olderAnnouncements;
    }
    protected function getOlderNewsItems(){
        
        // Get older news items (excluding the latest ones)
        $olderNewsItems = News::orderBy('created_at', 'desc')->get();
        \Log::info($olderNewsItems); // Log data to check

        return $olderNewsItems;
    }


    protected function getNews()
    {
        $newsItems = News::orderBy(column: 'created_at', direction: 'desc')->paginate(3);  
        //return $newsItems;
        return News::paginate(3); 

    }
    protected function getUser()
    {
        $user = Auth::user();
        return $user;
    }

    protected function sanitizeContent(string $content): string
    {
        // Create a default HTMLPurifier config
        $config = HTMLPurifier_Config::createDefault();
    
        // Set rules first
        $config->set('HTML.Allowed', 'iframe[src|width|height|style|scrolling|frameborder|allowfullscreen|allow],a[href],p,br,strong,b,i,u,span');
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'ftp' => true,
        ]);
        $config->set('Core.EscapeInvalidTags', false);
    
        // Now retrieve the HTML definition and modify it
        $def = $config->getHTMLDefinition(true);
        $def->addElement(
            'iframe', // Element name
            'Block',  // Content set
            'Flow',   // Content model
            'Common', // Attribute collection
            [
                'src' => 'URI',
                'width' => 'Length',
                'height' => 'Length',
                'style' => 'Text',
                'scrolling' => 'Enum#yes,no,auto',
                'frameborder' => 'Enum#0,1',
                'allowfullscreen' => 'Bool',
                'allow' => 'Text',
            ]
        );
    
        // Create the HTMLPurifier instance with the finalized configuration
        $purifier = new HTMLPurifier($config);
    
        // Purify the content (sanitize it) and return
        return $purifier->purify($content);
    }
    protected function getForms()
    {
        $forms = [
            [
                'name' => 'Institutional Research Support Services-SY 2024-2025',
                'file' => 'Institutional Research Support Services-SY 2024-2025.pdf',
            ],
            [
                'name' => 'FORM 1-ADVISER APPOINTMENT',
                'file' => 'FORM 1-ADVISER APPOINTMENT.pdf',
            ],
            [
                'name' => 'FORM 2 -CHAIRMAN APPOINTMENT',
                'file' => 'FORM 2 -CHAIRMAN APPOINTMENT.pdf',
            ],
            [
                'name' => 'FORM 3 -PANELIST MEMBER APPOINTMENT',
                'file' => 'FORM 3 -PANELIST MEMBER APPOINTMENT.pdf',
            ],
            [
                'name' => 'FORM 4 -ADVISORY COMMITTEE',
                'file' => 'FORM 4 -ADVISORY COMMITTEE.pdf',
            ],
            [
                'name' => 'FORM 5 -ADVISER CONSULTATION FORM',
                'file' => 'FORM 5 -ADVISER CONSULTATION FORM.pdf',
            ],
            [
                'name' => 'FORM 6 -RECOMMENDATION FOR ORAL DEFENSE',
                'file' => 'FORM 6 -RECOMMENDATION FOR ORAL DEFENSE.pdf',
            ],
            [
                'name' => 'FORM 7 -CERTIFICATE OF READINESS',
                'file' => 'FORM 7 -CERTIFICATE OF READINESS.pdf',
            ],
            [
                'name' => 'FORM 8 -MANUSCRIPT EVALUATION TOOL',
                'file' => 'FORM 8 -MANUSCRIPT EVALUATION TOOL.pdf',
            ],
            [
                'name' => 'FORM 9a -THESIS PROPOSAL DEFENSE EVALUATION',
                'file' => 'FORM 9a -THESIS PROPOSAL DEFENSE EVALUATION.pdf',
            ],
            [
                'name' => 'FORM 9b -THESIS FINAL DEFENSE EVALUATION',
                'file' => 'FORM 9b -THESIS FINAL DEFENSE EVALUATION.docx.pdf',
            ],
            [
                'name' => 'FORM 9c -PROPOSAL _ FINAL DEFENSE EVALUATION (FEASIBILITY STUDY)',
                'file' => 'FORM 9c -PROPOSAL _ FINAL DEFENSE EVALUATION (FEASIBILITY STUDY).docx.pdf',
            ],
            [
                'name' => 'FORM 10 -MINUTES OF ORAL DEFENSE  (To be accomplished by the student)',
                'file' => 'FORM 10 -MINUTES OF ORAL DEFENSE  (To be accomplished by the student).docx.pdf',
            ],
            [
                'name' => 'FORM 11 -MANUSCRIPT ROUTING FORM',
                'file' => 'FORM 11 -MANUSCRIPT ROUTING FORM.pdf',
            ],
            [
                'name' => 'FORM 12 a-QUESTIONNAIRE VALIDATION SHEET (Quantitative Research Study)-revised 10-11-16',
                'file' => 'FORM 12 a-QUESTIONNAIRE VALIDATION SHEET (Quantitative Research Study)-revised 10-11-16.pdf',
            ],
            [
                'name' => 'FORM 12 b-VALIDATION SHEET FOR INTERVIEW GUIDE (Qualitative Research Study)',
                'file' => 'FORM 12 b-VALIDATION SHEET FOR INTERVIEW GUIDE (Qualitative Research Study).docx.pdf',
            ],
            [
                'name' => 'FORM 13 a-CERTIFICATE OF DATA ANALYST (for QualitativeResearch)',
                'file' => 'FORM 13 a-CERTIFICATE OF DATA ANALYST (for QualitativeResearch).pdf',
            ],
            [
                'name' => 'FORM 13b -CERTIFICATE OF STATISTICIAN (for Quantitative Research)',
                'file' => 'FORM 13b -CERTIFICATE OF STATISTICIAN (for Quantitative Research).docx.pdf',
            ],
            [
                'name' => 'FORM 14 -CERTIFICATE OF GRAMMARIAN_EDITOR',
                'file' => 'FORM 14 -CERTIFICATE OF GRAMMARIAN_EDITOR.docx - Copy.pdf',
            ],
            [
                'name' => 'FORM 15 -CERTIFICATION (Proposal_Final Defense)',
                'file' => 'FORM 15 -CERTIFICATION (Proposal_Final Defense).pdf',
            ],
            [
                'name' => 'FORM 17-DECLARATION OF ORIGINAL WORK',
                'file' => 'FORM 17-DECLARATION OF ORIGINAL WORK.pdf',
            ],
            [
                'name' => 'FORM 18 -APPROVAL SHEET',
                'file' => 'FORM 18 -APPROVAL SHEET.docx.pdf',
            ],
            [
                'name' => 'FORM 19 -Publishable Format (IMRAD) Certification',
                'file' => 'FORM 19 -Publishable Format (IMRAD) Certification.docx.pdf',
            ],
        ];
        return $forms;
    }

    protected function stripTitle($name) {
        // List of titles to remove from the names
        $titles = ['Dr.', 'Prof.', 'Mr.', 'Engr.'];
    
        // Remove title from the name (trim spaces after removing the title)
        foreach ($titles as $title) {
            if (strpos($name, $title) === 0) {
                $name = trim(substr($name, strlen($title)));
            }
        }
        
        return $name;
    }

        public function getPendingSubmissionCount()
    {
        return Paper::where('status', 'pending')->count();
    }
        public function getPendingUserCount()
    {
        return User::where('status', 'pending') // Filter for users with 'pending' status
        ->where('is_verified', 1)  // Ensure only verified users are counted
        ->count();  // Count the users that match the criteria
    }
    public function getPendingTicketCount()
    {
        return Ticket::where('status', 'pending')->count();
    }
    public function getPendingDownloadRequestCount(){
        return DownloadRequest::where('status','pending')->count();
    }

    // Method to get shared data (SDGs, recent papers, SDG counts, featured advisers)
    protected function getSharedData()
    {
        return [
            'sdgs' => $this->getAllSdgs(),
            'recentPapers' => $this->getRecentApprovedPapers(),
            'sdgCounts' => $this->getSdgCounts(),
            'featuredAdvisers' => $this->getFeaturedAdvisers(),
            'documentTypes' => $this->getDocumentTypes(),
            'featuredDocuments' => $this->getFeaturedDocument(),
            'recentApprovedPapers' => $this->getRecentApproved(),
            'announcements' => $this->getAnnouncement(),
            'user' => $this->getUser(),
            'newsItems' => $this->getNews(),
            'olderAnnouncements' => $this->getOlderAnnouncements(),
            'olderNewsItems' => $this->getOlderNewsItems(),
            'forms' => $this->getForms(),
            'pendingSubmissionCount' => $this->getPendingSubmissionCount(),
            'pendingUserCount' => $this->getPendingUserCount(),
            'pendingTicketCount' => $this->getPendingTicketCount(),
            'pendingDownloadRequestCount' => $this->getPendingDownloadRequestCount()


        ];
    }
}
