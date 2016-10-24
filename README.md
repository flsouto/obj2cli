# obj2cli

## Overview

This library allows you to create an interactive shell application from a plain php object. Commands are routed to instance methods, and command arguments are passed over as method arguments.

## Installation

You can simply download obj2cli.php file to your project's folder or install it via composer:

```
composer require flsouto/obj2cli
``` 

*Notice*: in both cases you will have to include the file manually, since it will not be autoloaded!

## Usage

Let's say we want to create an app that has two available commands:

* say_hello - prints "hello" to stdout
* say - which takes a parameter and prints it

This is how you could implement it using obj2cli:

```
<?php // my_app.php

require_once('obj2cli.php');

class MyApp{
	
	function say_hello(){
		echo "Hello!";
	}

	function say($what){
		echo $what;
	}

}

obj2cli(new MyApp());

```

That's all! Save it as my_app.php and run it through the command line:

```
$ php my_app.php
```

A new session will start with the name of your app (which by default is the name of your object's class):

```
MyApp>
```

Now let's play around with it and see if it works as expected:

```
MyApp> say_hello
hello
MyApp> say Cool!
Cool!
```

*Notice:* command arguments are separated by space.

## Optional parameters

If you provide a default value to a parameter it will work as expected:

```
<?php 
require_once('obj2cli.php');

class MyApp{
	
	function generate_file_name($name, $ext='txt'){
		echo $name.'.'.$ext;
	}

}

obj2cli(new MyApp());

```

```
$ php my_app.php
MyApp> generate_file_name cache	
cache.txt
MyApp> generate_file_name cache json
cache.json
MyApp> ^C
```

## Specia Commands

### help

The help command, if not defined in your class, will list all methods/commands available:

```
MyApp> help
- say_hello (no parameters)
- say <what>
- generate_file_name <name> [ext = txt]
```

### command --help

Shows usage of one specific command:

```
MyApp> generate_file_name --help
generate_file_name <name> [ext = txt]
```

### exit

Exists the app. 

*Notice*: this is not the same as hitting CTRL+C. The exit command returns to the parent context (see below) while CTRL+C exists the entire app.

## Creating child contexts 

If a command returns another object, control will be passed to that object. See an example:

```
<?php
require_once('obj2cli.php');

class MyApp{
	
	function multiply($number){
		return new Multiply($number);
	}

}

class Multiply{

	var $number;

	function __construct($number){
		$this->number = $number;
	}

	function by($number2){
		echo $this->number * $number2;
	}

}

obj2cli(new MyApp());
```

```
$ php my_app.php 
MyApp> multiply 3

Multiply> by 5
15
Multiply> by 6
18
Multiply> by 7
21
Multiply> exit
MyApp> 
```

Notice how the "exit" command finished the "Multiply" context and brought us back to the "MyApp" parent context. CTRL+C would have closed both contexts.

## Naming contexts

While the example above worked, it would be nice to customize the name of the "Multiply" context so that it read "Multiply 3...>". By default, the object's class name is used as name for the context, but this can be changed by implementing a method called *getObj2cliName*:

```
// ...
class Multiply{

	var $number;

	function __construct($number){
		$this->number = $number;
	}

	// Customize name of context
	function getObj2cliName(){
		return "Multiply $this->number....";
	}

	function by($number2){
		echo $this->number * $number2;
	}

}
//...
```

## Running any class from the command line

This repository comes with a script called "run.php" which allows you to instantiate any class into an interactive shell program:

```
$ php run.php /path/to/file.php MyClass arg1 arg2
MyClass> 
```

If the class you want to work with is autoloaded by composer's autoloader, you should provide the path to vendor/autoload.php:

```
$ php run.php /path/to/vendor/autoload.php SomeClass arg1 arg2
SomeClass>
```

# Final thoughts

This is a useful tool for building interactive shell programs very quickly and also can be used to test/debug classes on the fly.





