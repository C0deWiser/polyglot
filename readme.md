# Laravel Translator

It scans app source code and collects translatable strings.

## Installation

    composer require codewiser/polyglot

    php artisan vendor:publish --tag=polyglot

Install [Gettext](https://www.gnu.org/software/gettext/).

## Configuration

Polyglot supports few modes:

* `inherit`
  
    Polyglot do nothing.
  

* `collector`

    Polyglot provides console command to collect strings from app source codes.
    
    The mode requires additional settings.

  * `includes` = [`app`, `resources/views`]
    
    Array of files and folders with source codes.
  
  * `excludes` 
  
    Array of files and folders that should be excluded from scanning.
  
  * `storage` = `resources/lang`
    
    Directory to store collected strings.

* `translator`

  Polyglot enables Getext functionality.

  * `legacy`
     
    Provide an array with strings, that should be translated by Laravel Translator.

## Collector mode

    php artisan polyglot:collect
  
The command will scan source codes and update lang files with new values.

## Translator mode

    php artisan polyglot:collect

Collect command will store collected strings into `po` files.

    php artisan polyglot:compile

Compile command will compile `po` into `mo`.