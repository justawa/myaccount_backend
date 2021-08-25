@extends('layouts.dashboard')

@section('content')

{{-- {!! Breadcrumbs::render('gst-setoff') !!} --}}

<div class="container">
    
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-8">
                            GST Setoff
                        </div>
                        <div class="col-md-4">
                            <div class="dropdown">
                                <a href="#" style="border: 1px solid #000; color: #000; padding: 5px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Period Table <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" style="padding: 5px 10px;">
                                    <form>
                                    <li>
                                        <div class="form-group">
                                            <label style="color: #000;">Date</label>
                                            <select name="fix_month" class="form-control">
                                                <option value="01">Jan</option>
                                                <option value="02">Feb</option>
                                                <option value="03">Mar</option>
                                                <option value="04">Apr</option>
                                                <option value="05">May</option>
                                                <option value="06">Jun</option>
                                                <option value="07">Jul</option>
                                                <option value="08">Aug</option>
                                                <option value="09">Sep</option>
                                                <option value="10">Oct</option>
                                                <option value="11">Nov</option>
                                                <option value="12">Dec</option>
                                            </select>
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
                                <th></th>
                                <th>GST Setoff</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if($otherThanReverseCharge || $reverseCharge || $liabilityCharge)
                            @foreach($otherThanReverseCharge as $data)    
                            @php $data->otr_date = \Carbon\Carbon::parse($data->otr_date)->format('d/m/Y'); @endphp
                            <tr>
                                <th>Date</th>
                                <td><a href="{{ route('show.gst.setoff', ['fix_date'=>$data->otr_date]) }}">{{ $data->otr_date }}</a></td>
                            </tr>
                            @endforeach
                            @foreach($reverseCharge as $data) 
                            @php $data->r_date = \Carbon\Carbon::parse($data->r_date)->format('d/m/Y'); @endphp                       
                            <tr>
                                <th>Date</th>
                                <td><a href="{{ route('show.gst.setoff', ['fix_date'=>$data->r_date]) }}">{{ $data->r_date }}</a></td>
                            </tr>
                            @endforeach
                            @foreach($liabilityCharge as $data)  
                            @php $data->liability_date = \Carbon\Carbon::parse($data->liability_date)->format('d/m/Y'); @endphp                      
                            <tr>
                                <th>Date</th>
                                <td><a href="{{ route('show.gst.setoff', ['fix_date'=>$data->liability_date]) }}">{{ $data->liability_date }}</a></td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="2">No DATA</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                    <hr/>

                    
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
