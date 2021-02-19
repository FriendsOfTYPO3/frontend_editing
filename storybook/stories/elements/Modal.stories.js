import React from 'react';
import ModalWrapper, {Type} from './ModalWrapper';

const Template = (args) => (<ModalWrapper {...args}/>);

export default {
    title: 'Elements/Modal',
    component: ModalWrapper,
    parameters: {
        layout: 'fullscreen',
        storyshots: {disable: true},
    },
    argTypes: {
        onDismiss: {action: 'modal-dismiss'},
        type: {
            control: {
                type: 'inline-radio',
                options: Type,
            }
        },
    }
};

export const Default = Template.bind({});
Default.args = {
    message: 'Some simple question to answer here.'
};
Default.parameters = {
    async puppeteerTest (page) {
        const element = await page.$('.t3js-modal');
    },
};

export const ConfirmNavigate = Template.bind({});
ConfirmNavigate.args = {
    message: 'Shit could happen and you would lose your data. So it is up to you :P',
    type: 'confirmNavigate',
};

export const Warning = Template.bind({});
Warning.args = {
    message: 'Record tt_content is locked or something. So please contact your administrator if it is not you.',
    type: 'warning',
};
