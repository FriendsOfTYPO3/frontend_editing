import React from 'react';
import loadFragment from './loadFragments';

let fragments;

try {
    fragments = loadFragment(require.context('./EditorBar', true, /\.html$/), './EditorBar.html');
} catch (typeError) {
    //non webpack environment
    const path = './EditorBar/EditorBar.html';
    fragments = {
        cache: {[path]: require(path)},
        defaultKey: path
    };
}

const {
    cache: EditorBars,
    defaultKey: defaultEditorBar
} = fragments;

const Template = ({template, ...args}) => {
    let html = EditorBars[template];
    if (!html) {
        html = EditorBars[defaultEditorBar];
        if (!html) {
            html = Object.values(EditorBars)[0];
        }
    }
    return (
        <div dangerouslySetInnerHTML={{__html: html}}/>
    );
};

export default {
    title: 'Design/Bar/Editor',
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
                options: Object.keys(EditorBars),
            }
        },
    },
};

export const Default = Template.bind({});
