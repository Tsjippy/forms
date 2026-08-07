import { createReduxStore, register } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

const DEFAULT_STATE = {
    data: null,
    isLoading: false,
};

const store = createReduxStore('tsjippy/prefill', {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case 'SET_LOADING':
                return {
                    ...state,
                    isLoading: true,
                };

            case 'SET_DATA':
                return {
                    data: action.data,
                    isLoading: false,
                };
        }

        return state;
    },

    actions: {
        async fetchPrefill() {
            return async ({ dispatch, select }) => {
                if (select.getData() || select.isLoading()) {
                    return;
                }

                dispatch({ type: 'SET_LOADING' });

                const data = await apiFetch({
                    path: `${tsjippy.restApiPrefix}/forms/get_prefill`,
                    method: 'POST',
                });

                dispatch({
                    type: 'SET_DATA',
                    data,
                });
            };
        }
    },

    selectors: {
        getData(state) {
            return state.data;
        },
        isLoading(state) {
            return state.isLoading;
        },
    },
});

try {
    dispatch(STORE_NAME);
} catch {
    register(store);
}