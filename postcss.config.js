module.exports = {
    plugins: [
        require('autoprefixer'),
        require('postcss-clean')({
            rebase: false,
            level: {
                1: {
                    specialComments: 0
                }
            }
        })
    ]
};
