import React, {useEffect, useRef, useState} from 'react';
import PropTypes from 'prop-types';
import LoadingScreenWrapperEl from '../design/LoadingScreenWrapper';

const LoadingScreenWrapper = ({className = 't3-frontend-editing__loading-screen', children}) => {
    const wrapper = useRef(null);
    const addButtonRef = useRef(null);
    const removeButtonRef = useRef(null);
    const countSpanRef = useRef(null);

    useEffect(() => {
        let addButton = addButtonRef.current;
        let removeButton = removeButtonRef.current;
        let countSpan = countSpanRef.current;

        let loadingScreen = null;

        function add () {
            if (loadingScreen) {
                loadingScreen.showLoadingScreen();
                countSpan.innerText = loadingScreen.getLoadingScreenLevel();
            }
        }

        function remove () {
            if (loadingScreen) {
                loadingScreen.hideLoadingScreen();
                countSpan.innerText = loadingScreen.getLoadingScreenLevel();
            }
        }

        import('TYPO3/CMS/FrontendEditing/LoadingScreen').then(({default: LoadingScreen}) => {
            loadingScreen = LoadingScreen(
                wrapper.current.querySelector('.' + className),
            );
        });

        addButton.addEventListener('click', add);
        removeButton.addEventListener('click', remove);

        return function () {
            addButton.removeEventListener('click', add);
            removeButton.removeEventListener('click', remove);
        };
    });

    return (
        <div style={{
            display: 'flex',
            flexDirection: 'column',
            justifyItems: 'center',
            width: '100vw',
            height: '100vh',
        }}>
            <div style={{
                display: 'flex',
                padding: '16px',
                gap: '8px',
                background: 'lightgray',
                borderBottom: '1px solid gray',
            }}>
                <div style={{marginRight: '8px', minWidth: '10ch', borderRight: '1px solid gray'}}>
                    <span style={{verticalAlign: 'middle'}}>
                        <strong style={{marginRight: '8px'}}>Count:</strong>
                        <span ref={countSpanRef}>0</span>
                    </span>
                </div>
                <button ref={addButtonRef}>+</button>
                <button ref={removeButtonRef}>-</button>
            </div>
            <div ref={wrapper} style={{
                position: 'relative',
                flex: 1,
            }}>
                {children}
            </div>
        </div>
    );
};

export default LoadingScreenWrapper;

LoadingScreenWrapper.propTypes = {
    className: PropTypes.string,
    children: PropTypes.element,
};


