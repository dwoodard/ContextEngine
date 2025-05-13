<?php

namespace App\Agents\Traits\System;

trait VerifiesOutput
{
    public function verify(string $output): bool
    {
        // Example implementation
        return ! empty($output);
    }
}
