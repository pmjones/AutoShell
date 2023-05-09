# AutoShell

_AutoShell_ automatically maps command names to PHP command classes in a specified
namespace, reflecting on a specified main method within that class to determine
the argument and option values. The method parameters may be scalar values
(int, float, string, bool) or arrays.

_AutoShell_ is low-maintenance. Merely adding a class to your source code, in the
recognized namespace and with the recognized main method name, automatically
makes it available as a command.

Think of _AutoShell_ as the "router" for your command classes:

-  Under MVC or ADR, you would have a Front Controller pass the URL to a Router,
   and get back a Route DTO describing which Controller/Action to invoke
   (with the arguments thereto). The Front Controller would then invoke the
   Controller/Action with those arguments.

-  Here, you would have a Console class pass `$_SERVER['argv']` to a Shell, and
   get back an Exec DTO describing which Command class to invoke (with the
   options and arguments thereto). The Console would then invoke the Command
   with those options and arguments.

That is:

    Front Controller    => Console
    Router              => Shell
    Route               => Exec
    Controller/Action   => Command

## Getting Started

> Note:
>
> This documentation follows the
> [pds/skeleton][]
> standard for directory and file names.

  [pds/skeleton]: https://github.com/php-pds/skeleton

### Installation

Install _AutoShell_ using Composer:

```
composer require pmjones/auto-shell
```

### Console Script

You will need a console script to run your commands. To create a console script, open a file in your project at `bin/console.php` and add the following code:

```php
use AutoShell\Console;

require dirname(__DIR__) . '/vendor/autoload.php';

$console = Console::new(
    namespace: 'Project\Cli\Command',
    directory: dirname(__DIR__) . '/src/Cli/Command',
    help: 'The console for my Project.' . PHP_EOL . PHP_EOL,
);

$code = $console($_SERVER['argv']);
exit($code);
```

You will need to specify the `namespace` for your command classes, and the `directory` where those class files are saved. You can also specify `help` text to be shown at the top of all help output, but doing so is optional.

Now you can issue `php bin/console.php` and see some output:

```
The console for my Project.

No commands found.
Namespace: Project\Cli\Command\
Directory: /path/to/project/src/Cli/Command
```

This output is to be expected, since there are no commands yet.

### Command Class

Open a file at `src/Project/Cli/Command/Hello.php` and add the following code:

```php
namespace Project\Cli\Command;

class Hello
{
    public function __invoke(string $name) : int
    {
        echo "Hello {$name}" . PHP_EOL;
        return 0;
    }
}
```

That's all -- the command should now be available via the console script. If you issue the follwing ...

    php bin/console.php hello world

... you should see `Hello world` as the output.

> Note:
>
> This example uses `echo` to generate output, but you can use
> any other output mechanism you like.

### Adding Options

To enable options on the command, create a class that implements the _Options_
marker interface, using `#[Option]` attributes on constructor-promoted
properties. Then add that _Options_ implementation to the `__invoke()`
parameters to make those options available to the command logic.

First, open a file at `src/Project/Cli/Command/HelloOptions` and add the
following code:

```php
namespace Project\Cli\Command\HelloOptions;

use AutoShell\Option;
use AutoShell\Options;

class HelloOptions implements Options
{
    public function __construct(

        #[Option('u,upper')]
        public readonly ?bool $useUpperCase;

    ) {
    }
}
```

Then in the command, add a typehinted main method parameter for the options,
along with some logic for the option behavior:

```php
namespace Project\Cli\Command;

class Hello
{
    public function __invoke(
        HelloOptions $options,
        string $name
    ) : int
    {
        if ($options->useUpperCase) {
            $name = strtoupper($name);
        }

        echo "Hello {$name}" . PHP_EOL;
        return 0;
    }
}
```

Now if you issue one of the following ...

    php bin/console.php hello world -u
    php bin/console.php hello world --upper

... you will see `Hello WORLD` as the output.

### Giving Help

To add help for your command, use the `#[Help]` attribute on the command
and its arguments, as well as the `help` parameter on any `#[Option]`
attributes.

Edit the command to add `#[Help]` attributes ...

```php
namespace Project\Cli\Command;

#[Help("Says hello to a _name_ of your choice.")]
class Hello
{
    public function __invoke(
        HelloOptions $options,

        #[Help("The _name_ to say hello to.")]
        string $name
    ) : int
    {
        if ($options->useUpperCase) {
            $name = strtoupper($name);
        }

        echo "Hello {$name}" . PHP_EOL;
        return 0;
    }
}
```

Likewise, edit the options to add `help` attribute parameters:

```php
namespace Project\Cli\Command\HelloOptions;

use AutoShell\Option;
use AutoShell\Options;

class HelloOptions implements Options
{
    public function __construct(

        #[Option('u,upper', help: "Output the _name_ in upper case.")]
        public readonly ?bool $useUpperCase;

    ) {
    }
}
```

Now when you issue `php bin/console.php` or `php bin/console.php help`,
you should see your command listed in the roster of commands:

```
The console for my Project.

hello
    Says hello to a name of your choice.
```

Similarly, when you issue `php bin/console.php help hello`, you should
see a manual page for your command:

```
The console for my Project.

NAME
    hello

SYNOPSIS
    hello [options] [--] name ...

ARGUMENTS
    name
         The name to say hello to.

OPTIONS
    -u
    --upper
        Output the _name_ in upper case.
```


