/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

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
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/icon/index.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/plus.mjs");
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./editor.scss */ "./src/formbuilder/editor.scss");
/* harmony import */ var _innerblock_filter_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./innerblock_filter.js */ "./src/formbuilder/innerblock_filter.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__);












const MY_TEMPLATE = [
/* [ 
	'tsjippy-forms/label', 
	{ text: "Your Name"}, 
	[
       	[ 'tsjippy-forms/input', { type: 'number', name: 'amount'} ]
   	] 
], */
['tsjippy-forms/input', {
  type: 'submit',
  name: 'submit',
  value: 'Submit the form'
}]];
var formRemindersForm = '';
document.addEventListener("DOMContentLoaded", () => {
  _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default()({
    path: tsjippy.restApiPrefix + `/forms/get_form_reminder_form`,
    method: "POST"
  }).then(res => {
    formRemindersForm = res;
  });
});
var emailsForm = '';
document.addEventListener("DOMContentLoaded", () => {
  _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default()({
    path: tsjippy.restApiPrefix + `/forms/get_emails_form`,
    method: "POST"
  }).then(res => {
    emailsForm = res;
  });
});

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
  clientId,
  isSelected
}) {
  /**
   * Register the form if not done yet
   */
  if (attributes.name != '' && attributes.id == -1) {
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default()({
      path: tsjippy.restApiPrefix + `/forms/register_form`,
      method: "POST",
      data: {
        slug: attributes.name
      }
    }).then(res => {
      setAttributes({
        id: res
      });
    });
  }
  const CustomAppender = ({
    clientId
  }) => {
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.Inserter, {
      rootClientId: clientId
      // renderToggle passes the function to open the inline popup
      ,
      renderToggle: ({
        onToggle,
        isOpen
      }) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
        className: "add-form-element-button",
        onClick: onToggle,
        "aria-expanded": isOpen,
        variant: "tertiary",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_icons__WEBPACK_IMPORTED_MODULE_7__["default"], {
          icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_8__["default"]
        }), "Add More Form Blocks"]
      }),
      isAppender: true
    });
  };
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps)();
  const {
    children,
    ...innerBlocksProps
  } = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useInnerBlocksProps)(blockProps, {
    orientation: 'vertical',
    // Enables drag & drop functionality
    template: MY_TEMPLATE,
    renderAppender: CustomAppender
  });

  // Get roles
  const [availableRoles, setAvailableRoles] = (0,react__WEBPACK_IMPORTED_MODULE_3__.useState)([]);
  (0,react__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default()({
      path: tsjippy.restApiPrefix + `/forms/get_roles`,
      method: "POST"
    }).then(res => {
      setAvailableRoles(res);
    });
  }, []);

  /**
   * Actions
   */
  // Get available actions
  const [availableActions, setAvailableActions] = (0,react__WEBPACK_IMPORTED_MODULE_3__.useState)([]);
  (0,react__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default()({
      path: tsjippy.restApiPrefix + `/forms/get_form_actions`,
      method: "POST"
    }).then(res => {
      setAvailableActions(res);
    });
  }, []);

  // Build the checkboxes
  const getActionCheckboxes = () => {
    return [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("b", {
      children: "Select available actions for form submission data"
    }), availableActions.map(action => {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CheckboxControl, {
        label: action,
        onChange: checked => actionSelected(checked, action),
        checked: attributes.actions.indexOf(action) > -1
      }, action);
    })];
  };

  // Store the settings
  const actionSelected = function (checked, action) {
    let actions = attributes.actions;

    // An action just got selected
    if (checked) {
      // Add to stored roles
      actions.push(action);
    } else {
      // remove from array
      actions = actions.filter(p => {
        return p != action;
      });
    }

    // Store in Attributes
    // We need to set a new array to trigger a re-render
    setAttributes({
      actions: [...actions]
    });
  };

  // Stores whetther to show the forms or the main form
  const [isEmailsFormVisible, setEmailsFormVisibility] = (0,react__WEBPACK_IMPORTED_MODULE_3__.useState)(false);
  const [isRemindersFormVisible, setRemindersFormVisibility] = (0,react__WEBPACK_IMPORTED_MODULE_3__.useState)(false);

  /**
   * ROLES
   */
  /**
      * Runs when a role gets (de)selected
      * @param {bool} checked true when selected, false otherwise
      */
  const onRoleSelected = function (checked, roleSlug) {
    let roles = attributes.roles;

    // A role just got selected
    if (checked) {
      // Add to stored roles
      roles.push(roleSlug);
    } else {
      // remove from array
      roles = roles.filter(p => {
        return p != roleSlug;
      });
    }

    // Store in Attributes
    // Store as a new array to trigger a new render
    setAttributes({
      roles: [...roles]
    });
  };

  /**
   * Get form elements as select options
   */
  const innerBlocks = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useSelect)(select => select('core/block-editor').getBlocks(clientId), [clientId]);
  const getFormElements = () => {
    let blockNames = [];
    innerBlocks.map(block => {
      blockNames.push({
        label: block.attributes.name,
        value: block.attributes.name
      });
    });
    return blockNames;
  };
  const getSplitElements = () => {
    let splittable = [];
    innerBlocks.map(block => {
      if (block.attributes.name != undefined && block.attributes.name.search(/\[[\d*]*\]/) > -1) {
        splittable.push({
          label: block.attributes.name,
          value: block.attributes.name
        });
      }
    });
    if (splittable.length === 0) {
      return;
    }
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Formdata Splitting', 'tsjippy'),
      initialOpen: false,
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
        __next40pxDefaultSize: true,
        multiple: true,
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Split Form Submissions on these input values"),
        value: attributes.split_elements,
        options: splittable,
        onChange: blockName => setAttributes({
          split_elements: blockName
        })
      })
    });
  };
  const resultingForm = () => {
    if (isEmailsFormVisible) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("div", {
        ...blockProps,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__.RawHTML, {
          children: [" ", emailsForm, " "]
        })
      });
    } else if (isRemindersFormVisible) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("div", {
        ...blockProps,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__.RawHTML, {
          children: [" ", formRemindersForm, " "]
        })
      });
    }
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("fieldset", {
      ...blockProps,
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)("legend", {
        children: [attributes.name.charAt(0).toUpperCase() + attributes.name.slice(1), " Form"]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("form", {
        ...innerBlocksProps,
        children: children
      })]
    });
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form Settings', 'tsjippy'),
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.RadioControl, {
          label: "Form Method",
          help: "The type of the form, get adds all form values to the url, post is invisble",
          selected: attributes.method,
          options: [{
            label: 'Get',
            value: 'get'
          }, {
            label: 'Post',
            value: 'post'
          }],
          onChange: method => setAttributes({
            method: method
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: "Form Name",
          value: attributes.name,
          onChange: value => setAttributes({
            name: value
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.RadioControl, {
          label: "Form Target",
          help: "Target location for the form response",
          selected: attributes.target,
          options: [{
            label: 'New Tab',
            value: '_blank'
          }, {
            label: 'Current page',
            value: '_self'
          }, {
            label: 'Parent Frame',
            value: '_parent'
          }, {
            label: 'In the body',
            value: '_top'
          }, {
            label: 'iframe',
            value: 'iframe'
          }],
          onChange: target => setAttributes({
            target: target
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Enable autocomplete", "tsjippy"),
          checked: !!attributes.autocomplete,
          onChange: () => setAttributes({
            autocomplete: !attributes.autocomplete
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: "Submission Message",
          value: attributes.submission_message,
          onChange: value => setAttributes({
            submission_message: value
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Include submission ID in message", "tsjippy"),
          checked: !!attributes.submission_id,
          onChange: () => setAttributes({
            submission_id: !attributes.submission_id
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Save submissions in usermeta table", "tsjippy"),
          checked: !!attributes.user_meta,
          onChange: () => setAttributes({
            user_meta: !attributes.user_meta
          })
        }), getActionCheckboxes()]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form Permissions', 'tsjippy'),
        initialOpen: false,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          __next40pxDefaultSize: true,
          multiple: true,
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Select roles or users with form edit rights"),
          value: attributes.edit_roles,
          options: availableRoles,
          onChange: roles => setAttributes({
            edit_roles: roles
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          __next40pxDefaultSize: true,
          multiple: true,
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Select roles who can submit the form on behalve of somebody else"),
          value: attributes.submission_roles,
          options: availableRoles,
          onChange: roles => setAttributes({
            submission_roles: roles
          })
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form Submission Archive Settings', 'tsjippy'),
        initialOpen: false,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
          __next40pxDefaultSize: true,
          multiple: true,
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Auto archive a (sub) entry when field"),
          value: attributes.auto_archive_element,
          options: getFormElements(),
          onChange: blockName => setAttributes({
            auto_archive_element: blockName
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: "equals (A fixed value or you can use placeholders like \u2018%today%+3days\u2019 for a value)",
          value: attributes.auto_archive_value,
          onChange: value => setAttributes({
            auto_archive_value: value
          })
        })]
      }), getSplitElements(), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form E-mails', 'tsjippy'),
        initialOpen: false,
        onToggle: value => setEmailsFormVisibility(value),
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("p", {
          children: "Close this to hide the e-mails form again"
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Form Reminders', 'tsjippy'),
        initialOpen: false,
        onToggle: value => setRemindersFormVisibility(value),
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_11__.jsx)("p", {
          children: "Close this to hide the reminders form again"
        })
      })]
    }), resultingForm()]
  });
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
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./store */ "./src/formbuilder/store.js");
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

/***/ "./src/formbuilder/innerblock_filter.js"
/*!**********************************************!*\
  !*** ./src/formbuilder/innerblock_filter.js ***!
  \**********************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/plus.mjs");
/* harmony import */ var _wordpress_icons__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @wordpress/icons */ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/library/seen.mjs");
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! @wordpress/dom-ready */ "@wordpress/dom-ready");
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_11__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__);













