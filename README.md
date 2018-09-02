# CommandLine

A simple command line parser.

The main idea was to create a class that will not require any configuration.

A basic usage is to just create a new object and go.

The terms used by the class:
 - an `option` is an argument starting with dashes (`--`), i.e. `--start=now`
 - a `param` is an argument not being an `option`

Because the class requires no configuration:
 - it accepts only long `options`
 - it won't filter `options`, make them required, optional, etc.
 - it requires `options` to be passed before `params`; if the class encounters the first `param`, all arguments that come after this first `param` will be treated as `params` even if they start with dashes
 
Please, do not treat these rules as drawbacks: with no configuration come some hard assumptions ;)

## Installation

### Requirements

 - PHP 7
 
### with Composer

```
composer require jacekciach/commandline
``` 

### without Composer

Just download `CommandLine.php` from the repository; this is the only required file.

Then include this file in your code with `include` or `require`.
 
## Examples

### Example 1.

```php
<?php
require_once __DIR__. '/vendor/autoload.php';

use CommandLine\CommandLine;

$cmd = new CommandLine();
var_export(array(
  "binary"  => $cmd->binary(),  // returns PHP_BINARY
  "script"  => $cmd->script(),  // returns the name of the script
  "options" => $cmd->options(), // returns all "options" passed from command line
  "params"  => $cmd->params(),  // returns all "params" passed from command line
));
echo PHP_EOL;
```

So a command:
```
$ php example1.php --start --msg="Hello, World!" file1.txt --book
```
will return something like:
```
array (
  'binary' => '/usr/bin/php7.0',
  'script' => 'example1.php',
  'options' => 
  array (
    'start' => true,
    'msg' => 'Hello, World!',
  ),
  'params' => 
  array (
    0 => 'file1.txt',
    1 => '--book'
  ),
)
```

### Example 2.

```
$ php example2.php --start --msg="Hellow World!" "Test Application" user
```

```php
<?php

require_once __DIR__. '/vendor/autoload.php';

use CommandLine\CommandLine;

$cmd = new CommandLine();
echo 'start = ' . var_export($cmd->option('start'), true) . PHP_EOL;
echo 'msg   = ' . var_export($cmd->option('msg'), true) . PHP_EOL;
echo 'stop  = ' . var_export($cmd->option('stop'), true) . PHP_EOL; // reading a not existing options will return NULL 
echo PHP_EOL;
echo 'param(0) = ' . var_export($cmd->param(0), true) . PHP_EOL;
echo 'param(1) = ' . var_export($cmd->param(1), true) . PHP_EOL;
echo 'param(2) = ' . var_export($cmd->param(2), true) . PHP_EOL; // reading a not existing param will return NULL

```
will output:
```
start = true
msg   = 'Hellow World!'
stop  = NULL

param(0) = 'Application name'
param(1) = 'user'
param(2
```
