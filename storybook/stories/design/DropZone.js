import React from 'react';
import PropTypes from 'prop-types';

export const DropZoneEnabled = ({children}) => (
    <div className="dropzones-enabled">{children}</div>
);

DropZoneEnabled.propTypes = {
    children: PropTypes.element,
};

export const DropZoneStates = {
    'active': 1,
    'hidden': 2,
};

const DropZone = ({children, state}) => {
    let className = 't3-frontend-editing__dropzone';

    if ((state & DropZoneStates.active) > 0) {
        className += ' active';
    }
    if ((state & DropZoneStates.hidden) > 0) {
        className += ' t3-frontend-editing__dropzone-hidden';
    }

    return (
        <div className={className}>{children}</div>
    );
};

export default DropZone;

DropZone.propTypes = {
    children: PropTypes.element,
    classNames: PropTypes.number,
};
