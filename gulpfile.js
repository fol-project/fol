"use strict";

let gulp     = require('gulp'),
    path     = require('path'),
    stylecow = require('gulp-stylecow'),
    imagemin = require('gulp-imagemin'),
    rename   = require('gulp-rename'),
    sync     = require('browser-sync').create();

gulp.task('css', function () {
    let config = require('./stylecow.json');

    config.files.forEach(function (file) {
        gulp.src(file.input)
            .pipe(stylecow(config))
            .pipe(rename(file.output))
            .pipe(gulp.dest(''))
            .pipe(sync.stream());
    });
});

gulp.task('js', function () {
    //here your js tasks
});

gulp.task('img', function () {
    gulp.src('assets/img/**/*')
        .pipe(imagemin())
        .pipe(gulp.dest('public/img'));
});

gulp.task('sync', ['default'], function () {
    sync.watch(['app/**/*', 'public/**/*'], function (event, file) {
        if (event !== 'change') {
            return;
        }

        switch (path.extname(file)) {
            case '.php':
                return sync.reload('*.html');

            default:
                return sync.reload(path.basename(file));
        }
    });

    sync.init({
        port: process.env.APP_SYNC_PORT || 3000,
        proxy: process.env.APP_URL || 'http://127.0.0.1:8000'
    });

    gulp.watch('assets/**/*.js', ['js']);
    gulp.watch('assets/**/*.css', ['css']);
    gulp.watch('assets/**/*.{jpg,png,gif,svg}', ['img']);
});

gulp.task('default', ['css', 'js', 'img']);
gulp.task('build', ['default']);
