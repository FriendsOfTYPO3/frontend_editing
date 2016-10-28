import { applyMiddleware, createStore } from 'redux';
import thunk from 'redux-thunk'
import reducer from './reducer.js';

const middleware = applyMiddleware(thunk);

// Initial state for the whole app
const initialState = {
    unsavedElements: {},
    showLoadingOverlay: false,
    error: null,
};

const store = createStore(reducer, initialState, middleware);
export default store;