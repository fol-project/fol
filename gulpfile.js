var gulp     = require('gulp'),
    path     = require('path'),
    concat   = require('gulp-concat'),
    cache    = require('gulp-cached'),
    stylecow = require('gulp-stylecow'),
    imagemin = require('gulp-imagemin'),
    rename   = require('gulp-rename'),
    sync     = require('browser-sync').create(),
    webpack  = require('webpack'),
    url      = require('url'),
    env      = process.env;

gulp.task('apache', function () {
    gulp.src([
        'assets/.htaccess',
        'bower_components/apache-server-configs/dist/.htaccess',
    ])
    .pipe(concat('.htaccess'))
    .pipe(gulp.dest('public'))
});

gulp.task('css', function() {
    var config = require('./stylecow.json');

    if (env.APP_DEV) {
        config.code = 'normal';
        config.cssErrors = true;
    } else {
        config.code = 'minify';
    }

    config.files.forEach(function (file) {
        gulp
            .src(file.input)
            .pipe(stylecow(config))
            .on('error', function (error) {
                console.log(error.toString());
                this.emit('end');
            })
            .pipe(rename(file.output))
            .pipe(gulp.dest(''));
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

    config.output.publicPath = path.join(url.parse(env.APP_URL || '/').pathname, '/js/');

    webpack(config, function (err, stats) {
        done();
    });
});

gulp.task('img', function(done) {
    gulp.src('assets/img/**/*.{jpg,png,gif,svg}')
        .pipe(cache('img'))
        .pipe(imagemin().on('end', done))
        .pipe(gulp.dest('public/img'));
});

gulp.task('sync', ['css', 'js', 'img'], function () {
    sync.watch([
        'app/**/*',
        'templates/**/*'
    ], function (event, file) {
        sync.reload();
    });

    sync.watch('public/**/*', function (event, file) {
        sync.reload(path.basename(file));
    });

    sync.init({
        proxy: process.env.APP_URL
    });

    gulp.watch('assets/**/*.js', ['js']);
    gulp.watch('assets/**/*.css', ['css']);
    gulp.watch('assets/**/*.{jpg,png,gif,svg}', ['img']);
});

gulp.task('default', ['css', 'js', 'img', 'apache']);
