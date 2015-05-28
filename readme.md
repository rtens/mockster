# Mockster [![Build Status](https://travis-ci.org/rtens/mockster.png?branch=master)](https://travis-ci.org/rtens/mockster)

*mockster* is a full-fledged, zero-configuration [mocking] framework for PHP.

[mocking]: http://en.wikipedia.org/wiki/Mock_object

## Main Features ##

- Automatic mocking of dependencies, return values, method and constructor arguments
- Support of [Four-Phase][4phase] testing by defining the context first and asserting expectations second
- Fine-grained configuration of the behaviour

[4phase]: http://robots.thoughtbot.com/four-phase-test

## Installation ##

To use *mockster* in your project, require it with [Composer]

    composer require "rtens/mockster"
    
If you would like to develop on *mockster*, clone it with [git], download its dependencies with [Composer] and execute the specification with [scrut]

    git clone https://github.com/rtens/mockster.git
    cd mockster
    composer update
    vendor/bin/scrut

[Composer]: http://getcomposer.org/download/
[scrut]: https://github.com/rtens/scrut
[git]: https://git-scm.com/

## Documentation ##

You can find all documentation in form of [executable specification on dox][dox].

[dox]: http://dox.rtens.org/projects/rtens-mockster/specs/Introduction

## Contribution ##

I'm looking forward to any kind of contribution including feedback about how unnecessary this project is, bugs
and suggestions for missing features. Just open a [new issue] or check out the [open issues].

[new issue]: https://github.com/rtens/mockster/issues/new
[open issues]: https://github.com/rtens/mockster/issues
