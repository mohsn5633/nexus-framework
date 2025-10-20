# Process and Workers

Nexus Framework provides a powerful process management and parallel processing system for executing tasks concurrently and managing background workers.

## Table of Contents

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Process Management](#process-management)
- [Workers](#workers)
- [Process Pool](#process-pool)
- [Parallel Processing](#parallel-processing)
- [Examples](#examples)

## Introduction

The Process system enables:

- **Process Execution**: Run external commands and processes
- **Background Workers**: Execute tasks in background processes
- **Process Pool**: Manage multiple workers for parallel processing
- **Concurrent Execution**: Run tasks in parallel for better performance
- **Process Monitoring**: Track process status and resource usage
- **Timeout Management**: Control process execution time
- **Output Handling**: Capture and process output streams

## Configuration

### Environment Variables

Configure process settings in `.env`:

```env
# Process Configuration
PROCESS_WORKING_DIR=/path/to/project
PROCESS_TIMEOUT=300

# Worker Pool
WORKER_POOL_MAX_WORKERS=4
WORKER_POOL_TIMEOUT=300
WORKER_POOL_IDLE_TIMEOUT=60

# Process Limits
PROCESS_MEMORY_LIMIT=256M
PROCESS_TIME_LIMIT=300

# PHP Binary
PHP_BINARY_PATH=/usr/bin/php
```

### Configuration File

Process configuration is in `config/process.php`:

```php
return [
    'working_directory' => env('PROCESS_WORKING_DIR', base_path()),
    'timeout' => env('PROCESS_TIMEOUT', 300),

    'pool' => [
        'max_workers' => env('WORKER_POOL_MAX_WORKERS', 4),
        'timeout' => env('WORKER_POOL_TIMEOUT', 300),
    ],

    'limits' => [
        'memory' => env('PROCESS_MEMORY_LIMIT', '256M'),
        'time' => env('PROCESS_TIME_LIMIT', 300),
    ],
];
```

## Process Management

### Creating a Process

```php
use Nexus\Process\Process;

// Create process with command
$process = new Process('ls -la');

// Using helper
$process = process('ls -la');

// With working directory
$process = new Process('ls', '/path/to/dir');

// With environment variables
$process = new Process('env', null, ['VAR' => 'value']);
```

### Running Processes

```php
// Run and wait for completion
$exitCode = $process->run();

// Run with output callback
$process->run(function ($output, $type) {
    if ($type === 'stdout') {
        echo "OUT: {$output}";
    } else {
        echo "ERR: {$output}";
    }
});

// Start without waiting
$process->start();

// Wait for completion later
$exitCode = $process->wait();
```

### Process Configuration

```php
$process = new Process('long-running-task');

// Set timeout
$process->setTimeout(60);

// Set working directory
$process->setWorkingDirectory('/path/to/dir');

// Set environment
$process->setEnv(['KEY' => 'value']);

// Run
$process->run();
```

### Getting Output

```php
$process->run();

// Get output
$output = $process->getOutput();

// Get error output
$errors = $process->getErrorOutput();

// Get exit code
$exitCode = $process->getExitCode();

// Check if successful
if ($process->isSuccessful()) {
    echo "Process completed successfully";
}
```

### Process Status

```php
$process->start();

// Check if running
if ($process->isRunning()) {
    echo "Process is still running";
}

// Get status information
$status = $process->getStatus();
print_r($status);

// Get execution time
$time = $process->getExecutionTime();
echo "Executed in {$time} seconds";
```

### Stopping Processes

```php
$process->start();

// Stop process
$process->stop();

// Force kill
$process->stop(9); // SIGKILL
```

### Writing to Process

```php
$process = new Process('php -r "while(true) { echo fgets(STDIN); }"');
$process->start();

// Write to stdin
$process->write("Hello\n");
$process->write("World\n");

sleep(1);
$process->stop();
```

## Workers

### Creating Workers

```php
use Nexus\Process\Worker;

// Create a worker
$worker = new Worker();

// With custom ID
$worker = new Worker('worker-1');
```

### Executing Tasks

```php
// Execute task synchronously
$result = $worker->execute(function () {
    // Perform some work
    sleep(2);
    return "Task completed";
});

echo $result; // "Task completed"
```

### Async Execution

```php
// Execute task asynchronously
$process = $worker->executeAsync(function ($arg1, $arg2) {
    return $arg1 + $arg2;
}, 10, 20);

// Wait for result
$result = $worker->wait();
echo $result; // 30
```

### Worker Status

```php
// Check if worker is busy
if ($worker->isBusy()) {
    echo "Worker is busy";
}

// Get worker ID
$id = $worker->getId();

// Get statistics
$stats = $worker->getStats();
print_r($stats);
/*
[
    'tasks_completed' => 10,
    'tasks_failed' => 0,
    'total_execution_time' => 125.5
]
*/
```

### Managing Workers

```php
// Reset statistics
$worker->resetStats();

// Stop worker
$worker->stop();
```

## Process Pool

### Creating a Pool

```php
use Nexus\Process\ProcessPool;

// Create pool with 4 workers
$pool = new ProcessPool(4);

// Using helper
$pool = worker_pool(4);

// With timeout
$pool = new ProcessPool(4, 300); // 4 workers, 300s timeout
```

### Adding Tasks

```php
// Add tasks to pool
$pool->add(function () {
    return "Task 1";
});

$pool->add(function () {
    sleep(2);
    return "Task 2";
});

$pool->add(function ($name) {
    return "Hello {$name}";
}, 'John');

// Run all tasks
$results = $pool->run();
print_r($results);
```

### Map Function

```php
$items = [1, 2, 3, 4, 5];

$results = $pool->map($items, function ($item) {
    return $item * 2;
});

print_r($results); // [2, 4, 6, 8, 10]
```

### Pool Statistics

```php
// Get worker statistics
$stats = $pool->getStats();

foreach ($stats as $workerId => $workerStats) {
    echo "Worker {$workerId}:\n";
    print_r($workerStats);
}

// Get worker count
$count = $pool->getWorkerCount();

// Get available workers
$available = $pool->getAvailableWorkers();

// Get busy workers
$busy = $pool->getBusyWorkers();
```

### Stop Pool

```php
// Stop all workers
$pool->stop();
```

## Parallel Processing

### Parallel Execution

```php
use Nexus\Process\ProcessPool;

// Execute tasks in parallel
$results = ProcessPool::parallel([
    function () {
        sleep(2);
        return "Task 1";
    },
    function () {
        sleep(2);
        return "Task 2";
    },
    function () {
        sleep(2);
        return "Task 3";
    }
]);

// All 3 tasks run in parallel, completes in ~2s instead of ~6s
print_r($results);
```

### Using Helper

```php
// Execute tasks in parallel using helper
$results = parallel([
    fn() => file_get_contents('https://api1.example.com'),
    fn() => file_get_contents('https://api2.example.com'),
    fn() => file_get_contents('https://api3.example.com'),
]);
```

### Batch Processing

```php
$pool = new ProcessPool(8);

// Process 1000 items in batches
$items = range(1, 1000);
$batchSize = 100;
$batches = array_chunk($items, $batchSize);

foreach ($batches as $batch) {
    $pool->add(function ($items) {
        $results = [];
        foreach ($items as $item) {
            $results[] = processItem($item);
        }
        return $results;
    }, $batch);
}

$results = $pool->run();
```

## Examples

### Image Processing

```php
use Nexus\Process\ProcessPool;

class ImageProcessor
{
    protected ProcessPool $pool;

    public function __construct(int $workers = 4)
    {
        $this->pool = new ProcessPool($workers);
    }

    public function processImages(array $images): array
    {
        foreach ($images as $image) {
            $this->pool->add(function ($imagePath) {
                // Resize image
                $img = imagecreatefromjpeg($imagePath);
                $resized = imagescale($img, 800, 600);

                // Save
                $output = str_replace('.jpg', '_resized.jpg', $imagePath);
                imagejpeg($resized, $output);

                imagedestroy($img);
                imagedestroy($resized);

                return $output;
            }, $image);
        }

        return $this->pool->run();
    }
}

// Usage
$processor = new ImageProcessor(8);
$images = glob('/path/to/images/*.jpg');
$processed = $processor->processImages($images);
```

### Data Processing Pipeline

```php
use Nexus\Process\ProcessPool;

class DataPipeline
{
    protected ProcessPool $pool;

    public function __construct()
    {
        $this->pool = new ProcessPool(4);
    }

    public function process(array $data): array
    {
        // Stage 1: Extract
        $extracted = $this->pool->map($data, function ($item) {
            return $this->extract($item);
        });

        // Stage 2: Transform
        $transformed = $this->pool->map($extracted, function ($item) {
            return $this->transform($item);
        });

        // Stage 3: Load
        $loaded = $this->pool->map($transformed, function ($item) {
            return $this->load($item);
        });

        return $loaded;
    }

    protected function extract($item): array
    {
        // Extract logic
        return $item;
    }

    protected function transform($item): array
    {
        // Transform logic
        return $item;
    }

    protected function load($item): bool
    {
        // Load to database
        return true;
    }
}
```

### Web Scraper

```php
use Nexus\Process\ProcessPool;

class WebScraper
{
    protected ProcessPool $pool;

    public function __construct()
    {
        $this->pool = new ProcessPool(10);
    }

    public function scrapeUrls(array $urls): array
    {
        foreach ($urls as $url) {
            $this->pool->add(function ($url) {
                $html = file_get_contents($url);
                $dom = new DOMDocument();
                @$dom->loadHTML($html);

                // Extract data
                $data = [
                    'url' => $url,
                    'title' => $this->getTitle($dom),
                    'links' => $this->getLinks($dom),
                ];

                return $data;
            }, $url);
        }

        return $this->pool->run();
    }

    protected function getTitle(DOMDocument $dom): string
    {
        $titles = $dom->getElementsByTagName('title');
        return $titles->length > 0 ? $titles->item(0)->textContent : '';
    }

    protected function getLinks(DOMDocument $dom): array
    {
        $links = [];
        $anchors = $dom->getElementsByTagName('a');

        foreach ($anchors as $anchor) {
            $href = $anchor->getAttribute('href');
            if ($href) {
                $links[] = $href;
            }
        }

        return $links;
    }
}

// Usage
$scraper = new WebScraper();
$urls = [
    'https://example.com/page1',
    'https://example.com/page2',
    'https://example.com/page3',
];
$results = $scraper->scrapeUrls($urls);
```

### Report Generator

```php
use Nexus\Process\ProcessPool;

class ReportGenerator
{
    protected ProcessPool $pool;

    public function __construct()
    {
        $this->pool = new ProcessPool(4);
    }

    public function generateReports(array $userIds): array
    {
        foreach ($userIds as $userId) {
            $this->pool->add(function ($userId) {
                $user = User::find($userId);

                $pdf = new PDFGenerator();
                $pdf->setUser($user);
                $pdf->addSalesData($this->getSalesData($userId));
                $pdf->addCharts($this->generateCharts($userId));

                $filename = "report_{$userId}.pdf";
                $pdf->save(storage_path("reports/{$filename}"));

                return $filename;
            }, $userId);
        }

        return $this->pool->run();
    }

    protected function getSalesData(int $userId): array
    {
        // Fetch sales data
        return [];
    }

    protected function generateCharts(int $userId): array
    {
        // Generate charts
        return [];
    }
}
```

### Command Executor

```php
use Nexus\Process\Process;

class CommandExecutor
{
    public function execute(string $command, int $timeout = 60): array
    {
        $process = new Process($command);
        $process->setTimeout($timeout);

        $output = [];
        $errors = [];

        $exitCode = $process->run(function ($line, $type) use (&$output, &$errors) {
            if ($type === 'stdout') {
                $output[] = $line;
            } else {
                $errors[] = $line;
            }
        });

        return [
            'exit_code' => $exitCode,
            'output' => implode('', $output),
            'errors' => implode('', $errors),
            'success' => $process->isSuccessful(),
            'execution_time' => $process->getExecutionTime(),
        ];
    }

    public function executeMultiple(array $commands): array
    {
        $pool = new ProcessPool(count($commands));

        foreach ($commands as $command) {
            $pool->add(function ($cmd) {
                return $this->execute($cmd);
            }, $command);
        }

        return $pool->run();
    }
}

// Usage
$executor = new CommandExecutor();

// Single command
$result = $executor->execute('ls -la');
echo $result['output'];

// Multiple commands in parallel
$results = $executor->executeMultiple([
    'git status',
    'composer install',
    'npm install'
]);
```

### Background Task Manager

```php
use Nexus\Process\Worker;

class TaskManager
{
    protected array $workers = [];

    public function __construct(int $workerCount = 4)
    {
        for ($i = 0; $i < $workerCount; $i++) {
            $this->workers[] = new Worker("task-worker-{$i}");
        }
    }

    public function addTask(callable $task, ...$args): void
    {
        // Find available worker
        $worker = $this->getAvailableWorker();

        if ($worker) {
            $worker->executeAsync($task, ...$args);
        } else {
            throw new Exception("No available workers");
        }
    }

    protected function getAvailableWorker(): ?Worker
    {
        foreach ($this->workers as $worker) {
            if (!$worker->isBusy()) {
                return $worker;
            }
        }

        return null;
    }

    public function getStats(): array
    {
        $stats = [];

        foreach ($this->workers as $worker) {
            $stats[$worker->getId()] = [
                'busy' => $worker->isBusy(),
                'stats' => $worker->getStats()
            ];
        }

        return $stats;
    }
}
```

### Parallel API Calls

```php
use Nexus\Process\ProcessPool;

class ApiClient
{
    protected ProcessPool $pool;

    public function __construct()
    {
        $this->pool = new ProcessPool(10);
    }

    public function fetchMultiple(array $endpoints): array
    {
        foreach ($endpoints as $endpoint) {
            $this->pool->add(function ($url) {
                return file_get_contents($url);
            }, $endpoint);
        }

        return $this->pool->run();
    }

    public function aggregateData(array $sources): array
    {
        $results = $this->fetchMultiple($sources);

        $aggregated = [];
        foreach ($results as $result) {
            $data = json_decode($result, true);
            $aggregated = array_merge($aggregated, $data);
        }

        return $aggregated;
    }
}

// Usage
$api = new ApiClient();
$data = $api->aggregateData([
    'https://api1.example.com/data',
    'https://api2.example.com/data',
    'https://api3.example.com/data',
]);
```

## Best Practices

1. **Worker Count**: Set worker count based on CPU cores and workload
2. **Timeout Management**: Always set appropriate timeouts
3. **Error Handling**: Handle process failures gracefully
4. **Resource Cleanup**: Ensure processes are properly stopped
5. **Memory Management**: Monitor memory usage for long-running workers
6. **Task Size**: Keep tasks reasonably sized for better distribution
7. **Output Handling**: Capture and log process output
8. **Process Limits**: Set resource limits to prevent system overload
9. **Serialization**: Be careful with closures containing resources
10. **Monitoring**: Track worker statistics and health

## CLI Commands

### Run Workers

```bash
# Start worker pool
php nexus worker:run --workers=4 --timeout=300
```

## Troubleshooting

### Process Timeout

```php
// Increase timeout
$process->setTimeout(600); // 10 minutes
```

### Memory Issues

```php
// Monitor memory usage
$status = $process->getStatus();
echo "Memory: {$status['memory_usage']}\n";
```

### Zombie Processes

```php
// Ensure processes are stopped
$pool->stop();

// Or individual process
$process->stop(9); // Force kill
```

## Next Steps

- Learn about [Sockets and WebSockets](sockets.md)
- Explore [HTTP Client](http-client.md)
- Understand [Queue System](queues.md)
