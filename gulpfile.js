var gulp = require('gulp');
var phpunit = require('gulp-phpunit');
var watch = require('gulp-watch');

gulp.task('default', function () {
    // Callback mode, useful if any plugin in the pipeline depends on the `end`/`flush` event
    return watch('./**/*.php', function () {
        var options = {
            statusLine:        false
        };
        gulp.src('./**/*.php')
            .pipe(phpunit('./bin/phpunit', options));
    });
});