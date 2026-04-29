# TaxJar for Commerce

## Running Composer Commands

This package is symlinked into a Craft project at `/Users/luke/Code/Php/craft5`. To run composer commands like `phpstan`, `fix-cs`, or tests, use ddev from that project:

```
(builtin cd /Users/luke/Code/Php/craft5 && ddev exec "cd /tmp/packages/commerce-taxjar-2 && composer run phpstan")
(builtin cd /Users/luke/Code/Php/craft5 && ddev exec "cd /tmp/packages/commerce-taxjar-2 && composer run fix-cs")
(builtin cd /Users/luke/Code/Php/craft5 && ddev exec "cd /tmp/packages/commerce-taxjar-2 && composer run testunit")

# Run a specific test file
(builtin cd /Users/luke/Code/Php/craft5 && ddev exec "cd /tmp/packages/commerce-taxjar-2 && vendor/bin/codecept run unit")
```

The `builtin cd` is required because the shell has a zoxide alias on `cd` that doesn't work in subshells.

## Test Database Setup (one-time)

Tests run against a separate `test` postgres database inside ddev. Create it once:

```
(builtin cd /Users/luke/Code/Php/craft5 && ddev exec 'psql -U db -c "CREATE DATABASE test;"')
```

Then install test dependencies:

```
(builtin cd /Users/luke/Code/Php/craft5 && ddev exec "cd /tmp/packages/commerce-taxjar-2 && composer install")
```

Never add Co-Authored-By in any commit message

## Branches



### Feature branches

- `feature/3.x-xxx` for new features