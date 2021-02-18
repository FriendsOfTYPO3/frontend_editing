import React from 'react';
import LoadingScreenWrapper from './LoadingScreenWrapper';
import * as LoadingScreenStories from '../design/LoadingScreen.stories';

const Template = (args) => {
    return (
        <LoadingScreenWrapper {...args}>
            <LoadingScreenStories.Hidden />
        </LoadingScreenWrapper>
    );
};

export default {
    title: 'Elements/LoadingScreen',
    component: LoadingScreenWrapper,
    parameters: {
        layout: 'fullscreen',
    }
};

export const Default = Template.bind({});
