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

    protected $branch = 'master';
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
     *
     * @return void
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
        $this->projectpath = $this->cwd . DIRECTORY_SEPARATOR . $this->projectname;

        if ($this->projectExists()) {
            if (!$this->askToAndRemoveProject()) {
                $this->info("Sorry, the project at {$this->projectpath} already exists and you have chosen to not remove it. Exiting.");
                exit;
            }
        }

        $this->setDesiredBranch();

        $_branch = "";
        if ($this->branch === 'dev') {
            $this->info("The laravel installation will use the latest developmental branch by passing in --dev");
            $_branch = " --dev";
        }

        $command = "laravel new {$this->projectname}$_branch";

        $this->info("Creating a new project named {$this->projectname}");
        $this->info("Executing command '$command' in directory {$this->cwd}");

        $process = new Process($command);
        $process->setWorkingDirectory($this->cwd);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // @todo Determine why the above outputs before this point; so the following getOutput() call does nothing
        echo $process->getOutput();

        $this->doAuth();

        $this->doValetLink();
    }

    /**
     * Set branch to dev if --dev is passed in, else keep default
     */
    protected function setDesiredBranch() {
        if ($this->option('dev')) {
            $this->branch = 'dev';
        }
    }

    /**
     * Check if project's directory already exists
     * @return bool true if exists, else false
     */
    protected function projectExists() {

        if (is_dir($this->projectpath)) {
            return true;
        }
        return false;
    }

    /**
     * Check if directory already exists
     * If exists: prompt to remove, return true if user says yes, else return false
     * If not exists: return true
     * @todo Use --force here instead of rm?
     * @return bool true if able to continue, else false if directory exists and won't be deleted
     */
    protected function askToAndRemoveProject() {

        $this->info("The directory '{$this->projectpath}' already exists.");

        $command = "rm -rf {$this->projectpath}";

        if ($this->confirm("Shall I proceed by executing the following command? $command")) {
            $this->info("Removing directory {$this->projectpath}");
            // @todo Dangerous? Add some checks here, perhaps a confirmation e.g., delete $filepath?
            // @todo Check if it was removed or if there were errors e.g., permission errors
            $rm = new Process($command);
            $rm->run();
            if (!$rm->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Execute make:auth if user passed in --auth
     * @return string Output from make:auth command
     */
    protected function doAuth() {

        $command = "php artisan make:auth";
        if ($this->option('auth')) {
            $this->info("Executing $command");

            $process = new Process($command);
            $process->setWorkingDirectory($this->projectpath);

            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return $process->getOutput();
        }
        return false;
    }

    /**
     * Execute valet link $projectname if user passed in --link
     * @return string Output from the valet link command else false if not executed
     */
    protected function doValetLink() {

        if ($this->option('link')) {
            $command = "valet link {$this->projectname}";
            $this->info("Linking valet by executing '$command' in {$this->cwd}");

            $process = new Process($command);
            $process->setWorkingDirectory($this->projectpath);

            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            echo $process->getOutput();
        }
        return false;
    }

    /**
	 * Define the command's schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule $schedule
	 *
	 * @return void
	 */
	public function schedule(Schedule $schedule): void
	{
		// $schedule->command(static::class)->everyMinute();
	}
}
