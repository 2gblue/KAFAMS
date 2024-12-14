<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Student;
use App\Models\Participation;
use Carbon\Carbon;

class ActivityController extends Controller
{
    public function index()
    {
        $today = Carbon::today(); //Retrieve today/access date
        $datas = Activity::where('activityDate', '>=', $today) //Only retrieve data of ones (activityDate) later than today
            ->orderBy('activityDate', 'asc') //Sort by date in ascending order
            ->paginate(8);

        return view('manageActivity.activityList', compact('datas'));
    }


    public function create()
    {
        return view('manageActivity.addActivity'); //Return add form page
    }

    public function store(Request $request)
    {
        Activity::create($request->all()); //Create a new activity in db with requested data

        return redirect()->route('manageActivity.index')
            ->with('success', 'New activity successfully created!'); //Redirect to index with green message
    }

    public function edit($id)
    {
        $activity = Activity::find($id)->toArray(); //Find activity by ID and convert to array
        return view('manageActivity.editActivity', compact('activity')); //Return edit activity form page
    }

    public function update(Request $request, $id)
    {
        $activity = Activity::find($id); // Find the activity by ID
        $updated = $activity->update($request->all()); //Update the activity with the requested data

        if ($updated) {
            return redirect()->route('manageActivity.index')
                ->with('success', "Successfully edited activity."); //Redirect to index with green message
        } else {
        }
    }
    public function destroy(Activity $manageActivity)
    {
        $manageActivity->delete(); //Delete the activity
        return redirect()->route('manageActivity.index')->with('success', 'Activity successfully deleted!'); //Redirect to index with green message
    }

    public function show($id)
    {
        $activity = Activity::findOrFail($id); //Find activity ID
        $students = Student::where('user_id', auth()->user()->id)->get(); //Get user's student objects

        $participations = Participation::where('activity_id', $activity->id) //Get participations associated with activity ID
            ->with('student')
            ->get();

        $participatingStudentIds = $participations->pluck('student_id'); //Get participating student ID
        $participatingStudents = Student::whereIn('id', $participatingStudentIds)
            ->get(['id', 'stdName']);

        return view('manageActivity.showActivity', compact('activity', 'students', 'participations', 'participatingStudents'));
    }
    public function participate(Request $request, Activity $activity)
    {
        $validatedData = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
        ]);

        // Check if the student is already participating
        $existingParticipation = Participation::where('activity_id', $activity->id)
            ->where('student_id', $validatedData['student_id'])
            ->exists();

        if ($existingParticipation) {
            return redirect()->route('manageActivity.index')->with('failure', 'You are already participating in this activity.');
        }

        // Count the current number of participants for this activity
        $currentParticipants = Participation::where('activity_id', $activity->id)->count();

        // Check if the maximum number of participants has been reached
        if ($activity->max > 0 && $currentParticipants >= $activity->max) {
            return redirect()->route('manageActivity.index')->with('failure', 'The maximum number of participants has been reached.');
        }

        // Create a new participation entry
        $participation = new Participation;
        $participation->student_id = $validatedData['student_id'];
        $participation->activity_id = $activity->id;
        $participation->save();

        return redirect()->route('manageActivity.index')->with('success', 'Participation added successfully.');
    }


    public function participationList()
    {
        $user = auth()->user();

        // Get all students linked to the logged-in user
        $students = Student::where('user_id', $user->id)->pluck('id'); // Get only student IDs

        // Get all participations for these students with activities happening after today
        $participations = Participation::whereIn('student_id', $students)
            ->whereHas('activity', function ($query) {
                $query->where('activityDate', '>', Carbon::today()); // Filter for future activities
            })
            ->with(['activity', 'student']) // Eager load activity and student relationships
            ->join('activities', 'activities.id', '=', 'participations.activity_id') // Explicit join to the activities table
            ->orderBy('activities.activityDate') // Correct the reference to the activityDate column
            ->paginate(10); // Paginate the results

        return view('manageActivity.participation', [
            'datas' => $participations,
        ]);
    }

    public function deleteParticipation(Participation $participation)
    {
        $participation->delete();

        return redirect()->route('manageActivity.participation')
            ->with('success', 'Participation removed successfully.');
    }
}
