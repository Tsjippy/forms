import { createReduxStore, register } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

/**
 * The unique store name used by Gutenberg data APIs.
 */
const STORE_NAME = 'tsjippy-forms/conditions-store';

/**
 * Initial state for the store.
 * Each element keeps its own conditions, loading flag, saving flag,
 * error message, and loaded flag.
 */
const DEFAULT_STATE = {
	conditionsByElement: {},
	loadingByElement: {},
	savingByElement: {},
	errorByElement: {},
	loadedByElement: {},
};
/**
 * Internal API helper for loading conditions.
 * This is used by the resolver and is not exported.
 */
async function fetchConditions(blockId) {
	const response = await apiFetch({
		path: `${tsjippy.restApiPrefix}/forms/get_element_conditions`,
		method: 'POST',
		data: {
			blockId: blockId,
		},
	});

	return response;
}

/**
 * Internal API helper for saving conditions.
 * This is used by the store-owned save action and is not exported.
 */
async function saveConditionsRequest(blockId, conditions) {
	const savedConditions = await apiFetch({
		path: `${tsjippy.restApiPrefix}/forms/save_element_conditions`,
		method: 'POST',
		data: {
			blockId: blockId,
			conditions: conditions,
		},
	});

	return conditions;
}

/**
 * Store action creators.
 * Most actions are plain actions, while saveConditions is a generator
 * that performs the async save flow.
 */
const actions = {
	/**
	 * Set normalized conditions for one element.
	 */
	setConditions(blockId, conditions) {
		return {
			type: 'SET_CONDITIONS',
			blockId,
			conditions: conditions,
		};
	},

	/**
	 * Set the loading state for one element.
	 */
	setLoading(blockId, isLoading) {
		return {
			type: 'SET_LOADING',
			blockId,
			isLoading: !!isLoading,
		};
	},

	/**
	 * Set the saving state for one element.
	 */
	setSaving(blockId, isSaving) {
		return {
			type: 'SET_SAVING',
			blockId,
			isSaving: !!isSaving,
		};
	},

	/**
	 * Store an error message for one element.
	 */
	setError(blockId, error) {
		return {
			type: 'SET_ERROR',
			blockId,
			error: error || null,
		};
	},

	/**
	 * Store the loaded flag for one element.
	 */
	setLoaded(blockId, loaded) {
		return {
			type: 'SET_LOADED',
			blockId,
			loaded: !!loaded,
		};
	},

	/**
	 * Save conditions through the API and update store state.
	 * This is the store-owned mutation path.
	 */
	*saveConditions(blockId, conditions) {
		if (blockId === undefined || blockId === null || blockId === '') {
			return;
		}

		yield actions.setSaving(blockId, true);
		yield actions.setError(blockId, null);

		try {
			const savedConditions = yield saveConditionsRequest(
				blockId,
				conditions
			);

			yield actions.setConditions(blockId, savedConditions);
			yield actions.setLoaded(blockId, true);

			return savedConditions;
		} catch (error) {
			yield actions.setError(
				blockId,
				error?.message || 'Failed to save element conditions.'
			);

			throw error;
		} finally {
			yield actions.setSaving(blockId, false);
		}
	},
};

/**
 * Reducer for the conditions store.
 * This keeps all updates immutable and predictable.
 */
const reducer = (state = DEFAULT_STATE, action) => {
	switch (action.type) {
		case 'SET_CONDITIONS':
			return {
				...state,
				conditionsByElement: {
					...state.conditionsByElement,
					[action.blockId]: action.conditions,
				},
			};

		case 'SET_LOADING':
			return {
				...state,
				loadingByElement: {
					...state.loadingByElement,
					[action.blockId]: action.isLoading,
				},
			};

		case 'SET_SAVING':
			return {
				...state,
				savingByElement: {
					...state.savingByElement,
					[action.blockId]: action.isSaving,
				},
			};

		case 'SET_ERROR':
			return {
				...state,
				errorByElement: {
					...state.errorByElement,
					[action.blockId]: action.error,
				},
			};

		case 'SET_LOADED':
			return {
				...state,
				loadedByElement: {
					...state.loadedByElement,
					[action.blockId]: action.loaded,
				},
			};

		default:
			return state;
	}
};

/**
 * Selectors for reading store state.
 * These are what the modal uses through useSelect.
 */
const selectors = {
	/**
	 * Get the normalized conditions object for one element.
	 */
	getConditions(state, blockId) {
		return (
			state.conditionsByElement[blockId] || [{
				rules: [],
				actions: [],
			}]
		);
	},

	/**
	 * Check whether one element is currently loading.
	 */
	isLoading(state, blockId) {
		return !!state.loadingByElement[blockId];
	},

	/**
	 * Check whether one element is currently saving.
	 */
	isSaving(state, blockId) {
		return !!state.savingByElement[blockId];
	},

	/**
	 * Get the error message for one element.
	 */
	getError(state, blockId) {
		return state.errorByElement[blockId] ?? null;
	},

	/**
	 * Check whether one element has already loaded.
	 */
	hasLoaded(state, blockId) {
		return !!state.loadedByElement[blockId];
	},
};

/**
 * Resolver for getConditions.
 * The first read of the selector will load data from the server.
 */
const resolvers = {
	getConditions: (blockId) => async ({ dispatch }) => {
		if (blockId === undefined || blockId === null || blockId === '') {
			return;
		}

		dispatch.setLoading(blockId, true);
		dispatch.setError(blockId, null);

		try {
			const conditions = await fetchConditions(blockId);
			dispatch.setConditions(blockId, conditions);
			dispatch.setLoaded(blockId, true);
		} catch (error) {
			dispatch.setError(
				blockId,
				error?.message || 'Failed to load element conditions.'
			);
		} finally {
			dispatch.setLoading(blockId, false);
		}
	},
};

/**
 * Create and register the Gutenberg data store.
 */
const store = createReduxStore(STORE_NAME, {
	reducer,
	actions,
	selectors,
	resolvers,
});

register(store);