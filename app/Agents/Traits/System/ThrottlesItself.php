<?php

namespace App\Agents\Traits\System;

trait ThrottlesItself
{
    public function throttle(int $limit): string
    {
        // Example implementation
        return 'Throttled to limit: '.$limit;
    }
}
