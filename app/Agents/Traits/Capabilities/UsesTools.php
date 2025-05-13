<?php

namespace App\Agents\Traits\Capabilities;

trait UsesTools
{
    public function useTool(string $tool, array $params): string
    {
        // Example implementation
        return 'Used tool: '.$tool.' with params: '.json_encode($params);
    }
}