/**
 * Add a button behind each child block
 */

const addButtonToInnerBlocks = (0,_wordpress_compose__WEBPACK_IMPORTED_MODULE_7__.createHigherOrderComponent)(BlockEdit => {
  return props => {
    if (!props.isSelected) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(BlockEdit, {
        ...props
      });
    }

    // Recursive function to get all descendants
    const getAllInnerBlocks = blocks => {
      let allBlocks = [];
      blocks.forEach(block => {
        allBlocks.push(block);
        if (block.innerBlocks && block.innerBlocks.length > 0) {
          allBlocks = allBlocks.concat(getAllInnerBlocks(block.innerBlocks));
        }
      });
      return allBlocks;
    };
    const parentIds = wp.data.select('core/block-editor').getBlockParents(props.clientId);
    const parents = wp.data.select('core/block-editor').getBlocksByClientId(parentIds);
    let isChild = false;
    let parentId = -1;
    let parentForm = -1;
    parents.forEach(parent => {
      if (parent.name == "tsjippy-forms/formbuilder") {
        isChild = true;
        parentId = parent.attributes.id;
        parentForm = parent;
      }
    });

    // Not a child, do not do anything
    if (!isChild) {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(BlockEdit, {
        ...props
      });
    }
    const allNestedBlocks = getAllInnerBlocks(parentForm.innerBlocks);
    const [isConditionsFormVisible, setConditionsFormVisibility] = (0,react__WEBPACK_IMPORTED_MODULE_3__.useState)(false);
    const [conditionsForm, setConditionsForm] = (0,react__WEBPACK_IMPORTED_MODULE_3__.useState)('');
    let elementName = props.attributes.name;

    /**
     * Get the conditions form for this element
     * 
     * @param {boolean} toggled 
     */
    const getConditionsForm = toggled => {
      setConditionsFormVisibility(toggled);
    };
    const toggleConditionsForm = () => {
      setConditionsFormVisibility(!isConditionsFormVisible);
    };
    const updateElementConditions = (ruleIndex, subRuleIndex, key, value) => {
      let newConditions = [...conditions];

      // Create a new rule
      if (newConditions[ruleIndex] == undefined) {
        ruleIndex = rulenewConditions.push([]) - 1;
      }

      // Create a new rule
      if (newConditions[ruleIndex][subRuleIndex] == undefined) {
        subRuleIndex = rulenewConditions.push({}) - 1;
      }
      newConditions[ruleIndex][subRuleIndex][key] = value;
    };
    const formElementOptions = () => {
      return allNestedBlocks.map(block => {
        let name = block.attributes.name ?? block.attributes.text ?? '';
        let label = block.name;
        if (name != '') {
          label += `: ${name}`;
        }
        return {
          label: label,
          value: block.clientId
        };
      });
    };
    const extraOptions = () => {
      if (elementConditions[0][0]['equation'] == '+' || elementConditions[0][0]['equation'] == '-') {
        return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.Fragment, {
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
            label: "Element",
            name: "element-conditions[0][rules][0][conditional-field-2]",
            value: conditionalElement2,
            options: formElementOptions(),
            onChange: element => updateElementConditions(0, 0, "conditional-field-2", element)
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
            label: "equation 2",
            name: "element-conditions[0][rules][0][equation-2]",
            value: equation2,
            options: [{
              label: '---',
              value: ''
            }, {
              label: 'equals',
              value: '=='
            }, {
              label: 'is not',
              value: '!='
            }, {
              label: 'greather than',
              value: '>'
            }, {
              label: 'smaller than',
              value: '<'
            }],
            onChange: equation => updateElementConditions(0, 0, "equation-2", element)
          })]
        });
      }
    };
    const conditions = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useSelect)(select => select('tsjippy-forms/conditions-store').getConditions(props.clientId), [props.clientId]);
    const isLoading = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useSelect)(select => select('tsjippy-forms/conditions-store').isLoading(), []);
    const error = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useSelect)(select => select('tsjippy-forms/conditions-store').getError(), []);

    /**
     * Renders all the rule inputs
     */
    const conditionInputs = () => {
      if (isLoading) {
        return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)("p", {
          children: "Loading..."
        });
      }
      if (error) {
        return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)("p", {
          children: ["Error: ", error]
        });
      }
      for (const [index, condition] of Object.entries(conditions)) {
        /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)("div", {
          class: "condition-row",
          "data-condition-index": index,
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)("span", {
            class: "condition-if",
            children: "If"
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)("br", {}), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)("div", {
            class: "rule-row",
            "data-rule-index": index,
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
              label: "Element",
              value: condition["conditional-field"],
              options: formElementOptions(),
              onChange: element => updateElementConditions(index, 0, "conditional-field", element)
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
              label: "equation",
              value: equation,
              options: [{
                label: '---',
                value: ''
              }, {
                label: 'has changed',
                value: 'changed'
              }, {
                label: 'is clicked',
                value: 'clicked'
              }, {
                label: 'equals',
                value: '=='
              }, {
                label: 'is not',
                value: '!='
              }, {
                label: 'greather than',
                value: '>'
              }, {
                label: 'smaller than',
                value: '<'
              }, {
                label: 'is checked',
                value: 'checked'
              }, {
                label: 'is not checked',
                value: '!checked'
              }, {
                label: 'equals the value of',
                value: '== value'
              }, {
                label: 'does not equal the value of',
                value: '!= value'
              }, {
                label: 'greather than the value of',
                value: '> value'
              }, {
                label: 'smaller than the value of',
                value: '< value'
              }, {
                label: 'minus the value of',
                value: '-'
              }, {
                label: 'plus the value of',
                value: '+'
              }, {
                label: 'is visible',
                value: 'visible'
              }, {
                label: 'is not visible',
                value: 'invisible'
              }],
              onChange: equation => updateElementConditions(index, 0, "equation", element)
            }), extraOptions(), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
              label: "value",
              value: value,
              onChange: value => updateElementConditions(index, 0, "conditional-value", element)
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
              onClick: () => updateElementConditions(index, 0, "combinator", "and"),
              variant: "primary",
              children: "AND"
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
              onClick: () => updateElementConditions(index, 0, "combinator", "or"),
              variant: "secundary",
              children: "OR"
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
              onClick: () => updateElementConditions(index, 0, "add", "or"),
              variant: "tertiary",
              children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Icon, {
                icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_9__["default"]
              }), "Add another rule"]
            })]
          })]
        });
      }
    };

    /**
     * 
     * @returns The form modal
     */
    const elementConditions = () => {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.Fragment, {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)("div", {
          class: "modal-content",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)("span", {
            class: "close mobile-sticky",
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)("svg", {
              width: "24",
              height: "24",
              viewBox: "0 0 24 24",
              fill: "none",
              stroke: "currentColor",
              "stroke-width": "2",
              children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)("line", {
                x1: "18",
                y1: "6",
                x2: "6",
                y2: "18"
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)("line", {
                x1: "6",
                y1: "6",
                x2: "18",
                y2: "18"
              })]
            })
          }), conditionInputs()]
        })
      });
    };

    /**
     * 
     * @returns Shows the conditions form for an element if needed
     */
    const showConditionsForm = () => {
      if (document.querySelector(`#element-conditions-modal`) == null) {
        /**
         * Create the modal div to render the react inside
         */
        let div = document.createElement('div');
        div.id = 'element-conditions-modal';
        div.classList.add("modal");
        document.body.append(div);
      }

      /**
       * Register the react component
       */
      const domNode = document.getElementById('element-conditions-modal');
      const root = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_5__.createRoot)(domNode);
      root.render(elementConditions());

      // Show the form
      if (isConditionsFormVisible) {

        /* if(
            conditionsForm != '' && 
            document.querySelector(`#element-conditions-modal .loader`) != null
        ){
            document.querySelector(`#element-conditions-modal .modal-content`).innerHTML = conditionsForm;
        }
        else if( document.querySelector(`#element-conditions-modal`) == null ){
              div = document.createElement('div');
            div.id ='element-conditions-modal-' + props.clientId;
            div.classList.add("modal");
            div.style.display = "unset"; 
            div.style.zIndex  = "999999999 !important;"
              let content = document.createElement('div');
            content.classList.add("modal-content");
              div.append(content);
              
              if( conditionsForm == ''){
                content.innerHTML = Main.showLoader( null, true, 50, 'Loading Form', true);
            }else{
                content.innerHTML = conditionsForm;
            }
              return document.body.append(div);
        } */
      }
      return;
    };
    const blockControls = () => {
      let buttonText = "Set Input Conditions";
      if (isConditionsFormVisible) {
        buttonText = "Close Conditions Form";
      }
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.Fragment, {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.BlockControls, {
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToolbarGroup, {
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToolbarButton, {
              icon: _wordpress_icons__WEBPACK_IMPORTED_MODULE_10__["default"],
              label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)(buttonText, 'tsjippy'),
              onClick: toggleConditionsForm
            })
          })
        })
      });
    };

    /**
     * Actual Rendering
     */
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsxs)(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__.Fragment, {
      children: [blockControls(), showConditionsForm(), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(BlockEdit, {
        ...props
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
          title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)("Block Conditions", "tsjippy"),
          initialOpen: false,
          onToggle: value => getConditionsForm(value),
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_12__.jsx)("p", {
            children: "Close this to hide the conditions form again"
          })
        })
      })]
    });
  };
}, 'addButtonToInnerBlocks');

