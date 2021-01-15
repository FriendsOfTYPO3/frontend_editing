import React, {Fragment, useEffect, useRef, forwardRef} from 'react';
import PropTypes from 'prop-types';

import './Scroller.css';

export const withScroller = (EmbeddedElement) => {
    return ({speed, ...rest}) => {
        const scrollElement = useRef(null);
        const scrollTopElement = useRef(null);
        const scrollBottomElement = useRef(null);

        useEffect(() => {
            let currentScrollTopEl = scrollTopElement.current;
            let currentScrollBottomEl = scrollBottomElement.current;

            let scroller = null;

            function startScrollingTop() {
                scroller.startScrolling(-speed);
            }

            function startScrollingBottom() {
                scroller.startScrolling(speed);
            }

            import('TYPO3/CMS/FrontendEditing/Scroller').then(({default: Scroller}) => {
                scroller = Scroller(
                    scrollElement.current,
                    currentScrollTopEl,
                    currentScrollBottomEl
                );

                currentScrollTopEl.addEventListener('mouseleave', scroller.stopScrolling);
                currentScrollTopEl.addEventListener('mouseenter', startScrollingTop);
                currentScrollBottomEl.addEventListener('mouseleave', scroller.stopScrolling);
                currentScrollBottomEl.addEventListener('mouseenter', startScrollingBottom);

                scroller.enable();
            });

            return function () {
                if (scroller !== null) {
                    scroller.disable();
                    currentScrollTopEl.removeEventListener('mouseleave', scroller.stopScrolling);
                    currentScrollTopEl.removeEventListener('mouseenter', startScrollingTop);
                    currentScrollBottomEl.removeEventListener('mouseleave', scroller.stopScrolling);
                    currentScrollBottomEl.removeEventListener('mouseenter', startScrollingBottom);
                }
            }
        });

        return (
            <Fragment>
                <EmbeddedElement ref={scrollElement} {...rest}/>
                <div ref={scrollTopElement} className="scrollarea scrollarea-top"></div>
                <div ref={scrollBottomElement} className="scrollarea scrollarea-bottom"></div>
            </Fragment>
        );
    }
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
    if(!url){
        useEffect(() => {
            ref.current.contentDocument.body.style.height = "300vh";
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
};


