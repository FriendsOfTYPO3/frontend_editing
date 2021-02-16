import React, {Fragment, useEffect, useRef, forwardRef} from 'react';
import PropTypes from 'prop-types';

import ScrollArea from '../design/ScrollArea';

export const withScroller = (EmbeddedElement) => {
    return ({speed, onError, ...rest}) => {
        const scrollElement = useRef(null);
        const scrollTopElement = useRef(null);
        const scrollBottomElement = useRef(null);

        useEffect(() => {
            let currentScrollTopEl = scrollTopElement.current;
            let currentScrollBottomEl = scrollBottomElement.current;

            let scroller = null;

            function startScrollingTop () {
                if (scroller !== null) {
                    scroller.startScrolling(-speed);
                }
            }

            function startScrollingBottom () {
                if (scroller !== null) {
                    scroller.startScrolling(speed);
                }
            }

            function stopScrolling () {
                if (scroller !== null) {
                    scroller.stopScrolling();
                }
            }

            function reloadScroller () {
                if (scroller !== null) {
                    try {
                        scroller.reload();
                    } catch (scrollerError) {
                        onError(scrollerError.toString());
                    }
                }
            }

            import('TYPO3/CMS/FrontendEditing/Scroller').then(({default: Scroller}) => {
                try {
                    scroller = Scroller(
                        scrollElement.current,
                        currentScrollTopEl,
                        currentScrollBottomEl
                    );
                    scroller.enable();
                } catch (scrollerError) {
                    onError(scrollerError.toString());
                }
            });

            scrollElement.current.addEventListener('load', reloadScroller);
            const resizeObserver = new ResizeObserver(reloadScroller);
            resizeObserver.observe(scrollElement.current);

            currentScrollTopEl.addEventListener('mouseleave', stopScrolling);
            currentScrollTopEl.addEventListener('mouseenter', startScrollingTop);
            currentScrollBottomEl.addEventListener('mouseleave', stopScrolling);
            currentScrollBottomEl.addEventListener('mouseenter', startScrollingBottom);

            return function () {
                scrollElement.current.removeEventListener('load', reloadScroller);
                resizeObserver.unobserve(scrollElement.current);

                currentScrollTopEl.removeEventListener('mouseleave', stopScrolling);
                currentScrollTopEl.removeEventListener('mouseenter', startScrollingTop);
                currentScrollBottomEl.removeEventListener('mouseleave', stopScrolling);
                currentScrollBottomEl.removeEventListener('mouseenter', startScrollingBottom);

                if (scroller !== null) {
                    scroller.disable();
                }
            };
        });

        return (
            <Fragment>
                <EmbeddedElement ref={scrollElement} {...rest}/>
                <ScrollArea ref={scrollTopElement} />
                <ScrollArea ref={scrollBottomElement} place="bottom" />
            </Fragment>
        );
    };
};

export const ScrollableDiv = forwardRef(({children}, ref) => {
    return (
        <div ref={ref} style={{
            height: 'calc(100vh - 4rem)',
            overflow: 'scroll'
        }}>
            <div className="wrapper">
                {children}
            </div>
        </div>
    );
});

export const DivScroller = withScroller(ScrollableDiv);

DivScroller.propTypes = {
    speed: PropTypes.number,
};

export const IframeScroller = withScroller(forwardRef((props, ref) => {
    let url = props.url;
    if (!url) {
        useEffect(() => {
            ref.current.contentDocument.body.style.height = '300vh';
        });
        url = 'about:blank';
    }
    return (
        <iframe ref={ref} style={{
            height: 'calc(100vh - 4rem)',
            width: '100%',
            border: 0,
        }} src={url}/>
    );
}));
IframeScroller.propTypes = {
    speed: PropTypes.number,
    onError: PropTypes.func
};


