Fuelly 'API' client
====

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rudiedirkx/Fuelly/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/rudiedirkx/Fuelly/?branch=master)

This fake API client (using browser sessions and scraping like a fool) can
use some basic Fuelly functionality. It includes unit & number conversion, but
configuring it is up to the API consumer.

Features
----

* Log in
* List verhicles
* List vehicle fuel-ups
* <del>Add fuel-up</del>

Set up
----

1. Copy `env.php.original` to `env.php` and change the constants.
2. You'll need [`HTTP`][1] for the HTTP requests.

[1]: https://github.com/rudiedirkx/HTTP
