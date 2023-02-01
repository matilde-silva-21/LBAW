<?php

namespace App\Http\Controllers;

use DB;

use App\Models\User;
use App\Models\Event;
use App\Models\City;
use App\Models\Country;
use App\Models\Tag;
use App\Models\Photo;
use App\Models\Ticket;
use App\Models\EventHost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::all();
        return view('layouts.events', ['events' => $events]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!Auth::check()) return redirect('/login');
        return view('pages.createEvent');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Event::class);
        $event = new Event;

        if (!is_null($request->input('name'))) {
            $event->name = $request->input('name');
        }

        $event->description = $request->input('description');

        if (!is_null($request->input('date'))) {
            $event->date = $request->input('date');
        }

        if (($request->input('capacity')) > 0) {
            $event->capacity = $request->input('capacity');
        }

        if (!City::where('name', '=', $request->input('city'))->exists()) {

            $city = new City;
            $city->name = $request->input('city');

            if (!Country::where('name', '=', $request->input('country'))->exists()) {
                $country = new Country;
                $country->name = $request->input('country');
                $country->save();
            }

            $city->countryid = Country::where('name', $request->input('country'))->first()->countryid;
            $city->save();
        }

        $event->cityid = City::where('name', '=', $request->input('city'))->first()->cityid;

        if (!is_null($request->input('address'))) {
            $event->address = $request->input('address');
        }

        /*         if (($request->input('price')) >= 0) {
            $event->price = $request->input('price');
        } */

        if (($request->input('price')) >= 0) {
            $event->price = $request->input('price');
        }

        if (!Tag::where('name', '=', $request->input('tag'))->exists()) {
            $tag = new Tag;
            $tag->name = $request->input('tag');
            $tag->symbol = 'UDF';
            $tag->save();
        }

        $event->tagid = Tag::where('name', '=', $request->input('tag'))->first()->tagid;

        //dd($request->input('isprivate'));

        $event->isprivate = $request->input('isprivate');

        if (is_null($request->input('isprivate'))) {
            $event->isprivate = false;
        } else {
            $event->isprivate = true;
        }

        //$event->isprivate = false;  //for testing - implement switch later

        if ($request->hasFile('img')) {
            $file = $request->file('img');

            $photo = new Photo;

            $evid = DB::select(DB::raw("SELECT MAX(eventid) FROM event"))[0]->max;
            $evid = $evid + 1;

            $filename = "$evid" . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/event_photos'), $filename);

            $photo->path = 'event_photos/' . "$evid" . '.jpg';
            $photo->eventid = $event->id;
            $photo->save();
        }

        //dd($event);

        $event->save();

        //also create a new entry in eventhost with user id and event id - test when login is done
        $eventhost = new EventHost;
        $eventhost->userid = Auth::user()->userid;
        $eventhost->eventid = $event->eventid;
        $eventhost->save();

        return redirect('/event' . $event->eventid);
    }

    /**
     * Display the specified resource.
     * * @param  \App\Models\Event  $event
     * * @return \Illuminate\Http\Response
     *
     * *********************************************
     * CHANGE THIS TO BE DYNAMIC, SHOULD RECEIVE AN eventID AND DISPLAY THAT ONE
     * *********************************************
     */
    public function show($id)
    {
        $event = Event::find($id);
        $host = User::find(EventHost::where('eventid', $id)->first()->userid);

        $attendees = User::join('ticket', 'ticket.userid', '=', 'user_.userid')
                                ->where('ticket.eventid', '=', $id)
                                ->select(['user_.userid'])->get();
        $ids = [];
        foreach($attendees as $attendee){
            $ids[] = $attendee->userid;
        }

        return view('pages.event', ['event' => $event, 'host' => $host, 'attendees' => $ids]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Auth::check()) return redirect('/login');
        $event = Event::find($id);
        return view('pages.editEvent', ['event' => $event]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        //authorize user to update the event information

        if (!Auth::check()) return redirect('/login');
        //dd($request->all());
        $event = Event::find($request->input('eventid'));

        $eventhost = EventHost::where('eventid', $request->eventid)->first();

        $this->authorize('update', [$event, $eventhost]);

        if (!is_null($request->input('name'))) {
            $event->name = $request->input('name');
        }

        if (!is_null($request->input('description'))) {
            $event->description = $request->input('description');
        }

        if (!is_null($request->input('date'))) {
            $event->date = $request->input('date');
        }

        if (!is_null($request->input('capacity'))) {
            $event->capacity = $request->input('capacity');
        }

        //City::find(City::where('name', $request->input('city'))->first()) == null  if antigo

        if (!City::where('name', '=', $request->input('city'))->exists()) {  //se nao existir a cidade

            //create a new city and country and add it to the database
            $city = new City;
            $city->name = $request->input('city');

            //Country::find(Country::where('name', $request->input('country'))->first()) == null  if antigo

            if (!Country::where('name', '=', $request->input('country'))->exists()) {
                $country = new Country;
                $country->name = $request->input('country');
                $country->save();
            }

            $city->countryid = Country::where('name', $request->input('country'))->first()->countryid;

            $city->save();
        }

        $cityexistsid = City::where('name', '=', $request->input('city'))->first()->cityid;

        //$event->cityid = City::where('name', $request->input('city'))->first()->cityid;
        //$event->cityid = 1;
        $event->cityid = $cityexistsid;

        if (!is_null($request->input('price'))) {
            $event->price = $request->input('price');
        }

        if (!Tag::where('name', '=', $request->input('tag'))->exists()) {
            //create a new tag and add it to the database
            $tag = new Tag;
            $tag->name = $request->input('tag');
            $tag->symbol = 'UDF';
            $tag->save();
        }

        $tagexistsid = Tag::where('name', '=', $request->input('tag'))->first()->tagid;

        $event->tagid = $tagexistsid;

        //$event->tagid = 7;

        $event->save();
        return redirect('/event' . $event->eventid);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if (!Auth::check()) return redirect('/login');
        $event = Event::find($request->eventid);
        $user = User::find(Auth::user()->userid);
        //find the eventhost entry with the eventid and userid
        $eventhost = EventHost::where('eventid', $event->eventid)->first();
        //find photo entry with the eventid
        $photo = Photo::where('eventid', $event->eventid)->first();
        //authorize
        $this->authorize('delete', [$event, $eventhost]);
        //delete all info related to the event
        $photo->delete();
        $eventhost->delete();
        $event->delete();
        return redirect('/home');
    }


    public function transferOwnership(Request $request)
    {
        if (!Auth::check()) return redirect('/login');

        $event = Event::find($request->eventid);
        $user = Auth::user();

        //dd($user);
        //find the eventhost entry with the eventid and userid
        $eventhost = EventHost::where('eventid', $event->eventid)->first();
        //dd($eventhost);
        //authorize only if the user is host of the event
        $this->authorize('isHost', [$event, $eventhost]);

        $eventhost->delete();

        //create a nw eventhost table entry with a new userid from request and eventid from request
        $neweventhost = new EventHost;
        $neweventhost->userid = $request->newuserid;
        $neweventhost->eventid = $request->eventid;
        $neweventhost->save();

        return redirect('/event' . $request->eventid);
    }

    //Join an event
    public function join($event_id)
    {
        if (!Auth::check()) return redirect('/login');
        $event = Event::find($event_id);
        $this->authorize('join', $event);
        $user = Auth::user();
        $event->participants()->attach($user->userID);
        return redirect('event' . $event->eventID);
    }

    public function addUser(Request $request)
    {
        if (!Auth::check()) return redirect('/login');
        $event = Event::find($request->eventid);
        $user = User::find(Auth::user()->userid);
        //find the eventhost entry with the eventid and userid
        $eventhost = EventHost::where('eventid', $event->eventid)->first();
        //authorize only if the user is host of the event
        $this->authorize('isHost', [$event, $eventhost]);

        //create a new ticket with user userid and event eventid
        $ticket = new Ticket;
        $ticket->qr_genstring = '527b93cdc6fcf912f9d9e0f018ab784deb4dc672ac8b6e07fcd65ef7b00160ea';  //for testing
        $ticket->userid = $request->userid;
        $ticket->eventid = $request->eventid;
        $ticket->save();

        // return redirect('/event' . $request->eventid);
    }

    public function removeUser(Request $request)
    {
        if (!Auth::check()) return redirect('/login');
        $event = Event::find($request->eventid);
        $user = User::find(Auth::user()->userid);
        //find the eventhost entry with the eventid and userid
        $eventhost = EventHost::where('eventid', $event->eventid)->first();
        //authorize only if the user is host of the event
        $this->authorize('isHost', [$event, $eventhost]);

        //delete ticket record with user userid and event eventid
        $ticket = Ticket::where('userid', '=', $request->userid)->where('eventid', '=', $request->eventid);
        $ticket->delete();

        // return redirect('/event' . $request->eventid);
    }
}
