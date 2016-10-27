import logger from 'redux-logger';
import { applyMiddleware, createStore } from 'redux';
import thunk from 'redux-thunk'
import contentReducer from './reducers/contentReducer'

const middleware = applyMiddleware(thunk, logger());

// Initial state for the whole app
const initialState = {
    unsavedElements: {},
    showLoadingOverlay: false,
    error: null,
};

const store = createStore(contentReducer, initialState, middleware);
export default store;