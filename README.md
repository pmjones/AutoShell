# AutoShell

AutoShell automatically maps command names to PHP Command classes in a specified
namespace, reflecting on a specified main method within that class to determine
the argument and option values. Those parameters may be typical scalar values
(int, float, string, bool), or arrays,

Install AutoShell using Composer:

```
composer require pmjones/auto-shell ^2.0
```

AutoShell is low-maintenance. Merely adding a class to your source code, in the
recognized namespace and with the recognized main method name, automatically
makes it available as a Command.

Think of AutoShell as the "router" for your command classes:

-  Under MVC or ADR, you would have a Front Controller pass the URL to a Router,
   and get back a Route DTO describing which Controller/Action to invoke
   (with the arguments thereto). The Front Controller would then invoke the
   Controller/Action with those arguments.

-  Here, you would have a Console class pass `$_SERVER['argv']` to a Shell, and
   get back an Exec DTO describing which Command class to invoke (with the
   options and arguments thereto). The Console would then invoke the Command
   with those options and argumetns.

That is:

    Front Controller    => Console
    Router              => Shell
    Controller/Action   => Command

## Getting Started

### Basic Console

After installing AutoShell, set up a _Console_ with the namespace for your
command classes and the corresponding directory.

```php
use AutoShell\Console;

$console = Console::new(
    namespace: 'Project\Sapi\Cli\Command',
    directory: dirname(__DIR__) . '/src/Sapi/Cli/Command'
);

$code = $console($_SERVER['argv']);
exit($code);
```

If you are following the [pds/skeleton](https://github.com/php-pds/skeleton)
project directory structure, you can save this file as `bin/console.php`.

### Command Class

Create the following class file at `src/Sapi/Cli/Command/Hello.php`:

```php
namespace Project\Sapi\Cli\Command;

use AutoShell\Help;
use AutoShell\Option;
use AutoShell\Options;

#[Help('A hello-world example command.')]
#[Option('-u,--upper', help: "Output in upper case.")]
class Hello
{
    public function __invoke(Options $options, string $name) : int
    {
        if ($options['u']) {
            $name = strtoupper($name);
        }

        $output = "Hello, {$name}." . PHP_EOL;

        echo $output;
        return 0;
    }
}
```

When writing your own commands, note that the first parameter **must** be
_Options_, even if you do not define an _Option_ attributes on the command.

### Run The Command

You can now run the `Hello` command at the command line, like so:

```sh
php ./bin/run.php hello --upper world
```

The output will be:

```
Hello, WORLD.
```

### Get Help

You can see the list of available commands by invoking the console script without
any arguments, or with the single argument `help`:

```sh
./biin/console.php
./biin/console.php help
```

You can see the manual page for a command by invoking the console script with
a command name:

```sh
./bin/console.php help hello
```

### Adding A Command Factory

By default, the provided _Console_ will just create command classes using `new`.
To inject a command factory of your own, perhaps one based on psr/container,
pass a `$factory` callable to the _Console_:

```php
/** @var Psr\Container\ContainerInterface $container */

$console = Console::new(
    namespace: 'Project\Sapi\Cli\Command',
    directory: dirname(__DIR__) . '/src/Sapi/Cli/Command'
    factory: function (string $class) use ($container) : object {
        return $container->get($class);
    }
);

```

> N.b.: The _Console_ will not use the injected factory for HelpCommand classes;
  it will create those itself.

## How It Works

Command class files are presumed to be named according to PSR-4 standards;
further:

1. Each colon-separated portion of the command name maps to a subnamespace;

2. Dash-separated words are converted to CamelCase;

3. The command class file itself has a "main" method (usually `__invoke()`) with
   an _Options_ parameter along with any other command-line argument parameters.

Given a base namespace of `Project\Sapi\Cli\Command`, the command name
`create-article` maps to the class `Project\Sapi\Cli\Command\CreateArticle`.

Likewise, the command name `schema:dump` maps to the class
`Project\Sapi\Cli\Command\Schema\Dump`.

If the command class has defined _Options_ and parameters, the option and
argument values will be collected from the command line invocation.

The _Shell_ will parse the command name to find the correct class, then reflect
on that class to find the available options and arguments, and parse those out
as well.

Note that the _Shell_ **does not** presume any particular return type from the
Command classes. Typically this is an `int` representing an exit code, but that
is not required per se by the _Shell_; it *will* be important to the _Console_
you use, whether the one provided by AutoShell or one of your own.

You are not limited to using the provided _Console_ implmentation. Examine the
provided implementation for an example of how to write your own.

## Working with Options

You can define a long and short options for your command by adding an _Option_
attribute to the command class.

```php
#[Option(
    'f,foo', // comma-separated list of short and long names for this option
    argument: Option::VALUE_REJECTED, // or VALUE_REQUIRED or VALUE_OPTIONAL
    multiple: false, // true if the option may be specified multiple times
    type: 'string', // the value type for the option value: int, bool, etc.
    default: 'default_value', // the default value for the option
    help: 'Text for help.', // a help line for the command manual page for this option
    argname: 'foo-value', // a name for the argument help text
)]
```

Inside your command, you can address the _Option_ via the _Options_ parameter
as an object property or an array key:

```php
#[Option('f,foo')]
class FooCommand
{
    public function __invoke(Options $options) : int
    {
        if (isset($options['f'])) {
            // -f and --foo are the same value
            assert($options['f'] === $options['foo']);

            // display the value
            echo $options['f'];
        }

        return 0;
    }
}
```

## Adding Help

You can write the manual page for your command using the _Help_ attribute on
the command class, and on the individual main method arguments.

```php
#[Help('This command does something.')]
class BarCommand
{
    public function __invoke(
        Options $options,

        #[Help('The something to do.')]
        string $bar

    ) : int
    {
        echo $bar . PHP_EOL;
        return 0;
    }
}
```

You can add extra, long-form text to the command-level _Help_ as a second
parameter. A very light markup of `*bold*` and `_underline_` is supported.

```php
#[Help(
    'This command does something.',
    <<<HELP
    *DESCRIPTION*

    This is a longer description of the command.

    *EXAMPLES*

    Look for examples _elsewhere_.

    HELP;
)]
class BarCommand
{
    public function __invoke(
        Options $options,

        #[Help('The something to do.')]
        string $bar

    ) : int
    {
        echo $bar . PHP_EOL;
        return 0;
    }
}
```
