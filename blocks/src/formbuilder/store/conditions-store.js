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
async function fetchConditions(elementId) {
	const response = await apiFetch({
		path: `${tsjippy.restApiPrefix}/forms/get_element_conditions`,
		method: 'POST',
		data: {
			elementId,
		},
	});

	return response;
}

/**
 * Internal API helper for saving conditions.
 * This is used by the store-owned save action and is not exported.
 */
async function saveConditionsRequest(elementId, conditions) {
	const savedConditions = await apiFetch({
		path: `${tsjippy.restApiPrefix}/forms/save_element_conditions`,
		method: 'POST',
		data: {
			elementId,
			conditions,
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
	setConditions(elementId, conditions) {
		return {
			type: 'SET_CONDITIONS',
			elementId,
			conditions: conditions,
		};
	},

	/**
	 * Set the loading state for one element.
	 */
	setLoading(elementId, isLoading) {
		return {
			type: 'SET_LOADING',
			elementId,
			isLoading: !!isLoading,
		};
	},

	/**
	 * Set the saving state for one element.
	 */
	setSaving(elementId, isSaving) {
		return {
			type: 'SET_SAVING',
			elementId,
			isSaving: !!isSaving,
		};
	},

	/**
	 * Store an error message for one element.
	 */
	setError(elementId, error) {
		return {
			type: 'SET_ERROR',
			elementId,
			error: error || null,
		};
	},

	/**
	 * Store the loaded flag for one element.
	 */
	setLoaded(elementId, loaded) {
		return {
			type: 'SET_LOADED',
			elementId,
			loaded: !!loaded,
		};
	},

	/**
	 * Save conditions through the API and update store state.
	 * This is the store-owned mutation path.
	 */
	*saveConditions(elementId, conditions) {
		if (elementId === undefined || elementId === null || elementId === '') {
			return;
		}

		yield actions.setSaving(elementId, true);
		yield actions.setError(elementId, null);

		try {
			const savedConditions = yield saveConditionsRequest(
				elementId,
				conditions
			);

			yield actions.setConditions(elementId, savedConditions);
			yield actions.setLoaded(elementId, true);

			return savedConditions;
		} catch (error) {
			yield actions.setError(
				elementId,
				error?.message || 'Failed to save element conditions.'
			);

			throw error;
		} finally {
			yield actions.setSaving(elementId, false);
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
					[action.elementId]: action.conditions,
				},
			};

		case 'SET_LOADING':
			return {
				...state,
				loadingByElement: {
					...state.loadingByElement,
					[action.elementId]: action.isLoading,
				},
			};

		case 'SET_SAVING':
			return {
				...state,
				savingByElement: {
					...state.savingByElement,
					[action.elementId]: action.isSaving,
				},
			};

		case 'SET_ERROR':
			return {
				...state,
				errorByElement: {
					...state.errorByElement,
					[action.elementId]: action.error,
				},
			};

		case 'SET_LOADED':
			return {
				...state,
				loadedByElement: {
					...state.loadedByElement,
					[action.elementId]: action.loaded,
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
	getConditions(state, elementId) {
		return (
			state.conditionsByElement[elementId] || [{
				rules: [],
				actions: [],
			}]
		);
	},

	/**
	 * Check whether one element is currently loading.
	 */
	isLoading(state, elementId) {
		return !!state.loadingByElement[elementId];
	},

	/**
	 * Check whether one element is currently saving.
	 */
	isSaving(state, elementId) {
		return !!state.savingByElement[elementId];
	},

	/**
	 * Get the error message for one element.
	 */
	getError(state, elementId) {
		return state.errorByElement[elementId] ?? null;
	},

	/**
	 * Check whether one element has already loaded.
	 */
	hasLoaded(state, elementId) {
		return !!state.loadedByElement[elementId];
	},
};

/**
 * Resolver for getConditions.
 * The first read of the selector will load data from the server.
 */
const resolvers = {
	getConditions: (elementId) => async ({ dispatch }) => {
		if (elementId === undefined || elementId === null || elementId === '') {
			return;
		}

		dispatch.setLoading(elementId, true);
		dispatch.setError(elementId, null);

		try {
			const conditions = await fetchConditions(elementId);
			dispatch.setConditions(elementId, conditions);
			dispatch.setLoaded(elementId, true);
		} catch (error) {
			dispatch.setError(
				elementId,
				error?.message || 'Failed to load element conditions.'
			);
		} finally {
			dispatch.setLoading(elementId, false);
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