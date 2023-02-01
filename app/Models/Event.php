<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Auth;

class Event extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    //Explicitly associate with table name
    protected $table = 'event';
    //Force primary key
    protected $primaryKey = 'eventid';
    
    /**
     * The city that the event belongs to.
     */
    public function city()
    {
        return $this->belongsTo('App\Models\City', 'cityid');
    }

    /**
     * The photos that belong to the event.
     */
    public function photos()
    {
        return $this->hasMany('App\Models\Photo', 'photoid');
    }

    /**
     * The comments of the event.
     */
    public function comments()
    {
        $allowed = True;
        if(Auth::check()){
            $user = Auth::user();
            $allowed = (!$user->isblocked);
        }
        $dummy = [];
        if($allowed) {return $this->hasMany('App\Models\Comment', 'eventid');}
    }

    /**
     * The reviews of the event.
     */
    public function reviews()
    {
        return $this->hasMany('App\Models\Review', 'eventid');
    }

    /**
     * Hosts of the event
     */
    public function hosts(){
        return $this->belongsToMany('App\Models\User', 'event_host', 'eventid', 'userid');
    }

    /**
     * Users attending the event (with ticket)
     */
    public function participants()
    {
        return $this->belongsToMany('App\Models\User', 'ticket', 'eventid', 'userid');
    }


    /**
     * The tag of the event.
     */
    public function eventTag()
    {
        return $this->belongsTo('App\Models\Tag', 'tagid');
    }
    

    public function invites() {
        return $this->hasMany('App\Models\Invited');
    }


    /**
     * Full text search for events
     */
    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }
        return $query->whereRaw('tsvectors @@ to_tsquery(\'english\', ?)', [$search])
            ->orderByRaw('ts_rank(tsvectors, to_tsquery(\'english\', ?)) DESC', [$search]);
    }

    public function isNotFull(){
        return $this->capacity > 0 ;
    }

}

