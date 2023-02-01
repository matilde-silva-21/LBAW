<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    //Explicitly associate with table name
    protected $table = 'ticket';
    //Force primary key
    protected $primaryKey = ['userid', 'eventid'];
    //Disable increment - composed key
    public $incrementing = false;
    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    /**
     * The event.
     */
    public function event()
    {
        return $this->belongsTo('App\Models\Event');
    }

    /**
     * The participant of the event.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
