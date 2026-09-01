import { createReduxStore, register } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

const STORE_NAME = 'tsjippy-forms/conditions-store';

const DEFAULT_STATE = {
    conditionsByBlock: {},
    loadingByPost: {},
    errorByPost: {},
    loadedByPost: {},
};

async function fetchConditions(postId) {
    return apiFetch({
        path: `${tsjippy.restApiPrefix}/forms/get_block_conditions`,
        method: 'POST',
        data: {
            postId,
        },
    });
}

const actions = {
    setConditions(conditions) {
        return {
            type: 'SET_CONDITIONS',
            conditions,
        };
    },

    setCondition(blockId, conditions) {
        return {
            type: 'SET_CONDITION',
            blockId,
            conditions,
        };
    },

    setLoading(postId, isLoading) {
        return {
            type: 'SET_LOADING',
            postId,
            isLoading: !!isLoading,
        };
    },

    setError(postId, error) {
        return {
            type: 'SET_ERROR',
            postId,
            error: error || null,
        };
    },

    setLoaded(postId, loaded) {
        return {
            type: 'SET_LOADED',
            postId,
            loaded: !!loaded,
        };
    },

};

const reducer = (state = DEFAULT_STATE, action) => {
    switch (action.type) {
        case 'SET_CONDITIONS': {
            const normalized = {};

            action.conditions.forEach((condition) => {
                if (!condition.block_id) {
                    return;
                }

                if (!normalized[condition.block_id]) {
                    normalized[condition.block_id] = [];
                }

                normalized[condition.block_id].push(condition);
            });

            return {
                ...state,
                conditionsByBlock: {
                    ...state.conditionsByBlock,
                    ...normalized,
                },
            };
        }

        case 'SET_CONDITION':
            return {
                ...state,
                conditionsByBlock: {
                    ...state.conditionsByBlock,
                    [action.blockId]: action.conditions,
                },
            };

        case 'SET_LOADING':
            return {
                ...state,
                loadingByPost: {
                    ...state.loadingByPost,
                    [action.postId]: action.isLoading,
                },
            };

        case 'SET_ERROR':
            return {
                ...state,
                errorByPost: {
                    ...state.errorByPost,
                    [action.postId]: action.error,
                },
            };

        case 'SET_LOADED':
            return {
                ...state,
                loadedByPost: {
                    ...state.loadedByPost,
                    [action.postId]: action.loaded,
                },
            };

        default:
            return state;
    }
};

const selectors = {
    getFormConditions(state) {
        return state.conditionsByBlock;
    },

    getConditions(state, blockId) {
        return (
            state.conditionsByBlock[blockId] || [
                {
                    rules: [],
                    actions: [],
                },
            ]
        );
    },

    isLoading(state, postId) {
        return !!state.loadingByPost[postId];
    },

    getError(state, postId) {
        return state.errorByPost[postId] ?? null;
    },

    hasLoaded(state, postId) {
        return !!state.loadedByPost[postId];
    },

    hasConditions(state, blockId) {
		const conditions = state.conditionsByBlock[blockId] || [];

		return conditions.some(
			(condition) =>
				(condition.rules?.length || 0) > 0 &&
				(condition.actions?.length || 0) > 0
		);
	}
};

const resolvers = {
    getFormConditions:
        (postId) =>
        async ({ dispatch, select }) => {
            if (!postId) {
                return;
            }

            if (select.hasLoaded(postId)) {
                return;
            }

            dispatch.setLoading(postId, true);
            dispatch.setError(postId, null);

            try {
                const conditions = await fetchConditions(postId);

                dispatch.setConditions(
                    Array.isArray(conditions) ? conditions : []
                );

                dispatch.setLoaded(postId, true);
            } catch (error) {
                dispatch.setError(
                    postId,
                    error?.message || 'Failed to load conditions.'
                );
            } finally {
                dispatch.setLoading(postId, false);
            }
        },
};

register(
    createReduxStore(STORE_NAME, {
        reducer,
        actions,
        selectors,
        resolvers,
    })
);