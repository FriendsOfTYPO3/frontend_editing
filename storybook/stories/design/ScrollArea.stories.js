import React from 'react';
import ScrollArea from './ScrollArea';

export default {
    title: 'Design/ScrollArea',
    component: ScrollArea,
    parameters: {
        docs: {
            description: {
                component: 'Created with react because it get created on server by a function',
            },
        },
    },
    argTypes: {
        backgroundColor: {
            control: {
                type: 'color',
            },
        },
        arrow: {
            control: {
                type: 'boolean',
            },
        },
    },
};

const Template = ({html, backgroundColor, ...args}) => (
    <div style={{
        contain: 'content',
        position: 'relative',
        minHeight: '200px',
        background: backgroundColor ? backgroundColor : 'transparent'
    }}>
        {html? (<div dangerouslySetInnerHTML={{__html: html}} />): ''}
        <ScrollArea {...args} />
    </div>
);

export const simple = Template.bind({});
simple.args = {
    place: 'top',
    style: {}
};

export const content = Template.bind({});
content.args = {
    place: 'top',
    html: '<strong>HTML</strong> Content',
    style: {},
    backgroundColor: 'white',
};

