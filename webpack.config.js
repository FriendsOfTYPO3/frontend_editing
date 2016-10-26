var webpack = require('webpack');
var path = require('path');

var BUILD_DIR = path.resolve(__dirname, 'Resources/Public/Javascript');
var APP_DIR = path.resolve(__dirname, 'Resources/Private/Javascript');

var config = {
    entry: APP_DIR + '/client.js',
    output: {
        path: BUILD_DIR,
        filename: 'Bundle.js'
    },
    module : {
        loaders: [
            {
                test: /\.js?/,
                include: APP_DIR,
                loader: 'babel'
            }
        ]
    }
};

module.exports = config;