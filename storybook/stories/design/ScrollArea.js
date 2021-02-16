import React from 'react';

//TODO extract css to sass
import '../design/Scroller.css';

const ScrollArea = React.forwardRef(({place = 'top', ...args}, ref) => {
    return (
        <div
            ref={ref}
            className={`scrollarea scrollarea-${place === 'top' ? place : 'bottom'}`}
            style={{display: 'none'}}
            {...args}
        />
    );
});

export default ScrollArea;
