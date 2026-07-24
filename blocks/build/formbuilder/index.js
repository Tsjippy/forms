/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

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
/* harmony import */ var _hooks_useFormElementOptions__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../hooks/useFormElementOptions */ "./src/formbuilder/hooks/useFormElementOptions.js");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/copy.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/plus.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/trash.mjs");
/* harmony import */ var _RuleRow__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./RuleRow */ "./src/formbuilder/components/RuleRow.js");
/* harmony import */ var _input_element_attributes_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./../../input/element_attributes.js */ "./src/input/element_attributes.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__);









/**
 * Create a blank condition object.
 */

function createEmptyRule() {
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
function validateConditions(conditions) {
  const errors = [];
  const fieldErrors = [{
    rules: [{}],
    actions: [{}]
  }];
  const firstErrorTarget = {
    section: null,
    conditionIndex: null,
    ruleIndex: null,
    actionIndex: null,
    fieldKey: null
  };
  conditions = Array.isArray(conditions) ? conditions : [];
  const rules = Array.isArray(conditions[0]?.rules) ? conditions[0].rules : [];
  const actions = Array.isArray(conditions[0]?.actions) ? conditions[0].actions : [];
  if (rules.length === 0) {
    errors.push((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('At least one rule group is required.', 'tsjippy'));
  }

  /**
   * Loop over all conditions
   */
  conditions.forEach((condition, conditionIndex) => {
    if (!Array.isArray(condition.rules) || condition.rules.length === 0) {
      errors.push((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Condition %d must contain at least one rule.', 'tsjippy'), conditionIndex + 1));
      if (firstErrorTarget.section === null) {
        firstErrorTarget.section = 'rules';
        firstErrorTarget.conditionIndex = conditionIndex;
        firstErrorTarget.ruleIndex = 0;
        firstErrorTarget.fieldKey = 'conditionalField';
      }
      return;
    }
    if (!Array.isArray(condition.actions) || condition.actions.length === 0) {
      errors.push((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Condition %d must contain at least one action.', 'tsjippy'), conditionIndex + 1));
      if (firstErrorTarget.section === null) {
        firstErrorTarget.section = 'actions';
        firstErrorTarget.conditionIndex = conditionIndex;
        firstErrorTarget.ruleIndex = 0;
        firstErrorTarget.fieldKey = 'conditionalField';
      }
    }

    /**
     * Loop over all rules of this condition
     * And check validity
     */
    condition.rules.forEach((rule, ruleIndex) => {
      ((fieldErrors[conditionIndex] ||= {}).rules ||= [])[ruleIndex] ||= {};
      const ruleErrors = {};
      if (!rule?.['conditional-field']) {
        ruleErrors.conditionalField = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select an element.', 'tsjippy');
        if (firstErrorTarget.section === null) {
          firstErrorTarget.section = 'rules';
          firstErrorTarget.conditionIndex = conditionIndex;
          firstErrorTarget.ruleIndex = ruleIndex;
          firstErrorTarget.fieldKey = 'conditionalField';
        }
      }
      if (!rule?.equation) {
        ruleErrors.equation = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select an equation.', 'tsjippy');
        if (firstErrorTarget.section === null) {
          firstErrorTarget.section = 'rules';
          firstErrorTarget.conditionIndex = conditionIndex;
          firstErrorTarget.ruleIndex = ruleIndex;
          firstErrorTarget.fieldKey = 'equation';
        }
      }
      if (isEquationRequiringValue(rule?.equation)) {
        const value = rule?.['conditional-value'];
        if (value === undefined || value === null || String(value).trim() === '') {
          ruleErrors.conditionalValue = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Enter a value.', 'tsjippy');
          if (firstErrorTarget.section === null) {
            firstErrorTarget.section = 'rules';
            firstErrorTarget.conditionIndex = conditionIndex;
            firstErrorTarget.ruleIndex = ruleIndex;
            firstErrorTarget.fieldKey = 'conditionalValue';
          }
        }
      }
      if (rule?.equation === '+' || rule?.equation === '-') {
        if (!rule?.['conditional-field-2']) {
          ruleErrors.conditionalField2 = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select a second element.', 'tsjippy');
          if (firstErrorTarget.section === null) {
            firstErrorTarget.section = 'rules';
            firstErrorTarget.conditionIndex = conditionIndex;
            firstErrorTarget.ruleIndex = ruleIndex;
            firstErrorTarget.fieldKey = 'conditionalField2';
          }
        }
        if (!rule?.['equation-2']) {
          ruleErrors.equation2 = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select a second equation.', 'tsjippy');
          if (firstErrorTarget.section === null) {
            firstErrorTarget.section = 'rules';
            firstErrorTarget.conditionIndex = conditionIndex;
            firstErrorTarget.ruleIndex = ruleIndex;
            firstErrorTarget.fieldKey = 'equation2';
          }
        }
      }
      if (Object.keys(ruleErrors).length > 0) {
        fieldErrors[conditionIndex].rules[ruleIndex] = ruleErrors;
        errors.push((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Condition %1$d, rule %2$d has validation errors.', 'tsjippy'), conditionIndex + 1, ruleIndex + 1));
      }
    });

    /**
     * Loop over all actions of this condition
     * And check validity
     */
    condition.actions.forEach((actionItem, actionIndex) => {
      const actionErrors = {};
      if (!actionItem?.action) {
        actionErrors.action = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select an action.', 'tsjippy');
        if (firstErrorTarget.section === null) {
          firstErrorTarget.section = 'actions';
          firstErrorTarget.actionIndex = actionIndex;
          firstErrorTarget.fieldKey = 'action';
        }
      }
      if (actionItem?.action == 'set-property') {
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
      }
      if (Object.keys(actionErrors).length > 0) {
        ((fieldErrors[conditionIndex] ||= {}).actions ||= [])[actionIndex] ||= {};
        fieldErrors[conditionIndex].actions[actionIndex] = actionErrors;
        errors.push((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Condition %1$d, action %d has validation errors.', 'tsjippy'), conditionIndex + 1, actionIndex + 1));
      }
    });
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
  allNestedBlocks,
  blockProps
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

  /**
   * A conditions is an array of condition arrays
   * Each condition has one or more rules
   * And one or more actions
   */
  const [draftConditions, setDraftConditions] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)([{
    rules: [createEmptyRule()],
    actions: [createEmptyAction()]
  }]);
  const [successMessage, setSuccessMessage] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)('');
  const [validationErrors, setValidationErrors] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)([]);
  const [fieldErrors, setFieldErrors] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)({});
  const [focusTarget, setFocusTarget] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)(null);
  const [pulseTarget, setPulseTarget] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)(null);
  const formElementOptions = (0,_hooks_useFormElementOptions__WEBPACK_IMPORTED_MODULE_4__.useFormElementOptions)(allNestedBlocks);
  const modalRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useRef)(null);
  const previousBodyOverflow = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useRef)('');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    if (isVisible && Array.isArray(conditions)) {
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
      conditionIndex,
      ruleIndex,
      actionIndex,
      fieldKey
    } = focusTarget;
    let selector = '';
    if (section === 'rules') {
      selector = `[data-rule-index="${conditionIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] input,
				[data-rule-index="${conditionIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] select,
				[data-rule-index="${conditionIndex}"] [data-condition-index="${conditionIndex}"] [data-field-key="${fieldKey}"] textarea`;
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
    const result = validateConditions(draftConditions);
    if (result.errors.length > 0) {
      setValidationErrors(result.errors);
      setFieldErrors(result.fieldErrors);
      setFocusTarget(result.firstErrorTarget);
      setPulseTarget(result.firstErrorTarget);
    }
    return result;
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
  const addCondition = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(() => {
    resetErrors();
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.push({
        rules: [createEmptyRule()],
        actions: [createEmptyAction()]
      });
      return next;
    });
  }, [clearSuccessMessage]);

  /**
   * Update one rule on one condition.
   */
  const updateRuleCondition = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)((conditionIndex, ruleIndex, key, value) => {
    setDraftConditions(prev => {
      const next = deepClone(prev);

      /**
       * Create base structure if it does not exist yet
       */
      if (!next[conditionIndex]) {
        next[conditionIndex] = [];
      }
      if (!next[conditionIndex].rules) {
        next[conditionIndex].rules = [];
      }
      if (!next[conditionIndex].actions) {
        next[conditionIndex].actions = [];
      }
      if (!next[conditionIndex].rules[ruleIndex]) {
        next[conditionIndex].rules[ruleIndex] = createEmptyRule();
      }
      next[conditionIndex].rules[ruleIndex][key] = value;

      // Add a new sub-rule
      if (key == 'combinator') {
        next[conditionIndex].rules[ruleIndex + 1] = createEmptyRule();
      }
      return next;
    });
    resetErrors();
  }, [clearSuccessMessage]);
  const addRule = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(conditionIndex => {
    resetErrors();
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next[conditionIndex].rules = Array.isArray(next[conditionIndex].rules) ? next[conditionIndex].rules : [];
      next[conditionIndex].rules.push(createEmptyRule());
      return next;
    });
  }, [clearSuccessMessage]);
  const duplicateRule = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(conditionIndex => {
    resetErrors();
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next[conditionIndex].rules = Array.isArray(next[conditionIndex].rules) ? next[conditionIndex].rules : [];
      const ruleToDuplicate = next[conditionIndex].rules;
      if (!ruleToDuplicate) {
        return next;
      }
      const clonedRule = deepClone(ruleToDuplicate);
      next[conditionIndex].rules.splice(conditionIndex + 1, 0, clonedRule);
      return next;
    });
  }, [clearSuccessMessage]);
  const deleteCondition = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(conditionIndex => {
    resetErrors();
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.splice(conditionIndex, 1);
      return next;
    });
  }, [clearSuccessMessage]);
  const deleteRule = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)((conditionIndex, ruleIndex) => {
    resetErrors();
    setDraftConditions(prev => {
      const next = deepClone(prev);
      if (!next[conditionIndex].rules) {
        return next;
      }

      // Remove the rule
      next[conditionIndex].rules.splice(ruleIndex, 1);
      return next;
    });
  }, [clearSuccessMessage]);
  const moveRule = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)((conditionIndex, ruleIndex, direction) => {
    resetErrors();
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next[conditionIndex].rules = Array.isArray(next[conditionIndex].rules) ? next[conditionIndex].rules : [];
      const targetIndex = ruleIndex + direction;
      if (targetIndex < 0 || targetIndex >= next[conditionIndex].rules.length) {
        return next;
      }

      // Store the sub rule we are moving
      const temp = next[conditionIndex].rules[ruleIndex];

      // Store the rule that is currently in the desired location in the index of the rule we are moving
      next[conditionIndex].rules[ruleIndex] = next[conditionIndex].rules[targetIndex];

      // Store the rule in the new index
      next[conditionIndex].rules[targetIndex] = temp;
      return next;
    });
  }, [clearSuccessMessage]);
  const addAction = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(conditionIndex => {
    resetErrors();
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next[conditionIndex].actions = Array.isArray(next[conditionIndex].actions) ? next[conditionIndex].actions : [];
      next[conditionIndex].actions.push(createEmptyAction());
      return next;
    });
  }, [clearSuccessMessage]);
  const updateAction = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)((conditionIndex, actionIndex, key, value) => {
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next[conditionIndex].actions = Array.isArray(next[conditionIndex].actions) ? next[conditionIndex].actions : [];
      if (!next[conditionIndex].actions[actionIndex]) {
        next[conditionIndex].actions[actionIndex] = createEmptyAction();
      }
      next[conditionIndex].actions[actionIndex][key] = value;
      return next;
    });
    resetErrors();
  }, [clearSuccessMessage]);
  const deleteAction = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(actionIndex => {
    resetErrors();
    setDraftConditions(prev => {
      const next = deepClone(prev);
      next.actions = Array.isArray(next.actions) ? next.actions : [];
      next.actions.splice(actionIndex, 1);
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
      await saveConditions(elementId, draftConditions);
      resetErrors();
      setSuccessMessage((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Conditions saved successfully.', 'tsjippy'));
      showToastSuccess((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Conditions saved successfully.', 'tsjippy'));
    } catch (saveError) {
      const message = saveError?.message || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Saving failed. Please try again.', 'tsjippy');
      setError(elementId, message);
      showToastError(message);
    }
  }, [draftConditions, elementId, saveConditions, setError, showToastError, showToastSuccess]);
  const resetErrors = () => {
    clearSuccessMessage();
    setValidationErrors([]);
    setFieldErrors({});
  };
  const handleReset = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(() => {
    if (Array.isArray(conditions)) {
      resetErrors();
      setDraftConditions(deepClone(conditions));
      showToastSuccess((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Changes reset.', 'tsjippy'));
    }
  }, [conditions, clearSuccessMessage, showToastSuccess]);
  const renderRuleRow = (rule, ruleIndex, conditionIndex) => {
    const isPulsed = pulseTarget && pulseTarget.section === 'rules' && pulseTarget.ruleIndex === ruleIndex;
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("div", {
      className: `item ${isPulsed ? 'pulse' : ''}`,
      "data-condition-index": ruleIndex,
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_RuleRow__WEBPACK_IMPORTED_MODULE_8__["default"], {
        conditionIndex: conditionIndex,
        rule: rule,
        ruleIndex: ruleIndex,
        formElementOptions: formElementOptions,
        onUpdate: updateRuleCondition,
        onDeleteRule: () => deleteRule(conditionIndex, ruleIndex),
        onMoveRuleUp: () => moveRule(conditionIndex, ruleIndex, -1),
        onMoveRuleDown: () => moveRule(conditionIndex, ruleIndex, 1),
        canMoveRuleUp: ruleIndex > 0,
        canMoveRuleDown: ruleIndex < draftConditions[conditionIndex].rules.length - 1,
        ruleErrors: fieldErrors[conditionIndex]?.rules?.[ruleIndex] || {}
      })
    }, ruleIndex);
  };
  const renderActionRow = (actionItem, actionIndex, conditionIndex, blockProps) => {
    const actionErrors = fieldErrors[conditionIndex]?.actions?.[actionIndex] || {};
    const isPulsed = pulseTarget && pulseTarget.section === 'actions' && pulseTarget.actionIndex === actionIndex;
    const datalistOptions = [];
    _input_element_attributes_js__WEBPACK_IMPORTED_MODULE_9__.inputSchema.sharedAttributes.concat(_input_element_attributes_js__WEBPACK_IMPORTED_MODULE_9__.inputSchema.types[blockProps.attributes.type]).forEach(data => datalistOptions.push(data.attribute));
    _input_element_attributes_js__WEBPACK_IMPORTED_MODULE_9__.inputSchema.ariaAttributes.forEach(data => datalistOptions.push('aria-' + data.attribute));
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)("div", {
      className: `rule-row inner item ${Object.keys(actionErrors).length > 0 ? 'invalid' : ''} ${isPulsed ? 'pulse' : ''}`,
      "data-action-index": actionIndex,
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Action', 'tsjippy'),
        value: actionItem?.action || '',
        options: [{
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select action', 'tsjippy'),
          value: ''
        }, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Show', 'tsjippy'),
          value: 'show'
        }, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Hide', 'tsjippy'),
          value: 'hide'
        }, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Toggle visibility', 'tsjippy'),
          value: 'toggle'
        }, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Set property', 'tsjippy'),
          value: 'set-property'
        }],
        onChange: value => updateAction(conditionIndex, actionIndex, 'action', value),
        help: actionErrors.action || '',
        "data-field-key": "action"
      }), (actionItem?.action || '') == 'set-property' && blockProps.name == 'tsjippy-forms/input' ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.Fragment, {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Property name', 'tsjippy'),
          value: actionItem?.['property-name'] || '',
          onChange: value => updateAction(conditionIndex, actionIndex, 'property-name', value),
          help: actionErrors.propertyName || '',
          "data-field-key": "propertyName",
          list: "element-properties"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("datalist", {
          id: "element-properties",
          children: datalistOptions.map(attribute => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("option", {
            value: attribute
          }))
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("span", {
          class: "condition-label",
          style: {
            marginTop: ' 25px'
          },
          children: "To"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Property value', 'tsjippy'),
          value: actionItem?.['property-value'] || '',
          onChange: value => updateAction(conditionIndex, actionIndex, 'property-value', value),
          help: actionErrors.propertyValue || '',
          "data-field-key": "propertyValue",
          list: "possible-elements"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("datalist", {
          id: "possible-elements",
          children: formElementOptions.map(data => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("option", {
            value: "the-value-of-" + data.value
          }))
        })]
      }) : '', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        style: {
          marginTop: '20px'
        },
        variant: "secondary",
        isDestructive: true,
        onClick: () => deleteAction(actionIndex),
        icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_7__["default"],
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Delete action', 'tsjippy')
      })]
    }, actionIndex);
  };
  const displayConditions = blockProps => {
    if (!Array.isArray(draftConditions) || draftConditions.length === 0) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.Fragment, {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("p", {
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No conditions defined yet.', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "primary",
          onClick: addCondition,
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add first condition', 'tsjippy')
        })]
      });
    }

    /**
     * Loop over all conditons
     */
    return draftConditions.map((condition, conditionIndex) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.Fragment, {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)("div", {
        className: `condition-row ${Array.isArray(condition) && condition.length === 0 ? 'condition-row--empty' : ''}`,
        "data-condition-index": conditionIndex,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("span", {
          className: "condition-label",
          children: "If"
        }), (condition.rules || []).length === 0 ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.Fragment, {
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("p", {
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No rules defined yet.', 'tsjippy')
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
            variant: "primary",
            onClick: () => addRule(conditionIndex),
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add rule', 'tsjippy')
          })]
        }) : condition.rules.map((rule, ruleIndex) => renderRuleRow(rule, ruleIndex, conditionIndex)), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("br", {}), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("span", {
          className: "condition-label",
          children: "Then"
        }), (condition.actions || []).length === 0 ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.Fragment, {
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("p", {
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No actions defined yet.', 'tsjippy')
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
            variant: "primary",
            onClick: () => addAction(conditionIndex),
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add action', 'tsjippy')
          })]
        }) : condition.actions.map((action, actionIndex) => renderActionRow(action, actionIndex, conditionIndex, blockProps)), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("div", {
          className: "actions",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
            variant: "secondary",
            onClick: () => addAction(conditionIndex),
            icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_6__["default"],
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add another action', 'tsjippy')
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)("div", {
          className: "actions",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
            variant: "secondary",
            onClick: () => duplicateRule(conditionIndex),
            icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_5__["default"],
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Duplicate condition', 'tsjippy')
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
            variant: "secondary",
            isDestructive: true,
            onClick: () => deleteCondition(conditionIndex, ruleIndex),
            icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_7__["default"],
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Delete condition', 'tsjippy')
          })]
        })]
      }, conditionIndex)
    }));
  };
  const renderContent = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(blockProps => {
    if (isLoading && !hasLoaded) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.Fragment, {
        children: ["Fetching Condition Data...", /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Spinner, {})]
      });
    }
    if (error) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Notice, {
        status: "error",
        isDismissible: false,
        children: [(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Error:', 'tsjippy'), " ", error]
      });
    }
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.Fragment, {
      children: [successMessage && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Notice, {
        status: "success",
        isDismissible: true,
        onRemove: clearSuccessMessage,
        children: successMessage
      }), validationErrors.length > 0 && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Notice, {
        status: "error",
        isDismissible: true,
        onRemove: () => setValidationErrors([]),
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("strong", {
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Please fix the following issues:', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("ul", {
          style: {
            marginTop: '8px',
            marginBottom: 0,
            paddingLeft: '18px'
          },
          children: validationErrors.map((item, index) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("li", {
            children: item
          }, index))
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)("div", {
        ref: modalRef,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("h3", {
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Conditions', 'tsjippy')
        }), displayConditions(blockProps)]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)("div", {
        style: {
          marginTop: '16px',
          display: 'flex',
          gap: '8px',
          flexWrap: 'wrap'
        },
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "primary",
          onClick: addCondition,
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add New Condition', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "primary",
          onClick: handleSave,
          disabled: !isDirty || !isValid || isSaving,
          accessibleWhenDisabled: true,
          children: isSaving ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Saving...', 'tsjippy') : isDirty ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Save conditions', 'tsjippy') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Saved', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "secondary",
          onClick: handleReset,
          disabled: !isDirty,
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Reset changes', 'tsjippy')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          variant: "secondary",
          onClick: handleClose,
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Close', 'tsjippy')
        })]
      }), isDirty && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("p", {
        style: {
          marginTop: '12px',
          color: '#b45309'
        },
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('You have unsaved changes.', 'tsjippy')
      })]
    });
  }, [addAction, addRule, addCondition, clearSuccessMessage, conditions, deleteCondition, deleteRule, draftConditions, error, fieldErrors, formElementOptions, handleClose, handleReset, handleSave, hasLoaded, isDirty, isLoading, isSaving, moveRule, pulseTarget, successMessage, updateAction, updateRuleCondition, validationErrors]);
  if (!isVisible || typeof document === 'undefined') {
    return null;
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.createPortal)(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("div", {
    id: "element-conditions-modal",
    className: "modal",
    onClick: handleOverlayClick,
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)("div", {
      className: "modal-content",
      onClick: stopPropagation,
      onKeyDown: stopPropagation,
      style: {
        maxWidth: '90vw'
      },
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("span", {
        className: "close mobile-sticky",
        onClick: handleClose,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)("svg", {
          width: "24",
          height: "24",
          viewBox: "0 0 24 24",
          fill: "none",
          stroke: "currentColor",
          strokeWidth: "2",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("line", {
            x1: "18",
            y1: "6",
            x2: "6",
            y2: "18"
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("line", {
            x1: "6",
            y1: "6",
            x2: "18",
            y2: "18"
          })]
        })
      }), renderContent(blockProps)]
    })
  }), document.body);
}

