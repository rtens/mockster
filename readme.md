# Mockster [![Build Status](https://travis-ci.org/rtens/mockster.png?branch=master)](https://travis-ci.org/rtens/mockster)

*mockster* is a full-fledged, zero-configuration [mocking] framework for PHP.

[mocking]: http://en.wikipedia.org/wiki/Mock_object

## Main Features ##

- Automatic mocking of dependencies, return values, method and constructor arguments
- Support of [Four-Phase][4phase] testing by defining the context first and asserting expectations second
- Fine-grained configuration of the behaviour

[4phase]: http://robots.thoughtbot.com/four-phase-test

## Installation ##

You will need [Composer], [PHP] and [git] to download the project

    php composer.phar create-project rtens/mockster

or add it as a requirement to your projects `composer.json`

    "rtens/mockster": "*"
	
To run the test suite just execute `phpunit` in the base folder of mockster.

    cd mockster
    phpunit

[Composer]: http://getcomposer.org/download/
[PHP]: http://php.net/downloads.php
[git]: http://git-scm.com/downloads

## Documentation ##

You can find all documentation in form of [executable specification] on [dox].

[executable specification]: http://specificationbyexample.com/key_ideas.html
[dox]: http://dox.rtens.org/rtens-mockster

## Contribution ##

I'm looking forward to any kind of contribution including feedback about how unnecessary this project is, bugs
and suggestions for missing features. Just open a [new issue] or check out the [open issues].

[new issue]: https://github.com/rtens/mockster/issues/new
[open issues]: https://github.com/rtens/mockster/issues
