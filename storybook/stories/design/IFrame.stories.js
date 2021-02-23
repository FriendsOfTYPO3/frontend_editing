import React from 'react';
import loadFragment from './loadFragments';

let fragments;

try {
    fragments = loadFragment(require.context('./IFrame', true, /\.html$/), './IFrame.html');
} catch (typeError) {
    //non webpack environment
    const path = './IFrame/IFrame.html';
    fragments = {
        cache: {[path]: require(path)},
        defaultKey: path
    };
}

const {
    cache: IFrame,
    defaultKey: defaultIFrame
} = fragments;

const Template = ({template, ...args}) => {
    let html = IFrame[template];
    if (!html) {
        html = IFrame[defaultIFrame];
        if (!html) {
            html = Object.values(IFrame)[0];
        }
    }
    return (
        <div dangerouslySetInnerHTML={{__html: html}}/>
    );
};

export default {
    title: 'Design/IFrame',
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
                options: Object.keys(IFrame),
            }
        },
    },
};

export const Default = Template.bind({});
