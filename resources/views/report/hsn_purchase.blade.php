@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>HSC</th>
                    <th>GST</th>
                </tr>
            </thead>
            <tbody>
                @if( count($items) > 0 )
                    @php $count = 1; $total_gst = 0; @endphp
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->hsc_code }}</td>
                        @php $total_gst += $item->gst_per_item @endphp
                        <td>{{ $item->gst_per_item }}</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th>{{ $total_gst }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection