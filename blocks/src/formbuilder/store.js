import { createReduxStore, register } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

const DEFAULT_STATE = {
	conditionsByElement: {},
	isLoading: false,
	error: null,
};

const actions = {
	setConditions(elementId, conditions) {
		return {
			type: 'SET_CONDITIONS',
			elementId,
			conditions,
		};
	},

	setLoading(isLoading) {
		return {
			type: 'SET_LOADING',
			isLoading,
		};
	},

	setError(error) {
		return {
			type: 'SET_ERROR',
			error,
		};
	},
};

function reducer(state = DEFAULT_STATE, action) {
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
				isLoading: action.isLoading,
			};

		case 'SET_ERROR':
			return {
				...state,
				error: action.error,
			};

		default:
			return state;
	}
}

const selectors = {
	getConditions(state, elementId) {
		return state.conditionsByElement[elementId] ?? [];
	},

	isLoading(state) {
		return state.isLoading;
	},

	getError(state) {
		return state.error;
	},
};

const resolvers = {
	*getConditions(elementId) {
		yield actions.setLoading(true);
		yield actions.setError(null);

		try {
			const conditions = yield apiFetch({
				path: `${tsjippy.restApiPrefix}/forms/get_element_conditions`,
				method: 'POST',
				data: {
					elementId,
				},
			});

			yield actions.setConditions(elementId, conditions);
		} catch (error) {
			yield actions.setError(error?.message || 'Unknown error');
		}

		yield actions.setLoading(false);
	},
};

const store = createReduxStore('tsjippy-forms/conditions-store', {
	reducer,
	actions,
	selectors,
	resolvers,
});

register(store);