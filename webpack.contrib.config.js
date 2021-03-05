const path = require('path');

module.exports = {
    entry: {
        pino: path.resolve(__dirname, "node_modules/pino/browser.js"),
        // ulog: path.resolve(__dirname, "node_modules/ulog/base.js"),
        'ulog/ulog': path.resolve(__dirname, "node_modules/ulog/ulog.js"),
    },
    output: {
        path: path.resolve(__dirname, "Resources/Public/JavaScript/Contrib"),
        filename: '[name].js',
        libraryTarget: "amd"
    }
};
