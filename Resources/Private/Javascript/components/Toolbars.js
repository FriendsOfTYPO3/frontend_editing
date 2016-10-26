import React from 'react';

import TopBar from './Toolbars/TopBar';
import RightBar from './Toolbars/RightBar';
import LeftBar from './Toolbars/LeftBar';

export default class Toolbars extends React.Component {

    constructor() {
        super();
        this.state = {
            numberOfUnsavedItems: 0
        };
    }

    discardAllChanges () {
        const confirmed = confirm(FrontendEditing.labels['notifications.remove-all-changes']);
        if (confirmed) {
            this.setState({numberOfUnsavedItems: 0});
        }
    }

    render() {
        return (
            <div>
                <TopBar numberOfUnsavedItems={this.state.numberOfUnsavedItems} discardAllChanges={this.discardAllChanges.bind(this)} />
                <RightBar />
                <LeftBar />
            </div>
        );
    }
}
