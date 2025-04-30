# Context Engine with Laravel build on top of Prism and Relay

```bash
php artisan about
```

|                  | **Setting**            | **Value**            |
|------------------|------------------------|----------------------|
| **Environment**  | Application Name       | Laravel              |
|                  | Laravel Version        | 12.10.2             |
|                  | PHP Version            | 8.3.20              |
|                  | Composer Version       | 2.8.6               |
|                  | Environment            | local               |
|                  | Debug Mode             | ENABLED             |
|                  | URL                    | contextengine.test  |
|                  | Maintenance Mode       | OFF                 |
|                  | Timezone               | UTC                 |
|                  | Locale                 | en                  |
| **Drivers**      | Broadcasting           | log                 |
|                  | Cache                  | database            |
|                  | Database               | mysql               |
|                  | Logs                   | stack / single      |
|                  | Mail                   | smtp                |
|                  | Queue                  | redis               |
|                  | Session                | database            |


## Setup For Context Engine

### Step 1: Create a New Laravel Project and Install Prism

Ensure your environment meets the following requirements:

- **PHP**: Version 8.3 or higher
- **Laravel**: Version 12 (latest)
- **Database**: MySQL
- **Queue**: Redis

#### Create a New Laravel Project

Run the following command in your terminal to create a new Laravel project:

```bash
laravel new ContextEngine
```


### Step 1.1: Set Up Laravel Project Directory

Navigate to the directory where you want to create your Laravel project and run the following command:

```bash
cd ContextEngine
```


This will create a new Laravel application directory named `ContextEngine`. Alternatively, you can use the Laravel installer if you have it installed.

### Step 1.2: Install Prism Package

Require the Prism package via Composer. Prism provides a unified API to work with various LLM providers (e.g., OpenAI, Anthropic, etc.). Run the following command:

```bash
composer require prism-php/prism
```

### Step 1.3: Install Relay Package

```bash
composer require prism-php/relay
```

### Relay Package and External Tool Integration

The Relay package enables seamless integration with external MCP tool servers, allowing agents to perform actions like web searches or API calls as part of their reasoning process.

#### Install Prism and Relay Packages

Run the following command to install the Prism package:

```bash
composer require prism-php/prism
```

If you plan to integrate external tool usage via Model Context Protocol (MCP) (e.g., web browsing or code execution tools), install Prism’s Relay package as well:

```bash
composer require prism-php/relay
```

The Relay package provides seamless integration between Prism and external MCP tool servers, enabling agents to perform advanced actions like web searches or API calls.

#### Publish Configuration Files

After installing Prism (and Relay), publish their configuration files using the following commands:

```bash
php artisan vendor:publish --tag=prism-config
php artisan vendor:publish --tag=relay-config   
```

These configuration files can be adjusted as needed later.

#### Step 2: Configure Environment Variables

Open the `.env` file in the project root and set up the following:

1. **Application Key**: If not already set by the installer, generate the app key using:

    ```bash
    php artisan key:generate
    ```

    Ensure the `APP_KEY` is set in `.env`.

2. **Database (MySQL)**: Update the MySQL connection details. For example, for a local MySQL database named `laravel` with no password for the `root` user:

    ```dotenv
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=laravel
    DB_USERNAME=root
    DB_PASSWORD=
    ```

3. **Queue and Cache (Redis)**: Enable Redis for queue and caching:

    ```dotenv
    QUEUE_CONNECTION=redis
    CACHE_DRIVER=redis
    REDIS_HOST=127.0.0.1
    REDIS_PORT=6379
    ```

4. **API Keys for LLM Providers**: Add API keys for your chosen LLM provider. For example, for OpenAI:

    ```dotenv
    OPENAI_API_KEY=your-openai-api-key
    ```

    These settings will allow Laravel to connect to MySQL and Redis, and Prism to authenticate with the LLM provider.

   ### Database Configuration

    Update your `.env` file with the following MySQL settings:

    ```dotenv
    DB_CONNECTION=mysql  
    DB_HOST=127.0.0.1  
    DB_PORT=3306  
    DB_DATABASE=laravel  
    DB_USERNAME=root  
    DB_PASSWORD=          # (empty or your password)
    ```

   ### Queue and Cache Configuration

    Enable Redis for queue and caching. Add the following to your `.env` file:

    ```dotenv
    QUEUE_CONNECTION=redis  
    CACHE_DRIVER=redis  
    REDIS_HOST=127.0.0.1  
    REDIS_PORT=6379  
    ```

    Ensure you have a Redis server running locally (default on port 6379). Laravel will use it for job queues. If you don't have the PHP Redis extension, you can install the `predis` client:

```bash
composer require predis/predis
```

### Prism LLM Provider Configuration

Configure your default LLM provider in `config/prism.php` or via `.env`. For example, to use OpenAI, add your API key to `.env`:

```dotenv
OPENAI_API_KEY=your-openai-api-key
```

In `config/prism.php`, ensure the `providers` array includes an entry for `openai` using the above key. You can also set Prism’s default provider and model. For example:

