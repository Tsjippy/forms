/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/formbuilder/components/ConditionRow.js"
/*!****************************************************!*\
  !*** ./src/formbuilder/components/ConditionRow.js ***!
  \****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ ConditionRow)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-down.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-up.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/trash.mjs");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__);




/**
 * Render one condition row inside a rule group.
 * This component is presentational and sends all updates upward.
 */

function ConditionRow({
  condition,
  ruleIndex,
  subRuleIndex,
  formElementOptions,
  onUpdate,
  onDeleteRule,
  onMoveRuleUp,
  onMoveRuleDown,
  canMoveRuleUp,
  canMoveRuleDown,
  fieldErrors = {}
}) {
  /* Available equation choices for the main equation dropdown. */
  const equationOptions = [{
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Equals', 'tsjippy'),
    value: '=='
  }, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Does not equal', 'tsjippy'),
    value: '!='
  }, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Greater than', 'tsjippy'),
    value: '>'
  }, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Less than', 'tsjippy'),
    value: '<'
  }, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Equals value', 'tsjippy'),
    value: '== value'
  }, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Does not equal value', 'tsjippy'),
    value: '!= value'
  }, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Greater than value', 'tsjippy'),
    value: '> value'
  }, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Less than value', 'tsjippy'),
    value: '< value'
  }, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add', 'tsjippy'),
    value: '+'
  }, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Subtract', 'tsjippy'),
    value: '-'
  }];

  /* Render additional fields for arithmetic-style equations. */
  const renderExtraOptions = () => {
    if (!condition?.equation) {
      return null;
    }
    if (condition.equation === '+' || condition.equation === '-') {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.Fragment, {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Second element', 'tsjippy'),
          value: condition['conditional-field-2'] || '',
          options: [{
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select second element', 'tsjippy'),
            value: ''
          }, ...(formElementOptions || [])],
          onChange: element => onUpdate(ruleIndex, subRuleIndex, 'conditional-field-2', element),
          help: fieldErrors.conditionalField2 || '',
          "data-field-key": "conditionalField2"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Second equation', 'tsjippy'),
          value: condition['equation-2'] || '',
          options: [{
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select second equation', 'tsjippy'),
            value: ''
          }, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Equals', 'tsjippy'),
            value: '=='
          }, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Does not equal', 'tsjippy'),
            value: '!='
          }, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Greater than', 'tsjippy'),
            value: '>'
          }, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Less than', 'tsjippy'),
            value: '<'
          }],
          onChange: equation2 => onUpdate(ruleIndex, subRuleIndex, 'equation-2', equation2),
          help: fieldErrors.equation2 || '',
          "data-field-key": "equation2"
        })]
      });
    }
    return null;
  };

  /* Render the editable UI for one condition entry. */
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsxs)("div", {
    className: "condition-row__inner",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
      label: "Size",
      value: "50%",
      options: [{
        label: 'Big',
        value: '100%'
      }, {
        label: 'Medium',
        value: '50%'
      }, {
        label: 'Small',
        value: '25%'
      }],
      onChange: newSize => setSize(newSize)
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Conditional field', 'tsjippy'),
      value: condition?.['conditional-field'] || '',
      options: [{
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select element', 'tsjippy'),
        value: ''
      }, ...(formElementOptions || [])],
      onChange: element => onUpdate(ruleIndex, subRuleIndex, 'conditional-field', element),
      help: fieldErrors.conditionalField || '',
      "data-field-key": "conditionalField"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Equation', 'tsjippy'),
      value: condition?.equation || '',
      options: [{
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select equation', 'tsjippy'),
        value: ''
      }, ...equationOptions],
      onChange: equation => onUpdate(ruleIndex, subRuleIndex, 'equation', equation),
      help: fieldErrors.equation || '',
      "data-field-key": "equation"
    }), renderExtraOptions(), condition?.equation && condition.equation !== '+' && condition.equation !== '-' && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Value', 'tsjippy'),
      value: condition?.['conditional-value'] || '',
      onChange: value => onUpdate(ruleIndex, subRuleIndex, 'conditional-value', value),
      help: fieldErrors.conditionalValue || '',
      "data-field-key": "conditionalValue"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsxs)("div", {
      className: "condition-row__combinator",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        variant: condition?.combinator === 'and' ? 'primary' : 'secondary',
        isPressed: condition?.combinator === 'and',
        "aria-pressed": condition?.combinator === 'and',
        onClick: () => onUpdate(ruleIndex, subRuleIndex, 'combinator', 'and'),
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('AND', 'tsjippy')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        variant: condition?.combinator === 'or' ? 'primary' : 'secondary',
        isPressed: condition?.combinator === 'or',
        "aria-pressed": condition?.combinator === 'or',
        onClick: () => onUpdate(ruleIndex, subRuleIndex, 'combinator', 'or'),
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('OR', 'tsjippy')
      }), canMoveRuleUp && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        variant: "secondary",
        onClick: onMoveRuleUp,
        icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_3__["default"],
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Move rule up', 'tsjippy')
      }), canMoveRuleDown && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        variant: "secondary",
        onClick: onMoveRuleDown,
        icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_2__["default"],
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Move rule down', 'tsjippy')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        variant: "secondary",
        isDestructive: true,
        onClick: onDeleteRule,
        icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_4__["default"],
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Delete rule', 'tsjippy')
      })]
    })]
  });
}

/***/ },

/***/ "./src/formbuilder/components/ConditionsModal.js"
/*!*******************************************************!*\
  !*** ./src/formbuilder/components/ConditionsModal.js ***!
  \*******************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ ConditionsModal)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _ConditionRow__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ConditionRow */ "./src/formbuilder/components/ConditionRow.js");
/* harmony import */ var _hooks_useFormElementOptions__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../hooks/useFormElementOptions */ "./src/formbuilder/hooks/useFormElementOptions.js");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-down.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-up.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/copy.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/plus.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/trash.mjs");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__);








/**
 * Create a blank condition object.
 */

function createEmptyCondition() {
  return {
    'conditional-field': '',
    'equation': '',
    'conditional-value': '',
    'combinator': '',
    'conditional-field-2': '',
    'equation-2': ''
  };
}

/**
 * Create a blank action object.
 */
function createEmptyAction() {
  return {
    'action': '',
    'property-name': '',
    'property-value': '',
    'property-name1': '',
    'action-value': '',
    'addition': ''
  };
}

/**
 * Deep clone a plain object/array.
 */
function deepClone(value) {
  return JSON.parse(JSON.stringify(value || {}));
}

/**
 * Check whether an equation requires a value.
 */
function isEquationRequiringValue(equation) {
  return ['==', '!=', '>', '<', '== value', '!= value', '> value', '< value', '+', '-'].includes(equation);
}

/**
 * Validate the current conditions object.
 */
