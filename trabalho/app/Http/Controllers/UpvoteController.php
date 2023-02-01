<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Upvote;

class UpvoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUpvoteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Upvote  $upvote
     * @return \Illuminate\Http\Response
     */
    public function show(Upvote $upvote)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Upvote  $upvote
     * @return \Illuminate\Http\Response
     */
    public function edit(Upvote $upvote)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUpvoteRequest  $request
     * @param  \App\Models\Upvote  $upvote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Upvote $upvote)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Upvote  $upvote
     * @return \Illuminate\Http\Response
     */
    public function destroy(Upvote $upvote)
    {
        //
    }

    public function addUpvote(Request $request)
    {
        $upvote = new Upvote();
        $upvote->userid = $request->userid;
        $upvote->commentid = $request->commentid;
        $upvote->save();
    }

    public function removeUpvote(Request $request)
    {
        //delete ticket record with user userid and event eventid
        $upv = Upvote::where('userid', '=', $request->userid)->where('commentid', '=', $request->commentid);
        $upv->delete();
    }
}