```php
    'providers' => [
        'ollama' => [
            'base_url' => env('OLLAMA_URL', 'https://api.ollama.ai/v1'),
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_URL', 'https://api.openai.com/v1'),
            // other settings...
        ],
        // ... other providers like 'anthropic', 'google', etc.
    ],
    'default_provider' => 'openai',
    'default_model' => 'gpt-4',  // or whichever model you have access to
```

### Prism Relay

When using Relay for MCP (Model Context Protocol), configure `config/relay.php`. For example, define an MCP server (like a local Puppeteer tool server) in the `servers` array:

```php
'servers' => [
    'puppeteer' => [
        'command' => ['node', 'path/to/puppeteer_mcp_server.js'],
        'timeout' => 30,
        'env' => ['MCP_SERVER_PORT' => '3001'],
    ],
    // ... other servers or HTTP endpoints
],
```

Relay enables integration with external tools, such as web browsing or API calls, to enhance agent reasoning capabilities.


```php
'servers' => [
    'puppeteer' => [
        'command' => ['node', base_path('path/to/puppeteer_mcp_server.js')],
        'timeout' => 30,
        'env' => [
            'MCP_SERVER_PORT' => env('MCP_SERVER_PORT', '3001'),
        ],
    ],
    // Add additional servers or HTTP endpoints as needed
],
```

### Using MCP Server with Relay

If you plan to use an MCP server for external tool integration, ensure it is properly set up. For example, you can configure a Puppeteer-based MCP server to enable web browsing capabilities. With Relay configured, an agent can utilize external tools by calling `Relay::tools('puppeteer')` in Prism. This allows seamless integration of external functionalities into your AI workflows.

After updating the `.env` file with the necessary configurations, save the file. These settings will enable Laravel to connect to MySQL and Redis, and allow Prism to authenticate with the LLM provider.

---

### Step 3: Project Structure Overview

To maintain a clean and modular codebase, we will organize the project into the following components:

```plaintext
ContextEngine/
    ├── app/
    │   ├── Http/
    │   │   └── Controllers/
    │   │       └── TaskController.php        # Handles API requests for task submission and retrieval
    │   ├── Models/
    │   │   └── Task.php                      # Eloquent model for tasks
    │   ├── Services/
    │   │   ├── AgentPattern.php             # Base interface for agent pattern classes
    │   │   └── Patterns/
    │   │       ├── PlannerExecutorAgent.php   # Implements Planner-Executor pattern
    │   │       ├── ParallelAgent.php          # Implements Parallelization pattern
    │   │       ├── DebateAgent.php            # Implements Debate & Consensus pattern
    │   │       ├── SelfReflectorAgent.php     # Implements Self-Reflection pattern
    │   │       ├── MemoryAgent.php            # Implements Memory-Augmented pattern
    │   │       └── GoalDecomposerAgent.php    # Implements Goal Decomposition pattern
    │   └── Jobs/
    │       ├── ProcessTaskJob.php            # Orchestrates task execution using the chosen pattern
    │       └── SubTaskJob.php                # Handles sub-tasks for parallel or decomposed tasks
    ├── routes/
    │   └── api.php                           # Defines API routes (e.g., POST /tasks, GET /tasks/{id})
    ├── database/
    │   ├── migrations/
    │   │   └── 2025_04_26_000000_create_tasks_table.php   # Migration for tasks table
    │   └── seeders/                          # (Optional) Seeders for initial data
    └── config/
        ├── prism.php                         # Prism configuration (published earlier)
        └── relay.php                         # Relay configuration (if using MCP)
```

### TaskController

The `TaskController` is responsible for handling incoming API requests. It provides endpoints to create a new task and to fetch the status or results of an existing task. This controller ensures that tasks are processed asynchronously and results are returned once available.

### Task Model and Migration

The `Task` model represents tasks in the database. It stores essential fields such as the user’s prompt, the selected agent pattern, the task’s status, and the final result output. A corresponding migration defines the schema for the `tasks` table, ensuring proper storage and retrieval of task data.

### Services and Agent Patterns

The `Services/AgentPattern` directory contains the logic for selecting and executing agent patterns. It includes:

- **Task Interpreter/Matcher**: Determines the most suitable agent pattern based on the user’s input.
- **Base Interface**: Provides a contract for all agent pattern classes to implement.
- **Pattern Implementations**: Each agent pattern (e.g., `PlannerExecutor`, `ParallelAgent`) is implemented as a separate class under `Services/Patterns`. This modular design allows for easy addition of new patterns by simply creating a new class.

### Jobs

The heavy lifting of task processing is handled by queued jobs. Key jobs include:

- **`ProcessTaskJob`**: A high-level job that orchestrates the execution of the selected agent pattern for a task.
- **`SubTaskJob`**: Used by complex patterns (e.g., parallel processing) to handle distributed or concurrent work.

Laravel’s queue system, backed by Redis, ensures these jobs run asynchronously, enabling efficient task processing without blocking API responses.

---

### Step 4: Database Migration and Model for Tasks