function validateConditions(state) {
  const errors = [];
  const fieldErrors = {
    rules: {},
    actions: {}
  };
  const firstErrorTarget = {
    section: null,
    ruleIndex: null,
    conditionIndex: null,
    actionIndex: null,
    fieldKey: null
  };
  const rules = Array.isArray(state?.rules) ? state.rules : [];
  const actions = Array.isArray(state?.actions) ? state.actions : [];
  if (rules.length === 0) {
    errors.push((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('At least one rule group is required.', 'tsjippy'));
  }
  rules.forEach((rule, ruleIndex) => {
    if (!Array.isArray(rule) || rule.length === 0) {
      errors.push((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Rule group %d must contain at least one condition.', 'tsjippy'), ruleIndex + 1));
      if (firstErrorTarget.section === null) {
        firstErrorTarget.section = 'rules';
        firstErrorTarget.ruleIndex = ruleIndex;
        firstErrorTarget.conditionIndex = 0;
        firstErrorTarget.fieldKey = 'conditionalField';
      }
      return;
    }
    fieldErrors.rules[ruleIndex] = fieldErrors.rules[ruleIndex] || {};
    rule.forEach((condition, conditionIndex) => {
      const conditionErrors = {};
      if (!condition?.['conditional-field']) {
        conditionErrors.conditionalField = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select an element.', 'tsjippy');
        if (firstErrorTarget.section === null) {
          firstErrorTarget.section = 'rules';
          firstErrorTarget.ruleIndex = ruleIndex;
          firstErrorTarget.conditionIndex = conditionIndex;
          firstErrorTarget.fieldKey = 'conditionalField';
        }
      }
      if (!condition?.equation) {
        conditionErrors.equation = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select an equation.', 'tsjippy');
        if (firstErrorTarget.section === null) {
          firstErrorTarget.section = 'rules';
          firstErrorTarget.ruleIndex = ruleIndex;
          firstErrorTarget.conditionIndex = conditionIndex;
          firstErrorTarget.fieldKey = 'equation';
        }
      }
      if (isEquationRequiringValue(condition?.equation)) {
        const value = condition?.['conditional-value'];
        if (value === undefined || value === null || String(value).trim() === '') {
          conditionErrors.conditionalValue = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Enter a value.', 'tsjippy');
          if (firstErrorTarget.section === null) {
            firstErrorTarget.section = 'rules';
            firstErrorTarget.ruleIndex = ruleIndex;
            firstErrorTarget.conditionIndex = conditionIndex;
            firstErrorTarget.fieldKey = 'conditionalValue';
          }
        }
      }
      if (condition?.equation === '+' || condition?.equation === '-') {
        if (!condition?.['conditional-field-2']) {
          conditionErrors.conditionalField2 = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select a second element.', 'tsjippy');
          if (firstErrorTarget.section === null) {
            firstErrorTarget.section = 'rules';
            firstErrorTarget.ruleIndex = ruleIndex;
            firstErrorTarget.conditionIndex = conditionIndex;
            firstErrorTarget.fieldKey = 'conditionalField2';
          }
        }
        if (!condition?.['equation-2']) {
          conditionErrors.equation2 = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select a second equation.', 'tsjippy');
          if (firstErrorTarget.section === null) {
            firstErrorTarget.section = 'rules';
            firstErrorTarget.ruleIndex = ruleIndex;
            firstErrorTarget.conditionIndex = conditionIndex;
            firstErrorTarget.fieldKey = 'equation2';
          }
        }
      }
      if (Object.keys(conditionErrors).length > 0) {
        fieldErrors.rules[ruleIndex][conditionIndex] = conditionErrors;
        errors.push((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Rule %1$d, condition %2$d has validation errors.', 'tsjippy'), ruleIndex + 1, conditionIndex + 1));
      }
    });
  });
  actions.forEach((actionItem, actionIndex) => {
    const actionErrors = {};
    if (!actionItem?.action) {
      actionErrors.action = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select an action.', 'tsjippy');
      if (firstErrorTarget.section === null) {
        firstErrorTarget.section = 'actions';
        firstErrorTarget.actionIndex = actionIndex;
        firstErrorTarget.fieldKey = 'action';
      }
    }
    if (!actionItem?.['property-name']) {
      actionErrors.propertyName = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Enter a property name.', 'tsjippy');
      if (firstErrorTarget.section === null) {
        firstErrorTarget.section = 'actions';
        firstErrorTarget.actionIndex = actionIndex;
        firstErrorTarget.fieldKey = 'propertyName';
      }
    }
    if (!actionItem?.['property-value']) {
      actionErrors.propertyValue = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Enter a property value.', 'tsjippy');
      if (firstErrorTarget.section === null) {
        firstErrorTarget.section = 'actions';
        firstErrorTarget.actionIndex = actionIndex;
        firstErrorTarget.fieldKey = 'propertyValue';
      }
    }
    if (Object.keys(actionErrors).length > 0) {
      fieldErrors.actions[actionIndex] = actionErrors;
      errors.push((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Action %d has validation errors.', 'tsjippy'), actionIndex + 1));
    }
  });
  return {
    errors,
    fieldErrors,
    firstErrorTarget
  };
}

/**
 * Conditions modal UI.
 */
function ConditionsModal({
  isVisible,
  onClose,
  elementId,
  allNestedBlocks
}) {
  const {
    saveConditions,
    setError
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useDispatch)('tsjippy-forms/conditions-store');
  const {
    createSuccessNotice,
    createErrorNotice
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useDispatch)('core/notices');
  const conditions = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => select('tsjippy-forms/conditions-store').getConditions(elementId), [elementId]);
  const isLoading = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => select('tsjippy-forms/conditions-store').isLoading(elementId), [elementId]);
  const isSaving = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => select('tsjippy-forms/conditions-store').isSaving(elementId), [elementId]);
  const error = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => select('tsjippy-forms/conditions-store').getError(elementId), [elementId]);
  const hasLoaded = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => select('tsjippy-forms/conditions-store').hasLoaded(elementId), [elementId]);
  const [draftConditions, setDraftConditions] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)({
    rules: [],
    actions: []
  });
  const [successMessage, setSuccessMessage] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)('');
  const [validationErrors, setValidationErrors] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)([]);
  const [fieldErrors, setFieldErrors] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)({});
  const [focusTarget, setFocusTarget] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)(null);
  const [pulseTarget, setPulseTarget] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)(null);
  const formElementOptions = (0,_hooks_useFormElementOptions__WEBPACK_IMPORTED_MODULE_5__.useFormElementOptions)(allNestedBlocks);
  const modalRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useRef)(null);
  const previousBodyOverflow = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useRef)('');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    if (isVisible && conditions) {
      setDraftConditions(deepClone(conditions));
    }
  }, [isVisible, conditions]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    if (!successMessage) {
      return;
    }
    const timer = window.setTimeout(() => {
      setSuccessMessage('');
    }, 3000);
    return () => window.clearTimeout(timer);
  }, [successMessage]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    if (!isVisible || typeof document === 'undefined') {
      return;
    }
    previousBodyOverflow.current = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => {
      document.body.style.overflow = previousBodyOverflow.current || '';
    };
  }, [isVisible]);
  const handleClose = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(() => {
    const isDirty = JSON.stringify(draftConditions) !== JSON.stringify(conditions);
    if (isDirty) {
      const ok = window.confirm((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You have unsaved changes. Close without saving?', 'tsjippy'));
      if (!ok) {
        return;
      }
    }
    onClose();
  }, [draftConditions, conditions, onClose]);
  const handleOverlayClick = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(() => {
    handleClose();
  }, [handleClose]);
  const stopPropagation = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(event => {
    event.stopPropagation();
  }, []);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    if (!isVisible) {
      return;
    }
    const handleKeyDown = event => {
      if (event.key === 'Escape') {
        handleClose();
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => {
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [isVisible, handleClose]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    if (!focusTarget || !modalRef.current) {
      return;
    }
    const {
      section,
      ruleIndex,
      conditionIndex,
      actionIndex,
      fieldKey
    } = focusTarget;
    let selector = '';
    if (section === 'rules') {
      selector = `[data-rule-index="${ruleIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] input,
				[data-rule-index="${ruleIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] select,
				[data-rule-index="${ruleIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] textarea`;
    }
    if (section === 'actions') {
      selector = `[data-action-index="${actionIndex}"] [data-field-key="${fieldKey}"] input,
				[data-action-index="${actionIndex}"] [data-field-key="${fieldKey}"] select,
				[data-action-index="${actionIndex}"] [data-field-key="${fieldKey}"] textarea`;
    }
    const field = modalRef.current.querySelector(selector);
    if (field && typeof field.focus === 'function') {
      window.requestAnimationFrame(() => {
        field.focus();
        field.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
        setPulseTarget(focusTarget);
        window.setTimeout(() => {
          setPulseTarget(null);
        }, 1600);
      });
    }
    setFocusTarget(null);
  }, [focusTarget]);
  const validation = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useMemo)(() => {
    return validateConditions(draftConditions);
  }, [draftConditions]);
  const isDirty = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useMemo)(() => {
    return JSON.stringify(draftConditions) !== JSON.stringify(conditions);
  }, [draftConditions, conditions]);
  const isValid = validation.errors.length === 0;
  const clearSuccessMessage = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(() => {
    setSuccessMessage('');
  }, []);
  const showToastSuccess = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(message => {
    createSuccessNotice(message, {
      type: 'snackbar',
      isDismissible: true
    });
  }, [createSuccessNotice]);
  const showToastError = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(message => {
    createErrorNotice(message, {
      type: 'snackbar',
      isDismissible: true
    });
  }, [createErrorNotice]);

  /**
   * Update one field on one condition.
   * IMPORTANT: use value so dynamic fields like "combinator"
   * are written correctly.
   */
  const updateRuleCondition = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)((ruleIndex, subRuleIndex, key, value) => {
    clearSuccessMessage();
    console.log(ruleIndex);
    console.log(subRuleIndex);
    console.log(key);
    console.log(value);
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.rules = Array.isArray(next.rules) ? next.rules : [];
      if (!next.rules[ruleIndex]) {
        next.rules[ruleIndex] = [];
      }
      if (!next.rules[ruleIndex][subRuleIndex]) {
        next.rules[ruleIndex][subRuleIndex] = createEmptyCondition();
      }
      next.rules[ruleIndex][subRuleIndex][key] = value;

      // Add a new sub-rule
      if (key == 'combinator') {
        next.rules[ruleIndex][subRuleIndex + 1] = createEmptyCondition();
      }
      return next;
    });
    setValidationErrors([]);
    setFieldErrors({});
  }, [clearSuccessMessage]);
  const addRule = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(() => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.rules = Array.isArray(next.rules) ? next.rules : [];
      next.rules.push([createEmptyCondition()]);
      return next;
    });
  }, [clearSuccessMessage]);
  const addConditionToExistingRule = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(ruleIndex => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.rules = Array.isArray(next.rules) ? next.rules : [];
      if (!next.rules[ruleIndex]) {
        next.rules[ruleIndex] = [];
      }
      next.rules[ruleIndex].push(createEmptyCondition());
      return next;
    });
  }, [clearSuccessMessage]);
  const duplicateRule = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(ruleIndex => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.rules = Array.isArray(next.rules) ? next.rules : [];
      const ruleToDuplicate = next.rules[ruleIndex];
      if (!ruleToDuplicate) {
        return next;
      }
      const clonedRule = deepClone(ruleToDuplicate);
      next.rules.splice(ruleIndex + 1, 0, clonedRule);
      return next;
    });
  }, [clearSuccessMessage]);
  const deleteRule = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(ruleIndex => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.rules = Array.isArray(next.rules) ? next.rules : [];
      next.rules.splice(ruleIndex, 1);
      return next;
    });
  }, [clearSuccessMessage]);
  const deleteCondition = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)((ruleIndex, subRuleIndex) => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.rules = Array.isArray(next.rules) ? next.rules : [];
      if (!next.rules[ruleIndex]) {
        return next;
      }
      next.rules[ruleIndex].splice(subRuleIndex, 1);
      if (next.rules[ruleIndex].length === 0) {
        next.rules.splice(ruleIndex, 1);
      }
      return next;
    });
  }, [clearSuccessMessage]);
  const moveRule = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)((ruleIndex, direction) => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.rules = Array.isArray(next.rules) ? next.rules : [];
      const targetIndex = ruleIndex + direction;
      if (targetIndex < 0 || targetIndex >= next.rules.length) {
        return next;
      }
      const temp = next.rules[ruleIndex];
      next.rules[ruleIndex] = next.rules[targetIndex];
      next.rules[targetIndex] = temp;
      return next;
    });
  }, [clearSuccessMessage]);
  const moveCondition = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)((ruleIndex, subRuleIndex, direction) => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.rules = Array.isArray(next.rules) ? next.rules : [];
      if (!next.rules[ruleIndex]) {
        return next;
      }
      const targetIndex = subRuleIndex + direction;
      if (targetIndex < 0 || targetIndex >= next.rules[ruleIndex].length) {
        return next;
      }
      const temp = next.rules[ruleIndex][subRuleIndex];
      next.rules[ruleIndex][subRuleIndex] = next.rules[ruleIndex][targetIndex];
      next.rules[ruleIndex][targetIndex] = temp;
      return next;
    });
  }, [clearSuccessMessage]);
  const addAction = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(() => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.actions = Array.isArray(next.actions) ? next.actions : [];
      next.actions.push(createEmptyAction());
      return next;
    });
  }, [clearSuccessMessage]);
  const updateAction = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)((actionIndex, key, value) => {
    clearSuccessMessage();
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.actions = Array.isArray(next.actions) ? next.actions : [];
      if (!next.actions[actionIndex]) {
        next.actions[actionIndex] = createEmptyAction();
      }
      next.actions[actionIndex][key] = value;
      return next;
    });
    setValidationErrors([]);
    setFieldErrors({});
  }, [clearSuccessMessage]);
  const duplicateAction = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(actionIndex => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.actions = Array.isArray(next.actions) ? next.actions : [];
      const actionToDuplicate = next.actions[actionIndex];
      if (!actionToDuplicate) {
        return next;
      }
      next.actions.splice(actionIndex + 1, 0, deepClone(actionToDuplicate));
      return next;
    });
  }, [clearSuccessMessage]);
  const deleteAction = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(actionIndex => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.actions = Array.isArray(next.actions) ? next.actions : [];
      next.actions.splice(actionIndex, 1);
      return next;
    });
  }, [clearSuccessMessage]);
  const moveAction = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)((actionIndex, direction) => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.actions = Array.isArray(next.actions) ? next.actions : [];
      const targetIndex = actionIndex + direction;
      if (targetIndex < 0 || targetIndex >= next.actions.length) {
        return next;
      }
      const temp = next.actions[actionIndex];
      next.actions[actionIndex] = next.actions[targetIndex];
      next.actions[targetIndex] = temp;
      return next;
    });
  }, [clearSuccessMessage]);
  const handleSave = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(async () => {
    const result = validateConditions(draftConditions);
    if (result.errors.length > 0) {
      setValidationErrors(result.errors);
      setFieldErrors(result.fieldErrors);
      setFocusTarget(result.firstErrorTarget);
      setPulseTarget(result.firstErrorTarget);
      showToastError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Please fix the invalid conditions before saving.', 'tsjippy'));
      return;
    }
    try {
      setValidationErrors([]);
      setFieldErrors({});
      setError(elementId, null);
      await saveConditions(elementId, draftConditions);
      setSuccessMessage((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Conditions saved successfully.', 'tsjippy'));
      showToastSuccess((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Conditions saved successfully.', 'tsjippy'));
    } catch (saveError) {
      const message = saveError?.message || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Saving failed. Please try again.', 'tsjippy');
      setError(elementId, message);
      showToastError(message);
    }
  }, [draftConditions, elementId, saveConditions, setError, showToastError, showToastSuccess]);
  const handleReset = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(() => {
    setDraftConditions(deepClone(conditions));
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
    showToastSuccess((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Changes reset.', 'tsjippy'));
  }, [conditions, clearSuccessMessage, showToastSuccess]);
  const renderActionRow = (actionItem, actionIndex) => {
    const actionErrors = fieldErrors?.actions?.[actionIndex] || {};
    const isPulsed = pulseTarget && pulseTarget.section === 'actions' && pulseTarget.actionIndex === actionIndex;
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("div", {
      className: `condition-row__item ${Object.keys(actionErrors).length > 0 ? 'condition-row__item--invalid' : ''} ${isPulsed ? 'condition-row__item--pulse' : ''}`,
      "data-action-index": actionIndex,
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Action', 'tsjippy'),
        value: actionItem?.action || '',
        options: [{
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select action', 'tsjippy'),
          value: ''
        }, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Set property', 'tsjippy'),
          value: 'set-property'
        }, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add value', 'tsjippy'),
          value: 'add-value'
        }, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Subtract value', 'tsjippy'),
          value: 'subtract-value'
        }, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Toggle', 'tsjippy'),
          value: 'toggle'
        }],
        onChange: value => updateAction(actionIndex, 'action', value),
        help: actionErrors.action || '',
        "data-field-key": "action"
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Property name', 'tsjippy'),
        value: actionItem?.['property-name'] || '',
        onChange: value => updateAction(actionIndex, 'property-name', value),
        help: actionErrors.propertyName || '',
        "data-field-key": "propertyName"
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Property value', 'tsjippy'),
        value: actionItem?.['property-value'] || '',
        onChange: value => updateAction(actionIndex, 'property-value', value),
        help: actionErrors.propertyValue || '',
        "data-field-key": "propertyValue"
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Property name 1', 'tsjippy'),
        value: actionItem?.['property-name1'] || '',
        onChange: value => updateAction(actionIndex, 'property-name1', value),
        "data-field-key": "propertyName1"
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Action value', 'tsjippy'),
        value: actionItem?.['action-value'] || '',
        onChange: value => updateAction(actionIndex, 'action-value', value),
        "data-field-key": "actionValue"
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Addition', 'tsjippy'),
        value: actionItem?.addition || '',
        onChange: value => updateAction(actionIndex, 'addition', value),
        "data-field-key": "addition"
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("div", {
        className: "condition-row__actions",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "secondary",
          onClick: addAction,
          icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_9__["default"],
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add another action', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "secondary",
          onClick: () => duplicateAction(actionIndex),
          icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_8__["default"],
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Duplicate action', 'tsjippy')
        }), actionIndex > 0 && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "secondary",
          onClick: () => moveAction(actionIndex, -1),
          icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_7__["default"],
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Move action up', 'tsjippy')
        }), actionIndex < (draftConditions.actions || []).length - 1 && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "secondary",
          onClick: () => moveAction(actionIndex, 1),
          icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_6__["default"],
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Move action down', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "secondary",
          isDestructive: true,
          onClick: () => deleteAction(actionIndex),
          icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_10__["default"],
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Delete action', 'tsjippy')
        })]
      })]
    }, actionIndex);
  };
  const renderContent = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(() => {
    if (isLoading && !hasLoaded) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Spinner, {});
    }
    if (error) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Notice, {
        status: "error",
        isDismissible: false,
        children: [(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Error:', 'tsjippy'), " ", error]
      });
    }
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.Fragment, {
      children: [successMessage && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Notice, {
        status: "success",
        isDismissible: true,
        onRemove: clearSuccessMessage,
        children: successMessage
      }), validationErrors.length > 0 && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Notice, {
        status: "error",
        isDismissible: true,
        onRemove: () => setValidationErrors([]),
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("strong", {
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Please fix the following issues:', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("ul", {
          style: {
            marginTop: '8px',
            marginBottom: 0,
            paddingLeft: '18px'
          },
          children: validationErrors.map((item, index) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("li", {
            children: item
          }, index))
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("div", {
        ref: modalRef,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("h3", {
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Rules', 'tsjippy')
        }), (draftConditions.rules || []).length === 0 ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.Fragment, {
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("p", {
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No rules defined yet.', 'tsjippy')
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
            variant: "primary",
            onClick: addRule,
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add first rule', 'tsjippy')
          })]
        }) : (draftConditions.rules || []).map((rule, ruleIndex) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("div", {
          className: `condition-row ${Array.isArray(rule) && rule.length === 0 ? 'condition-row--empty' : ''}`,
          "data-rule-index": ruleIndex,
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("span", {
            className: "condition-if",
            children: "If"
          }), (Array.isArray(rule) ? rule : []).map((condition, subRuleIndex) => {
            const hasErrors = fieldErrors?.rules?.[ruleIndex]?.[subRuleIndex] && Object.keys(fieldErrors.rules[ruleIndex][subRuleIndex]).length > 0;
            const isPulsed = pulseTarget && pulseTarget.section === 'rules' && pulseTarget.ruleIndex === ruleIndex && pulseTarget.conditionIndex === subRuleIndex;
            return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("div", {
              className: `condition-row__item ${hasErrors ? 'condition-row__item--invalid' : ''} ${isPulsed ? 'condition-row__item--pulse' : ''}`,
              "data-condition-index": subRuleIndex,
              children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_ConditionRow__WEBPACK_IMPORTED_MODULE_4__["default"], {
                condition: condition,
                ruleIndex: ruleIndex,
                subRuleIndex: subRuleIndex,
                formElementOptions: formElementOptions,
                onUpdate: updateRuleCondition,
                onDeleteRule: () => deleteRule(ruleIndex),
                onMoveRuleUp: () => moveRule(ruleIndex, -1),
                onMoveRuleDown: () => moveRule(ruleIndex, 1),
                canMoveRuleUp: ruleIndex > 0,
                canMoveRuleDown: ruleIndex < draftConditions.rules.length - 1,
                fieldErrors: fieldErrors?.rules?.[ruleIndex]?.[subRuleIndex] || {}
              })
            }, subRuleIndex);
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("div", {
            className: "condition-row__actions",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
              variant: "secondary",
              onClick: () => addConditionToExistingRule(ruleIndex),
              icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_9__["default"],
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add another condition', 'tsjippy')
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
              variant: "secondary",
              onClick: () => duplicateRule(ruleIndex),
              icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_8__["default"],
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Duplicate condition', 'tsjippy')
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
              variant: "secondary",
              isDestructive: true,
              onClick: () => deleteCondition(ruleIndex, subRuleIndex),
              icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_10__["default"],
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Delete condition', 'tsjippy')
            })]
          })]
        }, ruleIndex)), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("div", {
          style: {
            marginTop: '16px'
          },
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
            variant: "primary",
            onClick: addRule,
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add new rule', 'tsjippy')
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("h3", {
          style: {
            marginTop: '32px'
          },
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Actions', 'tsjippy')
        }), (draftConditions.actions || []).length === 0 ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.Fragment, {
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("p", {
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No actions defined yet.', 'tsjippy')
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
            variant: "primary",
            onClick: addAction,
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add first action', 'tsjippy')
          })]
        }) : (draftConditions.actions || []).map((actionItem, actionIndex) => renderActionRow(actionItem, actionIndex))]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("div", {
        style: {
          marginTop: '16px',
          display: 'flex',
          gap: '8px',
          flexWrap: 'wrap'
        },
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "primary",
          onClick: addRule,
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add new rule', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "primary",
          onClick: addAction,
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add new action', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "primary",
          onClick: handleSave,
          disabled: !isDirty || !isValid || isSaving,
          children: isSaving ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Saving...', 'tsjippy') : isDirty ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Save conditions', 'tsjippy') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Saved', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "secondary",
          onClick: handleReset,
          disabled: !isDirty,
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Reset changes', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "secondary",
          onClick: handleClose,
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Close', 'tsjippy')
        })]
      }), isDirty && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("p", {
        style: {
          marginTop: '12px',
          color: '#b45309'
        },
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You have unsaved changes.', 'tsjippy')
      })]
    });
  }, [addAction, addConditionToExistingRule, addRule, clearSuccessMessage, conditions, deleteCondition, deleteRule, draftConditions, error, fieldErrors, formElementOptions, handleClose, handleReset, handleSave, hasLoaded, isDirty, isLoading, isSaving, moveAction, moveCondition, moveRule, pulseTarget, successMessage, updateAction, updateRuleCondition, validationErrors]);
  if (!isVisible || typeof document === 'undefined') {
    return null;
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.createPortal)(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("div", {
    id: "element-conditions-modal",
    className: "modal",
    onClick: handleOverlayClick,
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("div", {
      className: "modal-content",
      onClick: stopPropagation,
      onKeyDown: stopPropagation,
      style: {
        maxWidth: '90vw'
      },
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("span", {
        className: "close mobile-sticky",
        onClick: handleClose,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("svg", {
          width: "24",
          height: "24",
          viewBox: "0 0 24 24",
          fill: "none",
          stroke: "currentColor",
          strokeWidth: "2",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("line", {
            x1: "18",
            y1: "6",
            x2: "6",
            y2: "18"
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("line", {
            x1: "6",
            y1: "6",
            x2: "18",
            y2: "18"
          })]
        })
      }), renderContent()]
    })
  }), document.body);
}

