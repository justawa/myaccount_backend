@extends('layouts.dashboard')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            Eway Bills
                        </div>
                        <div class="col-md-3 col-md-offset-3">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">From Date</label>
                                            <input type="text" name="from_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                                        </div>
                                    </li>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">To Date</label>
                                            <input type="text" name="to_date" class="form-control custom_date" placeholder="DD/MM/YYYY" />
                                        </div>
                                    </li>
                                    <li class="divider"></li>
                                    <li><button class="btn btn-success btn-block">Search</button></li>
                                    </form>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-body">
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

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Eway Bill No</th>
                                <th>Created On</th>
                                <th>Valid Upto</th>
                                <th>Invoice Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($ewaybills) > 0)
                                @foreach($ewaybills as $ewaybill)
                                <tr>
                                    <td>{{ $ewaybill->bill_no }}</td>
                                    <td>{{ $ewaybill->created_on }}</td>
                                    <td>{{ $ewaybill->valid_upto }}</td>
                                    <td>{{ $ewaybill->invoice->total_amount }}</td>
                                    <td>
                                        @if($ewaybill->status == 1)
                                        <form method="post" action="{{ route('eway.bill.cancel', $ewaybill->bill_no) }}">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-danger btn-sm">cancel</button>
                                        </form>
                                        @elseif($ewaybill->status == 0)
                                            <button type="button" class="btn btn-danger btn-sm" disabled>Cancelled</button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No Ewaybills</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection