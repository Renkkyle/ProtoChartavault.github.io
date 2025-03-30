<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
class DataAnalyticsController extends DashboardController
{
    // Export Data to CSV
// Export data to CSV
public function exportData()
{
    // Fetch data from the database
    $data = DB::table('papers')
        ->select('document_type', 'downloads', 'views', 'datetime', 'college', 'course', 'sdgs', 'keywords')
        ->get();

    // Define the path to save the CSV file
    $csvFile = storage_path('app/analytics_data.csv'); // Use the 'app' folder within 'storage'

    // Open the file for writing
    $file = fopen($csvFile, 'w');

    // Write the header row to the CSV
    fputcsv($file, ['document_type', 'downloads', 'views', 'datetime', 'college', 'course', 'sdgs', 'keywords']); // Add all necessary columns

    // Loop through data and write each row to the CSV
    foreach ($data as $row) {
        fputcsv($file, (array) $row);
    }

    // Close the file
    fclose($file);

    // Log for debugging (check if the file was created)
    Log::info("CSV file created at: " . $csvFile);

    // Return the CSV file as a download
    return response()->download($csvFile);
}

// Run predictive analytics
public function runPredictiveAnalytics()
{
    $inputPath = storage_path('app/public/submission_data.csv');
    $outputPath = storage_path('app/public/predicted_submissions.csv');

    $command = "python3 " . base_path('scripts/predictive.py') . " {$inputPath} {$outputPath}";
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
    // Path to the Python script
    $command = 'C:\Users\Kyle\AppData\Local\Programs\Python\Python313\python.exe ' . base_path('scripts/data_analytics.py');
    
    // Run the Python script and capture the output
    $output = shell_exec($command);

    // Log the output for debugging
    Log::info('Python script output: ' . $output);

    // If no output, set a default message
    if (empty($output)) {
        $output = "No output returned or there was an error with the Python script.";
    }

    // Log the output to check if the script ran and returned anything
    Log::info('Output after Python script execution: ' . $output);

    // Get the data for the dashboard
    $user = auth()->user();
    $insights = $this->generateInsights();

    // Pass the output to the view
    return view('AdminDashboard', array_merge(
        compact('user', 'insights'),
        ['output' => $output] // Pass the descriptive and predictive output
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
}
