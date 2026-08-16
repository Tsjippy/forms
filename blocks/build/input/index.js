/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./shared/AddOptions.js"
/*!******************************!*\
  !*** ./shared/AddOptions.js ***!
  \******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ AddOptions)
/* harmony export */ });
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__);



function AddOptions({
  attributes,
  setAttributes
}) {
  const {
    options
  } = attributes;
  const addOption = () => {
    setAttributes({
      options: [...options, {
        value: '',
        label: ''
      }]
    });
  };
  const updateOption = (value, index, type) => {
    const newOptions = [...options];
    if (newOptions[index] == undefined) {
      newOptions[index] = {
        value: '',
        label: ''
      };
    }
    newOptions[index][type] = value;
    setAttributes({
      options: newOptions
    });
  };
  const removeOption = index => {
    setAttributes({
      options: options.filter((_, i) => i !== index)
    });
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.Fragment, {
    children: [options.map((option, index) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      style: {
        marginBottom: '10px'
      },
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
        label: `Option value ${index + 1}`,
        value: option.value,
        onChange: value => updateOption(value, index, 'value')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TextControl, {
        label: `Option Label ${index + 1}`,
        value: option.label,
        onChange: value => updateOption(value, index, 'label')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
        isDestructive: true,
        onClick: () => removeOption(index),
        children: ["Remove Option ", index + 1]
      })]
    }, index)), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
      variant: "primary",
      onClick: addOption,
      children: "Add Option"
    })]
  });
}

/***/ },

/***/ "./shared/usePrefill.js"
/*!******************************!*\
  !*** ./shared/usePrefill.js ***!
  \******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PrefillOptionsSelector: () => (/* binding */ PrefillOptionsSelector),
/* harmony export */   PrefillValueSelector: () => (/* binding */ PrefillValueSelector),
/* harmony export */   usePrefill: () => (/* binding */ usePrefill)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__);






const usePrefill = () => {
  const {
    data,
    isLoading
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.useSelect)(select => ({
    data: select('tsjippy/prefill').getData(),
    isLoading: select('tsjippy/prefill').isLoading()
  }), []);
  const {
    fetchPrefill
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.useDispatch)('tsjippy/prefill');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!data && !isLoading) {
      fetchPrefill();
    }
  }, [data, isLoading]);
  return {
    data,
    isLoading
  };
};
const PrefillOptionsSelector = ({
  value,
  onChange
}) => {
  const {
    data: prefillData,
    isLoading
  } = usePrefill();
  if (isLoading || !prefillData) {
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Spinner, {});
  }
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Key for dynamically filled options', 'tsjippy'),
    value: value,
    options: [{
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Select an option', 'tsjippy'),
      value: ''
    }, ...Object.keys(prefillData.multi || {}).map(key => ({
      label: key,
      value: key
    }))],
    onChange: onChange
  });
};
const PrefillValueSelector = ({
  value,
  onChange
}) => {
  const {
    data: prefillData,
    isLoading
  } = usePrefill();
  if (isLoading || !prefillData) {
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Spinner, {});
  }
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Key for dynamically set value', 'tsjippy'),
    value: value,
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Select a key for the dynamically set value. This is used to pre-fill the input field based on the current logged-in user.', 'tsjippy'),
    options: [{
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Select an option', 'tsjippy'),
      value: ''
    }, ...Object.keys(prefillData.single || {}).map(key => ({
      label: key,
      value: key
    }))],
    onChange: onChange
  });
};

/***/ },

/***/ "./src/input/components/InputHtml.js"
/*!*******************************************!*\
  !*** ./src/input/components/InputHtml.js ***!
  \*******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   InputHtml: () => (/* binding */ InputHtml)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Multiple_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Multiple.js */ "./src/input/components/Multiple.js");
/* harmony import */ var _shared_usePrefill_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../shared/usePrefill.js */ "./shared/usePrefill.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__);




