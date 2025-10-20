<?php

namespace Nexus\Process;

use Exception;
use Closure;

/**
 * Process
 *
 * Manages individual process execution
 */
class Process
{
    protected mixed $process = null;
    protected array $pipes = [];
    protected ?string $command = null;
    protected ?string $cwd = null;
    protected array $env = [];
    protected ?int $timeout = null;
    protected bool $running = false;
    protected int $exitCode = 0;
    protected string $output = '';
    protected string $errorOutput = '';
    protected float $startTime = 0;

    /**
     * Create a new process
     */
    public function __construct(?string $command = null, ?string $cwd = null, array $env = [])
    {
        $this->command = $command;
        $this->cwd = $cwd ?? getcwd();
        $this->env = $env ?: $_ENV;
    }

    /**
     * Set the command to execute
     */
    public function setCommand(string $command): self
    {
        $this->command = $command;
        return $this;
    }

    /**
     * Set working directory
     */
    public function setWorkingDirectory(string $cwd): self
    {
        $this->cwd = $cwd;
        return $this;
    }

    /**
     * Set environment variables
     */
    public function setEnv(array $env): self
    {
        $this->env = $env;
        return $this;
    }

    /**
     * Set execution timeout
     */
    public function setTimeout(?int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Start the process
     */
    public function start(?Closure $callback = null): self
    {
        if ($this->running) {
            throw new Exception("Process is already running");
        }

        if (!$this->command) {
            throw new Exception("No command specified");
        }

        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $this->process = proc_open(
            $this->command,
            $descriptors,
            $this->pipes,
            $this->cwd,
            $this->env
        );

        if (!is_resource($this->process)) {
            throw new Exception("Failed to start process");
        }

        // Set streams to non-blocking
        stream_set_blocking($this->pipes[1], false);
        stream_set_blocking($this->pipes[2], false);

        $this->running = true;
        $this->startTime = microtime(true);

        if ($callback) {
            $this->wait($callback);
        }

        return $this;
    }

    /**
     * Run the process and wait for completion
     */
    public function run(?Closure $callback = null): int
    {
        $this->start();
        return $this->wait($callback);
    }

    /**
     * Wait for the process to complete
     */
    public function wait(?Closure $callback = null): int
    {
        if (!$this->running) {
            throw new Exception("Process is not running");
        }

        $output = '';
        $errorOutput = '';

        while ($this->running) {
            // Check timeout
            if ($this->timeout && (microtime(true) - $this->startTime) > $this->timeout) {
                $this->stop();
                throw new Exception("Process timed out after {$this->timeout} seconds");
            }

            // Read output
            $stdout = fread($this->pipes[1], 8192);
            $stderr = fread($this->pipes[2], 8192);

            if ($stdout !== false && $stdout !== '') {
                $output .= $stdout;
                if ($callback) {
                    $callback($stdout, 'stdout');
                }
            }

            if ($stderr !== false && $stderr !== '') {
                $errorOutput .= $stderr;
                if ($callback) {
                    $callback($stderr, 'stderr');
                }
            }

            // Check if process has finished
            $status = proc_get_status($this->process);
            if (!$status['running']) {
                $this->running = false;
                $this->exitCode = $status['exitcode'];
                break;
            }

            usleep(10000); // 10ms
        }

        // Read any remaining output
        $output .= stream_get_contents($this->pipes[1]);
        $errorOutput .= stream_get_contents($this->pipes[2]);

        $this->output = $output;
        $this->errorOutput = $errorOutput;

        // Close pipes
        fclose($this->pipes[0]);
        fclose($this->pipes[1]);
        fclose($this->pipes[2]);

        // Close process
        proc_close($this->process);

        return $this->exitCode;
    }

    /**
     * Stop/kill the process
     */
    public function stop(int $signal = 15): bool
    {
        if (!$this->running) {
            return false;
        }

        $status = proc_get_status($this->process);

        if ($status['running']) {
            if (PHP_OS_FAMILY === 'Windows') {
                exec("taskkill /F /T /PID {$status['pid']}");
            } else {
                proc_terminate($this->process, $signal);
            }
        }

        $this->running = false;
        return true;
    }

    /**
     * Check if process is running
     */
    public function isRunning(): bool
    {
        if (!$this->running || !is_resource($this->process)) {
            return false;
        }

        $status = proc_get_status($this->process);
        return $status['running'];
    }

    /**
     * Get process output
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * Get error output
     */
    public function getErrorOutput(): string
    {
        return $this->errorOutput;
    }

    /**
     * Get exit code
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Get process status
     */
    public function getStatus(): array|false
    {
        if (!is_resource($this->process)) {
            return false;
        }

        return proc_get_status($this->process);
    }

    /**
     * Write to process stdin
     */
    public function write(string $input): int|false
    {
        if (!$this->running || !isset($this->pipes[0])) {
            return false;
        }

        return fwrite($this->pipes[0], $input);
    }

    /**
     * Get execution time
     */
    public function getExecutionTime(): float
    {
        if ($this->startTime === 0) {
            return 0;
        }

        return microtime(true) - $this->startTime;
    }

    /**
     * Check if process succeeded
     */
    public function isSuccessful(): bool
    {
        return $this->exitCode === 0;
    }

    /**
     * Create a process from a callable
     */
    public static function fromCallable(callable $callable, ...$args): self
    {
        $serialized = base64_encode(serialize([
            'callable' => $callable,
            'args' => $args
        ]));

        $command = sprintf(
            '%s -r "eval(base64_decode(\'%s\'));"',
            PHP_BINARY,
            $serialized
        );

        return new self($command);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->running) {
            $this->stop();
        }
    }
}
