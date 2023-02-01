<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    //Explicitly associate with table name
    protected $table = 'comment';
    //Force primary key
    protected $primaryKey = 'commentid';

    /**
     * The event that the comment belongs to.
     */
    public function event()
    {
        return $this->belongsTo('App\Models\Event', 'eventid');
    }

    public function user() {
        return $this->belongsTo('App\Models\User', 'userid');
    }

    /**
     * The reports of the comment
     */
    public function reports()
    {
        return $this->hasMany('App\Models\Report', 'commentid');
    }

    /**
    * The upvotes of the comment
    */
    public function upvotes()
    {
        return $this->hasMany('App\Models\Upvote', 'commentid');
    }

    // check if a user has upvoted a comment
    public function hasUpvoted($userid) {
        $upvotes = $this->upvotes;
        foreach ($upvotes as $upvote) {
            if ($upvote->userid == $userid) {
                return true;
            }
        }
        return false;
    }

    public function getUpvoteCount() {
        return $this->upvotes->count();
    }

    public function userIsBlocked() {
        return $this->user->isblocked;
    }

}
