<?php

namespace App\Http\Controllers;

use App\Models\Invited;
use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\InviteRequest;


class InvitedController extends Controller
{
    public function get_id_from_email($email){
        $user = User::where('user_.email', '=',  $email)
                        ->firstOrFail();
        return $user->userid;
    }

    public function joinEvent($event_id)
    {
        if (!Auth::check()) return redirect('/login');
        $event = Event::find($event_id);
        $user = Auth::user();
        $this->authorize('attend', [User::class, $event, $user]);
        $event->participants()->attach($user->userID);
    }

     /**
     * Accepts an invite by updating accept boolean of invite to true
     */
    public function accept(Request $request){
        if (!Auth::check()) {return response(route('login'), 302);}

        $invited_user_id = Auth::user()->userid;
        $event_id = $request['event_id'];

        $invite = Invited::where('invited.inviteduserid', '=',  $invited_user_id)
                        ->where('invited.eventid', '=', $event_id)
                        ->first();
        if(!$invite){
            return response("null", 404);
        }

        $this->authorize('update', $invite);
        InvitedController::joinEvent($event_id);

        DB::table('invited')
            ->where('invited.inviteduserid', '=',  $invited_user_id)
            ->where('invited.eventid', '=', $event_id)
            ->update(['status'=>TRUE]);


        return response(route('event.show', ['eventid' => $event_id]), 302);

    }

    /**
    * Rejects an invite by removing it from the database
    */
    public function reject(Request $request) {
        if (!Auth::check()) {return response(route('login'), 302);}

        $invited_user_id = Auth::user()->userid;
        $event_id = $request['event_id'];

        $invite = Invited::where('invited.inviteduserid', '=',  $invited_user_id)
                        ->where('invited.eventid', '=', $event_id)
                        ->first();

        if(!$invite){
            return response("null", 404);
        }

        $this->authorize('delete', $invite);

        DB::table('invited')
            ->where('invited.inviteduserid', '=',  $invited_user_id)
            ->where('invited.eventid', '=', $event_id)
            ->delete();

        return response(route('user.show', ['userid' => $invited_user_id]), 302);
    }

    public function create(InviteRequest $request) {
        if (!Auth::check()) {return response(route('login'), 302);}
        $inviter_user = Auth::user();

        // $this->authorize('create', $inviter_user);

        $eventid = $request['event_id'];
        $invited_user_id = InvitedController::get_id_from_email($request['invited_user']);
        $inviter_user_id = Auth::user()->userid;

        $hasTicket = Ticket::where('ticket.eventid', '=', $eventid)
                            ->where('ticket.userid', '=',  $invited_user_id)
                            ->first();

        if($hasTicket){
            return response(null, 412);
        }

        if($invited_user_id === $inviter_user_id){
            return response(null, 400);
        }


        $invite = Invited::where('invited.inviteduserid', '=',  $invited_user_id)
                        ->where('invited.eventid', '=', $eventid)
                        ->first();
        if(!$invite){
            $inv = new Invited;
            $inv->inviteduserid = $invited_user_id;
            $inv->inviteruserid = $inviter_user_id;
            $inv->eventid = $eventid;
            $inv->save();
            return response(null, 200);
        }

        return response(null, 409);
    }

    public function read(){
        if (!Auth::check()) {return response(route('login'), 302);}

        $user_id = Auth::user()->userid;

        DB::table('invited')
            ->where('invited.inviteduserid', '=',  $user_id)
            ->update(['read'=>TRUE]);

        return response(null, 200);

    }

    public function numberNotifications(){
        if (!Auth::check()) {return response(route('login'), 302);}

        $id = Auth::user()->userid;

        $numberNotifications = Invited::where('invited.inviteduserid', '=',  $id)
                ->where('invited.read', '=', FALSE)->get()->count();

        return response($numberNotifications, 200);
    }

}

