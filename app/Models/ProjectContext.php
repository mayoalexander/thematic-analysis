<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectContext extends Model
{
    protected $fillable = [
        'name',
        'reasoning_context',
        'working_context',
        'draft_context',
        'analysis_results',
        'final_draft',
        'progress',
        'metadata'
    ];

    protected $casts = [
        'reasoning_context' => 'array',
        'working_context' => 'array',
        'draft_context' => 'array',
        'analysis_results' => 'array',
        'final_draft' => 'array',
        'progress' => 'array',
        'metadata' => 'array',
    ];
}
