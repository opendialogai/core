# OpenDialog Core Package

[![CircleCI](https://circleci.com/gh/opendialogai/core/tree/develop.svg?style=svg&circle-token=d14bcacaf3cd3e6ae4dfd2fb3bf03658cf0ca8fa)](https://circleci.com/gh/opendialogai/core/tree/develop)

[OpenDialog](https://docs.opendialog.ai) is a platform that helps you design, develop, deploy and administer chatbots - or as we like to call them, conversational applications.

OpenDialog Core is the heart of this platform. It provides a framework for developing and integrating the different components that are required to build a conversational application. 

OpenDialog is, on the one hand, very opinionated about how to model a conversational application, but, on the other hand, provides a lot of flexibility with respect to intent identification (or NLU more generally) . You can read more about it [here](https://docs.opendialog.ai).

If you are interested in seeing how OpenDialog works please head over to the [OpenDialog app](https://github.com/opendialogai/opendialog) repository and follow the instructions there to install. 

This repository is for those that are more interested in the inner workings of OpenDialog, and in particular those that would like to be involved with the core development of OpenDialog. Finally, this is also the right place if you want to integrate OpenDialog functionality into your own PHP application without a GUI. 

## Installing

To install using [Composer](https://getcomposer.org/) run the following command:

`composer require opendialogai/core`

## Local Config
To publish config files for local set up and customisation, run

```php artisan vendor:publish --tag="config"```

This will copy over all required config files into `config/opendialog/`

## Running Code Sniffer

To run code sniffer, run the following command
```./vendor/bin/phpcs --standard=od-cs-ruleset.xml src/ --ignore=*/migrations/*,*/tests/*```

This will ignore all files inside of migration directories as they will never have a namespace

## Git Hooks

To set up the included git pre-commit hook, first make sure the pre-commit script is executable by running

```chmod +x .githooks/pre-commit```

Then configure your local git to use this directory for git hooks by running:

```git config core.hooksPath .githooks/```

Now every commit you make will trigger php codesniffer to run. If there is a problem with the formatting
of the code, the script will echo the output of php codesniffer. If there are no issues, the commit will
go into git.

## Local development and tests

We provide a [Docker-based environment](https://github.com/opendialogai/opendialog-dev-environment) for local development and running tests. Head over there for more information.

### Tests that need DGraph

Lots of tests int he suite require a DGraph instance to be running. The dev environment mentioned above spins up a test DGraph for this reason.
`phpunit.xml` defines the DGraph host as `dgraph-server-test` to match that in the dev environment.

This package makes use of a phpunit annotation to make which tests require DGraph to run. These tests need to be marked 
with the following phpdoc:

```
    /**
     * @requires DGRAPH
     */
```

This will check if DGraph is reachable - if it is, it clears any data and refreshes the schema ready for the test. If 
not, the test is marked as skipped.

Remember to add this annotation to any tests that will need to load conversations or make use of the user context.

### Dgraph Query Logging

OpenDialog stores conversation and conversation state to a graph database called Dgraph. To log DGraph queries to the standard application log, set the `LOG_DGRAPH_QUERIES` environment variable to true.

All queries are logged at info level.

## Logging API requests

By default, all incoming and outgoing API calls will be logged to the request and response mysql tables.
To prevent this happening, set the `LOG_API_REQUESTS` env variable to `false`.

## Introspection logging

To turn on introspection processing set the `INTROSPECTION_PROCESSOR_ENABLED` env variable to true. This will add
extra information to all log messages including the class and line that generated the message.

