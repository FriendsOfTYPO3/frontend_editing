import initStoryshots from '@storybook/addon-storyshots';
import {imageSnapshot} from '@storybook/addon-storyshots-puppeteer';

initStoryshots({
    configPath: './storybook/.storybook',
    test: imageSnapshot(),
});
