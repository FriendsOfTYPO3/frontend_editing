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
    return ({onError, namespace, ...rest}) => {
        const [translators, setTranslators] = useState({});

        useEffect(() => {
            if (!translators[namespace]) {
                import('TYPO3/CMS/FrontendEditing/Utils/TranslatorLoader').then(({default: factory}) => {
                    try {
                        let translator = factory.getTranslator(namespace);
                        setTranslators({
                            ...namespaceMapping,
                            [namespace]: translator
                        });
                    } catch (translationError) {
                        onError(translationError.toString());
                    }
                });
            }
        });

        let namespaceMapping = {};
        if (translators[namespace]) {
            namespaceMapping = translators[namespace].getKeys();
        }

        return (
            <EmbeddedElement {...rest} namespace={namespace} namespaceMapping={namespaceMapping}>
            </EmbeddedElement>
        );
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

export const ListTranslator = withNamespaceMapping(({namespace, namespaceMapping, ...rest}) => {
    return (
        <div className="translator-table-wrapper">
            <h4>{namespace}</h4>
            <table>
                <tr>
                    <th>namespace key</th>
                    <th>translation key</th>
                    <th>translation</th>
                </tr>
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
