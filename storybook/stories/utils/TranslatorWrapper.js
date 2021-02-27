import React, {Fragment, useEffect, useState} from 'react';
import {compareObjects} from "./utils";

export const withTranslator = (EmbeddedElement, translateProp, placeHolder = '') => {
    return ({onError, parameters = [], ...rest}) => {
        const [translation, setTranslation] = useState(placeHolder);

        let key;
        if (translateProp) {
            key = rest[translateProp];
            rest[translateProp] = translation;
        } else {
            key = rest.children;
        }

        useEffect(() => {
            let translator = null;

            import('TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader').then(({default: factory}) => {
                try {
                    translator = factory.getTranslator();
                    setTranslation(translator.translate(key, parameters));
                } catch (translationError) {
                    onError(translationError.toString());
                }
            });
        });

        return (
            <EmbeddedElement {...rest} >
                {translateProp ? rest.children : translation}
            </EmbeddedElement>
        );
    };
};
export const withNamespaceMapping = (EmbeddedElement) => {
    return class NamespaceMappingWrapper extends React.Component {
        constructor (props) {
            super(props);
            this.state = {
                contexts: {},
                mapping: {}
            };

            this.initFactory = this.initFactory.bind(this);
            this.useTranslator = this.useTranslator.bind(this);
            this.setContexts = this.setContexts.bind(this);
        }

        setContexts (namespace, context) {
            this.setState({
                contexts: {
                    ...this.state.contexts,
                    [namespace]: context
                }
            });
        }

        initFactory ({default: factory}) {
            if (!this.mounted) {
                // component seems to be unmounted
                return;
            }

            this.factory = factory;

            this.loadTranslator();
        }

        loadTranslator () {
            const {translationLabels, namespaceMapping, namespace, onError} = this.props;
            const configuration = {
                translationLabels,
                namespaceMapping
            };

            this.factory.configure(configuration, 'override');

            if (!this.state.contexts[namespace]) {
                if (onError) {
                    try {
                        this.useTranslator(configuration);
                    } catch (translationError) {
                        onError(translationError.toString());
                    }
                } else {
                    this.useTranslator(configuration);
                }
            }
        }

        useTranslator (configuration) {
            const {namespace} = this.props;
            this.setContexts(namespace,
                this.factory.useTranslator(namespace, (translator, initial) => {
                    if (!initial) {
                        if (this.props.namespace === namespace) {
                            if (!compareObjects(this.state.mapping, translator.getKeys())) {
                                this.setState({
                                    mapping: translator.getKeys()
                                });
                            }
                        }
                    }
                })
            );
        }

        componentDidMount () {
            this.mounted = true;
            import('TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader')
                .then(this.initFactory);
        }

        componentDidUpdate () {
            if (this.factory) {
                this.loadTranslator();
            }
        }

        componentWillUnmount () {
            this.mounted = false;
            Object.keys(this.state.contexts)
                .forEach(namespace => {
                    this.state.contexts[namespace].unregister();
                });
            this.setState({
                contexts: null
            });
        }

        render () {
            const {
                onError, namespace,
                translationLabels, namespaceMapping,
                ...rest
            } = this.props;

            return (
                <EmbeddedElement
                    {...rest}
                    onError={onError}
                    namespace={namespace}
                    namespaceMapping={this.state.mapping}>
                </EmbeddedElement>
            );
        }
    };
};

export const DivTranslator = ({children, ...rest}) => {
    let mapper = children;
    if (Array.isArray(children)) {
        if (children[0] === children[1]) {
            mapper = children[1];
        } else {
            mapper = children[0] + ' (' + children[1] + ')';
        }
        children = children[1];
    }
    return (
        <div className="translator-div-wrapper">
            <strong>{mapper}:</strong><br/>
            <span><FragmentTranslator {...rest}>{children}</FragmentTranslator></span>
        </div>
    );
};

export const ListTranslator = withNamespaceMapping(({namespace, namespaceMapping, keys, ...rest}) => {
    return (
        <div className="translator-table-wrapper">
            <h4>{namespace}</h4>
            <table>
                <thead>
                    <tr>
                        <th>namespace key</th>
                        <th>translation key</th>
                        <th>translation</th>
                    </tr>
                </thead>
                <tbody>
                    {Object.entries(namespaceMapping)
                        .map( ([name, key]) => (
                            <tr key={key}>
                                <td>{name}</td>
                                <td>{key}</td>
                                <td><FragmentTranslator {...rest}>{key}</FragmentTranslator></td>
                            </tr>
                        ))
                    }
                </tbody>
            </table>
        </div>
    );
});

export const FragmentTranslator = withTranslator(Fragment);

export default FragmentTranslator;