function InputHtml({
  attributes,
  blockProps,
  labelChild,
  isSaving = false
}) {
  let html;
  let prefillValue = '';
  if (!isSaving) {
    var prefill = (0,_shared_usePrefill_js__WEBPACK_IMPORTED_MODULE_2__.usePrefill)();
    prefillValue = prefill?.single?.[attributes.dynamic_value ?? ''] ?? '';
  }

  /**
   * Checkboxes
   */
  if (['radio', 'checkbox'].includes(attributes.type)) {
    let options = [];
    if (isSaving) {
      options = attributes.options;
    } else {
      const dynamicOptions = Object.entries(prefill?.multi?.[attributes.options_dynamic ?? ''] || {}).map(([key, value]) => ({
        value: String(key).trim(),
        label: String(value || key).trim()
      }));
      options = [...attributes.options, ...dynamicOptions];
    }
    html = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
      ...blockProps,
      className: `${blockProps.className} checkbox-wrapper`,
      "data-blockid": attributes.blockId,
      children: [options.map((option, index) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("label", {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("input", {
          type: attributes.type,
          name: attributes.name,
          value: option.value,
          className: "formbuilder",
          autoComplete: "on",
          checked: prefillValue === option.value,
          "data-blockid": attributes.blockId,
          ...attributes.inputAttributes,
          required: attributes.required
        }), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)(option.label, 'tsjippy')]
      }, `${option.value}-${index}`)), isSaving && "%options-placeholder%"]
    });
  }

  /**
   * Text area
   */else if (attributes.type == 'textarea') {
    html = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("textarea", {
      ...blockProps,
      type: attributes.type,
      name: attributes.name,
      required: attributes.required,
      "data-blockid": attributes.blockId,
      autoComplete: "on",
      ...attributes.inputAttributes,
      children: isSaving ? "%value-placeholder%" : prefillValue
    });
  }

  /**
   * Others
   */else {
    html = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("input", {
      ...blockProps,
      type: attributes.type,
      name: attributes.name,
      required: attributes.required,
      "data-blockid": attributes.blockId,
      autoComplete: "on",
      ...attributes.inputAttributes,
      value: isSaving ? "%value-placeholder%" : prefillValue
    });
  }

  /**
   * Render the the multpiple version if not wrapped in an label and not a text input
   */
  return attributes.multiple && !labelChild && attributes.type != 'text' ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_Multiple_js__WEBPACK_IMPORTED_MODULE_1__.Multiple, {
    inner: html,
    attributes: attributes,
    isSaving: isSaving
  }) : html;
}

/***/ },

/***/ "./src/input/components/Multiple.js"
/*!******************************************!*\
  !*** ./src/input/components/Multiple.js ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Multiple: () => (/* binding */ Multiple)
/* harmony export */ });
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

const Multiple = props => {
  const addText = props?.attributes?.add_button_content ?? '+';
  const removeText = props?.attributes?.remove_button_content ?? '-';
  /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", {
    className: "input-wrapper required flex",
    style: {
      width: '85%'
    },
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", {
      className: "clone-divs-wrapper",
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", {
        className: "clone-div",
        "data-div-id": "0",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", {
          className: "button-wrapper",
          style: {
            margin: 'auto',
            display: 'flex'
          },
          children: [props.inner, /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("button", {
            type: "button",
            className: "remove button hidden",
            style: {
              flex: 1,
              maxWidth: 'max-content'
            },
            children: removeText
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("button", {
            type: "button",
            className: "add button",
            style: {
              flex: 1,
              maxWidth: 'max-content'
            },
            children: addText
          })]
        })
      })
    })
  });
};

/***/ },

/***/ "./src/input/components/dynamic_inputs.js"
/*!************************************************!*\
  !*** ./src/input/components/dynamic_inputs.js ***!
  \************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   dynamicInputs: () => (/* binding */ dynamicInputs)
/* harmony export */ });
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _element_attributes_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./element_attributes.js */ "./src/input/components/element_attributes.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__);




/**
 * Stores data-* attributes
 */

