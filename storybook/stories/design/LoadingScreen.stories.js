import React from 'react';
import LoadingScreenWrapper from './LoadingScreenWrapper';

import loadFragment from './loadFragments';

const {
    cache: loadingScreens,
    defaultKey: defaultLoadingScreen
} = loadFragment(require.context('./LoadingScreen', true, /\.html$/));

const Template = ({children, ...args}) => {
    return (
        <LoadingScreenWrapper {...args}>
            <div dangerouslySetInnerHTML={{__html: loadingScreens[children]}}/>
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
