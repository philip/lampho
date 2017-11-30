## Lambo port using Laravel Zero

Honestly, this is a test project written to help me learn Laravel. 
All advice is welcome. Ideally it'll be refactored several times. 
The initial version no doubt breaks every known best practice! :)

## Things that inspired this project

- [Lambo](https://github.com/tightenco/lambo): A useful shell script that generates Laravel projects
- [Laravel](https://laravel.com/): Something I'm starting to learn
- [Laravel Zero](http://laravel-zero.com/): Base of this project, and I like CLI
- Me: reprogramming my brain from PHP 4 to PHP 7 (I'm an old-timer that still uses `array()`)

## Status

- It works and contains most lambo commands. However, *all* logic is thrown into `app\Commands\NewCommand.php` so that's not ideal
- The name `lampho` is strange but it's probably temporary and my brain thinks replacing `b` (bash) with `ph` (PHP) makes sense; that and maybe lamb pho exists?
- It'll do more tomorrow than it does today

## Installation
#### Source

    $ git clone git@github.com:philip/lampho.git
    $ cd lampho
    $ composer update
    $ ./lampho

## Documentation

```
 ___  ___   ___  _ __  
/ __|/ _ \ / _ \| '_ \ 
\__ \ (_) | (_) | | | |
|___/\___/ \___/|_| |_|
```

## Examples

##### Creates a new Laravel project named foo

    $ lampho new foo

##### Same, but with a few options

    $ lampho new foo --auth --link --node
    
##### Configuration options

Soon we'll be able to define configuration options and common settings as typing all of the above can become tedious -- all suggestions welcome.


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

