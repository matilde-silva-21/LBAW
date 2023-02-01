<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upvote extends Model
{
    use HasFactory;

    protected $table = 'upvote_comment';

    protected $primaryKey = ['userid', 'commentid'];

    public $incrementing = false;

    public $timestamps = false;


    public function user()
    {
        return $this->belongsTo('App\Models\User', 'userid');
    }

    public function comment()
    {
        return $this->belongsTo('App\Models\Comment', 'commentid');
    }
    
}
