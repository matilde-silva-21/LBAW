<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table = 'report';

    // protected $primaryKey = [''];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'reason',
        'description',
        'userid',
        'commentid',
    ];

    public function comment()
    {
        return $this->belongsTo('App\Models\Comment', 'commentid');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'userid');
    }
}