/***/ },

/***/ "./src/formbuilder/edit.js"
/*!*********************************!*\
  !*** ./src/formbuilder/edit.js ***!
  \*********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/plus.mjs");
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./editor.scss */ "./src/formbuilder/editor.scss");
/* harmony import */ var _filters_addButtonToInnerBlocks_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./filters/addButtonToInnerBlocks.js */ "./src/formbuilder/filters/addButtonToInnerBlocks.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__);










/* Default inner block template for the form. */

const MY_TEMPLATE = [['tsjippy-forms/input', {
  type: 'submit',
  name: 'submit',
  value: 'Submit the form'
}]];

/**
 * Gutenberg block edit component.
 * This is the editor-side UI for the form block.
 */
function Edit({
  attributes,
  setAttributes,
  clientId
}) {
  const {
    name = '',
    id = -1,
    actions = [],
    roles = [],
    method = 'post'
  } = attributes;

  /* Local state for available roles and actions fetched from the API. */
  const [availableRoles, setAvailableRoles] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useState)([]);
  const [availableActions, setAvailableActions] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useState)([]);
  const [isEmailsFormVisible, setEmailsFormVisibility] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useState)(false);
  const [isRemindersFormVisible, setRemindersFormVisibility] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useState)(false);

  /* Register the form if it has a name but has not been saved yet. */
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    if (!name || id !== -1) {
      return;
    }
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default()({
      path: `${tsjippy.restApiPrefix}/forms/register_form`,
      method: 'POST',
      data: {
        name
      }
    }).then(res => {
      if (res?.id) {
        setAttributes({
          id: res.id
        });
      }
    });
  }, [name, id, setAttributes]);

  /* Load available roles from the server for the inspector panel. */
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default()({
      path: `${tsjippy.restApiPrefix}/forms/get_roles`,
      method: 'POST'
    }).then(res => {
      setAvailableRoles(Array.isArray(res) ? res : []);
    });
  }, []);

  /* Load available actions from the server for the inspector panel. */
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default()({
      path: `${tsjippy.restApiPrefix}/forms/get_form_actions`,
      method: 'POST'
    }).then(res => {
      setAvailableActions(Array.isArray(res) ? res : []);
    });
  }, []);

  /* Read inner blocks so the editor can inspect nested form elements if needed. */
  (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.useSelect)(select => select('core/block-editor').getBlocks(clientId), [clientId]);

  /* Block wrapper props. */
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps)();

  /* Configure inner blocks and custom appender. */
  const {
    children,
    ...innerBlocksProps
  } = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useInnerBlocksProps)(blockProps, {
    orientation: 'vertical',
    template: MY_TEMPLATE,
    renderAppender: () => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.Inserter, {
      rootClientId: clientId,
      isAppender: true,
      renderToggle: ({
        onToggle
      }) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
        variant: "primary",
        onClick: onToggle,
        icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_6__["default"],
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add More Form Blocks', 'tsjippy')
      })
    })
  });

  /* Add or remove a role from the stored attributes. */
  const onRoleSelected = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useCallback)((checked, roleSlug) => {
    let nextRoles = Array.isArray(roles) ? [...roles] : [];
    if (checked) {
      if (!nextRoles.includes(roleSlug)) {
        nextRoles.push(roleSlug);
      }
    } else {
      nextRoles = nextRoles.filter(role => role !== roleSlug);
    }
    setAttributes({
      roles: nextRoles
    });
  }, [roles, setAttributes]);

  /* Add or remove an action from the stored attributes. */
  const actionSelected = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useCallback)((checked, action) => {
    let nextActions = Array.isArray(actions) ? [...actions] : [];
    if (checked) {
      if (!nextActions.includes(action)) {
        nextActions.push(action);
      }
    } else {
      nextActions = nextActions.filter(item => item !== action);
    }
    setAttributes({
      actions: nextActions
    });
  }, [actions, setAttributes]);

  /* Build role checkboxes for the inspector panel. */
  const getRoleCheckboxes = () => {
    if (!availableRoles.length) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("p", {
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No roles available.', 'tsjippy')
      });
    }
    return availableRoles.map(role => {
      const roleSlug = role.slug || role.value || role;
      const roleLabel = role.label || role.name || roleSlug;
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CheckboxControl, {
        label: roleLabel,
        checked: (roles || []).includes(roleSlug),
        onChange: checked => onRoleSelected(checked, roleSlug)
      }, roleSlug);
    });
  };

  /* Build action checkboxes for the inspector panel. */
  const getActionCheckboxes = () => {
    if (!availableActions.length) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("p", {
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No actions available.', 'tsjippy')
      });
    }
    return availableActions.map(action => {
      const actionSlug = action.slug || action.value || action;
      const actionLabel = action.label || action.name || actionSlug;
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CheckboxControl, {
        label: actionLabel,
        checked: (actions || []).includes(actionSlug),
        onChange: checked => actionSelected(checked, actionSlug)
      }, actionSlug);
    });
  };

  /* Toggleable placeholder panels for additional form-related UI. */
  const resultingForm = () => {
    if (isEmailsFormVisible) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("div", {
        className: "tsjippy-form-secondary-panel",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("p", {
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Emails form is visible.', 'tsjippy')
        })
      });
    }
    if (isRemindersFormVisible) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("div", {
        className: "tsjippy-form-secondary-panel",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("p", {
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Reminders form is visible.', 'tsjippy')
        })
      });
    }
    return null;
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form Settings', 'tsjippy'),
        initialOpen: true,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.RadioControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form Method', 'tsjippy'),
          help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('The type of the form. Get adds values to the URL. Post submits invisibly.', 'tsjippy'),
          selected: method,
          options: [{
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Get', 'tsjippy'),
            value: 'get'
          }, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Post', 'tsjippy'),
            value: 'post'
          }],
          onChange: nextMethod => setAttributes({
            method: nextMethod
          })
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Roles', 'tsjippy'),
        initialOpen: false,
        children: getRoleCheckboxes()
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Actions', 'tsjippy'),
        initialOpen: false,
        children: getActionCheckboxes()
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Extra Forms', 'tsjippy'),
        initialOpen: false,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
          variant: "secondary",
          onClick: () => setEmailsFormVisibility(prev => !prev),
          children: isEmailsFormVisible ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Hide Emails Form', 'tsjippy') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Show Emails Form', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
          variant: "secondary",
          onClick: () => setRemindersFormVisibility(prev => !prev),
          style: {
            marginLeft: '8px'
          },
          children: isRemindersFormVisible ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Hide Reminders Form', 'tsjippy') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Show Reminders Form', 'tsjippy')
        })]
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)("div", {
      ...innerBlocksProps,
      children: [resultingForm(), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks, {
        allowedBlocks: ['tsjippy-forms/input', 'tsjippy-forms/label'],
        template: MY_TEMPLATE,
        renderAppender: _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks.ButtonBlockAppender
      })]
    })]
  });
}

