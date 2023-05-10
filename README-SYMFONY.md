# Comparison to Symfony Console

Much of the difference from Symfony Console relates to what AutoShell
does *not* do.

## Pro

- Automatically discovers command classes by matching the command line
  input to a namespace hierarchy

- Does not require you to register commands via an _Application_

- Does not require you to extend a _Command_ class

- Arguments are defined as parameters on the command `__invoke()` method

- All help is defined via attributes

- Does not instantiate the command just to generate help output

## Con

- Does not offer console completion

- Does not support registering "inline" commands via _Application_

## ???

- Help looks like a `man` page (black and white, bold, underline, dim)

- Options are defined as properties with attributes in a class separate from
  the command

- Does not come with I/O classes; use your own in your own commands, whether a
  echo/printf, a logger, or a complex I/O system like CLImate
