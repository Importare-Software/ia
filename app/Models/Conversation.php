<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'session_id', 'message', 'is_user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
