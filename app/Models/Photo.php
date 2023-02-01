<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    //Explicitly associate with table name
    protected $table = 'photo';
    //Force primary key
    protected $primaryKey = 'photoid';


    
    /**
     * The event that the photo belongs to.
     */
    public function event()
    {
        return $this->belongsTo('App\Models\Event', 'eventid');
    }
}