const storeDataAttributes = (type, newValue, name, saveFunction, all) => {
  const updated = {
    ...all
  };
  if (type === 'name') {
    if (newValue !== name) {
      updated[newValue] = updated[name] ?? '';
      delete updated[name];
    }
  } else {
    updated[name] = newValue;
  }
  saveFunction(updated, 'data-*');
};

/**
 * Creates inputs based on an array
 */
const dynamicInputs = (attributes, type, saveFunction) => {
  let inputData;
  if (type === 'area') {
    inputData = _element_attributes_js__WEBPACK_IMPORTED_MODULE_2__.inputSchema.ariaAttributes;
  } else {
    inputData = (_element_attributes_js__WEBPACK_IMPORTED_MODULE_2__.inputSchema.types?.[attributes.type] || []).concat(_element_attributes_js__WEBPACK_IMPORTED_MODULE_2__.inputSchema.sharedAttributes);
  }
  const values = attributes.inputAttributes || [];
  const controls = [];
  const sortedInputData = [...inputData].sort((a, b) => {
    const aValue = values[a.attribute];
    const bValue = values[b.attribute];
    const aHasValue = aValue !== '' && aValue !== null && aValue !== undefined && !(typeof aValue === 'object' && Object.keys(aValue).length === 0);
    const bHasValue = bValue !== '' && bValue !== null && bValue !== undefined && !(typeof bValue === 'object' && Object.keys(bValue).length === 0);
    return Number(bHasValue) - Number(aHasValue);
  });
  sortedInputData.forEach((data, index) => {
    const attributeName = data.attribute;
    let attributeValue = values[data.attribute] ?? '';

    /**
     * Multiple data-* entries possible
     */
    if (attributeName === 'data-*') {
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("h4", {
        style: {
          marginTop: '20px'
        },
        children: "Data Attributes"
      }, "data-attributes-heading"));
      const dataAttributes = typeof attributeValue === 'object' && attributeValue !== null ? attributeValue : {};
      const entries = dataAttributes[''] === undefined ? {
        ...dataAttributes,
        '': ''
      } : dataAttributes;
      Object.entries(entries).forEach(([key, value], entryIndex) => {
        controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.TextControl, {
          label: "data-name",
          value: key,
          onChange: name => storeDataAttributes('name', name, key, saveFunction, dataAttributes)
        }, `data-name-${entryIndex}-${key}`));
        controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.TextControl, {
          label: `data-${key} value`,
          value: value,
          onChange: newValue => storeDataAttributes('value', newValue, key, saveFunction, dataAttributes)
        }, `data-value-${entryIndex}-${key}`));
      });
    } else if (data.expectedType === 'string') {
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.TextControl, {
        label: attributeName,
        value: attributeValue,
        onChange: value => saveFunction(value, attributeName)
      }, `string-${attributeName}-${index}`));
    } else if (data.expectedType === 'boolean') {
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.ToggleControl, {
        label: attributeName,
        checked: !!attributeValue,
        onChange: checked => saveFunction(checked, attributeName)
      }, `boolean-${attributeName}-${index}`));
    } else if (data.expectedType === 'number') {
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.__experimentalNumberControl, {
        label: attributeName,
        isShiftStepEnabled: true,
        onChange: value => saveFunction(value, attributeName),
        shiftStep: 1,
        value: attributeValue
      }, `number-${attributeName}-${index}`));
    } else if (typeof data.expectedType === 'string' && data.expectedType.includes('|')) {
      const options = data.expectedType.split('|').map(value => ({
        label: value,
        value
      }));
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.RadioControl, {
        label: attributeName,
        selected: attributeValue,
        options: options,
        onChange: selected => saveFunction(selected, attributeName)
      }, `radio-${attributeName}-${index}`));
    } else {
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)("div", {
        children: ["Not sure how to render this ", data.expectedType]
      }, `unknown-${attributeName}-${index}`));
    }
  });
  return controls;
};

/***/ },

/***/ "./src/input/components/element_attributes.js"
/*!****************************************************!*\
  !*** ./src/input/components/element_attributes.js ***!
  \****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   inputSchema: () => (/* binding */ inputSchema),
