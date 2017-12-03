<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class EditConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edit-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit an existing lampho configuration file';

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
        $this->info('You have chosen to edit the existing lampho configuration file.');
    }
}
