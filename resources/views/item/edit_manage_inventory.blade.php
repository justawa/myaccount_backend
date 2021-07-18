@extends('layouts.dashboard')

@section('content')
{!! Breadcrumbs::render('manage-inventory') !!}
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Edit Physical Stock</div>

                <div class="panel-body">
                    {{-- @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('failure'))
                        <div class="alert alert-danger">
                            {{ session('failure') }}
                        </div>
                    @endif --}}

                    <form method="POST" action="{{ route('manage.inventory.update', $managed_inventory->id) }}">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="form-group">
                            <label>Select Item</label>
                            <select class="form-control" name="item" id="item">
                                <option disabled selected>Select Item</option>
                                @foreach($items as $item)
                                <option @if($managed_inventory->item_id == $item->id) selected="selected" @endif value="{{ $item->id }}" data-base="{{ $item->measuring_unit }}" data-alternate="{{ $item->alternate_measuring_unit }}" data-compound="{{ $item->compound_measuring_unit }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Physical Stock Qty</label>
                            <input type="text" class="form-control" name="count" id="count" Placeholder="Physical Stock Qty" value={{ old('count') ?? $managed_inventory->qty }} required />
                        </div>

                        <div class="form-group">
                            <label>Measuring Unit</label>
                            <select class="form-control" name="measuring_unit" id="measuring_unit" required>
                                <option selected disabled>Select Unit</option>
                                <option @if( $managed_inventory->measuring_unit == $managed_inventory->item->measuring_unit ) selected="selected" @endif value="{{ $managed_inventory->item->measuring_unit }}">{{ $managed_inventory->item->measuring_unit }}</option>
                                
                                @if($managed_inventory->item->alternate_measuring_unit != null)
                                <option @if( $managed_inventory->measuring_unit == $managed_inventory->item->alternate_measuring_unit ) selected="selected" @endif value="{{ $managed_inventory->item->alternate_measuring_unit }}">{{ $managed_inventory->item->alternate_measuring_unit }}</option>
                                @endif

                                @if($managed_inventory->item->compound_measuring_unit != null)
                                <option @if( $managed_inventory->measuring_unit == $managed_inventory->item->compound_measuring_unit ) selected="selected" @endif value="{{ $managed_inventory->item->compound_measuring_unit }}">{{ $managed_inventory->item->compound_measuring_unit }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Per Item Rate</label>
                            <input type="text" class="form-control" name="rate" id="rate" Placeholder="Per Item Rate" value="{{ old('rate') ?? $managed_inventory->rate }}" />
                        </div>

                        <div class="form-group">
                            {{-- <label>Value of Inventory</label> --}}
                            <input type="hidden" class="form-control" name="value" id="value" Placeholder="Value of Inventory" value="{{ old('value') ?? $managed_inventory->value }}" />
                        </div>

                        <div class="form-group">
                            <label>Date of updation</label>
                            <input type="text" class="form-control custom_date" placeholder="DD/MM/YYYY" name="update_date" value="{{ old('update_date') ?? \Carbon\Carbon::parse($managed_inventory->value_updated_on)->format('d/m/Y') }}" />
                        </div>

                        <div class="form-group">
                            <label>Reason</label>
                            <select class="form-control" name="reason">
                                <option @if(strtolower($managed_inventory->reason) == 'due to loss') selected="selected" @endif value="due to loss">Due to Loss</option>
                                <option @if(strtolower($managed_inventory->reason) == 'theft') selected="selected" @endif value="theft">Theft</option>
                                <option @if(strtolower($managed_inventory->reason) == 'fire') selected="selected" @endif value="fire">Fire</option>
                                <option @if(strtolower($managed_inventory->reason) == 'damage') selected="selected" @endif value="damage">Damage</option>
                                <option @if(strtolower($managed_inventory->reason) == 'any other') selected="selected" @endif value="any other">Any Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Note about inventory</label>
                            <textarea class="form-control" placeholder="Narration" name="note">{{ old('note') ?? $managed_inventory->note }}</textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-mine">
                                Update Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            $("#count").on("keyup", function() {
                var count = $(this).val();
                var rate = $("#rate").val();

                if(count == ''){
                    count = 0;
                }

                if(rate == ''){
                    rate = 0;
                }

                $("#value").val( count * rate );
            });

            $("#rate").on("keyup", function() {
                var rate = $(this).val();
                var count = $("#count").val();

                if(count == ''){
                    count = 0;
                }

                if(rate == ''){
                    rate = 0;
                }

                $("#value").val( count * rate );
            });



            $("#item").on("change", function() {
                var base = $("option:selected", $(this)).data('base');
                var alternate = $("option:selected", $(this)).data('alternate');
                var compound = $("option:selected", $(this)).data('compound');

                $("#measuring_unit").html('');
                $("#measuring_unit").append(`<option selected disabled>Select Unit</option>`);

                if(base != ''){
                    $("#measuring_unit").append(`<option value="${base}">${base}</option>`);
                }

                if(alternate != ''){
                    $("#measuring_unit").append(`<option value="${alternate}">${alternate}</option>`);
                }

                if(compound != ''){
                    $("#measuring_unit").append(`<option value="${compound}">${compound}</option>`);
                }

            });

        });
    </script>
@endsection
