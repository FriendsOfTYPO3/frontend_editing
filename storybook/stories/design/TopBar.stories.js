import React from 'react';
import loadFragment from './loadFragments';

let fragments;

try {
    fragments = loadFragment(require.context('./TopBar', true, /\.html$/), './TopBar.html');
} catch (typeError) {
    //non webpack environment
    const path = './TopBar/TopBar.html';
    fragments = {
        cache: {[path]: require(path)},
        defaultKey: path
    };
}

const {
    cache: TopBars,
    defaultKey: defaultTopBar
} = fragments;

const Template = ({template, ...args}) => {
    let html = TopBars[template];
    if (!html) {
        html = TopBars[defaultTopBar];
        if (!html) {
            html = Object.values(TopBars)[0];
        }
    }
    return (
        <div dangerouslySetInnerHTML={{__html: html}}/>
    );
};

export default {
    title: 'Design/Bar/Top',
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
                options: Object.keys(TopBars),
            }
        },
    },
};

export const Default = Template.bind({});
