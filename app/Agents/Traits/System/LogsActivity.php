<?php

namespace App\Agents\Traits\System;

trait LogsActivity
{
    public function log(string $activity): void
    {
        // Example implementation
        echo 'Logged activity: '.$activity;
    }
}
