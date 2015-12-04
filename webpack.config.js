"use strict";

let path    = require("path"),
    webpack = require("webpack"),
    bower   = require("bower-webpack-plugin");

module.exports = {
    context: __dirname + '/assets/js',
    entry: {
        main: './main.js'
    },
    output: {
        path: __dirname + '/public/js',
        publicPath: '/js/',
        filename: '[name].js'
    },
    plugins: [
        new bower(),
        new webpack.optimize.CommonsChunkPlugin('common.js')
    ]
};
