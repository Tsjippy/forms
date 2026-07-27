/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

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
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_3__);




function InputHtml({
  attributes,
  blockProps,
  hasLabelParent
}) {
  let html;
  if (['radio', 'checkbox'].includes(attributes.type)) {
    html = attributes.selectable_options.split("\n").map((option, index) => {
      const [value, label] = option.split("|");
      return /*#__PURE__*/(0,react__WEBPACK_IMPORTED_MODULE_3__.createElement)("label", {
        ...blockProps,
        key: index
      }, /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
        type: attributes.type,
        name: attributes.name,
        value: value,
        className: "formbuilder",
        "data-blockid": attributes.blockId
      }), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)(label, 'tsjippy'));
    });
  } else if (attributes.type === 'select') {
    html = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("select", {
      name: attributes.name,
      className: "formbuilder",
      multiple: attributes.multiple,
      "data-blockid": attributes.blockId,
      ...blockProps,
      children: attributes.selectable_options.split("\n").map((option, index) => {
        const [value, label] = option.split("|");
        return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("option", {
          value: value,
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)(label, 'tsjippy')
        }, index);
      })
    });
  } else if (attributes.type === 'datalist') {
    html = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("datalist", {
      id: attributes.name,
      "data-blockid": attributes.blockId,
      ...blockProps,
      children: attributes.selectable_options.split("\n").map((option, index) => {
        const [value, label] = option.split("|");
        return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("option", {
          "data-value": value,
          value: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)(label, 'tsjippy')
        }, index);
      })
    });
  } else {
    html = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
      ...blockProps,
      type: attributes.type,
      name: attributes.name,
      value: attributes.value,
      className: "formbuilder",
      "data-blockid": attributes.blockId
    });
  }
  return attributes.multiple && !hasLabelParent ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_Multiple_js__WEBPACK_IMPORTED_MODULE_1__.Multiple, {
    inner: html,
    attributes: attributes
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
  const addText = props.attributes.add_button_content ?? '+';
  const removeText = props.attributes.remove_button_content ?? '-';
  const inputType = props.attributes.type ?? '';
  return inputType === 'text' ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", {
    className: "option-wrapper",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("ul", {
      className: "list-selection-list"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", {
      className: "multi-text-input-wrapper",
      children: [props.inner, /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("button", {
        type: "button",
        className: "small add-list-selection hidden",
        children: "add"
      })]
    })]
  }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", {
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

/***/ "./src/input/dynamic_inputs.js"
/*!*************************************!*\
  !*** ./src/input/dynamic_inputs.js ***!
  \*************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   dynamicInputs: () => (/* binding */ dynamicInputs)
/* harmony export */ });
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__);


/**
 * Stores data- attributes
 * @param {*} type 
 * @param {*} newValue 
 * @param {*} name 
 * @param {*} saveFunction 
 * @param {*} all 
 */

const storeDataAtributes = (type, newValue, name, saveFunction, all) => {
  // Remove old entry if it is a name update
  if (type == 'name') {
    all[newValue] = all[name] ?? '';
    delete all[name];
  } else {
    all[name] = newValue;
  }
  saveFunction({
    ...all
  }, 'data-*');
};

/**
 * Creates inputs based on an array
 */
const dynamicInputs = (inputData, values, saveFunction) => {
  let controls = [];
  inputData.forEach(data => {
    let attributeName = data.attribute;
    let attributeValue = values[data.attribute] ?? '';

    /**
     * Multiple entries possible
     */
    if (attributeName == 'data-*') {
      // The name
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("h4", {
        style: {
          marginTop: '20px'
        },
        children: "Data- Attributes"
      }));

      /**
       * attributeValue should be an array
       * of name values
       */
      if (attributeValue == '') {
        attributeValue = {};
      }

      // Add an empty one to allow new data- attributes
      attributeValue[''] = '';

      // Loop over all existing data- attributes
      for (const [key, value] of Object.entries(attributeValue)) {
        controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.TextControl, {
          label: `data-name`,
          value: key,
          onChange: name => storeDataAtributes('name', name, key, saveFunction, attributeValue)
        }));
        controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.TextControl, {
          label: `data-${key} value`,
          value: value,
          onChange: value => storeDataAtributes('value', value, key, saveFunction, attributeValue)
        }));
      }
    } else if (data.expectedType == 'string') {
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.TextControl, {
        label: attributeName,
        value: attributeValue,
        onChange: value => saveFunction(value, attributeName)
      }));
    } else if (data.expectedType == 'boolean') {
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.ToggleControl, {
        label: attributeName,
        checked: !!attributeValue,
        onChange: checked => saveFunction(checked, attributeName)
      }));
    } else if (data.expectedType == 'number') {
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.__experimentalNumberControl, {
        label: attributeName,
        isShiftStepEnabled: true,
        onChange: value => saveFunction(value, attributeName),
        shiftStep: 1,
        value: attributeValue
      }));
    } else if (data.expectedType.includes('|')) {
      let options = [];
      data.expectedType.split('|').forEach(value => {
        options.push({
          label: value,
          value: value
        });
      });
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__.RadioControl, {
        label: attributeName,
        selected: attributeValue,
        options: options,
        onChange: checked => saveFunction(checked, attributeName)
      }));
    } else {
      controls.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("div", {
        children: ["Not sure how to render this ", data.expectedType]
      }));
    }
  });
  return controls;
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
/* harmony import */ var _element_attributes_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./element_attributes.js */ "./src/input/element_attributes.js");
/* harmony import */ var _dynamic_inputs_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./dynamic_inputs.js */ "./src/input/dynamic_inputs.js");
/* harmony import */ var _components_InputHtml_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./components/InputHtml.js */ "./src/input/components/InputHtml.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__);










