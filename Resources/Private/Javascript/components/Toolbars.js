import React from 'react';

import TopBar from './Toolbars/TopBar';
import RightBar from './Toolbars/RightBar';
import LeftBar from './Toolbars/LeftBar';

export default class Toolbars extends React.Component {
    render() {
        return (
            <div className="t3-frontend-editing__toolbars-container">
                <TopBar />
                <RightBar />
                <LeftBar />
            </div>
        );
    }
}