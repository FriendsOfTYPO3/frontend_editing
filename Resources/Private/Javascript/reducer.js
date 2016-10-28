import { SAVE_CONTENT_START, SAVE_CONTENT_ERROR, SAVE_CONTENT_SUCCESS} from './actions/contentActions';

const contentReducer = (state, action) => {
    switch (action.type) {
        case SAVE_CONTENT_START:
            return Object.assign({}, state, {
                showLoadingOverlay: true,
            });
        case SAVE_CONTENT_ERROR:
            return Object.assign({}, state, {
                showLoadingOverlay: false,
            });
        case SAVE_CONTENT_SUCCESS:
            return Object.assign({}, state, {
                showLoadingOverlay: false,
            });
    }
    return state;
};

export default contentReducer;