/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */

function Edit({
  attributes,
  setAttributes,
  isSelected,
  clientId
}) {
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps)();
  const getTypeOptions = () => {
    let typeOptions = [{
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Select an input type', 'tsjippy'),
      value: ''
    }];
    _element_attributes_js__WEBPACK_IMPORTED_MODULE_6__.inputTypes.forEach(type => {
      typeOptions.push({
        label: type,
        value: type
      });
    });
    return typeOptions;
  };

  /**
   * For a submit type the value is what is shown in the button
   */
  const inputValue = () => {
    if (attributes.type != 'submit') {
      return '';
    }
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
      label: "Input Content",
      value: attributes.value,
      onChange: value => setAttributes({
        value: value
      })
    });
  };

  /**
   * Stores the input attribute value
   */
  const storeAttributeAttributes = (value, name) => {
    let inputAttributes = {
      ...attributes.inputAttributes
    };
    inputAttributes[name] = value;
    setAttributes({
      inputAttributes: inputAttributes
    });
  };

  /**
   * The input type selector
   */
  const inputTypeSelector = () => {
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
      label: "Input Type",
      value: attributes.type,
      options: getTypeOptions(),
      onChange: type => setAttributes({
        type: type
      })
    });
  };

  /**
   * Set a debounce for the label text input so it disappears when we stop typing, not straight after the first character
   */
  const [inputName, setInputName] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useState)(attributes.name);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    const timeoutId = setTimeout(() => {
      setAttributes({
        name: inputName
      });
    }, 800);
    return () => clearTimeout(timeoutId);
  }, [inputName, 800]);

  /**
   * The input name component
   */
  const inputNameComponent = () => {
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
      label: "Input Name",
      value: inputName,
      onChange: name => setInputName(name)
    });
  };
  const hasLabelParent = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.useSelect)(select => select('core/block-editor').getBlockParentsByBlockName(clientId, 'tsjippy-forms/label').length > 0, [clientId]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    if (attributes.hasLabelParent !== hasLabelParent) {
      setAttributes({
        hasLabelParent
      });
    }
  }, [hasLabelParent]);

  /**
   * Shows the input attributes form if this is an selected input
   * 
   * @returns 
   */
  const propertiesForm = () => {
    if (!isSelected) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_components_InputHtml_js__WEBPACK_IMPORTED_MODULE_8__.InputHtml, {
        attributes: attributes,
        blockProps: blockProps,
        hasLabelParent: hasLabelParent
      });
    }

    // First set an input type
    if (attributes.type == '') {
      return inputTypeSelector();
    }

    // Then set a name
    if (attributes.name == '') {
      return [inputTypeSelector(), inputNameComponent()];
    }
    let attributeControls = (0,_dynamic_inputs_js__WEBPACK_IMPORTED_MODULE_7__.dynamicInputs)(_element_attributes_js__WEBPACK_IMPORTED_MODULE_6__.inputSchema.sharedAttributes, attributes.inputAttributes, storeAttributeAttributes);
    let ariaControls = [];

    /**
     * Add aria attributes if we need them
     */
    if (attributes.ariaAttributes) {
      ariaControls = (0,_dynamic_inputs_js__WEBPACK_IMPORTED_MODULE_7__.dynamicInputs)(_element_attributes_js__WEBPACK_IMPORTED_MODULE_6__.inputSchema.ariaAttributes, attributes.inputAttributes, storeAttributeAttributes);
    }

    /**
     * Input type specific options
     */
    const inputTypeSpecificOptions = () => {
      if (['radio', 'checkbox', 'select'].includes(attributes.type)) {
        return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextareaControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Selectable Options", 'tsjippy'),
          help: "One option per line. If the value and label differ seperate them with a |  i.e. car|auto",
          value: attributes.selectable_options,
          onChange: value => setAttributes({
            selectable_options: value
          })
        });
      }
    };
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.Fragment, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_components_InputHtml_js__WEBPACK_IMPORTED_MODULE_8__.InputHtml, {
        attributes: attributes,
        blockProps: blockProps,
        hasLabelParent: hasLabelParent
      }), inputTypeSelector(), inputNameComponent(), inputTypeSpecificOptions(), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)("div", {
        className: "attributes-form",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("h3", {
          children: "Input properties"
        }), attributeControls, /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Add aria attributes', 'tsjippy'),
          checked: !!attributes.ariaAttributes,
          onChange: checked => setAttributes({
            ariaAttributes: checked
          })
        }), ariaControls]
      })]
    });
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Input Settings', 'tsjippy'),
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          label: "Input Type",
          value: attributes.type,
          options: getTypeOptions(),
          onChange: type => setAttributes({
            type: type
          })
        }), inputNameComponent(), inputValue(), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Allow multiple answers', 'tsjippy'),
          checked: !!attributes.multiple,
          onChange: checked => setAttributes({
            multiple: checked
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('This is a required input', 'tsjippy'),
          checked: !!attributes.required,
          onChange: checked => setAttributes({
            required: checked
          })
        }),
        /**
         * If we allow multiple answer we have a + and - button
         * This allows to customize that
         */
        attributes.multiple ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.Fragment, {
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
            label: "Add Button Text",
            value: attributes.add_button_content,
            onChange: value => setAttributes({
              add_button_content: value
            })
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
            label: "Remove Button Text",
            value: attributes.remove_button_content,
            onChange: value => setAttributes({
              remove_button_content: value
            })
          })]
        }) : '']
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("div", {
      ...blockProps,
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)("fieldset", {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)("legend", {
          children: [attributes.type.charAt(0).toUpperCase() + attributes.type.slice(1), " input"]
        }), propertiesForm()]
      })
    })]
  });
}

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
    attribute: "data-*",
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

