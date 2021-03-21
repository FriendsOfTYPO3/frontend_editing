import React from 'react';
import ModalWrapper, {Type} from './ModalWrapper';

const Template = ({message, messageElement, ...args}) => {
    if (messageElement) {
        message = createContentElement(message, messageElement);
    }
    return (
        <ModalWrapper message={message} {...args}/>
    );
};

export default {
    title: 'Elements/Modal',
    component: ModalWrapper,
    parameters: {
        layout: 'fullscreen',
        storyshots: {disable: true},
    },
    argTypes: {
        onError: {action: 'error'},
        onDismiss: {action: 'modal-dismiss'},
        type: {
            control: {
                type: 'inline-radio',
                options: Type,
            }
        },
        messageElement: {control: {type: 'text'}},
    },
};

export const Default = Template.bind({});
Default.args = {
    message: 'Some simple question to answer here.'
};

function createContentElement (content, type) {
    if (!type) {
        type = 'p';
    }
    let contentElement = document.createElement(type);
    contentElement.innerHTML = content;
    return contentElement;
}

export const ConfirmNavigate = Template.bind({});
ConfirmNavigate.args = {
    message: 'Shit could happen and you would lose your data.<br/>So it is up to you :P',
    messageElement: 'p',
    type: 'confirmNavigate',
};

export const Warning = Template.bind({});
Warning.args = {
    message: '<p>Record tt_content is locked or something</p><p style="color: #8c8c8c;">So please contact your administrator if it is not you.</p>',
    messageElement: 'p',
    type: 'warning',
};

export const VariableNotDefined = Template.bind({});
VariableNotDefined.args = {
    type: 'variable_not_defined',
};

export const VariableNotFunction = Template.bind({});
VariableNotFunction.args = {
    type: 'variable_not_function',
};

export const VariableNotInteger = Template.bind({});
VariableNotInteger.args = {
    type: 'variable_not_integer',
};
