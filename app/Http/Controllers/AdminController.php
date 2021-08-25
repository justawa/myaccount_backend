<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\GstList;
use App\User;

class AdminController extends Controller
{

    public function create_gst() {
        return view('admin.gst.create_gst');
    }

    public function store_gst(Request $request) {
        $gst = new GstList;

        if( is_string($request->gst) ){
            $this_gst = strtoupper($request->gst);
        } else {
            $this_gst = $request->gst;
        }

        $gst->name = $this_gst;

        if( $gst->save() ) {
            return redirect()->back()->with('success', 'Data saved successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to save data');
        }
    }

    public function show_gst() {
        $gsts = GstList::all();
        
        return view('admin.gst.show_gst', compact('gsts')); 
    }

    public function edit_gst($gst) {
        $gst = GstList::find($gst);
        
        return view('admin.gst.edit_gst', compact('gst')); 
    }

    public function update_gst(Request $request, $id) {
        $gst = GstList::find($id);

        if( is_string($request->updated_gst) ){
            $updated_gst = strtoupper($request->updated_gst);
        } else {
            $updated_gst = $request->updated_gst;
        }

        $gst->name = $updated_gst;

        if ( $gst->save() ) {
            return redirect()->back()->with('success', 'Data updated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update data');
        }
    }

    public function delete_gst($id) {
        $gst = GstList::find($id);

        // return $gst;

        if ($gst->delete()) {
            return redirect()->back()->with('success', 'Data deleted successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to delete data');
        }
    }

    public function view_users() {
        $users = User::all();

        return view('admin.user.view', compact('users'));
    }

    public function deactivate_user($id) {

        $user = User::find($id);

        $user->status = 0;

        if( $user->save() ) {
            return redirect()->back()->with('success', 'User deactivated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to deactivate User');
        }

    }

    public function activate_user($id) {

        $user = User::find($id);

        $user->status = 1;

        if( $user->save() ) {
            return redirect()->back()->with('success', 'User activated successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to activate User');
        }

    }
}
