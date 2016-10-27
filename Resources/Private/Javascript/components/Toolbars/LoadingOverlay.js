import React from 'react';
import { connect } from 'react-redux';

const mapStateToProps = (state) => {
    return {
        showLoadingOverlay: state.showLoadingOverlay,
    }
}

class LoadingOverlay extends React.Component {

    render () {
        return (
            <div class="t3-frontend-editing__loading-screen">
                HELLO THIS IS LOADING SCREEN :)
            </div>
        )
    }
}

export default connect(mapStateToProps)(LoadingOverlay);

