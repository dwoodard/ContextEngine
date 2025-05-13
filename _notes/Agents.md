# Agents

```
app/
└── Agents/                          # Contains all agent-related classes and logic
    ├── BaseAgent.php                # Abstract base class providing shared functionality for all agents
    ├── Contracts/                   # Defines interfaces that agents must implement
    │   └── AgentInterface.php       # Interface specifying the contract for agent behavior
    ├── Traits/                      # Reusable pieces of code to be included in agent classes
    │   └── SelfReflects.php         # Trait providing self-reflection capabilities to agents
    └── RouterAgent.php              # Agent coordinating actions among multiple agents
```

## AgentInterface

### app/Agents/Contracts/AgentInterface.php

```php
<?php

namespace App\Agents\Contracts;

interface AgentInterface
{
    public function name(): string;

    public function description(): string;

    public function handle(array $input): array;
}
```

## BaseAgent

app/Agents/BaseAgent.php

```php
<?php

namespace App\Agents;

use App\Agents\Contracts\AgentInterface;

abstract class BaseAgent implements AgentInterface
{
    public function description(): string
    {
        return 'No description provided.';
    }

    abstract public function name(): string;

    abstract public function handle(array $input): array;
}

```


## Traits

### app/Agents/Traits/SelfReflects.php

```
app/
└── Agents/
    ├── Traits/
    │   └── Cognitive/
    │   │   ├── BuildsChains.php
    │   │   ├── FormsIntent.php
    │   │   ├── ImprovesItself.php
    │   │   └── PlansAhead.php
    │   ├── Capabilities/
    │   │   ├── CallsLLM.php
    │   │   ├── HandlesMemory.php
    │   │   ├── HandlesTasks.php
    │   │   ├── SelfReflects.php
    │   │   └── UsesTools.php
    │   ├── Social/
    │   │   ├── Delegates.php
    │   │   ├── Negotiates.php
    │   │   └── TalksToAgents.php
    │   ├── System/
    │       ├── AdaptsPersona.php
    │       ├── LogsActivity.php
    │       ├── ThrottlesItself.php
    │       └── VerifiesOutput.php
```


## SelfReflects

### app/Agents/Traits/SelfReflects.php

```php
<?php

namespace App\Agents\Traits;

trait SelfReflects
{
    public function reflect(string $input, string $output): array
    {
        // Base version: simple reflection
        $summary = "The input was: '{$input}'. The agent responded: '{$output}'.";

        // In the future, call Prism or tools to generate deeper insight
        return [
            'reflection' => $summary,
            'insight' => "Consider if this output meets the user's intent.",
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}

```
