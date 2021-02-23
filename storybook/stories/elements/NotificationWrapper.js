import React, {useEffect, useRef, useState} from 'react';
import PropTypes from 'prop-types';

export const Severity = ['success', 'warning', 'error'];

const NotificationWrapper = ({title, message, severity = 'success'}) => {
    let notification = null;

    function show () {
        if (notification) {
            notification[severity](message, title);
        }
    }

    useEffect(() => {
        import('TYPO3/CMS/FrontendEditing/Notification')
            .then(({default: Notification}) => {
                notification = Notification;
                show();
            });
    });

    return (
        <div style={{
            display: 'flex',
            padding: '16px',
            gap: '8px',
            background: 'lightgray',
            borderBottom: '1px solid gray',
        }}>
            <button onClick={show}>show</button>
        </div>
    );
};

export default NotificationWrapper;

NotificationWrapper.propTypes = {
    title: PropTypes.string,
    message: PropTypes.string,
};


