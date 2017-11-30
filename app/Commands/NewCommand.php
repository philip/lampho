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
                        {--browser : Browser you want to open the project in}
                        {--dev  : Choose the dev branch instead of master}
                        {--editor= : Text editor to open the project in}
                        {--link : Create a Valet link to the project directory}
                        {--message= : Set the first commit message}
                        {--node : Set to execute yarn or npm install}
                        {--path= : Base path for the installation, otherwise CWD is used}';

    protected $projectname = '';

    protected $projectpath = '';

    protected $projecturl = '';

    protected $cwd = '';

    protected $basepath = '';

    protected $commitmessage = 'Initial commit.';

    protected $editors_terminal = array('vim', 'vi', 'nano', 'pico', 'ed', 'emacs', 'nvim');

    protected $editors_gui = array('pstorm', 'subl', 'atom', 'textmate', 'geany');

    protected $editor = '';

    protected $tools = array();

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
        $this->projecturl = 'http://'.$this->projectname.'.dev';

        $this->getAvailableTools();
        if (! $this->tools['laravel']) {
            $this->error("Unable to find laravel installer so I must exit. One day I might use composer here instead of exiting.");
            exit;
        }

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

        if ($this->replaceEnvVariables()) {
            $this->info("I replaced .env variables in your new Laravel application");
        }

        $this->doNodeOrYarn();

        $this->doAuth();

        $this->doValetLink();

        $this->doGit();

        $this->openEditor();

        $this->openBrowser();
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
    protected function openBrowser()
    {
        $this->info("Attempting to find a browser to open this in.");

        $browser = '';
        if ($this->option('browser')) {
            $browser = $this->option('browser');
        }

        // macOS (darwin)
        if (false !== stripos(PHP_OS, 'darwin')) {
            if ($browser === '') {
                $command = 'open "'.$this->projecturl.'"';
            } else {
                $command = 'open -a "'. $browser .'" "'. $this->projecturl . '"';
            }
        }

        // Windows @todo do we support Windows?
        if (windows_os()) {
            $command = '';
        }

        // Probably Linux @todo test me
        if (empty($command)) {
            $finder = new ExecutableFinder();
            if ($finder->find('xdg-open')) {
                $command = 'xdg-open "' . $this->projecturl . '"';
            }
        }

        if (isset($command)) {
            $this->info("Opening in your browser now by executing '$command'");
            $process = new Process($command);
            $process->setWorkingDirectory($this->cwd);
            $process->run();
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
            if ($this->tools['yarn']) {
                $command = 'yarn';
            } elseif ($this->tools['npm']) {
                $command = 'npm install';
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

        if (! $this->tools['git']) {
            $this->info("Unable to find 'git' on the system so I cannot initialize a git repo in '{$this->projectpath}'");
            return false;
        }

        $process = new Process("dummy command");
        $process->setWorkingDirectory($this->projectpath);

        $commands = array(
            'git init',
            'git add .',
            'git commit -m "'. str_replace('"', '\"', $this->commitmessage) . '"',
        );

        foreach ($commands as $command) {
            $process->setCommandLine($command);
            $process->run();
            $this->info($process->getOutput());
        }

        return true;
    }

    /**
     * Execute valet link $projectname if user passed in --link
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    protected function doValetLink()
    {
        if ($this->option('link')) {

            if (! $this->tools['valet']) {
                $this->warn("Cannot find valet on your system so a valet link was not created.");
                return false;
            }

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

    protected function replaceEnvVariables()
    {
        // @todo make this a configuration option
        $changes = array(
            'DB_DATABASE' => $this->projectname,
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
            'APP_URL' => $this->projecturl,
        );

        // @todo will .env always exist here? Check if not and copy over .env.example?
        // @todo research a more official way to replace .env values... I could not find one
        $contents = file_get_contents($this->projectpath.DIRECTORY_SEPARATOR.'.env');

        $newcontents = $contents;
        foreach ($changes as $name => $value) {
            preg_match("@$name=(.*)@", $contents, $matches);
            if (isset($matches[1])) {
                // @todo sanitize new value and research .env guidelines
                $newcontents = str_replace("$name=$matches[1]", "$name=$value", $newcontents);
            }
        }

        file_put_contents($this->projectpath.DIRECTORY_SEPARATOR.'.env', $newcontents);

        return ($newcontents !== $contents) ? true : false;
    }

    protected function getAvailableTools() {

        $finder = new ExecutableFinder();

        $checks = array('yarn', 'npm', 'git', 'valet', 'laravel', 'composer');
        foreach ($checks as $check) {
            $this->tools[$check] = $finder->find($check);
        }
    }
}
