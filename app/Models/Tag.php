<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    //Explicitly associate with table name
    protected $table = 'tag';
    //Force primary key
    protected $primaryKey = 'tagid';
    
    /**
     * The events with this tag.
     */
    public function events()
    {
        return $this->hasMany('App\Models\Event', 'eventid');  //returns the id of the event with the searched tag, still not working
    }
}

