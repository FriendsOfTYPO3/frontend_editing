import React from 'react';
import loadFragment from './loadFragments';

let fragments;

try {
    fragments = loadFragment(require.context('./LeftBar', true, /\.html$/), './LeftBar.html');
} catch (typeError) {
    //non webpack environment
    const path = './LeftBar/LeftBar.html';
    fragments = {
        cache: {[path]: require(path)},
        defaultKey: path
    };
}

const {
    cache: LeftBars,
    defaultKey: defaultLeftBar
} = fragments;

const Template = ({template, ...args}) => {
    let html = LeftBars[template];
    if (!html) {
        html = LeftBars[defaultLeftBar];
        if (!html) {
            html = Object.values(LeftBars)[0];
        }
    }
    return (
        <div dangerouslySetInnerHTML={{__html: html}}/>
    );
};

export default {
    title: 'Design/Bar/Left',
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
                options: Object.keys(LeftBars),
            }
        },
    },
};

export const Default = Template.bind({});