/***/ },

/***/ "./src/formbuilder/filters/addButtonToInnerBlocks.js"
/*!***********************************************************!*\
  !*** ./src/formbuilder/filters/addButtonToInnerBlocks.js ***!
  \***********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/seen.mjs");
/* harmony import */ var _components_ConditionsModal__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../components/ConditionsModal */ "./src/formbuilder/components/ConditionsModal.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__);









function getAllInnerBlocks(blocks) {
  let allBlocks = [];
  (blocks || []).forEach(block => {
    allBlocks.push(block);
    if (block.innerBlocks && block.innerBlocks.length > 0) {
      allBlocks = allBlocks.concat(getAllInnerBlocks(block.innerBlocks));
    }
  });
  return allBlocks;
}
function isInsideFormBuilder(clientId) {
  const parentIds = wp.data.select('core/block-editor').getBlockParents(clientId);
  const parents = wp.data.select('core/block-editor').getBlocksByClientId(parentIds);
  let parentForm = null;
  for (const parent of parents) {
    if (parent?.name === 'tsjippy-forms/formbuilder') {
      parentForm = parent;
      break;
    }
  }
  return parentForm;
}
const addButtonToInnerBlocks = (0,_wordpress_compose__WEBPACK_IMPORTED_MODULE_4__.createHigherOrderComponent)(BlockEdit => {
  return props => {
    if (!props.isSelected) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(BlockEdit, {
        ...props
      });
    }
    const parentForm = isInsideFormBuilder(props.clientId);
    if (!parentForm) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(BlockEdit, {
        ...props
      });
    }
    const allNestedBlocks = getAllInnerBlocks(parentForm.innerBlocks || []);
    const [isConditionsFormVisible, setConditionsFormVisibility] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useState)(false);
    const toggleConditionsForm = () => {
      setConditionsFormVisibility(prev => !prev);
    };
    const buttonText = isConditionsFormVisible ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Close Conditions Form', 'tsjippy') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Set Input Conditions', 'tsjippy');
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.Fragment, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.BlockControls, {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToolbarGroup, {
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToolbarButton, {
            icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_6__["default"],
            label: buttonText,
            onClick: toggleConditionsForm
          })
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_components_ConditionsModal__WEBPACK_IMPORTED_MODULE_7__["default"], {
        isVisible: isConditionsFormVisible,
        onClose: toggleConditionsForm,
        elementId: props.clientId,
        allNestedBlocks: allNestedBlocks
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(BlockEdit, {
        ...props
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
          title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Block Conditions', 'tsjippy'),
          initialOpen: false,
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_8__.jsx)("p", {
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Use the toolbar button to open or close the conditions editor.', 'tsjippy')
          })
        })
      })]
    });
  };
}, 'addButtonToInnerBlocks');
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_5__.addFilter)('editor.BlockEdit', 'tsjippy-forms/add-conditions-button', addButtonToInnerBlocks);

