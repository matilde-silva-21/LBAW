<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Invited;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        return view('layouts.users', ['users' => $users]);
    }

    /**
     * Display the specified resource.
     * * @param  \App\Models\Event  $event
     * * @return \Illuminate\Http\Response
    **/
    public function show($id)
    {
        if (!Auth::check()) return redirect('/login');
        $user = User::find($id);
        $this->authorize('view', $user);

        $number = Invited::where('invited.inviteduserid', '=',  $id)->get()->count();

        $sentInvites = Invited::join('event', 'event.eventid', '=', 'invited.eventid')
                                ->join('user_', 'user_.userid', '=', 'invited.inviteduserid')
                                ->where('invited.inviteruserid', '=', $id)
                                ->select(['user_.email as email', '*', 'event.name as name'])->get();


        $receivedInvites = Invited::join('event', 'event.eventid', '=', 'invited.eventid')
                                    ->join('user_', 'user_.userid', '=', 'invited.inviteruserid')
                                    ->join('city', 'city.cityid', '=', 'event.cityid')
                                    ->where('invited.inviteduserid', '=', $id)
                                    ->select(['city.name as cityName', 'user_.email as email','*', 'event.name as name'])->get();


        return view('pages.userPage', ['user' => $user, 'receivedInvites' => $receivedInvites, 'sentInvites' => $sentInvites, 'numberInvites' => $number]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request)
    {
        if (!Auth::check()) return redirect('/login');
        $user = User::find($request->input('userid'));
        $this->authorize('update', $user);

        if (!is_null($request->input('name'))) {
            $user->name = $request->input('name');
        }

        if (!is_null($request->input('email'))) {
            $user->email= $request->input('email');
        }

        if (!is_null($request->input('birthdate'))) {
            $user->birthdate = $request->input('birthdate');
        }

        if (!is_null($request->input('password'))) {
            $user->password = bcrypt($request->input('password'));
        }

        if (!is_null($request->input('gender'))) {
            $user->gender= $request->input('gender');
        }


        if ($request->hasFile('profilePic')) {

            $file = $request->file('profilePic');
            $filename = Auth::user()->userid . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/profile_pictures'), $filename);

        }

        $user->save();
        return redirect('/user' . $user->userid);
    }


     /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
/*     public function create(Request $request)
    {
        if (!Auth::check()) return redirect('/login');
        return view('pages.createEvent');
    } */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!Auth::check()) return redirect('/login');

        $user = new User;

        $user_id = User::max('userid') + 1;

        $user->userid = $user_id;

        if (!is_null($request->input('name'))) {
            $user->name = $request->input('name');
        }

        if (!is_null($request->input('email'))) {
            $user->email= $request->input('email');
        }

        if (!is_null($request->input('birthdate'))) {
            $user->birthdate = $request->input('birthdate');
        }

        if (!is_null($request->input('password'))) {
            $user->password = bcrypt($request->input('password'));
        }

        if (!is_null($request->input('gender'))) {
            $user->gender= $request->input('gender');

        }

        if ($request->hasFile('profilePic')) {

            $file = $request->file('profilePic');
            $filename = $user_id . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/profile_pictures'), $filename);
            $user->profilepic = '/profile_pictures/' . $filename;
        }

        $user->save();

        return redirect('/user' . $user->userid);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        if (!Auth::check()) return redirect('/login');
        $user = User::find($request->input('userid'));
        // check if user is still hosting events
        $events = $user->hostedEvents()->get();
        if (count($events) > 0) {
            // return response with status 200
            return response()->json(['status' => 'error'], 401);
        }
        $this->authorize('delete', $user);
        $user->delete();
        return redirect('/home');
    }



    public function attendEvent(Request $request) {
        if (!Auth::check()) return redirect('/login');
        $user = Auth::user();  //o user atual
        $event = Event::find($request->eventid);  //o evento que o user quer participar

        //autorizar se o evento for private ou se já estiver no evento
        // $this->authorize('attend', $event);

        //Se o authorize nao fizer sentido, passsar o codigo para aqui

        $user_ticket = new Ticket;
        $user_ticket->qr_genstring = '527b93cdc6fcf912f9d9e0f018ab784deb4dc672ac8b6e07fcd65ef7b00160ea';  //for testing
        $user_ticket->userid = $user->userid;
        $user_ticket->eventid = $event->eventid;
        $user_ticket->save();
    }

    public function leaveEvent(Request $request) {
        if (!Auth::check()) return redirect('/login');
        $user = Auth::user();  //o user atual
        $event = Event::find($request->eventid);  //o evento que o user quer deixar de participar

        //autorizar se o evento for private ou se já estiver no evento
        // $this->authorize('leave', $event);

        //Se o authorize nao fizer sentido, passsar o codigo para aqui

        $user_ticket = Ticket::where('userid', $user->userid)->where('eventid', $event->eventid);
        $user_ticket->delete();
    }

    public function block(Request $request){

        if(User::find($request->userID)->userid == Auth::user()->userid){
            return response("Can't block self", 403);
        }

        $this->authorize('changeBlock', Auth::user());

        $userToBlock = $request->userID;
        $blocked = !(User::find($userToBlock)->isblocked);

        DB::table('user_')
        ->where('user_.userid', '=',  $userToBlock)
        ->update(['isblocked'=> $blocked]);

        $blocked = User::find($userToBlock)->isblocked;

        $converted_res = $blocked ? 'Unblock User' : 'Block User';

        return response($converted_res, 200);
    }

    public function ban_user(Request $request) {

        if($request->userID == Auth::user()->userid){
            return response()->json(['status' => '403', 'msg' => 'Cant ban self'], 403);
        }
        $this->authorize('changeBlock', Auth::user());

        $banned = User::find($request->userID)->isblocked;

        ($banned) ? $converted_res = 'User is already banned!' : $converted_res = 'User was banned!';



        if ($banned) {
            return response()->json(['status' => '401', 'msg' => $converted_res], 401);
        }

        $user = User::find($request->userID);
        $user->getComment()->delete();
        $user->isblocked = true;
        $user->save();



        return response()->json(['status' => '200', 'msg' => $converted_res], 200);
    }

}
