<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Filesystem;

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
                        {--editor= : Text editor to open the project in}
                        {--link : Create a Valet link to the project directory}
                        {--message= : Set the first commit message}
                        {--node : Set to execute yarn or npm install}
                        {--path= : Base path for the installation, otherwise CWD is used}';

    protected $projectname = '';

    protected $projectpath = '';

    protected $cwd = '';

    protected $basepath = '';

    protected $commitmessage = 'Initial commit.';

    protected $editors_terminal = array('vim', 'vi', 'nano', 'pico', 'ed', 'emacs', 'nvim');

    protected $editors_gui = array('pstorm', 'subl', 'atom', 'textmate', 'geany');

    protected $editor = '';

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
        $this->basepath = $this->cwd;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $this->projectname = $this->argument('name');

        $this->setBasePath();

        $this->projectpath = $this->basepath.DIRECTORY_SEPARATOR.$this->projectname;

        if (is_dir($this->projectpath)) {
            if (! $this->askToAndRemoveProject()) {
                $this->error("Goodbye!");
                exit;
            }
        }

        if ($this->option('dev')) {
            $this->warn("The laravel installation will use the latest developmental branch by passing in --dev");
            $branch = " --dev";
        } else {
            $branch = '';
        }

        $command = "laravel new {$this->projectname}$branch";

        $this->info("Creating a new project named {$this->projectname}");
        $this->info("Executing command '$command' in directory {$this->basepath}");

        $process = new Process($command);
        $process->setWorkingDirectory($this->basepath);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // @todo Determine why the above outputs before this point; so the following getOutput() call does nothing
        $this->info($process->getOutput());

        $this->doNodeOrYarn();

        $this->doAuth();

        $this->doValetLink();

        $this->doGit();

        $this->openEditor();

    }

    /**
     * Check if directory already exists
     * If exists: prompt to remove, return true if user says yes, else return false
     * If not exists: return true
     *
     * @todo Use --force here instead of rm?
     * @return bool true if able to continue, else false if directory exists and won't be deleted
     */
    protected function askToAndRemoveProject()
    {
        $this->info("The directory '{$this->projectpath}' already exists.");

        if ($this->confirm("Shall I proceed by removing the following directory? {$this->projectpath}")) {

            $fs = new Filesystem\Filesystem();

            if ($fs->deleteDirectory($this->projectpath)) {
                $this->info("I removed the following directory: {$this->projectpath}");
                return true;
            } else {
                $this->error("I was unable to remove the '{$this->projectpath}' directory so I must exit.");
                return false;
            }

        } else {
            $this->error("You have chosen to not remove the '{$this->projectpath}' directory so I must exit.");
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
     * Open project in text editor
     */
    protected function openEditor()
    {
        if ($this->option('editor')) {
            $editor = $this->option('editor');
        } else {
            $finder = new ExecutableFinder();
            foreach ($this->editors_gui as $_editor) {
                if ($finder->find($_editor)) {
                    $editor = $_editor;
                    break;
                }
            }
            if (empty($editor)) {
                foreach ($this->editors_terminal as $_editor) {
                    if ($finder->find($_editor)) {
                        $editor = $_editor;
                        break;
                    }
                }
            }
        }

        if (empty($editor)) {
            $this->warn("Unable to find a text editor to open, skipping this step.");
            return false;
        }

        $this->info("Found editor $editor so an opening it now");

        $process = new Process("$editor .");
        $process->setWorkingDirectory($this->projectpath);

        $process->setTty(true);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return true;
    }

    /**
     * Set base filepath; defaults to CWD, or uses --path if set
     */
    protected function setBasePath()
    {
        $this->basepath = $this->cwd;
        if ($this->option('path')) {
            $path = $this->option('path');
            if (is_dir($path)) {
                $this->basepath = $path;
            } else {
                $this->warn("Your defined '--path $path' is not a directory, so I am skipping it and using '{$this->basepath}' instead.");

            }
        }
        return $this->basepath;
    }

    /**
     * Set browser to open valet project in
     */
    protected function setBrowser()
    {
        if ($this->option('browser')) {

        }
    }

    /**
     * Execute node or yarn
     */
    protected function doNodeOrYarn()
    {
        $finder = new ExecutableFinder();

        if ($this->option('node')) {

            $command = '';
            if ($finder->find('yarn')) {
                $command = 'yarn';
            } else {
                if ($finder->find('npm')) {
                    $command = 'npm install';
                }
            }

            if (empty($command)) {
                $this->error("Either yarn or npm are required");
                return false;
            }

            $this->info("Executing $command now; in {$this->projectpath}");

            $process = new Process($command);
            $process->setWorkingDirectory($this->projectpath);
            #$process->run();

            $process->start();
            $iterator = $process->getIterator($process::ITER_SKIP_ERR | $process::ITER_KEEP_OUTPUT);
            foreach ($iterator as $data) {
                echo $data."\n";
            }

            #$this->info($process->getOutput());
        }
    }

    protected function doGit() {
        if ($this->option('message')) {
            $this->commitmessage = $this->option('message');
        }
        $commands = array(
            'git init',
            'git add .',
            'git commit -m "'. $this->commitmessage . '"',
        );
        return $commands;
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
            $this->info("Linking valet by executing '$command' in {$this->basepath}");

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
