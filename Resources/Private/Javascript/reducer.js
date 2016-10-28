import { SAVE_CONTENT_START, SAVE_CONTENT_FINISH} from './actions/contentActions';

const contentReducer = (state, action) => {
    switch (action.type) {
        case SAVE_CONTENT_START:
            return Object.assign({}, state, {
                showLoadingOverlay: true,
            });
        case SAVE_CONTENT_FINISH:
            return Object.assign({}, state, {
                showLoadingOverlay: false,
            });
    }
    return state;
};

export default contentReducer;