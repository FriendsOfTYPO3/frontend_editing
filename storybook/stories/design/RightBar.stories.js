import React from 'react';
import loadFragment from './loadFragments';

let fragments;

try {
    fragments = loadFragment(require.context('./RightBar', true, /\.(html|css)$/), './RightBar.html');
} catch (typeError) {
    //non webpack environment
    const path = './RightBar/RightBar.html';
    fragments = {
        cache: {[path]: require(path)},
        defaultKey: path,
        resources: {},
    };
}

const {
    cache: RightBar,
    defaultKey: defaultLeftBar
} = fragments;

const cssFileExtension = '.html';
const Template = ({template, ...args}) => {
    let html = RightBar[template];
    if (!html) {
        html = RightBar[defaultLeftBar];
        if (!html) {
            html = Object.values(RightBar)[0];
        }
    }
    if (Array.isArray(fragments.resources[template])) {
        fragments.resources[template].forEach(key => {
            if (key.slice(-cssFileExtension.length) === cssFileExtension) {
                (async () => {
                    key = key.slice('./'.length, -cssFileExtension.length);
                    await import('./RightBar/' + key + '.css');
                })();
            }
        });
    }
    return (
        <div dangerouslySetInnerHTML={{__html: html}}/>
    );
};

export default {
    title: 'Design/Bar/Right',
    parameters: {
        layout: 'fullscreen',
        docs: {
            description: {
                component: 'Server generated template as design only element with simple wrapper',
            },
            inlineStories: false,
        },
    },
    argTypes: {
        template: {
            control: {
                type: 'select',
                options: Object.keys(RightBar),
            }
        },
    },
};

export const Default = Template.bind({});
