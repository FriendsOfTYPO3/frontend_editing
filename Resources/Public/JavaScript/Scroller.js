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
 * FrontendEditing.Scroller: Controller for basic scrolling
 */
define(['jquery'], function ScrollerModule ($) {
    'use strict';

    var framesPerSecond = 50;
    /*eslint-disable-next-line no-magic-numbers*/
    var updateRate = 1000 / framesPerSecond;

    return function Scroller (target, scrollAreaTop, scrollAreaBottom) {
        var $target = $(target);
        var $scrollAreaTop = $(scrollAreaTop);
        var $scrollAreaBottom = $(scrollAreaBottom);

        // Listening
        var enabled = false;
        // Scrolling
        var scrolling = false;
        var timeoutId = -1;
        var speed = 0;

        function stopScrolling () {
            scrolling = false;
            if (timeoutId >= 0) {
                window.clearTimeout(timeoutId);
            }
        }

        function startScrolling (newSpeed) {
            if (!enabled) {
                return;
            }
            stopScrolling();
            speed = newSpeed;

            if (speed !== 0) {
                var checkFnc;
                var contents = $target.contents();
                var scrollY = contents.scrollTop();
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
                        var $contents = $target.contents();
                        var scrollY = $contents.scrollTop() + speed;
                        scrollY = checkFnc(scrollY);
                        if (scrolling) {
                            $contents.scrollTop(scrollY);
                            timeoutId = setTimeout(scroll, updateRate);
                        }
                    }
                };
                scroll();

            }
        }

        function checkScrollUpBound (scrollY) {
            if (scrollY <= 0) {
                $scrollAreaTop.hide();
                scrollY = 0;
                scrolling = false;
            } else {
                $scrollAreaTop.show();
            }
            return scrollY;
        }

        function checkScrollDownBound (scrollY) {
            var $contents = $target.contents();
            var maxScroll = $contents.height() - $target.height();
            if (scrollY >= maxScroll) {
                $scrollAreaBottom.hide();
                scrollY = maxScroll;
                scrolling = false;
            } else {
                $scrollAreaBottom.show();
            }
            return scrollY;
        }

        function enable () {
            enabled = true;
            scrolling = false;
            var $contents = $target.contents();
            var scrollY = $contents.scrollTop();
            checkScrollUpBound(scrollY);
            checkScrollDownBound(scrollY);
        }

        function disable () {
            enabled = false;
            stopScrolling();
            $scrollAreaBottom.hide();
            $scrollAreaTop.hide();
        }

        return {
            enable: enable,
            disable: disable,
            startScrolling: startScrolling,
            stopScrolling: stopScrolling
        };
    };
});