/***/ },

/***/ "./src/formbuilder/hooks/useFormElementOptions.js"
/*!********************************************************!*\
  !*** ./src/formbuilder/hooks/useFormElementOptions.js ***!
  \********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useFormElementOptions: () => (/* binding */ useFormElementOptions)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function useFormElementOptions(allNestedBlocks) {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useMemo)(() => {
    return (allNestedBlocks || []).map(block => {
      let name = block.attributes?.name ?? block.attributes?.text ?? '';
      let label = block.name;
      if (name !== '') {
        label += `: ${name}`;
      }
      return {
        label,
        value: block.clientId
      };
    });
  }, [allNestedBlocks]);
}

/***/ },

/***/ "./src/formbuilder/index.js"
/*!**********************************!*\
  !*** ./src/formbuilder/index.js ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./src/formbuilder/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/formbuilder/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./save */ "./src/formbuilder/save.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./block.json */ "./src/formbuilder/block.json");
/* harmony import */ var _store_conditions_store__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./store/conditions-store */ "./src/formbuilder/store/conditions-store.js");
/* harmony import */ var _filters_addButtonToInnerBlocks__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./filters/addButtonToInnerBlocks */ "./src/formbuilder/filters/addButtonToInnerBlocks.js");
/* harmony import */ var _styles_conditions_css__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./styles/conditions.css */ "./src/formbuilder/styles/conditions.css");
/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */


