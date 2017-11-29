<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class MakeAfterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make-after';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a PHP or Bash script to execute after the new Laravel application is created';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $this->info("I will open a file at location path/to/location and insert boilerplate markup for you to edit.");
    }
}
