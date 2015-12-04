var gulp     = require('gulp'),
    path     = require('path'),
    stylecow = require('gulp-stylecow'),
    imagemin = require('gulp-imagemin'),
    htmlmin  = require('gulp-htmlmin'),
    rename   = require('gulp-rename'),
    sync     = require('browser-sync').create(),
    webpack  = require('webpack'),
    env      = process.env;

gulp.task('css', function() {
    var config = require('./stylecow.json');

    config.code = env.APP_DEV ? 'normal' : 'minify';

    config.files.forEach(function (file) {
        gulp
            .src(file.input)
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

gulp.task('js', function(done) {
    var config = require('./webpack.config');

    if (!env.APP_DEV) {
        config.plugins = config.plugins.concat(
            new webpack.optimize.DedupePlugin(),
            new webpack.optimize.UglifyJsPlugin()
        );
    }

    webpack(config, function (err, stats) {
        done();
    });
});

gulp.task('img', function() {
    gulp
        .src([
            'build/**/*.jpg',
            'build/**/*.png',
            'build/**/*.gif',
            'build/**/*.svg'
        ])
        .pipe(imagemin())
        .pipe(gulp.dest('build'));
});

gulp.task('html', function () {
    gulp
        .src('build/**/*.html')
        .pipe(htmlmin({
            removeComments: true,
            collapseWhitespace: true,
            collapseBooleanAttributes: true,
            removeAttributeQuotes: true,
            removeRedundantAttributes: true,
            useShortDoctype: true,
            removeEmptyAttributes: true,
            removeScriptTypeAttributes: true,
            removeStyleLinkTypeAttributes: true,
            removeEmptyElements: true,
            minifyJS: true,
            minifyCSS: true,
            minifyURLS: {
                output: 'rootRelative',
                removeEmptyQueries: true
            }
        }))
        .pipe(gulp.dest('build'));
});

gulp.task('sync', ['css', 'js'], function () {
    sync.watch('source/**/*', function (event, file) {
        if (event !== 'change') {
            return;
        }

        switch (path.extname(file)) {
            case '.yml':
            case '.php':
                sync.reload('*.html');
                return;

            default:
                sync.reload(path.basename(file));
                return;
        }
    });

    sync.init({
        port: process.env.APP_SYNC_PORT || 3000,
        proxy: process.env.APP_URL || 'http://127.0.0.1:8000'
    });

    gulp.watch('source/**/*.js', ['js']);
    gulp.watch('source/**/*.css', ['css']);
});

gulp.task('default', ['css', 'js', 'img', 'html']);
