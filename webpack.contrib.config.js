const path = require('path');

module.exports = {
    entry: {
        'ulog/ulog': path.resolve(__dirname, "node_modules/ulog/ulog.js"),
    },
    output: {
        path: path.resolve(__dirname, "Resources/Public/JavaScript/Contrib"),
        filename: '[name].js',
        libraryTarget: "amd"
    },
    devtool: 'source-map',
};
