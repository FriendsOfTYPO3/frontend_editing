import React from 'react';
import LoadingScreenWrapper from './LoadingScreenWrapper';

import loadFragment from './loadFragments';

let fragments;

try {
    fragments = loadFragment(require.context('./LoadingScreen', true, /\.html$/), './LoadingScreen.html');
} catch (typeError) {
    //non webpack environment
    const path = './LoadingScreen/LoadingScreen.html';
    fragments = {
        cache: {[path]: require(path)},
        defaultKey: path
    };
}

const {
    cache: loadingScreens,
    defaultKey: defaultLoadingScreen
} = fragments;

const Template = ({children, ...args}) => {
    let html = loadingScreens[children];
    if (!html) {
        html = loadingScreens[defaultLoadingScreen];
        if (!html) {
            html = Object.values(loadingScreens)[0];
        }
    }
    return (
        <LoadingScreenWrapper {...args}>
            <div dangerouslySetInnerHTML={{__html: html}}/>
        </LoadingScreenWrapper>
    );
};

const Decorator = (Story) => (
    <div style={{
        position: 'fixed',
        top: 0,
        bottom: 0,
        left: 0,
        right: 0,
    }}>
        <Story/>
    </div>
);

export default {
    title: 'Design/LoadingScreen',
    component: LoadingScreenWrapper,
    decorators: [Decorator],
    parameters: {
        docs: {
            description: {
                component: 'Server generated template as design only element with simple wrapper',
            },
            inlineStories: false,
        },
    },
    argTypes: {
        children: {
            control: {
                type: 'select',
                options: Object.keys(loadingScreens),
            }
        },
    },
};

export const Hidden = Template.bind({});
Hidden.args = {
    className: 't3-frontend-editing__loading-screen',
    children: defaultLoadingScreen
};

export const Visible = Template.bind({});
Visible.args = {
    className: 't3-frontend-editing__loading-screen',
    visible: true,
    children: defaultLoadingScreen
};
