<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';
    public $incrementing = false;
    protected $keyType = 'uuid';
    public $timestamps = true;

    protected $fillable = ['title', 'content', 'metadata', 'user_id', 'document_group_id'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
