# CommandLine

[![Build Status](https://travis-ci.com/jacekciach/commandline.svg?branch=master)](https://travis-ci.com/jacekciach/commandline)

A simple command-line options parser.

The main idea was to create a class that will not require any configuration.

A basic usage is to just create a new object and go.

## Description

The class parses all arguments passed in `$argv`. Parsed arguments are easily accessed with class' methods. These methods are documents in `CommandLine.php`.

A command-line argument can be either an `option` or a `param`: 

 - `options` have to be passed before `params`; when the parser encounters the first `param`, all arguments that come after will be treated as `params` as well
 - an `option` is an argument starting with a dash `-` (i.e. `-v`, `-4=yes`) or dashes `--` (i.e. `--enabled`, `--start=now`)
 - `params` start with a first argument not being an `option` 
 - the class generally won't filter `options`, make them required, optional, etc.
 - however, the class supports `$allowedOptions` and `$shortOptionsMap` (see the class' `__construct` documentation); they are optional and `CommandLine` works perfectly well without using them
 
 A developer needs to implement a logic connected with parsed arguments (their meaning, correctness, dependencies, etc.). The class will not take care of these things: its purpose is to be a convenient "reader" of `$argv`.
 
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
