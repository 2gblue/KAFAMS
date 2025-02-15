<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentResultController extends Controller
{
    public function viewStudentResult()
    {
        //retrieve student name and class for result view
        $studentID = 100;
        $student = DB::table('classes')
            ->select('studentName', 'className')
            ->where('studentID', $studentID)
            ->first();

        //retrieve results mark and grade for each subject
        $results = DB::table('results')
            ->join('subjects', 'results.subjectID', '=', 'subjects.subjectID')
            ->select('subjects.subjectName', 'subjects.subjectExamDate', 'results.resultMark', 'results.resultGrade')
            ->where('results.studentID', $studentID)
            ->get();

        return view('manageStudentResult.StudentResultPage', compact('student', 'results'));
    }

    public function viewSubjectList()
    {
        //retrieve subjects data from db (subjects table) & display it in SubjectList page
        $subjects = DB::table('subjects')->select('subjectID', 'subjectName', 'subjectExamDate')->paginate(10);

        $resultID = DB::table('results')->select('resultID', 'resultMark', 'resultGrade');
        return view('manageStudentResult.SubjectList', compact('subjects'));
    }

    public function newSubject()
    {
        //retrieve new subject added in subject list
        return view('manageStudentResult.AddSubject');
    }

    public function storeNewSubject(Request $request)
    {
        // Log the request data for debugging
        Log::info('Request data:', $request->all());

        //Validate request data to add
        $request->validate([
            'subjectName' => 'required|string|max:255',
            'subjectExamDate' => 'required|date',
        ]);

        //Insert new subject into db
        DB::table('subjects')->insert([
            'subjectName' => $request->input('subjectName'),
            'subjectExamDate' => $request->input('subjectExamDate'),
        ]);

        //Redirect to the subject list page with success message
        return redirect()->route('manageStudentResult.viewSubjectList')
            ->with('success', 'New subject successfully added!');
    }

    public function deleteSubject($id)
    {    
        $subject = DB::table('subjects')->where('subjectID', $id)->first();

        //check if the subject exists
        if (!$subject) {
            return redirect()->route('manageStudentResult.viewSubjectList')->with('error', 'Subject not found');
        }

        // Delete the subject record from the subjects table
        DB::table('subjects')->where('subjectID', $id)->delete();

        // Redirect to the viewSubjectList route with a success message
        return redirect()->route('manageStudentResult.viewSubjectList')->with('success', 'Subject deleted successfully');
    }

    public function addResult(Request $request)
    {
        // Debugging: Check what data is being submitted
        Log::info('Request data:', $request->all());

        // Validate request
        $request->validate([
            'resultMark' => 'required|array',
            'resultMark.*' => 'required|integer',
            'resultGrade' => 'required|array',
            'resultGrade.*' => 'required|string',
            'subjectID' => 'required|integer',
        ]);

        // Get the selected subjectID from the request
        $subjectID = $request->input('subjectID');

        // Loop through each student's result
        foreach ($request->resultMark as $studentID => $resultMark) {
            $resultGrade = $request->resultGrade[$studentID];

            // Insert the result into the database
            DB::table('results')->insert([
                'studentID' => $studentID,
                'subjectID' => $subjectID,
                'resultMark' => $resultMark,
                'resultGrade' => $resultGrade,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Redirect back to the subject list page with success message
        return redirect()->route('manageStudentResult.viewSubjectList')
            ->with('success', 'Data successfully saved!');
    }

    public function viewStudentList(Request $request)
    {
        // Retrieve distinct classes for the selection dropdown
        $classes = DB::table('classes')->select('className')->distinct()->get();

        // Retrieve the subjectID from the request
        $subjectID = $request->query('subjectID');

        // Fetch the subjectName based on subjectID
        $subjectName = null;
        if ($subjectID) {
            $subject = DB::table('subjects')->where('subjectID', $subjectID)->first();
            if ($subject) {
                $subjectName = $subject->subjectName;
            }
        }
        // Fetch students based on selected class
        if ($request->has('classID') && $request->classID) {
            $filteredClass = DB::table('classes')
                ->select('studentID', 'studentName', 'className')
                ->where('className', $request->classID)
                ->get();

            return view('manageStudentResult.AddStudentResult')
                ->with('classes', $classes)
                ->with('filteredClass', $filteredClass)
                ->with('subjectName', $subjectName); // Pass subjectName to the view
        }
        return view('manageStudentResult.AddStudentResult', compact('classes', 'subjectName'));
    }

    public function storeStudentResult()
    {
    }

    public function editResult(Request $request)
    {
    
    $classes = DB::table('classes')->select('className')->distinct()->get();
    $subjectID = $request->query('subjectID');
    $subjectName = null;

    if ($subjectID) {
        $subject = DB::table('subjects')->where('subjectID', $subjectID)->first();
        if ($subject) {
            $subjectName = $subject->subjectName;
        }
    }

    $searchQuery = $request->query('searchQuery', ''); // Get search query from request
    
    $filteredClass = DB::table('classes')
    ->leftJoin('results', 'classes.studentID', '=', 'results.studentID') // Join classes with results table
    ->select(
        'classes.studentID', 
        'classes.className',
        'classes.studentName',
        'results.resultID', 
        'results.resultMark',
        'results.resultGrade'
    ) // Only select necessary columns
    ->where('results.subjectID', $subjectID)
    ->when($request->has('classID'), function ($query) use ($request) {
        return $query->where('classes.className', $request->classID); // Filter by className
    })
    ->when($searchQuery, function ($query) use ($searchQuery) {
        if (is_numeric($searchQuery)) {
            return $query->where('classes.studentID', $searchQuery); // Search by studentID
        }
        return $query->where('classes.studentName', 'like', '%' . $searchQuery . '%'); // Or search by studentName
    })
    ->get(); // Execute and get results

    return view('manageStudentResult.EditStudentResult', compact('classes', 'filteredClass', 'subjectName', 'subjectID'));
    }



    public function updateResult(Request $request)
    {
        // Debugging: Check what data is being submitted
        Log::info('Request data:', $request->all());

        // Validate request
        $request->validate([
            'resultMark' => 'required|array',
            'resultMark.*' => 'required|integer',
            'resultGrade' => 'required|array',
            'resultGrade.*' => 'required|string',
            'subjectID' => 'required|integer',
        ]);

        // Get the selected subjectID from the request
        $subjectID = $request->input('subjectID');

        // Loop through each student's result
        foreach ($request->resultMark as $studentID => $resultMark) {
            $resultGrade = $request->resultGrade[$studentID];
            $resultID = $request->resultID[$studentID];

            // Update the result in the database  
            DB::table('results')
                ->where('resultID', $resultID)
                ->update([
                    'resultMark' => $resultMark,
                    'resultGrade' => $resultGrade,
                    'updated_at' => now(),
                ]);
        }
        return redirect()->route('manageStudentResult.viewSubjectList')
            ->with('success', 'Data successfully updated!');
    }
}
