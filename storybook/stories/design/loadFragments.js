const templateExtension = '.html';

/**
 * Used to load HTML fragment variants generated by fluid
 * @param context the webpack require context used to
 * @param defaultKey the preferred fragment defaultKey
 * @returns {{defaultKey: string, cache: {}}}
 */
export const loadFragments = (context, defaultKey) => {
    const cache = {};
    const resources = {};

    context.keys()
        .forEach(key => {
            if (key.slice(-templateExtension.length) === templateExtension) {
                cache[key] = context(key);
            } else {
                var templateKey = key.substring(0, key.lastIndexOf('.')) + templateExtension;
                if (!resources[templateKey]) {
                    resources[templateKey] = [];
                }
                resources[templateKey].push(templateKey);
            }
        });


    // check if defaultKey exists and reset if not
    if (!cache[defaultKey]) {
        defaultKey = Object.keys(cache)[0];
    }

    return {
        defaultKey,
        cache,
        resources,
    };
};

export default loadFragments;
