# OpenDialog Core Package

[![CircleCI](https://circleci.com/gh/opendialogai/core/tree/master.svg?style=svg&circle-token=d14bcacaf3cd3e6ae4dfd2fb3bf03658cf0ca8fa)](https://circleci.com/gh/opendialogai/core/tree/master)

This is the OpenDialog core package that can be used inside of your application.

## Installing

Install in composer with the following block:

```"opendialogai/core```

## Running Code Sniffer
To run code sniffer, run the following command
```./vendor/bin/phpcs --standard=psr12 src/ --ignore=*/migrations/*```

This will ignore all files inside of migration directories as they will never have a namespace

## Running Tests

```./vendor/bin/phpunit```

