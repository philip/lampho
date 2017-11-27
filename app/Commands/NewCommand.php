<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class NewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'new
                        {name : Name of the Laravel project}
                        {--auth : Run make:auth}
                        {--dev  : Choose the dev branch instead of master}
                        {--link : Create a Valet link to the project directory}';

    protected $projectname = '';

    protected $projectpath = '';

    protected $cwd = '';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new Laravel application';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->cwd = getcwd();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $this->projectname = $this->argument('name');
        $this->projectpath = $this->cwd.DIRECTORY_SEPARATOR.$this->projectname;

        if (is_dir($this->projectpath)) {
            if (! $this->askToAndRemoveProject()) {
                $this->error(
                    "Sorry, the project at {$this->projectpath} already exists and you have chosen to not remove it. Exiting."
                );
                exit;
            }
        }

        if ($this->option('dev')) {
            $this->warn("The laravel installation will use the latest developmental branch by passing in --dev");
            $branch = " --dev";
        } else {
            $branch = 'master';
        }

        $command = "laravel new {$this->projectname}$branch";

        $this->info("Creating a new project named {$this->projectname}");
        $this->info("Executing command '$command' in directory {$this->cwd}");

        $process = new Process($command);
        $process->setWorkingDirectory($this->cwd);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // @todo Determine why the above outputs before this point; so the following getOutput() call does nothing
        $this->info($process->getOutput());

        $this->doAuth();

        $this->doValetLink();
    }

    /**
     * Check if directory already exists
     * If exists: prompt to remove, return true if user says yes, else return false
     * If not exists: return true
     *
     * @todo Use --force here instead of rm?
     * @return bool true if able to continue, else false if directory exists and won't be deleted
     */
    protected function askToAndRemoveProject(): boolean
    {
        $this->warn("The directory '{$this->projectpath}' already exists.");

        $command = "rm -rf {$this->projectpath}";

        if ($this->confirm("Shall I proceed by executing the following command? $command")) {
            $this->info("Removing directory {$this->projectpath}");
            // @todo Dangerous? Add some checks here, perhaps a confirmation e.g., delete $filepath?
            // @todo Check if it was removed or if there were errors e.g., permission errors
            $rm = new Process($command);
            $rm->run();
            if (! $rm->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Execute make:auth if user passed in --auth
     *
     * @return $this
     */
    protected function doAuth()
    {
        if ($this->option('auth')) {
            $command = "php artisan make:auth";

            $this->info("Executing $command");

            $process = new Process($command);
            $process->setWorkingDirectory($this->projectpath);

            $process->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $this->info($process->getOutput());
        }
    }

    /**
     * Execute valet link $projectname if user passed in --link
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    protected function doValetLink(): void
    {
        if ($this->option('link')) {
            $command = "valet link {$this->projectname}";
            $this->info("Linking valet by executing '$command' in {$this->cwd}");

            $process = new Process($command);
            $process->setWorkingDirectory($this->projectpath);

            $process->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $this->info($process->getOutput());
        }
    }
}
