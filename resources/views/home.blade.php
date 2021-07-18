@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Home</div>

                <div class="panel-body"> 

                    <ul>
                        <li><a href="{{ route('group.create') }}">Create New Group</a></li>
                        <li><a href="{{ route('group.index') }}">View all Groups</a></li>
                        <hr>
                        <li><a href="{{ route('party.create') }}">Create New Party</a></li>
                        <li><a href="{{ route('party.index') }}">View all Parties</a></li>
                        <hr>
                        <li><a href="{{ route('item.create') }}">Create New Item</a></li>
                        <li><a href="{{ route('item.index') }}">View all Items</a></li>
                        <hr>
                        <li><a href="{{ route('purchase.create') }}">Create New Purchase</a></li>
                        <li><a href="{{ route('purchase.index') }}">View all Purchases</a></li>
                        <li><a href="{{ route('purchase.filter.by.date') }}">View Filterable Purchases</a></li>
                        <li><a href="{{ route('all.tax.purchase') }}">View Taxes</a></li>
                        <hr>
                        <li><a href="{{ route('sale.create') }}">Create New Sale</a></li>
                        <hr>
                        <li><a href="{{ route('view.ledger') }}">View Ledgers</a></li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection