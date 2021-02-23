import React from 'react';
import NotificationWrapper, {Severity} from './NotificationWrapper';

const Template = (args) => (<NotificationWrapper {...args}/>);

export default {
    title: 'Elements/Notification',
    component: NotificationWrapper,
    parameters: {
        layout: 'fullscreen',
        storyshots: {disable: true},
    },
    argTypes: {
        severity: {
            control: {
                type: 'inline-radio',
                options: Severity,
            }
        },
    }
};

export const Default = Template.bind({});
Default.args = {
    title: 'There is nothing to see.',
    message: 'Some simple body message to show.<br/>Nothing special at all, so move along.'
};

export const Warning = Template.bind({});
Warning.args = {
    title: 'Something happend.',
    message: 'To be or not to be that shall be the question.',
    severity: 'warning',
};

export const Error = Template.bind({});
Error.args = {
    title: 'Something smell wrong.',
    message: 'Shit happens every single day, from every alive person.',
    severity: 'error',
};
