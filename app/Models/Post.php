<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'text',
    ];

    public function user(){
        return  $this->belongsTo(User::class);
    }
}