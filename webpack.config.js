const webpack = require('webpack');
const path = require('path');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const StyleLintPlugin = require('stylelint-webpack-plugin');

var BUILD_DIR = path.resolve(__dirname, 'Resources/Public/App');
var APP_DIR = path.resolve(__dirname, 'Resources/Private');

var config = {
    entry: [
        APP_DIR + '/Javascript/client.js',
        APP_DIR + '/Styles/Main.scss',
        APP_DIR + '/Styles/InlineEditing.scss'
    ],
    output: {
        path: BUILD_DIR,
        filename: 'Bundle.js'
    },
    module: {
        loaders: [
            {
                test: /\.js?/,
                include: APP_DIR,
                loader: 'babel'
            },
            {
                test: /\.[s]?css$/,
                loader: ExtractTextPlugin.extract('style-loader', 'css!sass')
            },
            {
                test: /\.(eot|woff|woff2|ttf|svg|png|jpe?g|gif)(\?\S*)?$/,
                loader: 'url?limit=100000&name=[name].[ext]'
            }
        ]
    },
    plugins: [
        new ExtractTextPlugin('Main.css'),
        new ExtractTextPlugin('InlineEditing.css'),
        new StyleLintPlugin({
            configFile: '.styleintrc',
            files: 'Resources/Private/Styles/**/*.scss',
            failOnError: false
        })
    ],
    sassLoader: {
        includePaths: [
            APP_DIR + '/Styles'
        ]
    }
};

module.exports = config;