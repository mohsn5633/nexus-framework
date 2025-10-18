<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class StorageLinkCommand extends Command
{
    protected string $signature = 'storage:link';
    protected string $description = 'Create the symbolic links configured for the application';

    public function handle(): int
    {
        $links = config('filesystems.links', []);

        if (empty($links)) {
            $this->info('No links configured in config/filesystems.php');
            return 0;
        }

        foreach ($links as $link => $target) {
            if (file_exists($link)) {
                $this->error("The [{$link}] link already exists.");
                continue;
            }

            if (!file_exists($target)) {
                $this->error("The target [{$target}] does not exist.");
                continue;
            }

            if (PHP_OS_FAMILY === 'Windows') {
                // Windows: Use mklink command
                $linkType = is_dir($target) ? '/D' : '';
                $command = sprintf('mklink %s "%s" "%s"', $linkType, $link, $target);
                exec($command, $output, $resultCode);

                if ($resultCode === 0) {
                    $this->success("The [{$link}] link has been connected to [{$target}].");
                } else {
                    $this->error("Failed to create link [{$link}]. You may need to run this command as Administrator.");
                }
            } else {
                // Unix/Linux/Mac: Use symlink
                if (symlink($target, $link)) {
                    $this->success("The [{$link}] link has been connected to [{$target}].");
                } else {
                    $this->error("Failed to create link [{$link}].");
                }
            }
        }

        $this->line('');
        $this->info('Links have been created.');

        return 0;
    }
}