/***/ },

/***/ "./src/formbuilder/components/RuleRow.js"
/*!***********************************************!*\
  !*** ./src/formbuilder/components/RuleRow.js ***!
  \***********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ RuleRow)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-down.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-up.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/row.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/trash.mjs");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__);




/**
 * Render one rule row inside a rule group.
 * This component is presentational and sends all updates upward.
 */

function RuleRow({
  conditionIndex,
  rule,
  ruleIndex,
  formElementOptions,
  onUpdate,
  onDeleteRule,
  onMoveRuleUp,
  onMoveRuleDown,
  canMoveRuleUp,
  canMoveRuleDown,
  ruleErrors = {}
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
    if (!rule?.equation) {
      return null;
    }
    if (rule.equation === '+' || rule.equation === '-') {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.Fragment, {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Second element', 'tsjippy'),
          value: rule['conditional-field-2'] || '',
          options: [{
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select second element', 'tsjippy'),
            value: ''
          }, ...(formElementOptions || [])],
          onChange: element => onUpdate(conditionIndex, ruleIndex, 'conditional-field-2', element),
          help: ruleErrors.conditionalField2 || '',
          "data-field-key": "conditionalField2"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Second equation', 'tsjippy'),
          value: rule['equation-2'] || '',
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
          onChange: equation2 => onUpdate(conditionIndex, ruleIndex, 'equation-2', equation2),
          help: ruleErrors.equation2 || '',
          "data-field-key": "equation2"
        })]
      });
    }
    return null;
  };

  /* Render the editable UI for one rule entry. */
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
    className: `rule-row inner ${Object.keys(ruleErrors).length > 0 ? 'invalid' : ''}`,
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Conditional Field', 'tsjippy'),
      value: rule?.['conditional-field'] || '',
      options: [{
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select element', 'tsjippy'),
        value: ''
      }, ...(formElementOptions || [])],
      onChange: element => onUpdate(conditionIndex, ruleIndex, 'conditional-field', element),
      help: ruleErrors.conditionalField || '',
      "data-field-key": "conditionalField"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Equation', 'tsjippy'),
      value: rule?.equation || '',
      options: [{
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select equation', 'tsjippy'),
        value: ''
      }, ...equationOptions],
      onChange: equation => onUpdate(conditionIndex, ruleIndex, 'equation', equation),
      help: ruleErrors.equation || '',
      "data-field-key": "equation"
    }), renderExtraOptions(), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Value', 'tsjippy'),
      value: rule?.['conditional-value'] || '',
      onChange: value => onUpdate(conditionIndex, ruleIndex, 'conditional-value', value),
      help: ruleErrors.conditionalValue || '',
      "data-field-key": "conditionalValue"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
      className: "combinator",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        variant: rule?.combinator === 'and' ? 'primary' : 'secondary',
        isPressed: rule?.combinator === 'and',
        "aria-pressed": rule?.combinator === 'and',
        onClick: () => onUpdate(conditionIndex, ruleIndex, 'combinator', 'and'),
        icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_4__["default"],
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('AND', 'tsjippy')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        variant: rule?.combinator === 'or' ? 'primary' : 'secondary',
        isPressed: rule?.combinator === 'or',
        "aria-pressed": rule?.combinator === 'or',
        onClick: () => onUpdate(conditionIndex, ruleIndex, 'combinator', 'or'),
        icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_4__["default"],
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('OR', 'tsjippy')
      }), canMoveRuleUp && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        variant: "secondary",
        onClick: onMoveRuleUp,
        icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_3__["default"],
        style: {
          width: '140px'
        },
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Move rule up', 'tsjippy')
      }), canMoveRuleDown && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        variant: "secondary",
        onClick: onMoveRuleDown,
        icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_2__["default"],
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Move rule down', 'tsjippy')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        variant: "secondary",
        isDestructive: true,
        onClick: onDeleteRule,
        icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_5__["default"],
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Delete rule', 'tsjippy')
      })]
    })]
  });
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
/* harmony import */ var _filters_storeClientIdInAttributes_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./filters/storeClientIdInAttributes.js */ "./src/formbuilder/filters/storeClientIdInAttributes.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__);











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
    renderAppender: () => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.Inserter, {
      rootClientId: clientId,
      isAppender: true,
      renderToggle: ({
        onToggle
      }) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
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
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("p", {
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No roles available.', 'tsjippy')
      });
    }
    return availableRoles.map(role => {
      const roleSlug = role.slug || role.value || role;
      const roleLabel = role.label || role.name || roleSlug;
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CheckboxControl, {
        label: roleLabel,
        checked: (roles || []).includes(roleSlug),
        onChange: checked => onRoleSelected(checked, roleSlug)
      }, roleSlug);
    });
  };

  /* Build action checkboxes for the inspector panel. */
  const getActionCheckboxes = () => {
    if (!availableActions.length) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("p", {
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('No actions available.', 'tsjippy')
      });
    }
    return availableActions.map(action => {
      const actionSlug = action.slug || action.value || action;
      const actionLabel = action.label || action.name || actionSlug;
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CheckboxControl, {
        label: actionLabel,
        checked: (actions || []).includes(actionSlug),
        onChange: checked => actionSelected(checked, actionSlug)
      }, actionSlug);
    });
  };

  /* Toggleable placeholder panels for additional form-related UI. */
  const resultingForm = () => {
    if (isEmailsFormVisible) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("div", {
        className: "tsjippy-form-secondary-panel",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("p", {
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Emails form is visible.', 'tsjippy')
        })
      });
    }
    if (isRemindersFormVisible) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("div", {
        className: "tsjippy-form-secondary-panel",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)("p", {
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Reminders form is visible.', 'tsjippy')
        })
      });
    }
    return null;
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form Settings', 'tsjippy'),
        initialOpen: true,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.RadioControl, {
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
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Roles', 'tsjippy'),
        initialOpen: false,
        children: getRoleCheckboxes()
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Actions', 'tsjippy'),
        initialOpen: false,
        children: getActionCheckboxes()
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('E-mail Settings', 'tsjippy'),
        initialOpen: false,
        onToggle: () => setEmailsFormVisibility(prev => !prev),
        children: isEmailsFormVisible ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Hide Emails Form', 'tsjippy') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Show Emails Form', 'tsjippy')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form Reminders', 'tsjippy'),
        initialOpen: false,
        onToggle: () => setRemindersFormVisibility(prev => !prev),
        children: isRemindersFormVisible ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Hide Reminders Form', 'tsjippy') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Show Reminders Form', 'tsjippy')
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)("fieldset", {
      ...innerBlocksProps,
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsxs)("legend", {
        children: [attributes.name, " Form"]
      }), resultingForm(), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_10__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InnerBlocks, {
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
const addConditionsForm = (0,_wordpress_compose__WEBPACK_IMPORTED_MODULE_4__.createHigherOrderComponent)(BlockEdit => {
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
        allNestedBlocks: allNestedBlocks,
        blockProps: props
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
}, 'addConditionsForm');
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_5__.addFilter)('editor.BlockEdit', 'tsjippy-forms/add-conditions-button', addConditionsForm);

/***/ },

