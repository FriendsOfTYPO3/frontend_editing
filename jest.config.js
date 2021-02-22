module.exports = {
    'rootDir': './storybook/stories',
    'testRegex': '((\\.|/)(test|spec))\\.(jsx?|tsx?)$',

    // 'modulePaths': ['/shared/vendor/modules'],
    'moduleFileExtensions': ['js', 'jsx', 'json'],
    // 'moduleDirectories': ['node_modules'],
    'moduleNameMapper': {
        '\\.(css|sass|scss)$': '<rootDir>/__mocks__/style.js',
        '\\.(html)$': '<rootDir>/__mocks__/file.js',

        '^TYPO3/CMS/FrontendEditing(.*)$': '<rootDir>/../../Resources/Public/JavaScript$1',
    },
    'transformIgnorePatterns': ['/node_modules/', '/Resources/Public/']
};
