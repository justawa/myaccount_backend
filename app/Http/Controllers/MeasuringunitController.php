<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\MeasuringUnit;

class MeasuringunitController extends Controller
{

    public function create()
    {    
        return view('measuringunit.create');
    }

    public function store(Request $request)
    {
        $measuring_unit = new MeasuringUnit;

        $measuring_unit->name = $request->name;

        if ( $measuring_unit->save() ) {
            return redirect()->back()->with('success', 'Unit added successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to add unit');
        }
    }
}
