import React from 'react';

const ScrollArea = React.forwardRef(({place = 'top', arrow = true, ...args}, ref) => {
    let className = 'scrollarea';
    if (arrow) {
        className += ' scrollarea--arrow';
    }
    if (place === 'top') {
        className += ' scrollarea-top';
        if (arrow) {
            className += ' scrollarea--arrow-up';
        }
    } else {
        className += ' scrollarea-bottom';
        if (arrow) {
            className += ' scrollarea--arrow-down';
        }
    }
    return (
        <div
            ref={ref}
            className={className}
            style={{display: 'none'}}
            {...args}
        />
    );
});

export default ScrollArea;