To persist tasks and their results, we need a database table. This table will store information such as the user’s input, the selected pattern, the task’s status, and the result. Let’s create a migration and model for the `tasks` table.

#### Create Migration and Model

Use the following Artisan command to generate the migration and model:

```bash
php artisan make:model Task -m
```

This command creates:

- A `Task` model in `app/Models/Task.php`.
- A timestamped migration file in `database/migrations/`.

#### Define the Schema

Open the generated migration file and define the schema for the `tasks` table. For example:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration {
    public function up() {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('pattern')->nullable();       // The agent pattern chosen (e.g., 'planner', 'parallel', etc.)
            $table->text('input');                       // The user prompt or task description
            $table->text('result')->nullable();          // Final result from the agent(s)
            $table->string('status')->default('pending'); // Task status: 'pending', 'running', 'completed', 'failed'
            $table->json('meta')->nullable();            // (Optional) Store additional data (sub-results, logs, memory, etc.)
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('tasks');
    }
}
```

#### Run the Migration

Execute the migration to create the `tasks` table in the database:

```bash
php artisan migrate
```

#### Define the Task Model

In `app/Models/Task.php`, define the model to match the table schema:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['pattern', 'input', 'result', 'status', 'meta'];

    protected $casts = [
        'meta' => 'array',  // Cast the meta JSON to an array
    ];
}
```

The `fillable` property allows mass assignment for the specified fields, and the `casts` property ensures the `meta` field is automatically converted to an array for easy manipulation.

With the migration and model in place, the application is ready to store and manage tasks in the database.

php artisan make:model Task -m
This creates app/Models/Task.php and a timestamped migration file in database/migrations/. Open the migration file and define the schema, for example:
php


// database/migrations/2025_04_26_000000_create_tasks_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration {
    public function up() {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('pattern')->nullable();       // The agent pattern chosen (e.g., 'planner', 'parallel', etc.)
            $table->text('input');                       // The user prompt or task description
            $table->text('result')->nullable();          // Final result from the agent(s)
            $table->string('status')->default('pending'); // Task status: 'pending','running','completed','failed'
            $table->json('meta')->nullable();            // (Optional) store additional data (sub-results, logs, memory, etc.)
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('tasks');
    }
}
Run the migration:

php artisan migrate
This will create the tasks table in MySQL. Task Model: In app/Models/Task.php, define the model to match the table. For instance:
php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['pattern', 'input', 'result', 'status', 'meta'];

    protected $casts = [
        'meta' => 'array',  // cast the meta JSON to array
    ];
}
The fillable fields allow mass assignment when creating tasks. The meta field (if used) is cast to array for easy manipulation. Note: You can extend this schema as needed. For example, you might add a user_id if multi-user, or a separate memories table for a more advanced memory mechanism. But for our minimal setup, this single table will hold the necessary info for each task.
Step 5: Task API Controller and Routes
Next, create a controller to handle API requests for tasks. We’ll create endpoints to submit a new task and retrieve task status/result. Create Controller: Use Artisan command:

php artisan make:controller TaskController --api
This generates TaskController with stub methods for RESTful actions. We’ll focus on store (to create a task) and show (to fetch a task). Define Routes: Open routes/api.php and add routes for tasks:
php


use App\Http\Controllers\TaskController;

Route::post('/tasks', [TaskController::class, 'store']);    // Submit a new task
Route::get('/tasks/{task}', [TaskController::class, 'show']); // Get status/result of a specific task
Laravel's API routes by default are prefixed with /api, so these endpoints will be /api/tasks and /api/tasks/{id}. Implement Controller Logic: In TaskController.php:
php


namespace App\Http\Controllers;

