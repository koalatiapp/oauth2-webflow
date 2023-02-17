# Contributing

Contributions are welcome and will be fully credited.

We accept contributions via Pull Requests on [Github](https://github.com/koalatiapp/oauth2-webflow).


## Pull Requests

- **[PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)** - Ensure your code follows the coding standards (see below).

- **Add tests for new features** - New features must be tested or they won't be accepted.

- **Document any change in behaviour** - Make sure the README and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We do our best to follow semantic versioning. Please consider this before introducing breaking changes to public APIs.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.

- **Ensure tests and CS pass!** - Please run the tests and coding standard checks / fixers (see below) before submitting your pull request, and make sure they pass. We won't accept a patch until all tests and CS checks pass.


## Tests

This repository's tests are powered by PHPUnit.

To run the tests, use the command below:

``` bash
composer test
```

Make sure that all tests passes before submitting a pull request.


## Coding standards and static analysis

This repository follows PSR-2 and a few other common "clean code" practices.

To run coding standard checks, use the command below:

``` bash
composer check
```

This will automatically fix any issues that can be fixed automatically, and 
will report other issues for you to fix if there are any.

Make sure this passes before submitting a pull request.

