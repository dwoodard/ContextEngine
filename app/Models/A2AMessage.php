<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class A2AMessage extends Model
{
    protected $table = 'a2a_messages';

    protected $fillable = [
        'task_id',
        'role',
        'sequence_id',
        'parts',
    ];

    protected $casts = [
        'parts' => 'array',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
