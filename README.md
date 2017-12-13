## Lambo port using Laravel Zero

This started as a test project to help me learn Laravel. All advice is welcome. Ideally it'll be refactored several times. 
The initial version breaks every known best practice! :)

## Things that inspired this project

- [Lambo](https://github.com/tightenco/lambo): A useful shell script that generates new Laravel projects
- [Laravel](https://laravel.com/): The PHP framework
- [Laravel Zero](http://laravel-zero.com/): Base for this project; Laravel for CLI
- Me: reprogramming my brain from PHP 4 to PHP 7 (I'm an old-timer that still uses `array()` and `OOP` is hard)

## Status

- It works well and includes most lambo commands.
- However, *all* logic is thrown into `app\Commands\NewCommand.php` so that's not good
- The name `lampho` is strange but it's probably temporary and my brain thinks replacing `b` (bash) with `ph` (PHP) makes sense; that and `lamb pho` has logo potential
- Expect progress in the future

## How you can help

- Refactor! Especially [app\Commands\NewCommand.php](app/Commands/NewCommand.php) (please don't laugh)
- Suggest or implement features
- Write tests

## Installation
#### Global

    $ composer global require philip/lampho
    $ lampho help

#### Source

    $ git clone git@github.com:philip/lampho.git
    $ cd lampho
    $ composer update
    $ ./lampho help

## Documentation

```
 ___  ___   ___  _ __  
/ __|/ _ \ / _ \| '_ \ 
\__ \ (_) | (_) | | | |
|___/\___/ \___/|_| |_|
```

## Examples

##### Creates a new Laravel project named `foo`

    $ lampho new foo

##### Same, but with several options

    $ lampho new foo --auth --link --node --createdb=sqlite 
    
##### Configuration options

Soon we'll be able to define configuration options and common settings as typing all of the above can become tedious -- all suggestions welcome. Related, today only `.env` is modified.


##### Options according to `lampho help new`
```
      --auth                 Run make:auth
      --browser              Browser you want to open the project in
      --createdb[=CREATEDB]  Create a database; pass in sqlite or mysql
      --dev                  Choose the dev branch instead of master
      --editor[=EDITOR]      Text editor to open the project in
      --link                 Create a Valet link to the project directory
      --message[=MESSAGE]    Set the first commit message
      --node                 Set to execute yarn or npm install
      --path[=PATH]          Base path for the installation, otherwise CWD is used
```

