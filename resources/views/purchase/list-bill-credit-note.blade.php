@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('credit-or-debit-purchase-note') !!}
<div class="container">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('failure'))
        <div class="alert alert-danger">
            {{ session('failure') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Note No</th>
                    <th>Note Date</th>
                    <th colspan="2">Action</th>
                </tr>
            </thead>
            <tbody id="dynamic-body">
                @if( count($credit_notes) > 0)
                @php $count = 1; @endphp
                @foreach($credit_notes as $record)
                <tr>
                    <td>{{ $count++ }}</td>
                    @php $note_no = $record->note_no ?? 0 @endphp
                    <td>
                        {{-- @if($record->status) --}}
                            <a href="{{ route('show.purchase.credit.note', $note_no) }}">{{ $note_no }}</a>
                        {{-- @else
                            {{ $note_no }}
                        @endif --}}
                    </td>
                    <td>{{ $record->note_date ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('update.credit.note.status', $note_no) }}">
                            {{ csrf_field() }}
                            @if(!$record->status)
                            <input type="hidden" name="type" value="ACTIVATE" />
                            @else
                            <input type="hidden" name="type" value="CANCEL" />
                            @endif
                            <button type="submit" class="btn {{ (!$record->status) ? 'btn-success' : 'btn-danger' }}">{{ (!$record->status) ? 'Activate' : 'Cancel' }}</button>
                        </form>
                    </td>
                    <td><a href="{{ route('print.purchase.credit.note', $note_no) }}" class="btn btn-primary">Print</a></td>
                </tr>
                @endforeach
                @else
                    <tr>
                        <td colspan="6">No Data</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