/***/ "./src/formbuilder/filters/storeClientIdInAttributes.js"
/*!**************************************************************!*\
  !*** ./src/formbuilder/filters/storeClientIdInAttributes.js ***!
  \**************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__);




// 1. Inject the 'blockId' attribute into ALL block configurations

function addIdAttribute(settings, name) {
  // Optional: Skip specific core blocks if needed
  if (!settings.attributes) {
    settings.attributes = {};
  }
  settings.attributes.blockId = {
    type: 'string'
  };
  return settings;
}
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.addFilter)('blocks.registerBlockType', 'tsjippy-forms/add-id-attribute', addIdAttribute);

// 2. Intercept the Edit component to generate the ID ONLY for children of your parent
const addBlockId = (0,_wordpress_compose__WEBPACK_IMPORTED_MODULE_1__.createHigherOrderComponent)(BlockEdit => {
  return props => {
    const {
      clientId,
      attributes,
      setAttributes,
      context
    } = props;
    const {
      blockId
    } = attributes;

    /**
     * Find the parent form builder block
     */

    // Get the parent form
    const parents = wp.data.select('core/block-editor').getBlockParentsByBlockName(clientId, 'tsjippy-forms/formbuilder');
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
      const isChildOfFormBuilder = parents.length > 0;
      if (isChildOfFormBuilder && blockId !== clientId) {
        setAttributes({
          blockId: clientId
        });
      } else if (!isChildOfFormBuilder && blockId) {
        setAttributes({
          blockId: undefined
        });
      }
    }, [parents, clientId, blockId, setAttributes]);
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(BlockEdit, {
      ...props
    });
  };
}, 'addBlockId');
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_0__.addFilter)('editor.BlockEdit', 'tsjippy-forms/addblock-id', addBlockId);

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
 * Internal API helper for loading conditions.
 * This is used by the resolver and is not exported.
 */
