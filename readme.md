# Mockster [![Build Status](https://travis-ci.org/rtens/mockster.png?branch=v2)](https://travis-ci.org/rtens/mockster)

*mockster* is a full-fledged, zero-configuration [mocking] framework for PHP.

[mocking]: http://en.wikipedia.org/wiki/Mock_object

## Main Features ##

- Automatic mocking of dependencies, return values, method and constructor arguments
- Support of [Four-Phase][4phase] testing by defining the context first and asserting expectations second
- Fine-grained configuration of the behaviour

[4phase]: http://robots.thoughtbot.com/four-phase-test

## Installation ##

You will need [Composer], [PHP] and [git].

To use *mockster*, add it as a requirement to your projects `composer.json`

    "rtens/mockster": "*"

You can also download the project directly with

    php composer.phar create-project rtens/mockster

and the run the test suite by executing `vendor/bin/phpunit` in the project's root folder.

[Composer]: http://getcomposer.org/download/
[PHP]: http://php.net/downloads.php
[git]: http://git-scm.com/downloads

## Documentation ##

You can find all documentation in form of executable specification in the [`spec`] folder.

[`spec`]: http://github.com/rtens/mockster/tree/v2/spec/rtens/mockster

## Contribution ##

I'm looking forward to any kind of contribution including feedback about how unnecessary this project is, bugs
and suggestions for missing features. Just open a [new issue] or check out the [open issues].

[new issue]: https://github.com/rtens/mockster/issues/new
[open issues]: https://github.com/rtens/mockster/issues
