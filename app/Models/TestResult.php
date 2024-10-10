<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'scenario_id',
        'condition',
        'use_case',
        'execution_detail',
        'expected_results',
        'locators',
        'input_data',
        'model_used',
        'ai_response',
        'ai_response_score',
        'ai_response_corrected',
        'user_id',
        'project_name'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