/***/ "./src/input/index.js"
/*!****************************!*\
  !*** ./src/input/index.js ***!
  \****************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./src/input/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/input/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./save */ "./src/input/save.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./block.json */ "./src/input/block.json");
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
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */

function save({
  attributes,
  clientId
}) {
  const blockProps = _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.useBlockProps.save();
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_components_InputHtml_js__WEBPACK_IMPORTED_MODULE_1__.InputHtml, {
    attributes: attributes,
    blockProps: blockProps,
    hasLabelParent: attributes.hasLabelParent
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

/***/ "./src/input/style.scss"
/*!******************************!*\
  !*** ./src/input/style.scss ***!
  \******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "react"
/*!************************!*\
  !*** external "React" ***!
  \************************/
(module) {

module.exports = window["React"];

/***/ },

/***/ "react/jsx-runtime"
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
(module) {

module.exports = window["ReactJSXRuntime"];

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

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"tsjippy-forms/input","version":"0.1.0","title":"Form Input","category":"form-elements","icon":"forms","description":"Input element for a form","example":{},"supports":{"html":false},"textdomain":"tsjippy","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","viewScript":"file:./view.js","attributes":{"type":{"type":"string","default":""},"name":{"type":"string","default":""},"value":{"type":"string","default":""},"inputAttributes":{"type":"object","default":{}},"ariaAttributes":{"type":"boolean","default":false},"selectable_options":{"type":"string","default":""},"add_button_content":{"type":"string","default":"+"},"remove_button_content":{"type":"string","default":"-"},"multiple":{"type":"boolean","default":false},"required":{"type":"boolean","default":false},"hasLabelParent":{"type":"boolean","default":false}}}');

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
/******/ 			"input/index": 0,
/******/ 			"input/style-index": 0
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
/******/ 		const chunkLoadingGlobal = globalThis["webpackChunkforms_blocks"] ||= [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	let __webpack_exports__ = __webpack_require__.O(undefined, ["input/style-index"], () => (__webpack_require__("./src/input/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map