/* harmony export */   inputTypes: () => (/* binding */ inputTypes)
/* harmony export */ });
const inputTypes = ["button", "checkbox", "color", "date", "datetime-local", "email", "file", "hidden", "image", "month", "number", "password", "radio", "range", "reset", "search", "submit", "tel", "text", "textarea", "time", "url", "week"];
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
    attribute: "disabled",
    expectedType: "boolean"
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
      attribute: "alpha",
      expectedType: "boolean"
    }, {
      attribute: "colorspace",
      expectedType: "limited-srgb|display-p3"
    }],
    date: [{
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string"
    }, {
      attribute: "min",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "step",
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    "datetime-local": [{
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string"
    }, {
      attribute: "min",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "step",
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    email: [{
      attribute: "autofocus",
      expectedType: "boolean"
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
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "capture",
      expectedType: "boolean"
    }, {
      attribute: "multiple",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }],
    hidden: [{
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
      attribute: "src",
      expectedType: "string"
    }, {
      attribute: "width",
      expectedType: "number"
    }],
    month: [{
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string"
    }, {
      attribute: "min",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "step",
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    number: [{
      attribute: "autofocus",
      expectedType: "boolean"
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
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    password: [{
      attribute: "autofocus",
      expectedType: "boolean"
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
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string"
    }, {
      attribute: "min",
      expectedType: "string"
    }, {
      attribute: "step",
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    reset: [{
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
      attribute: "value",
      expectedType: "string"
    }],
    search: [{
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "dirname",
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
      attribute: "value",
      expectedType: "string"
    }],
    tel: [{
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "dirname",
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
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "dirname",
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
    textarea: [{
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "cols",
      expectedType: "integer"
    }, {
      attribute: "maxlength",
      expectedType: "number"
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
      attribute: "rows",
      expectedType: "number"
    }, {
      attribute: "wrap",
      expectedType: "hard|soft"
    }],
    time: [{
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string"
    }, {
      attribute: "min",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "step",
      expectedType: "number"
    }, {
      attribute: "value",
      expectedType: "string"
    }],
    url: [{
      attribute: "autofocus",
      expectedType: "boolean"
    }, {
      attribute: "dirname",
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
      attribute: "list",
      expectedType: "string"
    }, {
      attribute: "max",
      expectedType: "string"
    }, {
      attribute: "min",
      expectedType: "string"
    }, {
      attribute: "readonly",
      expectedType: "boolean"
    }, {
      attribute: "required",
      expectedType: "boolean"
    }, {
      attribute: "step",
      expectedType: "number"
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
    expectedType: "boolean"
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
    expectedType: "boolean"
  }, {
    attribute: "hidden",
    expectedType: "boolean"
  }, {
    attribute: "invalid",
    expectedType: "boolean"
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
    expectedType: "boolean"
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

/***/ "./src/input/edit.js"
/*!***************************!*\
  !*** ./src/input/edit.js ***!
  \***************************/
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
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./editor.scss */ "./src/input/editor.scss");
/* harmony import */ var _components_element_attributes_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./components/element_attributes.js */ "./src/input/components/element_attributes.js");
/* harmony import */ var _components_dynamic_inputs_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./components/dynamic_inputs.js */ "./src/input/components/dynamic_inputs.js");
/* harmony import */ var _components_InputHtml_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./components/InputHtml.js */ "./src/input/components/InputHtml.js");
/* harmony import */ var _shared_usePrefill_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../../shared/usePrefill.js */ "./shared/usePrefill.js");
/* harmony import */ var _shared_AddOptions__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ../../shared/AddOptions */ "./shared/AddOptions.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__);












function Edit({
  attributes,
  setAttributes,
  isSelected,
  clientId
}) {
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps)();
  const typeOptions = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useMemo)(() => [{
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select an input type', 'tsjippy'),
    value: ''
  }, ..._components_element_attributes_js__WEBPACK_IMPORTED_MODULE_6__.inputTypes.map(type => ({
    label: type,
    value: type
  }))], []);
  const storeAttributeAttributes = (value, name) => {
    let newAttributes = {
      ...(attributes.inputAttributes || {})
    };
    newAttributes[name] = value;
    setAttributes({
      inputAttributes: newAttributes
    });
  };
  const [inputName, setInputName] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useState)(attributes.name || '');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    setInputName(attributes.name || '');
  }, [attributes.name]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    const timeoutId = setTimeout(() => {
      if (inputName !== attributes.name) {
        setAttributes({
          name: inputName
        });
      }
    }, 800);
    return () => clearTimeout(timeoutId);
  }, [inputName, attributes.name, setAttributes]);
  const labelChild = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.useSelect)(select => select('core/block-editor').getBlockParentsByBlockName(clientId, 'tsjippy-forms/label').length > 0, [clientId]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    if (attributes.labelChild !== labelChild) {
      setAttributes({
        labelChild: labelChild
      });
    }
  }, [labelChild, attributes.labelChild, setAttributes]);
  const inputNameComponent = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
    label: "Input Name",
    value: inputName,
    onChange: setInputName
  });
  const inputTypeSelector = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
    label: "Input Type",
    value: attributes.type,
    options: typeOptions,
    onChange: type => setAttributes({
      type
    })
  });
  const inputValue = attributes.type === 'submit' ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
    label: "Input Content",
    value: attributes.value,
    onChange: value => setAttributes({
      value
    })
  }) : null;
  const selectableOptions = ['radio', 'checkbox', 'select'].includes(attributes.type) ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("h4", {
      children: "Static Options"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_shared_AddOptions__WEBPACK_IMPORTED_MODULE_10__["default"], {
      attributes: attributes,
      setAttributes: setAttributes
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("h4", {
      children: "Dynamic Options (prefill)"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_shared_usePrefill_js__WEBPACK_IMPORTED_MODULE_9__.PrefillOptionsSelector, {
      value: attributes.options_dynamic,
      onChange: value => setAttributes({
        options_dynamic: value
      })
    })]
  }) : null;
  const renderPropertiesForm = () => {
    if (!isSelected) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_components_InputHtml_js__WEBPACK_IMPORTED_MODULE_8__.InputHtml, {
        attributes: attributes,
        blockProps: blockProps,
        labelChild: labelChild
      });
    }
    if (attributes.type === '') {
      return inputTypeSelector;
    }
    if (!attributes.name) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.Fragment, {
        children: [inputTypeSelector, inputNameComponent]
      });
    }
    const attributeControls = (0,_components_dynamic_inputs_js__WEBPACK_IMPORTED_MODULE_7__.dynamicInputs)(attributes, 'default', storeAttributeAttributes);
    const ariaControls = attributes.ariaAttributes ? (0,_components_dynamic_inputs_js__WEBPACK_IMPORTED_MODULE_7__.dynamicInputs)(attributes, 'aria', storeAttributeAttributes) : [];
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.Fragment, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_components_InputHtml_js__WEBPACK_IMPORTED_MODULE_8__.InputHtml, {
        attributes: attributes,
        blockProps: blockProps,
        labelChild: labelChild
      }), inputTypeSelector, inputNameComponent, selectableOptions, /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("h4", {
        children: "Dynamic Value (prefill)"
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_shared_usePrefill_js__WEBPACK_IMPORTED_MODULE_9__.PrefillValueSelector, {
        value: attributes.dynamic_value,
        onChange: value => setAttributes({
          dynamic_value: value
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("div", {
        className: "attributes-form",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("h3", {
          children: "Input properties"
        }), attributeControls, /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add aria attributes', 'tsjippy'),
          checked: !!attributes.ariaAttributes,
          onChange: ariaAttributes => setAttributes({
            ariaAttributes
          })
        }), ariaControls]
      })]
    });
  };
  const legend = attributes.type.charAt(0).toUpperCase() + attributes.type.slice(1);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Input Settings', 'tsjippy'),
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          label: "Input Type",
          value: attributes.type,
          options: typeOptions,
          onChange: type => setAttributes({
            type
          })
        }), inputNameComponent, inputValue, /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Allow multiple answers', 'tsjippy'),
          checked: !!attributes.multiple,
          onChange: multiple => setAttributes({
            multiple
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('This is a required input', 'tsjippy'),
          checked: !!attributes.required,
          onChange: required => setAttributes({
            required
          })
        }), attributes.multiple && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.Fragment, {
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
            label: "Add Button Text",
            value: attributes.add_button_content,
            onChange: add_button_content => setAttributes({
              add_button_content
            })
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
            label: "Remove Button Text",
            value: attributes.remove_button_content,
            onChange: remove_button_content => setAttributes({
              remove_button_content
            })
          })]
        })]
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("div", {
      ...blockProps,
      children: [legend, " input", /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("br", {}), renderPropertiesForm()]
    })]
  });
}