use App\Models\Task;
use App\Jobs\ProcessTaskJob;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Submit a new task
    public function store(Request $request)
    {
        // Validate input - ensure at least a prompt or input is provided
        $data = $request->validate([
            'input' => 'required|string',
            'pattern' => 'nullable|string'  // optionally user can specify a pattern
        ]);

        // Determine which agent pattern to use
        $pattern = $data['pattern'] ?? $this->matchPattern($data['input']);

        // Create a new task record in DB with status 'pending'
        $task = Task::create([
            'input' => $data['input'],
            'pattern' => $pattern,
            'status' => 'pending',
        ]);

        // Dispatch a job to process this task using the chosen pattern
        ProcessTaskJob::dispatch($task->id, $pattern);

        // Return a response with task ID and initial status
        return response()->json([
            'task_id' => $task->id,
            'pattern' => $pattern,
            'status' => $task->status
        ], 202);
    }

    // Retrieve task status/result
    public function show(Task $task)
    {
        // Using route model binding to get Task by ID
        return response()->json([
            'task_id' => $task->id,
            'pattern' => $task->pattern,
            'status' => $task->status,
            'result' => $task->result,
            'meta'   => $task->meta,
        ]);
    }

    // Simple Task Interpreter/Pattern Matcher 
    protected function matchPattern(string $userInput): string
    {
        // **Basic heuristic pattern selection** 
        // (This can be as complex as needed, even using an LLM to classify the task)
        $input = strtolower($userInput);
        if (str_contains($input, ' versus') || str_contains($input, ' vs ')) {
            return 'debate';  // user is asking for comparison -> debate pattern
        }
        if (preg_match('/\b(and|&|,)\b/', $input)) {
            return 'parallel';  // multiple parts in query -> parallelize
        }
        if (str_contains($input, 'step') || str_contains($input, 'plan')) {
            return 'planner';  // explicit steps or planning needed
        }
        // Default fallback:
        return 'planner';  // default to planner-executor if unsure
    }
}
A few notes on this implementation:
We validated that the request contains an input (the user’s prompt or task description). Optionally, the client can specify a desired pattern (like "debate", "parallel", etc.) to force a particular agent mode. If not provided, we call our matchPattern() method to pick one based on simple rules (this is our Task Interpreter/Pattern Matcher). The heuristic shown is very basic – in a real system, you might use more advanced logic or even an AI classifier to choose the best pattern.
We then create a Task in the database with status = "pending" and dispatch the ProcessTaskJob with the task’s ID and chosen pattern. This job will run asynchronously in the background (on the Redis queue).
We immediately return a JSON response with the task_id and initial status. We use HTTP 202 Accepted, indicating the request is accepted for processing. The client can then poll the GET endpoint for the result.
The show method simply returns the current state of the task (including the final result when ready). We register route-model binding, so Laravel will fetch the Task by ID automatically for show.
At this point, if you start your Laravel server (php artisan serve), the API is ready to accept requests, but the background job logic is not implemented yet. Let’s build the job and the agent pattern classes next.
Step 6: Agent Pattern Base Interface and Orchestrator Job
To make the system extensible, we define a base interface (or abstract class) for Agent Patterns. Each agent pattern class will implement this interface, encapsulating the logic for that strategy. The ProcessTaskJob will serve as a generic orchestrator that invokes the appropriate pattern class for a given task. AgentPattern Interface: Create a file app/Services/AgentPattern.php:
php


namespace App\Services;

use App\Models\Task;

interface AgentPattern
{
    public function execute(Task $task): void;
    // The execute method will perform the agent's logic on the given Task.
    // It should update the Task with results (and any intermediate data in meta) and set status.
}
We’ll ensure each pattern class implements AgentPattern. The execute method doesn’t return anything; instead it will directly update the Task record (persisting the result to DB). ProcessTaskJob: Now create the main job that runs a task through the selected pattern. Generate the job file:

php artisan make:job ProcessTaskJob
Then implement it in app/Jobs/ProcessTaskJob.php:
php


namespace App\Jobs;

use App\Models\Task;
use App\Services\AgentPattern;
use App\Services\Patterns\PlannerExecutorAgent;
use App\Services\Patterns\ParallelAgent;
use App\Services\Patterns\DebateAgent;
use App\Services\Patterns\SelfReflectorAgent;
use App\Services\Patterns\MemoryAgent;
use App\Services\Patterns\GoalDecomposerAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTaskJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected int $taskId;
    protected string $pattern;

    public function __construct(int $taskId, string $pattern)
    {
        $this->taskId = $taskId;
        $this->pattern = $pattern;
    }

    public function handle()
    {
        // Retrieve the task from DB
        $task = Task::find($this->taskId);
        if (!$task) return;

        // Update status to 'running'
        $task->update(['status' => 'running']);

        try {
            // Instantiate appropriate pattern handler
            $agent = $this->resolvePattern($this->pattern);
            // Execute the pattern logic
            $agent->execute($task);

            // Mark task as completed (the pattern should have set result)
            $task->update(['status' => 'completed']);
        } catch (\Exception $e) {
            // Handle any errors during execution
            $task->update([
                'status' => 'failed',
                'result' => null,
            ]);
            // (Optional) Log the error or store in task meta
            $task->update(['meta' => array_merge($task->meta ?? [], [
                'error' => $e->getMessage()
            ])]);
        }
    }

    protected function resolvePattern(string $patternName): AgentPattern
    {
        return match($patternName) {
            'planner'    => new PlannerExecutorAgent(),
            'parallel'   => new ParallelAgent(),
            'debate'     => new DebateAgent(),
            'reflect'    => new SelfReflectorAgent(),
            'memory'     => new MemoryAgent(),
            'decompose'  => new GoalDecomposerAgent(),
            default      => new PlannerExecutorAgent(),
        };
    }
}
Explanation:
The job takes a Task ID and pattern name. In handle(), it fetches the Task and sets its status to "running".
We use a resolvePattern() helper to map the pattern name to an instance of the corresponding agent class. This is a simple factory. We pass the task to the agent’s execute method, which will perform all the LLM calls and updates.
If execution succeeds, we mark the task status as "completed". If any exception occurs, we catch it and mark status "failed" and record the error (for debugging) in the task’s meta field.
Each agent pattern class will internally update the $task->result (and possibly $task->meta) during its execution. We will implement those next.
This design allows adding new patterns easily: create a class implementing AgentPattern, then add a case in resolvePattern() (or you could use a more dynamic registry, but a simple match is clear for now). Now, let’s implement each agent pattern class with the LLM (Prism) logic.
Step 7: Implementing Agent Pattern Classes
Before diving into each pattern, note that all patterns will use Prism to interact with the LLM. Prism gives a fluent API to prompt the model and handle responses. For example, to get a simple completion, one might call:
php