// Registreer het filter in de Gutenberg editor
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_8__.addFilter)('editor.BlockEdit', 'tsjippy-forms/add-conditions-button', addButtonToInnerBlocks);

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

/***/ "./src/formbuilder/store.js"
/*!**********************************!*\
  !*** ./src/formbuilder/store.js ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__);


const DEFAULT_STATE = {
  conditionsByElement: {},
  isLoading: false,
  error: null
};
const actions = {
  setConditions(elementId, conditions) {
    return {
      type: 'SET_CONDITIONS',
      elementId,
      conditions
    };
  },
  setLoading(isLoading) {
    return {
      type: 'SET_LOADING',
      isLoading
    };
  },
  setError(error) {
    return {
      type: 'SET_ERROR',
      error
    };
  }
};
function reducer(state = DEFAULT_STATE, action) {
  switch (action.type) {
    case 'SET_CONDITIONS':
      return {
        ...state,
        conditionsByElement: {
          ...state.conditionsByElement,
          [action.elementId]: action.conditions
        }
      };
    case 'SET_LOADING':
      return {
        ...state,
        isLoading: action.isLoading
      };
    case 'SET_ERROR':
      return {
        ...state,
        error: action.error
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
  }
};
const resolvers = {
  *getConditions(elementId) {
    yield actions.setLoading(true);
    yield actions.setError(null);
    try {
      const conditions = yield _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default()({
        path: `${tsjippy.restApiPrefix}/forms/get_element_conditions`,
        method: 'POST',
        data: {
          elementId
        }
      });
      yield actions.setConditions(elementId, conditions);
    } catch (error) {
      yield actions.setError(error?.message || 'Unknown error');
    }
    yield actions.setLoading(false);
  }
};
const store = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.createReduxStore)('tsjippy-forms/conditions-store', {
  reducer,
  actions,
  selectors,
  resolvers
});
(0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.register)(store);

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

/***/ "@wordpress/dom-ready"
/*!**********************************!*\
  !*** external ["wp","domReady"] ***!
  \**********************************/
(module) {

module.exports = window["wp"]["domReady"];

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

/***/ "./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/icon/index.mjs"
/*!************************************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/@wordpress+icons@15.2.0_@types+react@18.3.31_react@18.3.1/node_modules/@wordpress/icons/build-module/icon/index.mjs ***!
  \************************************************************************************************************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ icon_default)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
// packages/icons/src/icon/index.ts

var icon_default = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.forwardRef)(
  ({ icon, size = 24, ...props }, ref) => {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.cloneElement)(icon, {
      width: size,
      height: size,
      ...props,
      ref
    });
  }
);

//# sourceMappingURL=index.mjs.map


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