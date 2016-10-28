import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import Toolbars from './components/Toolbars';
import LoadingOverlay from './components/LoadingOverlay';
import store from './store';

ReactDOM.render(
    <Provider store={store}>
        <div>
            <Toolbars />
            <LoadingOverlay />
        </div>
    </Provider>, document.getElementById('t3-frontend-editing')
);