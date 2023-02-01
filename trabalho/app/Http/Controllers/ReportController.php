<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // create new report
        $report = new Report();
        $report->commentid = $request->comment_id;
        $report->eventid = $request->event_id;
        $report->userid = $request->user_id;
        $report->reason = $request->reason;
        $report->description = $request->explanation;
        $report->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function show(Report $report)
    {
        //
    }

    public function getReportedComments()
    {
        $reports = Report::orderBy('date', 'desc')->take(15)->get();
        // if there are no reports, return http 204

        if (count($reports) == 0) {
            return response()->json(['status' => 'error', 'message' => 'No reports found'], 204);
        }

        $response_array = array();

        foreach ($reports as $report) {

            if (!$report->comment->user->isblocked && !$report->isresolved) {
                $report->comment;
                $report->user;
                array_push($response_array, $report);
            }
        }

        // return reports
        return response()->json($response_array, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function destroy(Report $report)
    {
        //
    }
}
