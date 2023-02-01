<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{

    use Notifiable;

    //Explicitly associate with table name
     protected $table = 'user_';
    //Force primary key
    protected $primaryKey = 'userid';
    // Don't add create and update timestamps in database.
    public $timestamps = false;

    //The attributes that are mass assignable
    protected $fillable = [
        'name', 'email', 'birthdate', 'password', 'gender',
    ];

    //The attributes that should be hidden for arrays
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function hostedEvents()
    {
        return $this->belongsToMany('App\Models\Event', 'event_host', 'userid' , 'eventid');
    }

    /**
     * The events this user participates.
     */
    public function attendingEvents()
    {
        
        return $this->belongsToMany('App\Models\Event', 'ticket', 'userid', 'eventid');
    }

    /**
    * User who received the invite
    */
    public function invited() {
        return $this->hasMany('App\Models\Invited');
    }

    /**
    * User who made the invite
    */
    public function inviter() {
        return $this->hasMany('App\Models\Invited');
    }

    // CHECK AGAIN IF THIS IS THE INDENTED ROUTE /////////////////
    // REMOVE COMMENTS AFTER   ///////////////////////////////////
    //////////////////////////////////////////////////////////////
    public function login()
    {
        return redirect()->route('home');
    }

    public function logout()
    {
        return redirect()->route('home');
    }

    public function getComment() {
        return $this->belongsTo('App\Models\Comment', 'userid');
    }
    //////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////
}
