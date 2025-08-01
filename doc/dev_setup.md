# Development

## PHP dependencies
Just run `composer install` and you will get phpunit and dependencies in place.

## PHP Dev tools for Tests, Static analysis & automatic refactoring 
PHPUnit, Rector and PHPStan are automatically ran by GitHub Actions however you can also run them manually as composer scripts.
Type `composer run` to get into the interactive console and try them out.

## Contributing
Contributions are really welcome. But it saves time for both sides when at least the `phpunit` and `phpstan` runs do not fail.
We will not accept newly added fields/features without full test coverage in the PR.