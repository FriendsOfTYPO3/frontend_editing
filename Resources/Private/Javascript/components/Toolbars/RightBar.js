import React from 'react';

export default class RightBar extends React.Component {

    render () {
        return (
            <div>
                <div className="t3-frontend-editing__right-bar">
                    <div className="top-right-bar-wrapper">
                        <div className="top-right-title">
                            <span className="icons icon-icons-arrow-double right-bar-button"></span>
                            <span className="title-right">
                                {FrontendEditing.labels['right-bar.tools-properties']}
                            </span>
                            <span className="icons icon-icons-tools-settings"></span>
                        </div>
                        <div className="padding-wrapper">
                            <div className="page-seo">
                                <span className="title-default">
                                    {FrontendEditing.labels['right-bar.page-seo-score']}
                                </span>
                                <span className="page-seo-score">85</span>
                                <button className="btn-default">
                                    {FrontendEditing.labels['right-bar.seo-guide']}
                                </button>

                                <div className="page-seo-devices">
                                    <span className="page-seo-lg-desktop icons icon-icons-lg-desktop active" data-width="100%" data-height="100%"></span>
                                    <span className="page-seo-desktop icons icon-icons-desktop" data-width="1280px" data-height="100%"></span>
                                    <span className="page-seo-tablet icons icon-icons-tablet" data-width="960px" data-height="100%"></span>
                                    <span className="page-seo-phone icons icon-icons-phone" data-width="640px" data-height="100%"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="elements">
                        <div className="dark-transparent-bg accordion-container accordion-grid">
                            <div className="accordion ">
                                <div className="element-title">
                                    <span className="title-left">Typical Content Elements</span>
                                </div>
                                <div className="element-action">
                                    <span className="icons icon-icons-grid grid "></span>
                                    <span className="icons icon-icons-list list-view"></span>
                                    <span className="icons icon-icons-arrow-down trigger"></span>
                                </div>
                            </div>
                            <div className="accordion-content">
                                <div className="element">
                                    <span className="content-icon">
                                        <img src="/typo3conf/ext/bootstrap_package/Resources/Public/Icons/ContentElements/texticon.svg" width="32" height="32" />
                                    </span>
                                    <div className="description">
                                        <span className="title-default">Header</span>
                                        <span className="title-default">Adds a header only</span>
                                    </div>
                                </div>
                                <div className="element">
                                    <span className="content-icon">
                                        <img src="/typo3conf/ext/bootstrap_package/Resources/Public/Icons/ContentElements/texticon.svg" width="32" height="32" />
                                    </span>
                                    <div className="description">
                                        <span className="title-default">Text & Media</span>
                                        <span className="title-default">Any number of media wrapped right around a regular text element.</span>
                                    </div>
                                </div>
                                <div className="element">
                                    <span className="content-icon">
                                        <img src="/typo3conf/ext/bootstrap_package/Resources/Public/Icons/ContentElements/texticon.svg" width="32" height="32" />
                                    </span>
                                    <div className="description">
                                        <span className="title-default">Text & Media</span>
                                        <span className="title-default">Any number of media wrapped right around a regular text element.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div className="dark-transparent-bg accordion-container accordion-grid">
                    <div className="accordion ">
                        <div className="element-title">
                            <span className="title-left">Form Elements</span>
                        </div>
                        <div className="element-action">
                            <span className="icons icon-icons-grid grid "></span>
                            <span className="icons icon-icons-list list-view"></span>
                            <span className="icons icon-icons-arrow-down trigger"></span>
                        </div>
                    </div>
                    <div className="accordion-content">
                        <div className="element">
                            <span className="content-icon">
                                <img src="/typo3conf/ext/bootstrap_package/Resources/Public/Icons/ContentElements/texticon.svg" width="32" height="32" />
                            </span>
                            <div className="description">
                                <span className="title-default">Header</span>
                                <span className="title-default">Adds a header only</span>
                            </div>
                        </div>
                        <div className="element">
                            <span className="content-icon">
                                <img src="/typo3conf/ext/bootstrap_package/Resources/Public/Icons/ContentElements/texticon.svg" width="32" height="32" />
                            </span>
                            <div className="description">
                                <span className="title-default">Text & Media</span>
                                <span className="title-default">Any number of media wrapped right around a regular text element.</span>
                            </div>
                        </div>
                        <div className="element">
                            <span className="content-icon">
                                <img src="/typo3conf/ext/bootstrap_package/Resources/Public/Icons/ContentElements/texticon.svg" width="32" height="32" />
                            </span>
                            <div className="description">
                                <span className="title-default">Text & Media</span>
                                <span className="title-default">Any number of media wrapped right around a regular text element.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

}