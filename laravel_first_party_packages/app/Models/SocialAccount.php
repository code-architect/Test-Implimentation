<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $table = 'social_accounts';

    protected $fillable = ['provider_name','provider_id'];



    //------------------------- Relations ------------------------------------------------------//
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
