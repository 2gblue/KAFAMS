<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

          // Filter by role 'teacher'
          $query->where('role', 'teacher');

        // Filter by Staff ID if the search parameter exists
        if ($request->has('search') && $request->search) {
            $query->where('staffID', 'like', '%' . $request->search . '%');
        }

        // Paginate the results
        $datas = $query->paginate(10);

        // Return the view with data and the search term
        return view('manageAccountRegistration.teacherAccountPage', compact('datas'));
    }
}