## Advanced Topics

### Command Naming and Discovery

Command class files are presumed to be named according to PSR-4 standards;
further:

1. Each colon-separated portion of the command name maps to a subnamespace;

2. Dash-separated words are converted to CamelCase;

3. The command class file itself has a "main" method (usually `__invoke()`) with
   an _Options_ parameter along with any other command-line argument parameters.

Given a base namespace of `Project\Cli\Command`, the command name
`create-article` maps to the class `Project\Cli\Command\CreateArticle`.

Likewise, the command name `schema:dump` maps to the class
`Project\Cli\Command\Schema\Dump`.

If the command class has defined _Options_ and parameters, the option and
argument values will be collected from the command line invocation.

The _Shell_ will parse the command name to find the correct class, then
reflect on that class to find the available options and arguments, and parse
those out as well. (The _Shell_ ignores interfaces, traits, absrtact classes,
and _Options_ classes.)

Note that the _Shell_ **does not** presume any particular return type from the
Command classes. Typically this is an `int` representing an exit code, but that
is not required per se by the _Shell_; it *will* be important to the _Console_
you use, whether the one provided by _AutoShell_ or one of your own.

You are not limited to using the provided _Console_ implementation. Examine the
provided implementation for an example of how to write your own.

### Command Factory

By default, the _Console_ will just create command classes using `new`.
To inject a command factory of your own, perhaps one based on psr/container,
pass a `$factory` callable to the _Console_:

```php
/** @var Psr\Container\ContainerInterface $container */

$console = Console::new(
    namespace: 'Project\Cli\Command',
    directory: dirname(__DIR__) . '/src/Sapi/Cli/Command',
    factory: fn (string $class) => $container->get($class),
);
```

The _Console_ will not use the injected factory for HelpCommand classes; it
will create those itself.

### Argument Types

_AutoShell_ recognizes main method parameter typehints of `int`, `float`,
 `string`, `bool`, `mixed`, and `array`.

For `bool`, _AutoShell_ will case-insensitively cast these argument values
to `true`: `1, t, true, y, yes`. Similarly, it will case-insensitively cast
these argument values to `false`: `0, f, false, n, no`.

For `array`, _AutoShell_ will use `str_getcsv()` on the argument value to
generate an array. E.g., an array typehint for a segment value of `a,b,c` will
receive `['a', 'b', 'c']`.

Finally, trailing variadic parameters are also honored by _AutoShell_.

### Option Descriptions

You can define long and short options for your command by adding an
`#[Option]` attribute to a constructor-promoted property in a class
that implements _Options_ marker interface.

The property name can be anything you like, but must be nullable.
(_AutoShell_ indicates an option was not passed at the command line by
setting it to `null`).

The property should be defined as `readonly`, and should not have a default
value.

The first `#[Option]` parameter, a comma-separated list of short and long
names for the option, is the only one required:

```php
class FooOptions
{
    public function __construct(

        #[Option('b,bar')]
        public readonly ?bool $barval,

    ) {
    }
}
```

There are several optional named parameters for the `#[Option]` attribute:

- `argument`: (string) Must be one of `Option::VALUE_REJECTED`,
  `VALUE_REQUIRED`, or `VALUE_OPTIONAL`. Default is `VALUE_REJECTED`.

    - If `VALUE_REJECTED`, no value is allowed for the option.
    - If `VALUE_REQUIRED`, a value *must* be specified.
    - If `VALUE_OPTIONAL`, a value may be specified; if the option is
      specified without a value, it will use the default value (see below).

- `multiple`: (bool) `true` if the option may be specified multiple times.
  Default is `false`. When `true`, the argument values will be passed as an
  array, even if the option is specified only once.

- `default`: (mixed) The value for when the option is specified, but no value
  is given or allowed. Default is `true`.

- `help`: (string) A short line of help text about this option for the manual
  page.

- `argname`: (string) A short name for the argument in the help text.

Argument values will be cast to the property type of the `#[Option]`.

Inside your command, you can address the option via an _Options_ parameter
on the main method:

```php
class Foo
{
    public function __invoke(FooOptions $options) : int
    {
        if ($options->barval)) {
            // $barval is true
        }

        return 0;
    }
}
```

### Extended Help

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
class Foo
{
    // ...
}
```

### Console Input/Output

By default, the _Console_ writes help output to `STDOUT`, and writes
invocation-time error messages to `STDERR` (such as when it cannot parse
command line input).

To change where the _Console_ writes output, pass a callable for the `$stdout`
and/or `$stderr` arguments:

```php

/** @var Psr\Container\ContainerInterface $container */
$logger = $container->get(LoggerInterface::class);

$console = Console::new(
    namespace: 'Project\Cli\Command',
    directory: dirname(__DIR__) . '/src/Sapi/Cli/Command',
    stdout: fn (string $output) => $logger->info($output),
    stderr: fn (string $output) => $logger->critical($output),
);
```

Please note that these callables are used **only by the _Console_ itself.**

### Command Input/Output

TBD.

> Notes:
>
> Your command classes can use any output mechanism you like; they are
> completely under your own control. Your command factory can inject
> any I/O dependencies needed.
>
> echo: going to be hard(er) to test
> stdlog: ok
> climate
> symfony io? looks tough to extract from symfony/console
>
> Keep IO separate from business logic. See next section.
>
> Also: return codes.

### Application Layer

TBD.
