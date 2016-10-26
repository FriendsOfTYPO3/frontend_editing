import React from 'react';

import TopBar from './Toolbars/TopBar';

export default class Toolbars extends React.Component {
    constructor() {
        super();
        this.state = {
            numberOfUnsavedItems: 100
        }
    }

    discardAllChanges () {
        const confirmed = confirm(FrontendEditing.labels['notifications.remove-all-changes']);
        if (confirmed) {
            this.setState({numberOfUnsavedItems: 0});
        }
    }

    render() {
        return (
            <TopBar numberOfUnsavedItems={this.state.numberOfUnsavedItems} discardAllChanges={this.discardAllChanges.bind(this)} />
        );
    }
}