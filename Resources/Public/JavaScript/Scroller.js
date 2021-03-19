/*
* This file is part of the TYPO3 CMS project.
*
* It is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License, either version 2
* of the License, or any later version.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*
* The TYPO3 project - inspiring people to share!
*/

/**
 * Module: TYPO3/CMS/FrontendEditing/Scroller
 * FrontendEditing.Scroller: Controller for basic scrolling
 */
define(['jquery', './Utils/Logger'], function createScrollerModule ($, Logger) {
    'use strict';

    var log = Logger('FEditing:Utils:Scroller');
    log.trace('--> createScrollerModule');

    var framesPerSecond = 50;
    /*eslint-disable-next-line no-magic-numbers*/
    var updateRate = 1000 / framesPerSecond;

    return function Scroller (target, scrollAreaTop, scrollAreaBottom) {
        log.info('create Scroller');

        var $target = $(target);
        var $scrollTarget = $target;
        var isScrollTargetDocument = $target.is('iframe');
        var $scrollAreaTop = $(scrollAreaTop);
        var $scrollAreaBottom = $(scrollAreaBottom);

        // Listening
        var enabled = false;
        // Scrolling
        var scrolling = false;
        var timeoutId = -1;
        var speed = 0;

        function stopScrolling () {
            log.info('create Scroller');

            scrolling = false;
            if (timeoutId >= 0) {
                window.clearTimeout(timeoutId);
                timeoutId = -1;
            }
        }

        function startScrolling (newSpeed) {
            log.trace('startScrolling', newSpeed);

            if (!enabled) {
                return;
            }
            stopScrolling();
            speed = newSpeed;

            if (speed !== 0) {
                log.debug('start Scrolling');

                var checkFnc;
                var scrollY = $scrollTarget.scrollTop();
                if (speed < 0) {
                    checkFnc = checkScrollUpBound;
                    checkScrollDownBound(scrollY + speed, true);
                } else {
                    checkFnc = checkScrollDownBound;
                    checkScrollUpBound(scrollY + speed, true);
                }

                scrolling = true;
                //share checkFnc variable
                var scroll = function () {
                    if (scrolling) {
                        var scrollY = $scrollTarget.scrollTop() + speed;
                        log.debug('estimated scroll position', scrollY, speed);
                        scrollY = checkFnc(scrollY);
                        log.trace('new scroll position', scrollY, speed);
                        $scrollTarget.scrollTop(scrollY);
                        timeoutId = -1;
                        if (scrolling) {
                            timeoutId = window.setTimeout(scroll, updateRate);
                        }
                    }
                };
                scroll();

            }
        }

        function checkScrollUpBound (scrollY) {
            log.trace('checkScrollUpBound', scrollY);

            if (scrollY <= 0) {
                log.debug('up bound reached', scrollY);

                $scrollAreaTop.hide();
                scrollY = 0;
                scrolling = false;
            } else {
                $scrollAreaTop.show();
            }
            return scrollY;
        }

        function checkScrollDownBound (scrollY) {
            log.trace('checkScrollDownBound', scrollY);

            var maxScroll = getMaxScroll();
            if (scrollY >= maxScroll) {
                log.debug(
                    'down bound reached [scrollY, maxScroll]',
                    scrollY,
                    maxScroll
                );

                $scrollAreaBottom.hide();
                scrollY = maxScroll;
                scrolling = false;
            } else {
                $scrollAreaBottom.show();
            }
            return scrollY;
        }

        function enable () {
            log.info('enable');

            enabled = true;
            scrolling = false;

            reload();
        }

        function disable () {
            log.info('disable');

            enabled = false;
            stopScrolling();
            $scrollAreaBottom.hide();
            $scrollAreaTop.hide();
        }

        function reload () {
            log.debug('reload');

            if (isScrollTargetDocument) {
                $scrollTarget = $target.contents();
                if (!$scrollTarget || $scrollTarget.length === 0) {
                    // seems more to be a restriction error
                    // but as fact there is no reference of document
                    throw new ReferenceError(
                        'Unable to access the document of the iframe.'
                    );
                }
            }
            if (getMaxScroll() <= 0) {
                $scrollAreaBottom.hide();
                $scrollAreaTop.hide();
            } else  if (enabled) {
                var scrollY = $scrollTarget.scrollTop();
                checkScrollUpBound(scrollY);
                checkScrollDownBound(scrollY);
            }
        }

        function getMaxScroll () {
            var scrollHeight;
            if (isScrollTargetDocument) {
                scrollHeight = $scrollTarget.height();
            } else {
                scrollHeight = $target[0].scrollHeight;
            }
            var maxScroll = scrollHeight - $target[0].clientHeight;
            // round max scroll up so it match the height
            // eslint-disable-next-line no-magic-numbers
            var maxScroll = Math.round(maxScroll + 0.499);

            log.trace('getMaxScroll', maxScroll);

            return maxScroll;
        }

        return {
            reload: reload,
            enable: enable,
            disable: disable,
            startScrolling: startScrolling,
            stopScrolling: stopScrolling
        };
    };
});
