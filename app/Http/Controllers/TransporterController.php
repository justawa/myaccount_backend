<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

use App\Transporter;

class TransporterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transporters = Transporter::where('user_id', Auth::user()->id)->get();

        return view('transporter.index', compact('transporters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('transporter.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $transporter = new Transporter;

        $transporter->company_name = $request->company_name;
        $transporter->contact_person = $request->contact_person;
        $transporter->gst_registered = $request->is_registered;
        $transporter->gst = $request->gst;
        $transporter->phone = $request->phone;
        $transporter->address = $request->address;
        $transporter->user_id = Auth::user()->id;

        if ($transporter->save()) {
            return redirect()->back()->with('success', 'Transporter added successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to add transporter');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