/**
 * Internal dependencies
 */







/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_4__.name, {
  /**
   * @see ./edit.js
   */
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"],
  /**
   * @see ./save.js
   */
  save: _save__WEBPACK_IMPORTED_MODULE_3__["default"]
});

/***/ },

/***/ "./src/formbuilder/save.js"
/*!*********************************!*\
  !*** ./src/formbuilder/save.js ***!
  \*********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ save)
/* harmony export */ });
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__);
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */


/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */

function save({
  attributes
}) {
  const blockProps = _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.useBlockProps.save();
  const innerBlocksProps = _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.useInnerBlocksProps.save(blockProps);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("form", {
    method: attributes.method,
    target: attributes.target,
    autocomplete: attributes.autocomplete,
    ...innerBlocksProps
  });
}

/***/ },

/***/ "./src/formbuilder/store/conditions-store.js"
/*!***************************************************!*\
  !*** ./src/formbuilder/store/conditions-store.js ***!
  \***************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__);



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
  loadedByElement: {}
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
      'equation-2': ''
    };
  }
  return {
    'conditional-field': condition['conditional-field'] || '',
    equation: condition.equation || '',
    'conditional-value': condition['conditional-value'] || '',
    combinator: condition.combinator || 'and',
    'conditional-field-2': condition['conditional-field-2'] || '',
    'equation-2': condition['equation-2'] || ''
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
      addition: ''
    };
  }
  return {
    action: action.action || '',
    'property-name': action['property-name'] || '',
    'property-value': action['property-value'] || '',
    'property-name1': action['property-name1'] || '',
    'action-value': action['action-value'] || '',
    addition: action.addition || ''
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
      actions
    };
  }
  if (Array.isArray(response)) {
    return {
      rules: response,
      actions: []
    };
  }
  return {
    rules: [],
    actions: []
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
    rules: ensureArray(raw.rules).map(rule => {
      if (Array.isArray(rule)) {
        return rule.map(normalizeConditionItem);
      }
      if (rule && typeof rule === 'object') {
        return [normalizeConditionItem(rule)];
      }
      return [normalizeConditionItem()];
    }),
    actions: ensureArray(raw.actions).map(normalizeActionItem)
  };
}

/**
 * Internal API helper for loading conditions.
 * This is used by the resolver and is not exported.
 */
