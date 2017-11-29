<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Storage;

class MakeConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a new lampho configuration file';

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
        $this->info("You have chosen to make a configuration file");

        // @todo Determine if we should Storage:: a new text file, or use something like config/foo.php instead?
        // @todo Lambo uses ~/.lambo/config ... use this convention?
        Storage::put('config', "you=awesome");
    }
}
