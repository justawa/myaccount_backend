@extends('layouts.dashboard')

@section('content')
<div class="container">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @if(count($users) > 0)
            @php $count = 1 @endphp
                @foreach($users as $user)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $user->name }}</td>
                        <td><a href="{{ route('impersonate.user', $user->id) }}" class="btn btn-primary">Impersonate This User</a></td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
@endsection