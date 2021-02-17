import React from 'react';
import LoadingScreenHtml from './LoadingScreen.html';

const Template = (args) => {
    return (
        <div {...args} dangerouslySetInnerHTML={{__html: LoadingScreenHtml}}/>
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
    decorators: [Decorator],
};

export const normal = Template.bind({});