async function fetchConditions(blockId) {
  const response = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default()({
    path: `${tsjippy.restApiPrefix}/forms/get_element_conditions`,
    method: 'POST',
    data: {
      blockId: blockId
    }
  });
  return response;
}

/**
 * Internal API helper for saving conditions.
 * This is used by the store-owned save action and is not exported.
 */
async function saveConditionsRequest(blockId, conditions) {
  const savedConditions = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default()({
    path: `${tsjippy.restApiPrefix}/forms/save_element_conditions`,
    method: 'POST',
    data: {
      blockId: blockId,
      conditions: conditions
    }
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
      conditions: conditions
    };
  },
  /**
   * Set the loading state for one element.
   */
  setLoading(blockId, isLoading) {
    return {
      type: 'SET_LOADING',
      blockId,
      isLoading: !!isLoading
    };
  },
  /**
   * Set the saving state for one element.
   */
  setSaving(blockId, isSaving) {
    return {
      type: 'SET_SAVING',
      blockId,
      isSaving: !!isSaving
    };
  },
  /**
   * Store an error message for one element.
   */
  setError(blockId, error) {
    return {
      type: 'SET_ERROR',
      blockId,
      error: error || null
    };
  },
  /**
   * Store the loaded flag for one element.
   */
  setLoaded(blockId, loaded) {
    return {
      type: 'SET_LOADED',
      blockId,
      loaded: !!loaded
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
      const savedConditions = yield saveConditionsRequest(blockId, conditions);
      yield actions.setConditions(blockId, savedConditions);
      yield actions.setLoaded(blockId, true);
      return savedConditions;
    } catch (error) {
      yield actions.setError(blockId, error?.message || 'Failed to save element conditions.');
      throw error;
    } finally {
      yield actions.setSaving(blockId, false);
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
          [action.blockId]: action.conditions
        }
      };
    case 'SET_LOADING':
      return {
        ...state,
        loadingByElement: {
          ...state.loadingByElement,
          [action.blockId]: action.isLoading
        }
      };
    case 'SET_SAVING':
      return {
        ...state,
        savingByElement: {
          ...state.savingByElement,
          [action.blockId]: action.isSaving
        }
      };
    case 'SET_ERROR':
      return {
        ...state,
        errorByElement: {
          ...state.errorByElement,
          [action.blockId]: action.error
        }
      };
    case 'SET_LOADED':
      return {
        ...state,
        loadedByElement: {
          ...state.loadedByElement,
          [action.blockId]: action.loaded
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
  getConditions(state, blockId) {
    return state.conditionsByElement[blockId] || [{
      rules: [],
      actions: []
    }];
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
  }
};

/**
 * Resolver for getConditions.
 * The first read of the selector will load data from the server.
 */
const resolvers = {
  getConditions: blockId => async ({
    dispatch
  }) => {
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
      dispatch.setError(blockId, error?.message || 'Failed to load element conditions.');
    } finally {
      dispatch.setLoading(blockId, false);
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

/***/ "./src/input/element_attributes.js"
/*!*****************************************!*\
  !*** ./src/input/element_attributes.js ***!
  \*****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   inputSchema: () => (/* binding */ inputSchema),
/* harmony export */   inputTypes: () => (/* binding */ inputTypes)
/* harmony export */ });
const inputTypes = ["button", "checkbox", "color", "date", "datetime-local", "email", "file", "hidden", "image", "month", "number", "password", "radio", "range", "reset", "search", "submit", "tel", "text", "time", "url", "week"];
const inputSchema = {
  sharedAttributes: [{
    attribute: "id",
    expectedType: "string"
  }, {
    attribute: "class",
    expectedType: "string"
  }, {
    attribute: "style",
    expectedType: "string"
  }, {
    attribute: "title",
    expectedType: "string"
  }, {
    attribute: "hidden",
    expectedType: "boolean"
  }, {
    attribute: "lang",
    expectedType: "string"
  }, {
    attribute: "dir",
    expectedType: "ltr|rtl|auto"
  }, {
    attribute: "role",
    expectedType: "string"
  }, {
    attribute: "tabindex",
    expectedType: "number"
  }, {
    attribute: "accesskey",
    expectedType: "string"
  }, {
    attribute: "contenteditable",
    expectedType: "boolean"
  }, {
    attribute: "draggable",
    expectedType: "boolean"
  }, {
    attribute: "translate",
    expectedType: "boolean"
  }, {
    attribute: "data-*",
    expectedType: "string"
  }],
  types: {
    button: [{
      attribute: "value",
      expectedType: "string"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "formaction",
      expectedType: "string"
    }, {
      attribute: "formenctype",
      expectedType: "application/x-www-form-urlencoded|multipart/form-data|text/plain"
    }, {
      attribute: "formmethod",
      expectedType: "get|post|dialog"
    }, {
      attribute: "formnovalidate",
      expectedType: "boolean"
    }, {
      attribute: "formtarget",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "popovertarget",
      expectedType: "string"
    }, {
      attribute: "popovertargetaction",
      expectedType: "hide|show|toggle"
    }],
    checkbox: [{
      attribute: "checked",
      expectedType: "boolean"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "value",
      expectedType: "string"
    }, {
      attribute: "label",
      expectedType: "string"
    }],
    color: [{
      attribute: "value",
      expectedType: "string"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "alpha",
      expectedType: "boolean"
    }, {
      attribute: "colorspace",
      expectedType: "limited-srgb|display-p3"
    }],
    date: [{
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string"
    }, {
      attribute: "min",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "step",
      expectedType: "number|any"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    "datetime-local": [{
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string"
    }, {
      attribute: "min",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "step",
      expectedType: "number|any"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    email: [{
      attribute: "autocomplete",
      expectedType: "string"
    }, {
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "maxlength",
      expectedType: "number"
    }, {
      attribute: "minlength",
      expectedType: "number"
    }, {
      attribute: "multiple",
      expectedType: "boolean"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "pattern",
      expectedType: "string"
    }, {
      attribute: "placeholder",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "size",
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }, {
      attribute: "dirname",
      expectedType: "string"
    }],
    file: [{
      attribute: "accept",
      expectedType: "string"
    }, {
      attribute: "autocomplete",
      expectedType: "string"
    }, {
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "capture",
      expectedType: "user|environment|boolean"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "multiple",
      expectedType: "boolean"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }],
    hidden: [{
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "value",
      expectedType: "string"
    }, {
      attribute: "dirname",
      expectedType: "string"
    }],
    image: [{
      attribute: "alt",
      expectedType: "string"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "formaction",
      expectedType: "string"
    }, {
      attribute: "formenctype",
      expectedType: "application/x-www-form-urlencoded|multipart/form-data|text/plain"
    }, {
      attribute: "formmethod",
      expectedType: "get|post|dialog"
    }, {
      attribute: "formnovalidate",
      expectedType: "boolean"
    }, {
      attribute: "formtarget",
      expectedType: "string"
    }, {
      attribute: "height",
      expectedType: "number"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "src",
      expectedType: "string"
    }, {
      attribute: "width",
      expectedType: "number"
    }],
    month: [{
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string"
    }, {
      attribute: "min",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "step",
      expectedType: "number|any"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    number: [{
      attribute: "autocomplete",
      expectedType: "string"
    }, {
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string|number"
    }, {
      attribute: "min",
      expectedType: "string|number"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "placeholder",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "step",
      expectedType: "number|any"
    }, {
      attribute: "value",
      expectedType: "string|number"
    }],
    password: [{
      attribute: "autocomplete",
      expectedType: "string"
    }, {
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "maxlength",
      expectedType: "number"
    }, {
      attribute: "minlength",
      expectedType: "number"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "pattern",
      expectedType: "string"
    }, {
      attribute: "placeholder",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "size",
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }, {
      attribute: "dirname",
      expectedType: "string"
    }],
    radio: [{
      attribute: "checked",
      expectedType: "boolean"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "value",
      expectedType: "string"
    }, {
      attribute: "label",
      expectedType: "string"
    }],
    range: [{
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string|number"
    }, {
      attribute: "min",
      expectedType: "string|number"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "step",
      expectedType: "number|any"
    }, {
      attribute: "value",
      expectedType: "string|number"
    }],
    reset: [{
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "formaction",
      expectedType: "string"
    }, {
      attribute: "formenctype",
      expectedType: "application/x-www-form-urlencoded|multipart/form-data|text/plain"
    }, {
      attribute: "formmethod",
      expectedType: "get|post|dialog"
    }, {
      attribute: "formnovalidate",
      expectedType: "boolean"
    }, {
      attribute: "formtarget",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    search: [{
      attribute: "autocomplete",
      expectedType: "string"
    }, {
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "dirname",
      expectedType: "string"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "maxlength",
      expectedType: "number"
    }, {
      attribute: "minlength",
      expectedType: "number"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "pattern",
      expectedType: "string"
    }, {
      attribute: "placeholder",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "size",
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    submit: [{
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "formaction",
      expectedType: "string"
    }, {
      attribute: "formenctype",
      expectedType: "application/x-www-form-urlencoded|multipart/form-data|text/plain"
    }, {
      attribute: "formmethod",
      expectedType: "get|post|dialog"
    }, {
      attribute: "formnovalidate",
      expectedType: "boolean"
    }, {
      attribute: "formtarget",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    tel: [{
      attribute: "autocomplete",
      expectedType: "string"
    }, {
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "dirname",
      expectedType: "string"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "maxlength",
      expectedType: "number"
    }, {
      attribute: "minlength",
      expectedType: "number"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "pattern",
      expectedType: "string"
    }, {
      attribute: "placeholder",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "size",
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    text: [{
      attribute: "autocomplete",
      expectedType: "string"
    }, {
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "dirname",
      expectedType: "string"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "maxlength",
      expectedType: "number"
    }, {
      attribute: "minlength",
      expectedType: "number"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "pattern",
      expectedType: "string"
    }, {
      attribute: "placeholder",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "size",
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    time: [{
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string"
    }, {
      attribute: "min",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "step",
      expectedType: "number|any"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    url: [{
      attribute: "autocomplete",
      expectedType: "string"
    }, {
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "dirname",
      expectedType: "string"
    }, {
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "maxlength",
      expectedType: "number"
    }, {
      attribute: "minlength",
      expectedType: "number"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "pattern",
      expectedType: "string"
    }, {
      attribute: "placeholder",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "size",
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    week: [{
      attribute: "disabled",
      expectedType: "boolean"
    }, {
      attribute: "form",
      expectedType: "string"
    }, {
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string"
    }, {
      attribute: "min",
      expectedType: "string"
    }, {
      attribute: "name",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "step",
      expectedType: "number|any"
    }, {
      attribute: "value",
      expectedType: "string"
    }]
  },
  ariaAttributes: [{
    attribute: "activedescendant",
    expectedType: "string"
  }, {
    attribute: "atomic",
    expectedType: "boolean"
  }, {
    attribute: "autocomplete",
    expectedType: "inline|list|both|none"
  }, {
    attribute: "braillelabel",
    expectedType: "string"
  }, {
    attribute: "brailleroledescription",
    expectedType: "string"
  }, {
    attribute: "busy",
    expectedType: "boolean"
  }, {
    attribute: "checked",
    expectedType: "boolean|mixed"
  }, {
    attribute: "colcount",
    expectedType: "number"
  }, {
    attribute: "colindex",
    expectedType: "number"
  }, {
    attribute: "colindextext",
    expectedType: "string"
  }, {
    attribute: "colspan",
    expectedType: "number"
  }, {
    attribute: "controls",
    expectedType: "string"
  }, {
    attribute: "current",
    expectedType: "boolean|page|step|location|date|time"
  }, {
    attribute: "describedby",
    expectedType: "string"
  }, {
    attribute: "description",
    expectedType: "string"
  }, {
    attribute: "details",
    expectedType: "string"
  }, {
    attribute: "disabled",
    expectedType: "boolean"
  }, {
    attribute: "dropeffect",
    expectedType: "copy|move|link|execute|popup|none"
  }, {
    attribute: "errormessage",
    expectedType: "string"
  }, {
    attribute: "expanded",
    expectedType: "boolean"
  }, {
    attribute: "flowto",
    expectedType: "string"
  }, {
    attribute: "grabbed",
    expectedType: "boolean"
  }, {
    attribute: "haspopup",
    expectedType: "boolean|menu|listbox|tree|grid|dialog"
  }, {
    attribute: "hidden",
    expectedType: "boolean"
  }, {
    attribute: "invalid",
    expectedType: "boolean|grammar|spelling"
  }, {
    attribute: "keyshortcuts",
    expectedType: "string"
  }, {
    attribute: "label",
    expectedType: "string"
  }, {
    attribute: "labelledby",
    expectedType: "string"
  }, {
    attribute: "level",
    expectedType: "number"
  }, {
    attribute: "live",
    expectedType: "off|polite|assertive"
  }, {
    attribute: "modal",
    expectedType: "boolean"
  }, {
    attribute: "multiline",
    expectedType: "boolean"
  }, {
    attribute: "multiselectable",
    expectedType: "boolean"
  }, {
    attribute: "orientation",
    expectedType: "horizontal|vertical"
  }, {
    attribute: "owns",
    expectedType: "string"
  }, {
    attribute: "placeholder",
    expectedType: "string"
  }, {
    attribute: "posinset",
    expectedType: "number"
  }, {
    attribute: "pressed",
    expectedType: "boolean|mixed"
  }, {
    attribute: "readonly",
    expectedType: "boolean"
  }, {
    attribute: "relevant",
    expectedType: "additions|removals|text|all|additions text|additions removals|removals text|additions removals text"
  }, {
    attribute: "required",
    expectedType: "boolean"
  }, {
    attribute: "roledescription",
    expectedType: "string"
  }, {
    attribute: "rowcount",
    expectedType: "number"
  }, {
    attribute: "rowindex",
    expectedType: "number"
  }, {
    attribute: "rowindextext",
    expectedType: "string"
  }, {
    attribute: "rowspan",
    expectedType: "number"
  }, {
    attribute: "selected",
    expectedType: "boolean"
  }, {
    attribute: "setsize",
    expectedType: "number"
  }, {
    attribute: "sort",
    expectedType: "ascending|descending|none|other"
  }, {
    attribute: "valuemax",
    expectedType: "number"
  }, {
    attribute: "valuemin",
    expectedType: "number"
  }, {
    attribute: "valuenow",
    expectedType: "number"
  }, {
    attribute: "valuetext",
    expectedType: "string"
  }]
};

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

/***/ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/row.mjs"
/*!*************************************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/row.mjs ***!
  \*************************************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ row_default)
/* harmony export */ });
/* harmony import */ var _wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/primitives */ "@wordpress/primitives");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
// packages/icons/src/library/row.tsx


var row_default = /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", fill: "currentColor", children: /* @__PURE__ */ (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_primitives__WEBPACK_IMPORTED_MODULE_0__.Path, { d: "M4 6.5h5a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H4V16h5a.5.5 0 0 0 .5-.5v-7A.5.5 0 0 0 9 8H4V6.5Zm16 0h-5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h5V16h-5a.5.5 0 0 1-.5-.5v-7A.5.5 0 0 1 15 8h5V6.5Z" }) });

//# sourceMappingURL=row.mjs.map


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