<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

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

        Artisan::call('optimize:clear');
        Artisan::call('optimize');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        Artisan::call('migrate');
    }
}
