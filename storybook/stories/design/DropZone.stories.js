import React from 'react';
import DropZone, {DropZoneEnabled, DropZoneStates} from './DropZone';

const Template = ({state, ...args}) => {
    if (state) {
        state = state.reduce((tot, state) => (tot | DropZoneStates[state]), 0);
    }
    return (
        <DropZone state={state} {...args} />
    );
};

export const DropzoneEnableDecorator = (Story) => (
    <DropZoneEnabled><Story/></DropZoneEnabled>
);

const DropzoneBeforeAfterDecorator = (Story) => (
    <div>
        <div>Before Dropzone</div>
        <Story/>
        <div>After Dropzone</div>
    </div>
);

export default {
    title: 'Design/Dropzone',
    excludeStories: ['DropzoneEnableDecorator'],
    decorators: [DropzoneBeforeAfterDecorator],
    parameters: {
        docs: {
            description: {
                component: 'Created with react because it get created on server by a function',
            },
        },
    },
    argTypes: {
        state: {
            control: {
                type: 'multi-select',
                options: Object.keys(DropZoneStates),
            }
        },
    }
};

export const disabled = Template.bind({});

export const enabled = Template.bind({});
enabled.decorators = [DropzoneEnableDecorator];

export const enabledActive = Template.bind({});
enabledActive.decorators = enabled.decorators;
enabledActive.args = {
    state: ['active'],
};

export const enabledHidden = Template.bind({});
enabledHidden.decorators = enabled.decorators;
enabledHidden.args = {
    state: ['hidden'],
};

