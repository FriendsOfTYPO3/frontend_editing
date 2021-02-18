import React, {useEffect, useRef} from 'react';
import PropTypes from 'prop-types';

const LoadingScreenWrapper = ({visible, className = 't3-frontend-editing__loading-screen', children}) => {
    const wrapper = useRef(null);

    useEffect(() => {
        let loadingScreen = wrapper.current.querySelector('.' + className);
        if (visible){
            delete loadingScreen.style.display;
        } else {
            loadingScreen.style.display = 'none';
        }
    });

    return (
        <div ref={wrapper}>
            {children}
        </div>
    );
};

export default LoadingScreenWrapper;

LoadingScreenWrapper.propTypes = {
    visible: PropTypes.bool,
    className: PropTypes.string,
};


