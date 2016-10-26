import React from 'react';

export default class RightBar extends React.Component {

    render () {
        return (
            <div className="t3-frontend-editing__left-bar">
                <div className="top-left-title">
                    <span className="icons icon-icons-site-tree"></span>
                    <span className="title-left">
                        {FrontendEditing.labels['left-bar.site-tree']}
                    </span>
                    <span className="icons icon-icons-arrow-double left-bar-button"></span>
                </div>
                <div className="padding-wrapper">
                    <div className="t3-frontend-editing__page-tree-wrapper">
                        <ul className="t3-frontend-editing__page-tree">
                            [@TODO: Use FrontendEditing.pageTree to render pageTree]...
                        </ul>
                    </div>
                </div>
            </div>
        );
    }
}