async function fetchConditions(elementId) {
  const response = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default()({
    path: `${tsjippy.restApiPrefix}/forms/get_element_conditions`,
    method: 'POST',
    data: {
      elementId
    }
  });
  return normalizeConditionsStructure(response);
}

/**
 * Internal API helper for saving conditions.
 * This is used by the store-owned save action and is not exported.
 */
async function saveConditionsRequest(elementId, conditions) {
  const response = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default()({
    path: `${tsjippy.restApiPrefix}/forms/save_element_conditions`,
    method: 'POST',
    data: {
      elementId,
      conditions
    }
  });
  const savedConditions = normalizeConditionsStructure(response);
  return {
    rules: savedConditions.rules.length > 0 ? savedConditions.rules : ensureArray(conditions?.rules).map(rule => {
      if (Array.isArray(rule)) {
        return rule.map(normalizeConditionItem);
      }
      if (rule && typeof rule === 'object') {
        return [normalizeConditionItem(rule)];
      }
      return [normalizeConditionItem()];
    }),
    actions: savedConditions.actions.length > 0 ? savedConditions.actions : ensureArray(conditions?.actions).map(normalizeActionItem)
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
      conditions: normalized
    };
  },
  /**
   * Set the loading state for one element.
   */
  setLoading(elementId, isLoading) {
    return {
      type: 'SET_LOADING',
      elementId,
      isLoading: !!isLoading
    };
  },
  /**
   * Set the saving state for one element.
   */
  setSaving(elementId, isSaving) {
    return {
      type: 'SET_SAVING',
      elementId,
      isSaving: !!isSaving
    };
  },
  /**
   * Store an error message for one element.
   */
  setError(elementId, error) {
    return {
      type: 'SET_ERROR',
      elementId,
      error: error || null
    };
  },
  /**
   * Store the loaded flag for one element.
   */
  setLoaded(elementId, loaded) {
    return {
      type: 'SET_LOADED',
      elementId,
      loaded: !!loaded
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
      const savedConditions = yield saveConditionsRequest(elementId, conditions);
      yield actions.setConditions(elementId, savedConditions);
      yield actions.setLoaded(elementId, true);
      return savedConditions;
    } catch (error) {
      yield actions.setError(elementId, error?.message || 'Failed to save element conditions.');
      throw error;
    } finally {
      yield actions.setSaving(elementId, false);
    }
  }
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
          [action.elementId]: normalizeConditionsStructure(action.conditions)
        }
      };
    case 'SET_LOADING':
      return {
        ...state,
        loadingByElement: {
          ...state.loadingByElement,
          [action.elementId]: action.isLoading
        }
      };
    case 'SET_SAVING':
      return {
        ...state,
        savingByElement: {
          ...state.savingByElement,
          [action.elementId]: action.isSaving
        }
      };
    case 'SET_ERROR':
      return {
        ...state,
        errorByElement: {
          ...state.errorByElement,
          [action.elementId]: action.error
        }
      };
    case 'SET_LOADED':
      return {
        ...state,
        loadedByElement: {
          ...state.loadedByElement,
          [action.elementId]: action.loaded
        }
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
    return state.conditionsByElement[elementId] || {
      rules: [],
      actions: []
    };
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
  }
};

/**
 * Resolver for getConditions.
 * The first read of the selector will load data from the server.
 */
const resolvers = {
  getConditions: elementId => async ({
    dispatch
  }) => {
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
      dispatch.setError(elementId, error?.message || 'Failed to load element conditions.');
    } finally {
      dispatch.setLoading(elementId, false);
    }
  }
};

/**
 * Create and register the Gutenberg data store.
 */
const store = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.createReduxStore)(STORE_NAME, {
  reducer,
  actions,
  selectors,
  resolvers
});
(0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.register)(store);

/***/ },

/***/ "./src/formbuilder/styles/conditions.css"
/*!***********************************************!*\
  !*** ./src/formbuilder/styles/conditions.css ***!
  \***********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "./src/formbuilder/editor.scss"
/*!*************************************!*\
  !*** ./src/formbuilder/editor.scss ***!
  \*************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "./src/formbuilder/style.scss"
/*!************************************!*\
  !*** ./src/formbuilder/style.scss ***!
  \************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "react/jsx-runtime"
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
(module) {

module.exports = window["ReactJSXRuntime"];

/***/ },

/***/ "@wordpress/api-fetch"
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
(module) {

module.exports = window["wp"]["apiFetch"];

/***/ },

/***/ "@wordpress/block-editor"
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
(module) {

module.exports = window["wp"]["blockEditor"];

/***/ },

/***/ "@wordpress/blocks"
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
(module) {

module.exports = window["wp"]["blocks"];

/***/ },

/***/ "@wordpress/components"
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
(module) {

module.exports = window["wp"]["components"];

/***/ },

/***/ "@wordpress/compose"
/*!*********************************!*\
  !*** external ["wp","compose"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["compose"];

/***/ },

/***/ "@wordpress/data"
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["data"];

/***/ },

/***/ "@wordpress/element"
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["element"];

/***/ },

/***/ "@wordpress/hooks"
/*!*******************************!*\
  !*** external ["wp","hooks"] ***!
  \*******************************/
(module) {

module.exports = window["wp"]["hooks"];

/***/ },

/***/ "@wordpress/i18n"
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["i18n"];

/***/ },

/***/ "@wordpress/primitives"
/*!************************************!*\
  !*** external ["wp","primitives"] ***!
  \************************************/
(module) {

module.exports = window["wp"]["primitives"];

/***/ },

/***/ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-down.mjs"
/*!********************************************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-down.mjs ***!
  \********************************************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ arrow_down_default)
/* harmony export */ });
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
// packages/icons/src/library/arrow-down.tsx


var arrow_down_default = /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", fill: "currentColor", children: /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.Path, { d: "m16.5 13.5-3.7 3.7V4h-1.5v13.2l-3.8-3.7-1 1 5.5 5.6 5.5-5.6z" }) });

//# sourceMappingURL=arrow-down.mjs.map


/***/ },

