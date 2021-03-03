import React, {Fragment, useEffect, useState} from 'react';

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
            let unregister = null;

            import('TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader').then(({default: factory}) => {
                unregister = factory.useTranslator(null, translator => {
                    try {
                        setTranslation(translator.translate(key, parameters));
                    } catch (translationError) {
                        setTranslation('');
                        onError(translationError.toString());
                    }
                }).unregister;
            });
            return () => {
                if (unregister) {
                    unregister();
                }
            };
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
            this.contexts = {};
            this.state = {
                mapping: {}
            };

            this.initFactory = this.initFactory.bind(this);
            this.useTranslator = this.useTranslator.bind(this);
            this.unmountContexts = this.unmountContexts.bind(this);
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
            const {translationLabels, namespaceMapping, namespace, onError, mergeStrategy} = this.props;
            const configuration = {
                translationLabels,
                namespaceMapping
            };

            this.factory.configure(configuration, mergeStrategy);

            const context = this.contexts[namespace];
            if (!context) {
                this.currentNamespace = namespace;
                if (onError) {
                    try {
                        this.useTranslator();
                    } catch (translationError) {
                        onError(translationError.toString());
                    }
                } else {
                    this.useTranslator();
                }
            } else if (this.currentNamespace !== namespace) {
                this.currentNamespace = namespace;
                this.unmountContexts();
                this.contexts[namespace] = context;
                this.setState({
                    mapping: context.translator.getKeys()
                });
            }
        }

        useTranslator () {
            if (this.mounted) {
                const {namespace} = this.props;
                function configureCallback (translator) {
                    if (this.mounted) {
                        if (this.props.namespace === namespace) {
                            this.setState({
                                mapping: translator.getKeys()
                            });
                        }
                    }
                }
                configureCallback = configureCallback.bind(this);
                this.unmountContexts();
                this.contexts[namespace] = this.factory.useTranslator(namespace, configureCallback);
            }
        }

        unmountContexts () {
            Object.keys(this.contexts)
                .forEach(namespace => {
                    this.contexts[namespace].unregister();
                });
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
            this.unmountContexts();
            this.contexts = null;
        }

        render () {
            const {
                onError, namespace,
                translationLabels, namespaceMapping, mergeStrategy,
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
