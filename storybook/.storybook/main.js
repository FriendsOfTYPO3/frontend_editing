const path = require('path');

module.exports = {
    "stories": [
        "../stories/**/*.stories.@(mdx|js|jsx|ts|tsx)"
    ],
    "addons": [
        "@storybook/addon-links",
        "@storybook/addon-essentials"
    ],
    webpackFinal: (config) => {

        Object.assign(config.resolve.alias, {
            // "jquery": path.resolve(__dirname, '../../.Build/Web/typo3/sysext/core/Resources/Public/JavaScript/Contrib/jquery/jquery-3.2.1.js'),
            // "TYPO3/CMS/Core": path.resolve(__dirname, '../../.Build/Web/typo3/sysext/core/Resources/Public/JavaScript'),
            // "TYPO3/CMS/Backend": path.resolve(__dirname, '../../.Build/Web/typo3/sysext/backend/Resources/Public/JavaScript'),
            "TYPO3/CMS/FrontendEditing": path.resolve(__dirname, '../../Resources/Public/JavaScript'),
        });

        config.module.rules[0].include = [path.resolve(__dirname, '..')];

        config.module.rules.push({
            test: /\.html$/,
            use: [
                {
                    loader: require.resolve('html-loader'),
                },
            ],
        });

        config.module.rules.push({
            test: /\.s[ac]ss$/i,
            use: [
                "style-loader",
                "css-loader",
                "postcss-loader",
                "sass-loader",
            ],
        });


        return config;
    },
};
