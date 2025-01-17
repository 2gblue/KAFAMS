@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><b>Add Student Result</b></h2>
        </div>
        <div class="col-md-6 mt-4 d-flex justify-content-end align-items-center">
            @if (auth()->user()->role == 'admin' || auth()->user()->role == 'teacher')
            <!-- Add any button or functionality if needed -->
            @endif
        </div>
    </div>

    <!-- Class selection form -->
    <form action="{{ route('manageStudentResult.viewStudentList') }}" method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <input type="hidden" class="form-control" name="subjectID" value="{{ request('subjectID') }}" readonly>
                <input type="text" class="form-control" value="{{ $subjectName }}" readonly>
            </div>
        </div>
        <p></p>
        <div class="row">
            <div class="col-md-6">
                <select name="classID" class="form-control" onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    @foreach($classes->unique('className') as $class)
                    <option value="{{ $class->className }}" {{ request('classID') == $class->className ? 'selected' : '' }}>{{ $class->className }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <form action="{{ route('manageStudentResult.addResult') }}" method="POST">
            @csrf
            <input type="hidden" name="subjectID" value="{{ request('subjectID') }}">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 10%">No</th>
                        <th style="width: 40%">Student Name</th>
                        <th style="width: 20%">Class</th>
                        <th style="width: 15%">Marks</th>
                        <th style="width: 15%">Grade</th>
                    </tr>
                </thead>
                @if(isset($filteredClass) && $filteredClass->count())
                <tbody>
                    @foreach($filteredClass as $class)
                    <input type="hidden" name="studentID[{{ $class->studentName }}]" value="{{ $class->studentID }}">
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $class->studentName }}</td>
                        <td>{{ $class->className }}</td>
                        <td>
                            <input type="number" class="form-control" name="resultMark[{{ $class->studentID}}]" 
                            value="{{ old('resultMark.'.$class->studentID) }}" oninput="autoGrade(this, {{ $class->studentID }})">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="resultGrade[{{ $class->studentID }}]" id="resultGrade_{{ $class->studentID }}" 
                            value="{{ old('resultGrade.'.$class->studentID) }}" readonly>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="col-md-6 mt-4 d-flex">
                <!--Only authorised users can see ADD button -->
                @if (auth()->user()->role == 'admin' || auth()->user()->role == 'teacher')
                <button type="submit" class="btn" style="background-color:#647687; color:white;">Save</button>
                @endif
            </div>

        </form>
        @else
        <!-- Add a message or alternative content if there are no students -->
        @endif
    </div>

    <div class="d-flex flex-row-reverse">
        <!-- Additional content if needed -->
    </div>
</div>

<div style="position: fixed; left: 50%; transform: translate(-50%, -50%);">
    @if (session('failure'))
    <div class="alert alert-danger">
        {{ session('failure') }}
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
</div>

<script>
    function autoGrade(input, studentID) {
        var marks = input.value;
        var grade = '';

        // Adjusted grading scale
        if (marks >= 80 && marks <= 100) {
            grade = 'A';
        } else if (marks >= 60 && marks <= 79) {
            grade = 'B';
        } else if (marks >= 40 && marks <= 59) {
            grade = 'C';
        } else if (marks >= 0 && marks <= 39) {
            grade = 'D';
        } else {
            grade = 'Invalid';
        }

        // Set the grade value to the corresponding resultGrade field
        document.getElementById('resultGrade_' + studentID).value = grade;
    }
</script>
@endsection
