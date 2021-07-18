<?php

namespace App\Http\Controllers;

use Excel;
use Illuminate\Http\Request;

use App\Group;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = Group::where('user_id', auth()->user()->id)->get();

        // dd($groups);

        return view('group.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('group.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $group = new Group;

        $group->name = $request->name;
        $group->user_id = auth()->user()->id;

        if($group->save()){
            return redirect()->back()->with('success', 'Group added successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to add group');
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
        $group = Group::find($id);

        return view('group.show', compact('group'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $group = Group::find($id);

        return view('group.edit', compact('group'));
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
        $group = Group::find($id);

        $group->name = strtolower($request->name);

        if($group->save()){
            return redirect()->route('group.index')->with('success', 'Group edited successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to update group');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $group = Group::find($id);

        if($group->delete()){
            return redirect()->back()->with('success', 'Group deleted successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to delete group');
        }
    }


    public function post_import_to_table(Request $request)
    {

        $this->validate($request, [
            'group_file' => 'required'
        ]);


        if ( $request->hasFile('group_file') ) {

            $path = $request->file('group_file')->getRealPath();

            $data = Excel::load($path)->get();

            if (!empty($data) && $data->count()) {
                foreach ($data->toArray() as $row) {
                    if (!empty($row)) {
                        $dataArray[] = [
                            'name' => $row['name'],
                            'user_id' => Auth::user()->id,
                            'created_at' => date('Y-m-d H:i:s', time()),
                            'updated_at' => date('Y-m-d H:i:s', time()),
                        ];
                    }
                }
                if (!empty($dataArray)) {
                    Group::insert($dataArray);
                    return redirect()->back()->with('success', 'Data uploaded successfully');
                }
            }
        }

    }
}
