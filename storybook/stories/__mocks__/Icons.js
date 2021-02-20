/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with DocumentHeader source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

define(['jquery'], function ($) {

    var Icons = {
        cache: {
            'actions-close_small__default_inline': `
<span class="t3js-icon icon icon-size-small icon-state-default icon-actions-close" data-identifier="actions-close">
	<span class="icon-markup">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><g class="icon-color"><path d="M11.9 5.5L9.4 8l2.5 2.5c.2.2.2.5 0 .7l-.7.7c-.2.2-.5.2-.7 0L8 9.4l-2.5 2.5c-.2.2-.5.2-.7 0l-.7-.7c-.2-.2-.2-.5 0-.7L6.6 8 4.1 5.5c-.2-.2-.2-.5 0-.7l.7-.7c.2-.2.5-.2.7 0L8 6.6l2.5-2.5c.2-.2.5-.2.7 0l.7.7c.2.2.2.5 0 .7z"/></g></svg>
	</span>
</span>`
        },
        sizes: {
            small: 'small',
            default: 'default',
            large: 'large',
            overlay: 'overlay'
        },
        states: {
            default: 'default',
            disabled: 'disabled'
        },
        markupIdentifiers: {
            default: 'default',
            inline: 'inline'
        }
    };

    /**
     * Get the icon by its identifier.
     *
     * @param {String} identifier
     * @param {String} size
     * @param {String} overlayIdentifier
     * @param {String} state
     * @param {String} markupIdentifier
     * @return {Promise<Array>}
     */
    Icons.getIcon = function (identifier, size, overlayIdentifier, state, markupIdentifier) {
        return $.when(Icons.fetch(identifier, size, overlayIdentifier, state, markupIdentifier));
    };

    /**
     * Performs the AJAX request to fetch the icon.
     *
     * @param {string} identifier
     * @param {string} size
     * @param {string} overlayIdentifier
     * @param {string} state
     * @param {string} markupIdentifier
     * @return {String|Promise}
     * @private
     */
    Icons.fetch = function (identifier, size, overlayIdentifier, state, markupIdentifier) {
        /**
         * Icon keys:
         *
         * 0: identifier
         * 1: size
         * 2: overlayIdentifier
         * 3: state
         * 4: markupIdentifier
         */
        size = size || Icons.sizes.default;
        state = state || Icons.states.default;
        markupIdentifier = markupIdentifier || Icons.markupIdentifiers.default;

        var icon = [identifier, size, overlayIdentifier, state, markupIdentifier],
            cacheIdentifier = icon.join('_');

        return Icons.getFromCache(cacheIdentifier);
    };


    /**
     * Check whether icon was fetched already
     *
     * @param {String} cacheIdentifier
     * @returns {Boolean}
     * @private
     */
    Icons.isCached = function (cacheIdentifier) {
        return typeof Icons.cache[cacheIdentifier] !== 'undefined';
    };

    /**
     * Get icon from cache
     *
     * @param {String} cacheIdentifier
     * @returns {String}
     * @private
     */
    Icons.getFromCache = function (cacheIdentifier) {
        return Icons.cache[cacheIdentifier];
    };

    // attach to global frame
    TYPO3.Icons = Icons;

    return Icons;
});
