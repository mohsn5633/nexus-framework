<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;
use Nexus\View\View;

class ViewClearCommand extends Command
{
    protected string $signature = 'view:clear';
    protected string $description = 'Clear all compiled view files';

    public function handle(): int
    {
        View::clearCache();

        $this->success('Compiled views cleared successfully.');

        return 0;
    }
}
