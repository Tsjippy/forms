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
 * Ensure the provided value is always an array.
 * This prevents runtime errors from malformed API responses.
 */
function ensureArray(value) {
	return Array.isArray(value) ? value : [];
}

/**
 * Normalize one condition object so every expected key exists.
 * This keeps old or partial data from breaking the editor UI.
 */
function normalizeConditionItem(condition) {
	if (!condition || typeof condition !== 'object' || Array.isArray(condition)) {
		return {
			'conditional-field': '',
			equation: '',
			'conditional-value': '',
			combinator: 'and',
			'conditional-field-2': '',
			'equation-2': '',
		};
	}

	return {
		'conditional-field': condition['conditional-field'] || '',
		equation: condition.equation || '',
		'conditional-value': condition['conditional-value'] || '',
		combinator: condition.combinator || 'and',
		'conditional-field-2': condition['conditional-field-2'] || '',
		'equation-2': condition['equation-2'] || '',
	};
}

/**
 * Normalize one action object so every expected key exists.
 * This keeps the actions editor stable even if saved data is incomplete.
 */
function normalizeActionItem(action) {
	if (!action || typeof action !== 'object' || Array.isArray(action)) {
		return {
			action: '',
			'property-name': '',
			'property-value': '',
			'property-name1': '',
			'action-value': '',
			addition: '',
		};
	}

	return {
		action: action.action || '',
		'property-name': action['property-name'] || '',
		'property-value': action['property-value'] || '',
		'property-name1': action['property-name1'] || '',
		'action-value': action['action-value'] || '',
		addition: action.addition || '',
	};
}

/**
 * Convert any API response into a predictable top-level object.
 * The store expects an object with rules and actions arrays.
 */
function normalizeConditionsResponse(response) {
	if (response && typeof response === 'object' && !Array.isArray(response)) {
		const rules = Array.isArray(response.rules) ? response.rules : [];
		const actions = Array.isArray(response.actions) ? response.actions : [];

		return {
			rules,
			actions,
		};
	}

	if (Array.isArray(response)) {
		return {
			rules: response,
			actions: [],
		};
	}

	return {
		rules: [],
		actions: [],
	};
}

/**
 * Normalize the full conditions structure used by the UI.
 * This guarantees:
 * - rules is always an array of arrays
 * - actions is always an array of action objects
 */
function normalizeConditionsStructure(response) {
	const raw = normalizeConditionsResponse(response);

	return {
		rules: ensureArray(raw.rules).map((rule) => {
			if (Array.isArray(rule)) {
				return rule.map(normalizeConditionItem);
			}

			if (rule && typeof rule === 'object') {
				return [normalizeConditionItem(rule)];
			}

			return [normalizeConditionItem()];
		}),
		actions: ensureArray(raw.actions).map(normalizeActionItem),
	};
}

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

	return normalizeConditionsStructure(response);
}

/**
 * Internal API helper for saving conditions.
 * This is used by the store-owned save action and is not exported.
 */
async function saveConditionsRequest(elementId, conditions) {
	const response = await apiFetch({
		path: `${tsjippy.restApiPrefix}/forms/save_element_conditions`,
		method: 'POST',
		data: {
			elementId,
			conditions,
		},
	});

	const savedConditions = normalizeConditionsStructure(response);

	return {
		rules:
			savedConditions.rules.length > 0
				? savedConditions.rules
				: ensureArray(conditions?.rules).map((rule) => {
						if (Array.isArray(rule)) {
							return rule.map(normalizeConditionItem);
						}

						if (rule && typeof rule === 'object') {
							return [normalizeConditionItem(rule)];
						}

						return [normalizeConditionItem()];
					}),
		actions:
			savedConditions.actions.length > 0
				? savedConditions.actions
				: ensureArray(conditions?.actions).map(normalizeActionItem),
	};
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
		const normalized = normalizeConditionsStructure(conditions);

		return {
			type: 'SET_CONDITIONS',
			elementId,
			conditions: normalized,
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
					[action.elementId]: normalizeConditionsStructure(action.conditions),
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
			state.conditionsByElement[elementId] || {
				rules: [],
				actions: [],
			}
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