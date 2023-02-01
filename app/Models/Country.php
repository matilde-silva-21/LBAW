<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    //Explicitly associate with table name
    protected $table = 'country';
    //Force primary key
    protected $primaryKey = 'countryid';

    /**
     * The cities that belong to the country.
     */
    public function cities()
    {
        return $this->hasMany('App\Models\City', 'countryid');
    }
/*
    /**
    * The events that belong to the country.
    * !! DON'T KNOW IF THIS IS CORRECT !!                 INCLUDE THIS?
    */
    public function events()
    {
        return $this->hasMany('App\Models\Event', 'eventid');
    }

    /**
     * Full text search for countries
     */
    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }
        return $query->whereRaw('tsvectors_country @@ to_tsquery(\'english\', ?)', [$search])
            ->orderByRaw('ts_rank(tsvectors_country, to_tsquery(\'english\', ?)) DESC', [$search]);
    }
    
}