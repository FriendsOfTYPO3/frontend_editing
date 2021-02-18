import React from 'react';
import LoadingScreenHtml from './LoadingScreen.html';
import LoadingScreenWrapper from './LoadingScreenWrapper';

const Template = (args) => {
    return (
        <LoadingScreenWrapper {...args}>
            <div dangerouslySetInnerHTML={{__html: LoadingScreenHtml}}/>
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
};

export const Hidden = Template.bind({});
Hidden.args = {
    className: 't3-frontend-editing__loading-screen',
};

export const Visible = Template.bind({});
Visible.args = {
    className: 't3-frontend-editing__loading-screen',
    visible: true,
};
