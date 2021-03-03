import React from 'react';
import {DivTranslator, ListTranslator} from './TranslatorWrapper';

import translations from './locallang';
import './Translator.css';

export default {
    title: 'Utils/Translator',
    component: DivTranslator,
    argTypes: {
        onError: {action: 'error'},
        parameters: {control: {type: 'array'}},
    },
};

const Template = ({translate, ...args}) => (<DivTranslator {...args} >{translate}</DivTranslator>);
const ListTemplate = ({namespace, ...args}) => {
    if (namespace === 'All') {
        // return Object.keys(translations.namespaces)
        //     .map(namespace => (<ListTemplate {...args} namespace={namespace} />));
        namespace = '';
    }
    return (
        <ListTranslator
            {...args}
            namespace={namespace}
        />
    );
};

export const Single = Template.bind({});
Single.argTypes = {
    translate: {
        control: {
            type: 'select',
            options: Object.keys(translations.raw),
        }
    },
};
Single.args = {
    translate: Object.keys(translations.raw)[0],
};

export const Namespace = ListTemplate.bind({});
Namespace.argTypes = {
    namespace: {
        control: {
            type: 'select',
            options: ['All', ...Object.keys(translations.namespaces)],
        }
    },
};
Namespace.args = {
    namespace: 'All',
    parameters: [
        'titleOrMessageOrSomethingElse',
        'maybeOrMaybeNot',
    ]
};

export const Configure = ListTemplate.bind({});
Configure.argTypes = Namespace.argTypes;
Configure.argTypes.mergeStrategy = {
    control: {
        type: 'inline-radio',
        options: ['none', 'merge', 'mergeDeep', 'override'],
    }
};
Configure.args = {
    parameters: Namespace.args.parameters,
    namespace: 'modal',
    translationLabels: {
        'title.navigate': 'Navigate Titel override',
        'button.save_navigate': 'Save and Navigate',
    },
    namespaceMapping: {
        modal: {
            saveLabel: 'button.save_navigate',
        }
    },
    mergeStrategy: 'none',
};
