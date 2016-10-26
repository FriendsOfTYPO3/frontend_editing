import React from 'react';

export default class TopBar extends React.Component {

    handleClick(e) {
        this.props.discardAllChanges();
    }

    render () {
        const unsavedText = this.props.numberOfUnsavedItems > 0 ? '(' + this.props.numberOfUnsavedItems + ')' : '';
        return (
            <div className="t3-frontend-editing__top-bar">
                <div className="t3-frontend-editing__topbar-inner">
                    <div className="t3-frontend-editing__top-bar-left">
                        <div className="back-backend">
                            <a href="/typo3">
                                <span className="icons icon-icons-back"></span>
                                <span title="{FrontendEditing.labels['top-bar.to-backend']}">
                                    {FrontendEditing.labels['top-bar.to-backend']}
                                </span>
                            </a>
                        </div>
                    </div>
                    <div className="t3-frontend-editing__top-bar-right">
                        <ul className="top-bar-items">
                            <li className="dropdown item">
                                <div className="user">
                                    <span dangerouslySetInnerHTML={{__html: FrontendEditing.userIcon}} />
                                    <span title={FrontendEditing.userName}>{FrontendEditing.userName}</span>
                                </div>
                            </li>
                        </ul>
                        <div className="top-bar-action-buttons">
                            <button type="submit" className="t3-frontend-editing__save btn">
                                <span className="btn-text">
                                    {FrontendEditing.labels['top-bar.save-all']}
                                </span>
                                <span className="items-counter btn-text">{unsavedText}</span>
                                <span className="icons icon-icons-save"></span>
                            </button>
                            <button onClick={this.handleClick.bind(this)} type="#" className="t3-frontend-editing__discard btn-default">
                                <span className="btn-text">
                                    {FrontendEditing.labels['top-bar.discard-all']}
                                </span>
                                <span className="icons icon-icons-cancel"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}