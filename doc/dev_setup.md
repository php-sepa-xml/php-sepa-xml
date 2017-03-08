# Development

## PHP dependencies

Just run `composer install` and you will get phpunit and dependencies in place.
Phpunit ist located in _bin/phpunit_.

## Helper
The unit test are really fast and it's nice to run them without any extra command.

By running `yarn install` you will get a gulp setup to watch for changes
in PHP files and rerun _phpunit_. Read about [yarn](https://github.com/yarnpkg/yarn) if you don't know it jet.

If you don't have gulp installed:

```bash:
npm install --global gulp-cli
```

After all is in place just run `gulp` from root of project and now with every change to a PHP file
all the unit tests will be run.

## Contributing

Contributions are really welcome. But it saves time for both sides when at least the travis builds do not fail.
Please also consider to add new test for newly added fields/features.