/***/ },

/***/ "./src/input/save.js"
/*!***************************!*\
  !*** ./src/input/save.js ***!
  \***************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ save)
/* harmony export */ });
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_InputHtml_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/InputHtml.js */ "./src/input/components/InputHtml.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__);
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */




/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into post_content.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element}
 */

function save({
  attributes
}) {
  const blockProps = _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.useBlockProps.save();
  return attributes.type === 'text' && attributes.multiple ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: `${blockProps.className ?? ''} option-wrapper`,
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("ul", {
      className: "list-selection-list"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "multi-text-input-wrapper",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_components_InputHtml_js__WEBPACK_IMPORTED_MODULE_1__.InputHtml, {
        attributes: attributes,
        blockProps: blockProps,
        labelChild: false,
        isSaving: true
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
        type: "button",
        className: "small add-list-selection hidden",
        children: "add"
      })]
    })]
  }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_components_InputHtml_js__WEBPACK_IMPORTED_MODULE_1__.InputHtml, {
    attributes: attributes,
    blockProps: blockProps,
    labelChild: attributes.labelChild,
    isSaving: true
  });
}

/***/ },

/***/ "./src/input/editor.scss"
/*!*******************************!*\
  !*** ./src/input/editor.scss ***!
  \*******************************/
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

