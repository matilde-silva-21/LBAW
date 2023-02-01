<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invited extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    //Explicitly associate with table name
    protected $table = 'invited';
    //Force primary key
    protected $primaryKey = ['invitedUserID', 'inviterUserID','eventid'];
    //to check if a user is invited to an event, just check if a record in this table exists

    public $incrementing = false;

    /**
    * User who made the invite
    */
    public function inviter() {
        return $this->belongsToMany('App\Models\User', 'inviterUserID');
    }

    /**
    * User who made the invite
    */
    public function invited() {
        return $this->belongsToMany('App\Models\User', 'invitedUserID');
    }

    /**
    * Event to which the invite was made
    */
    public function event(){
        return $this->belongsToMany('App\Models\Event', 'eventid');
    }


}