use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;

$reply = Prism::text()
    ->using(Provider::OpenAI, 'gpt-4')         // use GPT-4 via OpenAI, if not default
    ->withPrompt($prompt)
    ->asText();

$text = $reply->text;  // the generated text
We will use similar calls within each pattern’s logic. Ensure you import the Prism classes at the top of each pattern class file (e.g., use Prism\Prism\Prism; and any needed enums/facades).
7.1 Planner-Executor Pattern (Step-by-Step Reasoning)
Goal: The agent first devises a plan or breakdown of the problem, then produces the final answer following that plan. This follows a chain-of-thought approach​
medium.com
. Class: app/Services/Patterns/PlannerExecutorAgent.php:
php


namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;

class PlannerExecutorAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $userQuery = $task->input;

        // Step 1: Use LLM to create a plan (sequence of steps or outline)
        $planPrompt = "Break down the following task into a step-by-step plan:\nTask: {$userQuery}";
        $planResponse = Prism::text()
            ->withPrompt($planPrompt)
            ->asText();
        $plan = $planResponse->text ?? '(no plan)';

        // Optional: store the plan in task meta for record
        $task->meta = array_merge($task->meta ?? [], ['plan' => $plan]);
        $task->save();

        // Step 2: Use LLM to execute/answer using the plan
        $execPrompt = "Given the plan:\n{$plan}\nNow provide the final answer or solution to the original task: {$userQuery}";
        $answerResponse = Prism::text()
            ->withPrompt($execPrompt)
            ->asText();
        $answer = $answerResponse->text ?? '';

        // Update task with the result
        $task->result = $answer;
        $task->save();
    }
}
In this implementation:
We first ask the LLM to generate a plan for the user’s task. The prompt explicitly asks for a breakdown of the problem. The LLM’s response (perhaps a numbered list of steps or a strategy) is captured as the plan.
We store the plan in the task’s meta field for transparency or debugging.
Next, we feed the plan into a second prompt, asking the LLM to now produce the final answer given that plan. This way, the model is guided by the structured approach it just created.
Finally, we save the resulting answer in task.result.
This pattern helps when the query is complex or open-ended, ensuring the solution is well-structured.
7.2 Parallelization Pattern (Concurrent Sub-tasks)
Goal: If a user request contains multiple independent questions or tasks, handle them in parallel to save time​
medium.com
. For example, “Tell me about X and Y” can be split into two prompts handled concurrently. Class: app/Services/Patterns/ParallelAgent.php:
php


namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Prism\Prism\Prism;

class ParallelAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $userQuery = $task->input;
        // Simple split logic: split by " and " (for demonstration)
        $parts = preg_split('/\\band\\b|&|,/i', $userQuery);
        $parts = array_filter(array_map('trim', $parts));  // clean empty entries

        if (count($parts) <= 1) {
            // If we couldn't detect multiple parts, just use PlannerExecutor as fallback
            (new PlannerExecutorAgent())->execute($task);
            return;
        }

        // Dispatch a sub-task job for each part (in parallel)
        $batch = Bus::batch([])->allowFailures()->then(function (Batch $batch) use ($task) {
            // This callback runs after all sub-tasks complete
            // Combine results from meta
            $subResults = $task->meta['sub_results'] ?? [];
            $combined = implode("\n", $subResults);
            $task->result = $combined;
            $task->save();
        })->dispatch();

        // Launch each part as a separate job
        foreach ($parts as $idx => $part) {
            $batch->add(new \App\Jobs\SubTaskJob($task->id, trim($part), $idx));
        }
    }
}
Here’s how the parallel pattern works:
We attempt to split the user’s input into separate parts. (This uses a very naive split on “and”/commas. In practice, you would parse the request more robustly.)
If only one part is found, there’s nothing to parallelize, so we delegate to the planner-executor as a fallback.
If multiple sub-tasks are identified, we use Laravel’s Bus Batch feature to dispatch a batch of jobs concurrently. We create a batch and in the then() callback (which fires when all jobs in the batch are finished) we combine the results.
Each part is handled by a SubTaskJob – we need to implement this job as well. The SubTaskJob will take the task ID and the sub-query, call the LLM, and save its result in the parent task’s meta.
SubTaskJob: In app/Jobs/SubTaskJob.php (create with php artisan make:job SubTaskJob):
php


namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Prism\Prism\Prism;

class SubTaskJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected int $taskId;
    protected string $subQuery;
    protected int $index;

    public function __construct(int $taskId, string $subQuery, int $index = 0)
    {
        $this->taskId = $taskId;
        $this->subQuery = $subQuery;
        $this->index = $index;
    }

    public function handle()
    {
        $task = Task::find($this->taskId);
        if (!$task) return;

        // Call LLM for this sub-query
        $response = Prism::text()->withPrompt($this->subQuery)->asText();
        $answer = $response->text ?? '';

        // Store sub-result in task.meta.sub_results[index]
        $meta = $task->meta ?? [];
        $meta['sub_results'][$this->index] = $answer;
        $task->meta = $meta;
        $task->save();
    }
}
Each SubTaskJob takes a portion of the query, gets an answer from the LLM, and saves it in the parent task’s meta['sub_results'] array at its index. The ParallelAgent batch callback then concatenates all sub_results (in order) into one combined task.result. With this, the user’s multiple questions are answered in parallel. For example, input “Explain quantum computing and describe the Theory of Relativity” would result in two parallel LLM calls, and the final result might contain two paragraphs, one for each topic.
7.3 Debate and Consensus Pattern
Goal: Have two or more agents debate a question from different perspectives and then converge on an answer​
medium.com
​
medium.com
. This can improve accuracy on contentious or complex queries by considering multiple solutions. Class: app/Services/Patterns/DebateAgent.php:
php


namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Prism\Prism\Prism;

class DebateAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $userQuery = $task->input;

        // Agent A: proponent perspective
        $promptA = "You are Agent A. You strongly support or agree with the following statement/question and provide an answer arguing for it:\nQ: {$userQuery}";
        $respA = Prism::text()->withPrompt($promptA)->asText();
        $answerA = $respA->text ?? '';

        // Agent B: opponent/skeptical perspective
        $promptB = "You are Agent B. You are skeptical or take an opposing view on the statement/question and provide an answer arguing against it or highlighting issues:\nQ: {$userQuery}";
        $respB = Prism::text()->withPrompt($promptB)->asText();
        $answerB = $respB->text ?? '';

        // Store both answers in meta for reference
        $task->meta = [
            'agent_A' => $answerA,
            'agent_B' => $answerB,
        ];
        $task->save();

        // Moderator: form a consensus or choose the best answer
        $moderatorPrompt = "Agent A answered:\n{$answerA}\n\nAgent B answered:\n{$answerB}\n\nYou are a moderator. Combine the insights from both agents and provide the best final answer to the question: {$userQuery}";
        $respM = Prism::text()->withPrompt($moderatorPrompt)->asText();
        $finalAnswer = $respM->text ?? '';

        // Update task result with the consensus answer
        $task->result = $finalAnswer;
        $task->save();
    }
}
How this works:
We prompt the LLM twice in different “roles”: Agent A is instructed to give a positive/supporting perspective, Agent B to give a critical/opposing perspective. (The exact prompt can be adjusted depending on the question; the idea is to get two diverse answers.)
We save both interim answers in task.meta (agent_A and agent_B) for traceability.
Then we prompt the LLM as a moderator or judge: it sees both answers and is asked to synthesize them or decide on the best reasoning. The result of this prompt is the final answer stored in task.result.
This pattern effectively uses the LLM to critique itself. The debate ensures multiple viewpoints are considered, potentially increasing the quality or fairness of the answer​
medium.com
.
You could extend this pattern with more than two agents, or multiple rounds of debate, but the basic structure remains: generate differing answers and then reconcile.
7.4 Self-Reflector Pattern (Self-Critique and Refinement)
Goal: The agent answers the question, then reviews its own answer to find flaws or improvements, and produces a refined final answer​
medium.com
. This helps catch mistakes or add details that the first pass might miss. Class: app/Services/Patterns/SelfReflectorAgent.php:
php


namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Prism\Prism\Prism;

class SelfReflectorAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $query = $task->input;
        // First attempt answer
        $initialResp = Prism::text()->withPrompt($query)->asText();
        $initialAnswer = $initialResp->text ?? '';

        // Self-reflection: have the LLM critique its answer
        $reflectPrompt = "You answered the question as follows:\n\"{$initialAnswer}\"\nNow reflect on this answer. Identify any incorrect assumptions, missing details, or improvements. Provide a critique.";
        $critiqueResp = Prism::text()->withPrompt($reflectPrompt)->asText();
        $critique = $critiqueResp->text ?? '';

        // Use the critique to improve the answer
        $improvePrompt = "Critique of the answer: {$critique}\n\nNow improve the original answer based on this critique. Provide a final, refined answer to the question: {$query}";
        $finalResp = Prism::text()->withPrompt($improvePrompt)->asText();
        $finalAnswer = $finalResp->text ?? $initialAnswer;

        // Store critique and set final result
        $task->meta = array_merge($task->meta ?? [], ['critique' => $critique]);
        $task->result = $finalAnswer;
        $task->save();
    }
}
Steps:
Ask the LLM directly for an answer (initialAnswer).
Then ask the LLM to critique that answer. The prompt explicitly provides the answer and asks for a reflection or identification of issues.
Finally, feed the critique back and ask for an improved answer. The resulting finalAnswer should ideally be better (more correct or complete) than the initial one.
We store the critique in meta and the refined answer as the result.
This self-feedback loop pattern leverages the LLM’s own evaluation ability to produce higher-quality outcomes, essentially self-correcting its output​
medium.com
.
7.5 Memory-Augmented Agent Pattern
Goal: Augment the agent with external memory or knowledge. Before answering, the agent retrieves relevant information from a stored knowledge base (or past conversations) and uses it in the prompt​
medium.com
. This pattern is akin to Retrieval-Augmented Generation (RAG) – providing the LLM with additional context so it doesn’t rely solely on its internal training. In our simple setup, we’ll use the MySQL database as a knowledge repository of past tasks. For example, the agent can search previous tasks for related topics and include those results as context. Class: app/Services/Patterns/MemoryAgent.php:
php


namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Prism\Prism\Prism;
use Illuminate\Support\Str;

class MemoryAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $query = $task->input;
        $context = '';

        // Retrieve some relevant past knowledge (simple strategy: find tasks with similar keywords)
        $keywords = array_slice(explode(' ', Str::lower($query)), 0, 3); // take first 3 keywords
        if (!empty($keywords)) {
            $relatedTasks = Task::where('id', '!=', $task->id)
                                 ->where(function($q) use ($keywords) {
                                     foreach ($keywords as $word) {
                                         $q->orWhere('input', 'LIKE', "%{$word}%");
                                     }
                                 })
                                 ->orderBy('id', 'desc')
                                 ->limit(3)
                                 ->get();
            foreach ($relatedTasks as $rel) {
                if ($rel->result) {
                    $context .= "Q: {$rel->input}\nA: {$rel->result}\n\n";
                }
            }
        }

        if ($context) {
            // If we found related info, prepend it to the prompt
            $prompt = "Use the following information to help answer the question.\n{$context}\nQuestion: {$query}\nAnswer:";
        } else {
            $prompt = $query;
        }

        // Call LLM with the context-enhanced prompt
        $response = Prism::text()->withPrompt($prompt)->asText();
        $answer = $response->text ?? '';

        // Save context used (for transparency) and the result
        $task->meta = array_merge($task->meta ?? [], ['used_context' => $context]);
        $task->result = $answer;
        $task->save();
    }
}
In this implementation:
We take the user’s query and pick a few keywords (this is a naive way to search; a real system might use vector embeddings for semantic search).
We query the tasks table for recent tasks that contain those keywords in their input (excluding the current task itself). We gather a few of those and build a context string containing past Q&A pairs.
If we found any, we prepend them to the new query in the prompt, instructing the model to use that information. If none found, we just use the query as-is.
Then we call Prism to get the answer. The result hopefully benefits from the added context (for example, if a similar question was answered before, the model can leverage that).
We store the context in meta.used_context for reference, and save the answer in result.
This demonstrates how memory can be incorporated. In real applications, you might integrate a vector database and store embeddings of past answers for more accurate retrieval, but the principle is the same: fetch relevant info and supply it to the LLM.
7.6 Goal Decomposer Pattern (Hierarchical Task Breakdown)
Goal: For very complex tasks, the agent repeatedly breaks the goal into sub-goals, solves them, and assembles a final solution​
medium.com
. This is similar to the Planner-Executor but possibly goes through multiple layers (like a recursive planner). We will implement a simple one-level decomposition: break the problem into sub-tasks, solve each, then combine results. (A true recursive approach could involve looping until sub-tasks are atomic, but that’s an advanced extension.) Class: app/Services/Patterns/GoalDecomposerAgent.php:
php


namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Prism\Prism\Prism;

class GoalDecomposerAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $query = $task->input;
        // Step 1: Ask LLM to decompose the goal into sub-goals
        $decomposePrompt = "Break down the following task into a list of smaller goals or questions:\nTask: {$query}";
        $decompResp = Prism::text()->withPrompt($decomposePrompt)->asText();
        $subGoalsText = $decompResp->text ?? '';
        $subGoals = preg_split('/\n+/', trim($subGoalsText));  // split by lines as subgoals
        $subGoals = array_filter(array_map('trim', $subGoals));

        if (empty($subGoals)) {
            // If no decomposition, just answer directly
            $directResp = Prism::text()->withPrompt($query)->asText();
            $task->result = $directResp->text ?? '';
            $task->save();
            return;
        }

        // Step 2: Solve each sub-goal
        $subResults = [];
        foreach ($subGoals as $subGoal) {
            $subAnswerResp = Prism::text()->withPrompt($subGoal)->asText();
            $subResults[] = ($subAnswerResp->text ?? '');
        }
        // Store sub-goals and sub-results in meta for traceability
        $task->meta = [
            'sub_goals' => $subGoals,
            'sub_results' => $subResults
        ];
        $task->save();

        // Step 3: Ask LLM to synthesize a final answer from all sub-results
        $synthesisPrompt = "We had broken the task into sub-goals and answered each:\n";
        foreach ($subGoals as $index => $sg) {
            $ans = $subResults[$index];
            $synthesisPrompt .= "- Sub-goal: {$sg}\n  Answer: {$ans}\n";
        }
        $synthesisPrompt .= "\nNow combine these findings into a comprehensive final answer for the original task: {$query}";

        $finalResp = Prism::text()->withPrompt($synthesisPrompt)->asText();
        $finalAnswer = $finalResp->text ?? implode("\n", $subResults);

        $task->result = $finalAnswer;
        $task->save();
    }
}
Process:
We first prompt the LLM to list sub-goals. For example, if the query is "How can we improve city traffic flow and reduce accidents?" the model might return something like:
Improve public transportation.
Implement better traffic signal timing.
Increase road safety education.
We then iterate through each sub-goal and prompt the LLM to answer that sub-problem. We collect all these sub-results.
We store the sub-goals and corresponding answers in the task’s meta (so we know the breakdown).
Finally, we ask the LLM to synthesize a final answer that combines all these points. We provide all sub-questions and answers in the prompt, and instruct it to produce a comprehensive answer to the original question.
The final answer is saved as the result.
This pattern essentially creates a mini pipeline: decompose → solve parts → recompose. It’s useful for very broad queries that benefit from structure and thoroughness​
medium.com
. Each of these pattern classes can be further refined or complexified, but we now have a template for all the listed patterns.
Step 8: Running the System Locally
Now that we have all components in place, we can test the entire workflow. Make sure you have the following running in separate terminals:
Laravel development server: php artisan serve (serving the API endpoints).
Queue worker: php artisan queue:work --tries=3 (listening on Redis for the jobs). This will process the ProcessTaskJob and any SubTaskJobs.
Also ensure MySQL and Redis services are up and your .env is correctly configured (including any API keys for the LLM). Testing a Task Request: You can use a tool like curl or Postman to send a task. For example:

curl -X POST <http://localhost:8000/api/tasks> \
     -H "Content-Type: application/json" \
     -d '{ "input": "Compare and contrast quantum computing and classical computing." }'
This will create a new task. The response might look like:
json


{
  "task_id": 1,
  "pattern": "debate",
  "status": "pending"
}
Here, our simple matchPattern logic detected “and” in the query and chose the parallel pattern (in this example, it might choose debate or parallel based on the heuristic). The task ID is 1. Now, the background worker will pick up the job. After a short time (depending on LLM response speed), the task should complete. You can then GET the result:

curl <http://localhost:8000/api/tasks/1>
Response (example):
json


{
  "task_id": 1,
  "pattern": "debate",
  "status": "completed",
  "result": "Quantum computing differs from classical computing in that it uses quantum bits ... (full answer here)",
  "meta": {
    "agent_A": "Quantum computing is superior for certain tasks because ...",
    "agent_B": "Classical computing will always be relevant because ...",
    /*... plus possibly the critique or plan etc, depending on pattern*/
  }
}
You can observe the result field for the final answer. The meta field may contain intermediate data (like the debate agents’ answers, plans, critiques, etc., as we coded). Adding New Patterns: To add a new agentic pattern, simply create a class in app/Services/Patterns implementing AgentPattern::execute. You can leverage any tools (including Prism’s tool integration via Relay if you installed it, e.g. ->withTools(Relay::tools('puppeteer')) to enable web browsing in the prompt​
github.com
). Then register that pattern in the ProcessTaskJob::resolvePattern() method with a unique key. The system is designed to be modular, so new patterns plug in with minimal changes elsewhere. Example (Optional) – Tool-augmented Agent: If you wanted an agent that can perform web searches as part of its reasoning, you could set up an MCP server (like a Puppeteer-based browser agent) and use Relay. For instance, Prism Relay allows including tools in the prompt:
php


use Prism\Relay\Facades\Relay;
use Prism\Prism\Enums\Provider;

$response = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-7-sonnet-latest')
    ->withPrompt("Find the latest Laravel release date by searching the web.")
    ->withTools(Relay::tools('puppeteer'))  // allows web browsing tool usage
    ->asText();
With proper MCP server config, the agent would actually perform the web search and return the info​
github.com
. This kind of extension can be integrated into any of the above patterns if needed. Finally, since this is a local dev setup, you don’t need to worry about deployment specifics. The system runs on the Laravel development server and uses local MySQL/Redis. You may use Tinker or logging to debug the agent behaviors. All critical pieces (controllers, jobs, services) have been provided in template form and can be adjusted as you refine prompts or logic.
Conclusion
We have built a Laravel-based AI orchestration API that dynamically selects and executes different agent patterns. By leveraging Laravel’s queue system and Prism for LLM integration, the app can handle complex AI workflows: planning, parallel reasoning, debating answers, self-refinement, using memory, and breaking down goals. The design is extensible – new patterns or tools can be added as needed. This framework provides a foundation for experimenting with advanced multi-agent AI strategies in a robust web API environment. Now you can further enhance each agent’s prompts or incorporate actual AI model responses to suit your specific use case. Happy coding!
