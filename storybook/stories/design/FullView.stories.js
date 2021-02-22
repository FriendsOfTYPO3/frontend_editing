import React from 'react';
import FullViewTemplate from './FullView.html';

const Template = ({...args}) => {
    return (
        <div dangerouslySetInnerHTML={{__html: FullViewTemplate}}/>
    );
};

export default {
    title: 'Design/FullView',
    parameters: {
        layout: 'fullscreen',
        docs: {
            description: {
                component: 'Server generated template as design only element with simple wrapper',
            },
            inlineStories: false,
        },
    },
};

export const Default = Template.bind({});
