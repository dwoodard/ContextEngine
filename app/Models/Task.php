<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config; // Import Config

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'pattern',
        'input',
        'result',
        'status', // Internal status
        'meta',
        'a2a_task_id',    // Add new fields
        'a2a_status',     // A2A standard status
        'a2a_last_message_sequence',
        'a2a_meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'a2a_meta' => 'array', // Cast new field
    ];

    // Helper to get A2A status based on internal status
    public function getA2aStatusAttribute($value): ?string
    {
        // Return explicitly set a2a_status if available, otherwise map internal
        if ($value) {
            return $value;
        }

        return Config::get('a2a.status_map.'.$this->status) ?? null;
    }

    // Optional: Helper to set internal status when a2a_status is set
    public function setA2aStatusAttribute($value): void
    {
        $this->attributes['a2a_status'] = $value;
        // Optionally update internal status based on reverse map if needed
        // $internalStatus = Config::get('a2a.reverse_status_map.' . $value);
        // if ($internalStatus) {
        //     $this->attributes['status'] = $internalStatus;
        // }
    }

    public function a2aMessages()
    {
        return $this->hasMany(A2AMessage::class);
    }
}
