<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use DB;
use App\Models\Tag;
use App\Models\Ticket;

class SearchController extends Controller
{
    public function index()
    {
        return view('pages.search');
    }

    public function search(Request $request)
    {
        if ($request->ajax()) {

            // Input Sanitization
            $input = preg_replace('/[^A-Za-z0-9\-]/', '', strip_tags($request->input('search')));

            if ($input) {
                /* EVENTS BY NAME OR DESCRIPTIONS*/
                $events = Event::search($input)->take(3)->get();
                if ($events) {
                    foreach ($events as $key => $event) {
                        $event->city_name = $event->city->name;
                    }

                    return Response($events);
                }
            }
        }
    }

    public function searchUsers(Request $request)
    {
        if ($request->ajax()) {

            $input = preg_replace('/[^A-Za-z0-9\-]/', '', strip_tags($request->input('search')));

            if ($input) {
                /* EVENTS BY NAME OR DESCRIPTIONS*/
                $users = User::query()->where('name', 'LIKE', "%{$input}%")->orWhere('email', 'LIKE', "%{$input}%")->take(7)->get();

                if ($users) {
                    foreach ($users as $key => $user) {
                        $ticket = Ticket::where('userid', $user->userid)->where('eventid', $request->event_id)->first();
                        // if user is not invited to event
                        (is_null($ticket)) ? ($user->attending_event = false) : ($user->attending_event = true);
                    }
                    return Response($users);
                }
            }
        }
    }

    public function searchAttendees(Request $request)
    {
        if ($request->ajax()) {

            if ($request->search != '') {

                /* EVENTS BY NAME OR DESCRIPTIONS*/
                $users = User::query()->where('name', 'LIKE', "%{$request->search}%")->orWhere('email', 'LIKE', "%{$request->search}%")->take(7)->get();
                $attendees = [];
                if ($users) {
                    foreach ($users as $key => $user) {
                        $ticket = DB::table('ticket')->where('userid', $user->userid)->where('eventid', $request->event_id)->first();
                        // if user is not invited to event
                        (is_null($ticket)) ? (true) : ($attendees[] = $user);
                    }
                    return Response($attendees);
                }
            }
            else{
                $attendees = User::join('ticket', 'ticket.userid', '=', 'user_.userid')
                                ->where('ticket.eventid', '=', $id)
                                ->select(['*'])->get();
                return Response($attendees);
            }
        }
    }

    public function searchUsersAdmin(Request $request)
    {
        if ($request->ajax()) {

            if ($request->search != '') {

                $input = preg_replace('/[^A-Za-z0-9\-]/', '', strip_tags($request->input('search')));

                /* EVENTS BY NAME OR DESCRIPTIONS*/
                $users = User::query()->where('name', 'LIKE', "%{$input}%")->orWhere('email', 'LIKE', "%{$input}%")->orWhere('userid', 'LIKE', "%{$input}%")->take(10)->get();

                if ($users) {
                    return Response($users);
                }
            }
        }
    }

    public function searchEventsByTag(Request $request)
    {
        if ($request->category_name == "all") {
            // get all events
            $events = Event::where('isprivate', 0)->get();

        } else if ($request->category_name) {
            $tag_id = Tag::where('name', $request->category_name)->get()->first()->tagid;
            $events = Event::where('tagid', $tag_id)->where('isprivate', false)->get();
        }
        return Response($events);
    }
}
