@extends('layouts.app') 
@if (auth()->user()->role != 'admin' && auth()->user()->role != 'teacher')
    {{ abort(403, 'Unauthorized action.') }}
@endif
@section('content')
  <div class="container">
    <div class="row mb-4 align-items-center">
      <div class="col-md-6">
        <h2><b>KAFA Teacher Accounts</b></h2>
      </div>
      <div class="col-md-6 d-flex justify-content-end align-items-center">
        <!-- Search Form -->
        <form action="{{ route('search') }}" method="GET" class="d-flex me-2">
          <input type="text" name="search" class="form-control me-2" placeholder="Search by Staff ID" value="{{ request('search') }}">
          <button type="submit" class="btn btn-secondary">Search</button>
        </form>
        <!-- Add Button -->
        <a href="{{ route('manageAccountRegistration.create') }}" class="btn btn-primary">Add</a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Staff ID</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          {{-- Display results if available --}}
          @if ($datas->isNotEmpty())
            @foreach ($datas as $user)
              <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->staffID }}</td>
                <td class="d-grid gap-2 d-md-block">
                  <button onclick="location.href='{{ route('manageAccountRegistration.edit', $user->id) }}'" class="btn btn-info">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  <form action="{{ route('manageAccountRegistration.destroy', $user->id) }}" class="d-inline-grid" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                      <i class="bi bi-trash-fill"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          @else
            <tr>
              <td colspan="4" class="text-center">No results found.</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>

    {{-- Pagination links --}}
    <div class="d-flex flex-row-reverse">
      {{ $datas->appends(['search' => request('search')])->links() }}
    </div>
  </div>
@endsection