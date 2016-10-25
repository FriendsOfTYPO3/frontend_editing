import React from 'react'

export default class TopBar extends React.Component {
    render () {
        return (
            <div className="t3-frontend-editing__top-bar">
                <div className="t3-frontend-editing__topbar-inner">
                    <div className="t3-frontend-editing__top-bar-left">
                        <div className="back-backend">
                            <a href="/typo3">
                                <span className="icons icon-icons-back"></span>
                                <span title="To backend">

                                </span>
                            </a>
                        </div>
                    </div>
                    <div className="t3-frontend-editing__top-bar-right">
                        <ul className="top-bar-items">
                            <li className="dropdown item">
                                <div className="user">


                                </div>
                            </li>
                        </ul>
                        <div className="top-bar-action-buttons">
                            <button type="submit" className="t3-frontend-editing__save btn">
                                <span className="btn-text">

                                </span>
                                <span className="items-counter btn-text"></span>
                                <span className="icons icon-icons-save"></span>
                            </button>
                            <button type="#" className="t3-frontend-editing__discard btn-default">
                                <span className="btn-text">

                                </span>
                                <span className="icons icon-icons-cancel"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}