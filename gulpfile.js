"use strict";

let gulp     = require('gulp'),
    path     = require('path'),
    stylecow = require('gulp-stylecow'),
    imagemin = require('gulp-imagemin'),
    rename   = require('gulp-rename'),
    cache    = require('gulp-cached'),
    webpack  = require('webpack'),
    sync     = require('browser-sync').create(),
    env      = process.env;

gulp.task('css', function () {
    var config = require('./stylecow.json');

    config.code = env.APP_DEV ? 'normal' : 'minify';

    config.files.forEach(function (file) {
        gulp.src(file.input)
            .pipe(stylecow(config))
            .on('error', function (error) {
                console.log(error.toString());
                this.emit('end');
            })
            .pipe(rename(file.output))
            .pipe(gulp.dest(''))
            .pipe(sync.stream());
    });
});

gulp.task('js', function (callback) {
    var config = require('./webpack.config');

    if (!env.APP_DEV) {
        config.plugins = config.plugins.concat(
            new webpack.optimize.DedupePlugin(),
            new webpack.optimize.UglifyJsPlugin()
        );
    }

    webpack(config, function (err, stats) {
        callback();
    });
});

gulp.task('img', function () {
    gulp.src('assets/img/**/*')
        .pipe(cache('img'))
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
        port: env.APP_SYNC_PORT || 3000,
        proxy: env.APP_URL || 'http://127.0.0.1:8000'
    });

    gulp.watch('assets/**/*.js', ['js']);
    gulp.watch('assets/**/*.css', ['css']);
    gulp.watch('assets/**/*.{jpg,png,gif,svg}', ['img']);
});

gulp.task('default', ['css', 'js', 'img']);