/***/ "@wordpress/i18n"
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["i18n"];

/***/ },

/***/ "./src/input/block.json"
/*!******************************!*\
  !*** ./src/input/block.json ***!
  \******************************/
(module) {

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"tsjippy-forms/input","version":"0.1.0","title":"Form Input","category":"form-elements","icon":"forms","description":"Input element for a form","example":{},"supports":{"html":false},"textdomain":"tsjippy","editorScript":"file:./index.js","editorStyle":"file:./index.css","attributes":{"type":{"type":"string","default":""},"name":{"type":"string","default":""},"value":{"type":"string","default":""},"inputAttributes":{"type":"object","default":{}},"ariaAttributes":{"type":"boolean","default":false},"options":{"type":"array","default":[]},"options_dynamic":{"type":"string","default":""},"dynamic_value":{"type":"string","default":""},"add_button_content":{"type":"string","default":"+"},"remove_button_content":{"type":"string","default":"-"},"multiple":{"type":"boolean","default":false},"required":{"type":"boolean","default":false},"hasLabelParent":{"type":"boolean","default":false},"reminderConditions":{"type":"array","default":[]},"roles":{"type":"array","default":[]}}}');

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
/************************************************************************/
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
/************************************************************************/
let __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!****************************!*\
  !*** ./src/input/index.js ***!
  \****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./edit */ "./src/input/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./save */ "./src/input/save.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./block.json */ "./src/input/block.json");
/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */


/**
 * Internal dependencies
 */




/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_3__.name, {
  /**
   * @see ./edit.js
   */
  edit: _edit__WEBPACK_IMPORTED_MODULE_1__["default"],
  /**
   * @see ./save.js
   */
  save: _save__WEBPACK_IMPORTED_MODULE_2__["default"]
});
})();

/******/ })()
;
//# sourceMappingURL=index.js.map