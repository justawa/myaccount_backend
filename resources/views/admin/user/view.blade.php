@extends('admin.layouts.dashboard')

@section('content')
<div class="container">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Password</th>
                <th>Status</th>
                <th>Registered on</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @if( count($users) > 0 )
                @php $count = 1; @endphp
                @foreach($users as $user)
                <tr>
                    <td>{{ $count++ }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->simple_password }}</td>
                    <td>{{ $user->status ? 'Active' : 'Not active' }}</td>
                    <td>{{ $user->created_at }}</td>
                    <td>@if( $user->status == 1 )
                            <a href="{{ route('deactivate.user', $user->id) }}">Deactivate</a>
                        @else
                            <a href="{{ route('activate.user', $user->id) }}">Activate</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
@endsection
