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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {

        // Project Name
        $name = $this->argument('name');

        // Dev branch or master
        $branch = "";
        if ($this->option('dev')) {
            $this->info("Installation will use the dev branch instead of master" . PHP_EOL );
            $branch = "--dev";
        }

        $this->info("Creating a new project named $name" . PHP_EOL );
        $this->info("Executing: laravel new $name $branch");

        $process = new Process("laravel new $name $branch");

        // @todo Check this without Process; learn how to do in native Laravel
        if (is_dir($process->getWorkingDirectory() . DIRECTORY_SEPARATOR . $name)) {
            if ($this->confirm("The directory $name already exists, would you like me to completely remove it and proceed?")) {
                $this->info("Removing directory $name/");
                // @todo Dangerous? Add some checks here, perhaps a confirmation e.g., delete $filepath?
                $rm = new Process("rm -rf ". $process->getWorkingDirectory() . DIRECTORY_SEPARATOR . $name);
                $rm->run();
                if (!$rm->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }
                echo $rm->getOutput();
            } else {
                $this->info("Directory $name already exists, exiting...");
                exit;
            }
        }

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // @todo Determine why the above outputs before this point; so the following getOutput() call does nothing
        echo $process->getOutput();

        if ($this->option('auth')) {
            $this->info("Executing make:auth" . PHP_EOL );

            $process = new Process("php artisan make:auth");
            $process->setWorkingDirectory($process->getWorkingDirectory() . '/' . $name);

            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            echo $process->getOutput();
        }

        if ($this->option('link')) {
            $this->info("Linking Valet here" . PHP_EOL );

            $process = new Process("valet link $name");
            $process->setWorkingDirectory($process->getWorkingDirectory() . '/' . $name);

            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            echo $process->getOutput();
        }

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