/***/ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-up.mjs"
/*!******************************************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-up.mjs ***!
  \******************************************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ arrow_up_default)
/* harmony export */ });
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
// packages/icons/src/library/arrow-up.tsx


var arrow_up_default = /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", fill: "currentColor", children: /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.Path, { d: "M12 3.9 6.5 9.5l1 1 3.8-3.7V20h1.5V6.8l3.7 3.7 1-1z" }) });

//# sourceMappingURL=arrow-up.mjs.map


/***/ },

/***/ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/copy.mjs"
/*!**************************************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/copy.mjs ***!
  \**************************************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ copy_default)
/* harmony export */ });
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
// packages/icons/src/library/copy.tsx


var copy_default = /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", fill: "currentColor", children: /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.Path, { fillRule: "evenodd", clipRule: "evenodd", d: "M5 4.5h11a.5.5 0 0 1 .5.5v11a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V5a.5.5 0 0 1 .5-.5ZM3 5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5Zm17 3v10.75c0 .69-.56 1.25-1.25 1.25H6v1.5h12.75a2.75 2.75 0 0 0 2.75-2.75V8H20Z" }) });

//# sourceMappingURL=copy.mjs.map


/***/ },

/***/ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/plus.mjs"
/*!**************************************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/plus.mjs ***!
  \**************************************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plus_default)
/* harmony export */ });
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
// packages/icons/src/library/plus.tsx


var plus_default = /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", fill: "currentColor", children: /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.Path, { d: "M11 12.5V17.5H12.5V12.5H17.5V11H12.5V6H11V11H6V12.5H11Z" }) });

//# sourceMappingURL=plus.mjs.map


/***/ },

/***/ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/seen.mjs"
/*!**************************************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/seen.mjs ***!
  \**************************************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ seen_default)
/* harmony export */ });
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
// packages/icons/src/library/seen.tsx


var seen_default = /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", fill: "currentColor", children: /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.Path, { d: "M3.99961 13C4.67043 13.3354 4.6703 13.3357 4.67017 13.3359L4.67298 13.3305C4.67621 13.3242 4.68184 13.3135 4.68988 13.2985C4.70595 13.2686 4.7316 13.2218 4.76695 13.1608C4.8377 13.0385 4.94692 12.8592 5.09541 12.6419C5.39312 12.2062 5.84436 11.624 6.45435 11.0431C7.67308 9.88241 9.49719 8.75 11.9996 8.75C14.502 8.75 16.3261 9.88241 17.5449 11.0431C18.1549 11.624 18.6061 12.2062 18.9038 12.6419C19.0523 12.8592 19.1615 13.0385 19.2323 13.1608C19.2676 13.2218 19.2933 13.2686 19.3093 13.2985C19.3174 13.3135 19.323 13.3242 19.3262 13.3305L19.3291 13.3359C19.3289 13.3357 19.3288 13.3354 19.9996 13C20.6704 12.6646 20.6703 12.6643 20.6701 12.664L20.6697 12.6632L20.6688 12.6614L20.6662 12.6563L20.6583 12.6408C20.6517 12.6282 20.6427 12.6108 20.631 12.5892C20.6078 12.5459 20.5744 12.4852 20.5306 12.4096C20.4432 12.2584 20.3141 12.0471 20.1423 11.7956C19.7994 11.2938 19.2819 10.626 18.5794 9.9569C17.1731 8.61759 14.9972 7.25 11.9996 7.25C9.00203 7.25 6.82614 8.61759 5.41987 9.9569C4.71736 10.626 4.19984 11.2938 3.85694 11.7956C3.68511 12.0471 3.55605 12.2584 3.4686 12.4096C3.42484 12.4852 3.39142 12.5459 3.36818 12.5892C3.35656 12.6108 3.34748 12.6282 3.34092 12.6408L3.33297 12.6563L3.33041 12.6614L3.32948 12.6632L3.32911 12.664C3.32894 12.6643 3.32879 12.6646 3.99961 13ZM11.9996 16C13.9326 16 15.4996 14.433 15.4996 12.5C15.4996 10.567 13.9326 9 11.9996 9C10.0666 9 8.49961 10.567 8.49961 12.5C8.49961 14.433 10.0666 16 11.9996 16Z" }) });

//# sourceMappingURL=seen.mjs.map


/***/ },

/***/ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/trash.mjs"
/*!***************************************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/trash.mjs ***!
  \***************************************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ trash_default)
/* harmony export */ });
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
// packages/icons/src/library/trash.tsx


var trash_default = /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", fill: "currentColor", children: /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.Path, { fillRule: "evenodd", clipRule: "evenodd", d: "M12 5.5A2.25 2.25 0 0 0 9.878 7h4.244A2.251 2.251 0 0 0 12 5.5ZM12 4a3.751 3.751 0 0 0-3.675 3H5v1.5h1.27l.818 8.997a2.75 2.75 0 0 0 2.739 2.501h4.347a2.75 2.75 0 0 0 2.738-2.5L17.73 8.5H19V7h-3.325A3.751 3.751 0 0 0 12 4Zm4.224 4.5H7.776l.806 8.861a1.25 1.25 0 0 0 1.245 1.137h4.347a1.25 1.25 0 0 0 1.245-1.137l.805-8.861Z" }) });

//# sourceMappingURL=trash.mjs.map


/***/ },

/***/ "./src/formbuilder/block.json"
/*!************************************!*\
  !*** ./src/formbuilder/block.json ***!
  \************************************/
(module) {

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"tsjippy-forms/formbuilder","version":"0.1.0","title":"Form Builder Test","category":"form-elements","icon":"forms","description":"Form builder using blocks","example":{},"supports":{"html":false},"textdomain":"tsjippy","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","viewScript":"file:./view.js","attributes":{"id":{"type":"integer","default":-1},"method":{"type":"string","default":"post"},"target":{"type":"string","default":"_self"},"autocomplete":{"type":"boolean","default":true},"submission_message":{"type":"string","default":"Succesfully received your request"},"submission_id":{"type":"boolean","default":true},"name":{"type":"string","default":""},"actions":{"type":"array","default":["archive","delete"]},"user_meta":{"type":"boolean","default":true},"edit_roles":{"type":"array","default":[]},"auto_archive_element":{"type":"string","default":""},"auto_archive_value":{"type":"string","default":""},"submission_roles":{"type":"array","default":[]},"split_elements":{"type":"array","default":[]}}}');

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			const e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		const deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			let notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				let [chunkIds, fn, priority] = deferred[i];
/******/ 				let fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					const r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			const getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter/value functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			if(Array.isArray(definition)) {
/******/ 				var i = 0;
/******/ 				while(i < definition.length) {
/******/ 					var key = definition[i++];
/******/ 					var binding = definition[i++];
/******/ 					if(!__webpack_require__.o(exports, key)) {
/******/ 						if(binding === 0) {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, value: definition[i++] });
/******/ 						} else {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, get: binding });
/******/ 						}
/******/ 					} else if(binding === 0) { i++; }
/******/ 				}
/******/ 			} else {
/******/ 				for(var key in definition) {
/******/ 					if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 						Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 					}
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.hasOwn(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		const installedChunks = {
/******/ 			"formbuilder/index": 0,
/******/ 			"formbuilder/style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		const webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			let [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		const chunkLoadingGlobal = globalThis["webpackChunkmy_block"] ||= [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	let __webpack_exports__ = __webpack_require__.O(undefined, ["formbuilder/style-index"], () => (__webpack_require__("./src/formbuilder/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map