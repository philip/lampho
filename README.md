## Lambo port using Laravel Zero

Honestly, this is a test project written to help me learn Laravel. 
All advice is welcome. Ideally it'll be refactored several times. 
The initial version no doubt breaks every known best practice. 

## Things that inspired this project

- Laravel: Something I'm starting to learn
- Lambo: A useful shell script that generates Laravel projects
- Laravel Zero: Base of this project, and I like CLI
- Me: reprogramming my brain from PHP 4 to PHP 7

## Status

- It works. However, *all* logic was thrown into a single `handle()` method so that's not good
- Also most files are from a new `Laravel Zero` installation
- Yes the name `lampho` is terrible but it's temporary and my brain thinks replacing `b` (bash) with `ph` (PHP) makes some sense
- It doesn't do much today

## Documentation

None yet.

## Examples

##### Creates a new Laravel project named foo

    $ lampho new foo

##### Same, but with all available options

    $ lampho new foo --dev --auth --link

##### Options

- `--dev` : installs Laravel from the dev branch
- `--auth`: executes `artisan make:auth`
- `--link`: executes `valet link` in the new project's directory

