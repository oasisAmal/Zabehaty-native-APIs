<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PullCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull the latest changes from the remote repository using git';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        shell_exec("git status 2>&1");
        $pull = shell_exec("git pull 2>&1");
        $this->info($pull);
    }
}
