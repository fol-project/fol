"use strict";

let path               = require("path"),
    webpack            = require("webpack"),
    CommonsChunkPlugin = require("webpack/lib/optimize/CommonsChunkPlugin");

module.exports = {
    context: __dirname + '/assets/js',
    entry: {
        main: './main.js'
    },
    output: {
        path: __dirname + '/public/js',
        filename: '[name].js'
    },
    resolve: {
        root: [path.join(__dirname, 'bower_components')]
    },
    plugins: [
        new webpack.ResolverPlugin(
            new webpack.ResolverPlugin.DirectoryDescriptionFilePlugin('bower.json', ['main'])
        ),
        new CommonsChunkPlugin('common.js')
    ]
};
