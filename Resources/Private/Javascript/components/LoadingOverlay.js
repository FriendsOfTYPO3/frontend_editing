import React from 'react';
import { connect } from 'react-redux';

const mapStateToProps = (state) => {
    return {
        showLoadingOverlay: state.showLoadingOverlay,
    }
}

class LoadingOverlay extends React.Component {

    render () {
        if (this.props.showLoadingOverlay === true) {
            return (
                <div className="t3-frontend-editing__loading-screen" 
                    dangerouslySetInnerHTML={{__html: FrontendEditing.userIcon}}>
                </div>
            )
        } else {
            return false;
        }
    }
}

export default connect(mapStateToProps)(LoadingOverlay);