@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2><b>Upcoming Participating Activities</b></h2>
            </div>
            <div class="col-md-6 mt-4 d-flex justify-content-end align-items-center">
                <a href="{{ route('manageActivity.index') }}">
                        <button type="button" class="btn" style="background-color:white; color:#647687; border:1px solid #647687">Return</button>
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 20%">Activity Name</th>
                        <th style="width: 40%">Activity Details</th>
                        <th style="width: 10%">Student Name</th>
                        <th style="width: 10%">Date</th>
                        <th style="width: 10%">Time</th>
                        <th style="width: 10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datas as $participation)
                        <tr style="height: 50px; vertical-align:middle;">
                            <td>{{ $participation->activity->activityName }}</td>
                            <td>{{ $participation->activity->activityDetails }}</td>
                            <td>{{ $participation->student->stdName }}</td>
                            <td>{{ $participation->activity->activityDate }}</td>
                            <td>{{ \Carbon\Carbon::parse($participation->activity->startTime)->format('g:i A') }} to
                                {{ \Carbon\Carbon::parse($participation->activity->endTime)->format('g:i A') }}</td>
                            <!--Change format to 12-hour format-->
                            <td>
                                <a href="#"
                                    onclick="event.preventDefault(); if(confirm('Are you sure you want to remove this participation?')) { document.getElementById('delete-form-{{ $participation->id }}').submit(); }">
                                    <box-icon name='trash' type='solid'></box-icon>
                                </a>
                                <form id="delete-form-{{ $participation->id }}"
                                    action="{{ url('manageActivity/participation/' . $participation->id) }}"
                                    method="POST" style="display: none;">
                                  @csrf
                                  @method('DELETE')
                              </form>                              
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No participations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-row-reverse">
            {{ $datas->links() }}
        </div>
        
    </div>
@endsection
