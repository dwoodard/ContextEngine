<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = ['pattern', 'input', 'result', 'status', 'meta'];

    protected $casts = [
        'meta' => 'array',  // cast the meta JSON to array
    ];
}
