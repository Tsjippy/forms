/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "../../tsjippy-shared-functionality/js/modules/alert.js"
/*!**************************************************************!*\
  !*** ../../tsjippy-shared-functionality/js/modules/alert.js ***!
  \**************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Alert: () => (/* binding */ Alert)
/* harmony export */ });
/* harmony import */ var _tsjippy_modals__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @tsjippy/modals */ "../../tsjippy-shared-functionality/js/modules/modals.js");
/* harmony import */ var _tsjippy_show_loader__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @tsjippy/show_loader */ "../../tsjippy-shared-functionality/js/modules/show_loader.js");


class Alert {
  /**
   *
   * @param {*} message           The message to show
   * @param {*} type              One of success (default), warning, error, info, question or loader
   * @param {*} options           Array of extra options:
   *                                  Title
   *                                  timer                   Time in miliseconds after wich the modal will close and returns cancel
   *                                  ConfirmButtonText
   *                                  ConfirmButtonColor
   *                                  ConfirmButtonPosition
   *                                  CancelButtonText
   *                                  CancelButtonColor
   *                                  CancelButtonPosition
   *                                  CustomButtonText
   *                                  CustomButtonColor
   *                                  CustomButtonPosition
   *
   * @returns                     A promise which fulfills when a button is clicked with a value of confirm, cancel or custom
   */
  constructor(message, type = "success", options = {}) {
    this.message = message;
    this.type = type;
    this.options = options;
    let title = "";
    if (options.title != undefined) {
      title = options.title;
    }
    this.modal = (0,_tsjippy_modals__WEBPACK_IMPORTED_MODULE_0__.createModal)("alert", title);
    this.addIcon();
    let content = document.createElement("div");
    content.classList.add("alert-content");
    content.innerHTML = String(this.message);
    this.modal.querySelector(".modal-content").append(content);
    this.addButtons();
    document.addEventListener("click", this.clicked.bind(this));
    this.show();
    this.promise = new Promise((resolve, reject) => {
      this.resolve = resolve;
      this.reject = reject;
    });
    return this;
  }
  show() {
    ;(0,_tsjippy_modals__WEBPACK_IMPORTED_MODULE_0__.showModal)(this.modal);
    if (this.options.timer != undefined) {
      this.timer();
    }
  }
  hide() {
    ;(0,_tsjippy_modals__WEBPACK_IMPORTED_MODULE_0__.hideModals)(this.modal);
  }
  timer() {
    setTimeout(this.expired.bind(this), this.options.timer);
  }
  expired() {
    this.resolve("cancel");
    this.hide();
  }
  addIcon() {
    let iconWrapper = document.createElement("div");
    iconWrapper.classList.add("tsjippy-alert-icon", `tsjippy-alert-${this.type}`);
    this.modal.querySelector(".modal-content").append(iconWrapper);
    if (this.type == "error") {
      iconWrapper.innerHTML = `
            <span class="tsjippy-alert-x-mark">
                <span class="tsjippy-alert-x-mark-line-left"></span>
                <span class="tsjippy-alert-x-mark-line-right"></span>
            </span>`;
    } else if (this.type == "warning" || this.type == "info" || this.type == "question") {
      let icon = document.createElement("div");
      icon.classList.add("tsjippy-alert-icon-content");
      if (this.type == "warning") {
        icon.textContent = "!";
      } else if (this.type == "question") {
        icon.textContent = "?";
      } else {
        icon.textContent = "i";
      }
      iconWrapper.appendChild(icon);
    } else if (this.type == "loader") {
      let loader = (0,_tsjippy_show_loader__WEBPACK_IMPORTED_MODULE_1__.showLoader)(iconWrapper);
    } else {
      iconWrapper.innerHTML = `
                <div class="success-circular-line-left"></div>
                <span class="success-line-tip"></span> 
                <span class="success-line-long"></span>
                <div class="success-ring"></div>
                <div class="success-fix"></div>
                <div class="success-circular-line-right"></div>
            `;
    }
  }
  addButtons() {
    let buttons = {};
    const types = ["Confirm", "Cancel", "Custom"];
    let position = 0;
    types.forEach(type => {
      if (this.options[`${type}ButtonText`] != undefined) {
        /**
         * Button properties
         */
        let text = this.options[`${type}ButtonText`];
        let color;
        let id = type.toLowerCase();

        /**
         * Determine color
         */
        if (this.options[`${type}ButtonColor`] == undefined) {
          if (id == "confirm") {
            color = "#bd2919";
          } else {
            color = "#8a1a0e";
          }
        } else {
          color = this.options[`${type}ButtonColor`];
        }

        /**
         * Determine position
         */
        if (this.options[`${type}ButtonPosition`] != undefined) {
          position = this.options[`${type}ButtonPosition`];
        } else {
          position++;
        }
        while (buttons[position] != undefined) {
          position++;
        }

        /**
         * Create button
         */
        let button = document.createElement("button");
        button.classList.add("button", "tsjippy", "alert");
        button.innerHTML = text;
        button.type = "button";
        button.id = `alert-${id}`;
        button.style.backgroundColor = color;
        buttons[position] = button;
      }
    });

    /**
     * Now add the buttons to the modal
     */
    let buttonWrapper = document.createElement("div");
    buttonWrapper.classList.add("alert-button-wrapper");
    this.modal.querySelector(".modal-content").appendChild(buttonWrapper);
    Object.values(buttons).forEach(button => {
      buttonWrapper.appendChild(button);
    });
  }
  clicked(event) {
    let target = event.target;
    let id = target.id;
    if (id.startsWith("alert-")) {
      this.resolve(id.replace("alert-", ""));
      this.hide();
    } else if (target.matches(".close") || target.closest("#alert-modal") == null && target.closest(`#wp-media-modal`) == null // we are not in a media selector popup
    ) {
      this.resolve("cancel");
    }
  }
}

/***/ },

/***/ "../../tsjippy-shared-functionality/js/modules/display_message.js"
/*!************************************************************************!*\
  !*** ../../tsjippy-shared-functionality/js/modules/display_message.js ***!
  \************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   displayMessage: () => (/* binding */ displayMessage)
/* harmony export */ });
/* harmony import */ var _tsjippy_alert__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @tsjippy/alert */ "../../tsjippy-shared-functionality/js/modules/alert.js");

function displayMessage(message, type = "success", timer = "") {
  if (message == undefined) {
    return;
  }
  let options = {
    confirmButtonText: "OK",
    cancelButtonColor: "Crimson",
    cancelButtonText: "Cancel"
  };
  if (timer != "") {
    options["timer"] = timer;
  }
  new _tsjippy_alert__WEBPACK_IMPORTED_MODULE_0__.Alert(message.toString().trim(), type, options);
}

/***/ },

/***/ "../../tsjippy-shared-functionality/js/modules/modals.js"
/*!***************************************************************!*\
  !*** ../../tsjippy-shared-functionality/js/modules/modals.js ***!
  \***************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createModal: () => (/* binding */ createModal),
/* harmony export */   hideModals: () => (/* binding */ hideModals),
/* harmony export */   showModal: () => (/* binding */ showModal)
/* harmony export */ });
function createModal(id, title, content = "") {
  let modal = document.createElement("div");
  modal.classList.add("modal", "hidden", "alert");
  modal.style.zIndex = "999999999 !important";
  modal.id = id + "-modal";
  let modalContent = document.createElement("div");
  modalContent.classList.add("modal-content");
  let closeButton = document.createElement("span");
  closeButton.classList.add("close", "mobile-sticky");
  closeButton.innerHTML = "&times;";
  modalContent.appendChild(closeButton);
  let titleEl = document.createElement("h3");
  titleEl.classList.add("alert-title");
  titleEl.innerHTML = title;
  modalContent.appendChild(titleEl);
  if (content != "") {
    modalContent.append(content);
  }
  modal.appendChild(modalContent);
  return document.querySelector("body").appendChild(modal);
}
function showModal(modal) {
  if (typeof modal == "string") {
    modal = document.getElementById(modal + "-modal");
  }
  if (modal != null) {
    // Prevent main page scrolling
    document.body.style.top = `-${window.scrollY}px`;
    document.body.style.position = "fixed";
    document.body.style.width = "100vw";
    let prim = document.getElementById("primary");
    if (prim != null) {
      prim.style.zIndex = "";
    }
    modal.classList.remove("hidden");
    modal.style.display = "block";
  }
}
function hideModals(modals = null) {
  if (modals == null) {
    modals = document.querySelectorAll(".modal:not(.hidden)");
  }
  if (modals.forEach == undefined) {
    modals = [modals];
  }
  if (modals.length != 0) {
    modals.forEach(modal => {
      modal.classList.add("hidden");
      modal.style.removeProperty("display");
      const event = new Event("modalclosed");
      modal.dispatchEvent(event);
    });

    // Turn main page scrolling on again
    const scrollY = document.body.style.top;
    document.body.style.position = "";
    document.body.style.top = "";
    document.body.style.width = "";
    window.scrollTo(0, parseInt(scrollY || "0") * -1);

    /* 		let prim						= document.getElementById('primary');
        if(prim != null){
            prim.style.zIndex			= 1;
        } */
  }
}

/***/ },

/***/ "../../tsjippy-shared-functionality/js/modules/show_loader.js"
/*!********************************************************************!*\
  !*** ../../tsjippy-shared-functionality/js/modules/show_loader.js ***!
  \********************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showLoader: () => (/* binding */ showLoader)
/* harmony export */ });
function showLoader(element, replace = true, size = 50, text = "", returnHtml = false, inButton = false) {
  if (element == null && returnHtml == false) {
    return;
  }
  if (isNaN(size)) {
    return false;
  }
  size = parseInt(size);
  let factor = size / 100;
  let wrapper = document.createElement("div");
  wrapper.style.height = factor * 110 + "px";
  wrapper.classList.add("loader-wrapper");
  let loader = document.createElement("div");
  loader.style.width = factor * 100 + "px";
  loader.style.height = factor * 100 + "px";
  loader.classList.add("loader");
  wrapper.appendChild(loader);
  for (let i = 0; i < 8; i++) {
    let dot = document.createElement("div");
    dot.classList.add("dot");
    dot.style.width = factor * 16 + "px";
    dot.style.height = factor * 16 + "px";
    if (inButton) {
      dot.style.border = "1px solid white";
    }
    switch (i) {
      case 0:
        dot.style.top = factor * 3 + "px";
        dot.style.left = factor * 44 + "px";
        break;
      case 1:
        dot.style.top = factor * 15 + "px";
        dot.style.left = factor * 73 + "px";
        dot.style.animationDelay = "0.15s";
        break;
      case 2:
        dot.style.top = factor * 44 + "px";
        dot.style.left = factor * 87 + "px";
        dot.style.animationDelay = "0.3s";
        break;
      case 3:
        dot.style.top = factor * 73 + "px";
        dot.style.left = factor * 73 + "px";
        dot.style.animationDelay = "0.45s";
        break;
      case 4:
        dot.style.top = factor * 85 + "px";
        dot.style.left = factor * 44 + "px";
        dot.style.animationDelay = "0.6s";
        break;
      case 5:
        dot.style.top = factor * 73 + "px";
        dot.style.left = factor * 15 + "px";
        dot.style.animationDelay = "0.75s";
        break;
      case 6:
        dot.style.top = factor * 44 + "px";
        dot.style.left = factor * 1 + "px";
        dot.style.animationDelay = "0.9s";
        break;
      case 7:
        dot.style.top = factor * 15 + "px";
        dot.style.left = factor * 15 + "px";
        dot.style.animationDelay = "1.05s";
        break;
      default:
        dot.style.top = 0;
        dot.style.left = 0;
        break;
    }
    loader.appendChild(dot);
  }
  let span = document.createElement("span");
  span.classList.add("loader-text");
  span.innerHTML = text;
  if (inButton) {
    span.style.fontWeight = "normal";
    span.style.marginLeft = "0px";
    span.style.marginRight = "10px";
    wrapper.prepend(span);
  } else {
    wrapper.appendChild(span);
  }
  if (returnHtml) {
    return wrapper.outerHTML;
  }
  if (replace && !inButton) {
    element.parentNode.replaceChild(wrapper, element);
  } else {
    let el = element.nextElementSibling;
    if (el == null) {
      if (inButton) {
        element.dataset.oldHtml = element.innerHTML;
        element.innerHTML = '';
        element.disabled = true;
        element.insertAdjacentElement("beforeEnd", wrapper);
      } else {
        element.parentNode.insertAdjacentElement("beforeEnd", wrapper);
      }
    } else {
      element.parentNode.insertBefore(wrapper, el);
    }
  }
  return wrapper;
}

/***/ },

/***/ "../js/modules/form_submit_functions.js"
/*!**********************************************!*\
  !*** ../js/modules/form_submit_functions.js ***!
  \**********************************************/
(module, __webpack_exports__, __webpack_require__) {

__webpack_require__.a(module, async (__webpack_handle_async_dependencies__, __webpack_async_result__) => { try {
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   fetchRestApi: () => (/* binding */ fetchRestApi),
/* harmony export */   formReset: () => (/* binding */ formReset),
/* harmony export */   markComplete: () => (/* binding */ markComplete),
/* harmony export */   prepareForValidation: () => (/* binding */ prepareForValidation),
/* harmony export */   submitForm: () => (/* binding */ submitForm)
/* harmony export */ });
/* harmony import */ var _tsjippy_show_loader__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @tsjippy/show_loader */ "../../tsjippy-shared-functionality/js/modules/show_loader.js");
/* harmony import */ var _tsjippy_display_message__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @tsjippy/display_message */ "../../tsjippy-shared-functionality/js/modules/display_message.js");
/* harmony import */ var _tsjippy_nonce_script__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @tsjippy/nonce_script */ "@tsjippy/nonce_script");
var __webpack_async_dependencies__ = __webpack_handle_async_dependencies__([_tsjippy_nonce_script__WEBPACK_IMPORTED_MODULE_2__]);
var __webpack_async_dependencies_result__ = (__webpack_async_dependencies__.then ? (await __webpack_async_dependencies__)() : __webpack_async_dependencies__);
_tsjippy_nonce_script__WEBPACK_IMPORTED_MODULE_2__ = __webpack_async_dependencies_result__[0];



function formReset(form) {
  //reset form to the default
  form.reset();

  //hide loaders
  form.querySelectorAll(".loader-wrapper").forEach(el => el.classList.add("hidden"));

  //hide button again if needed
  form.querySelectorAll(".multi-step-controls .form-submit").forEach(el => el.classList.add("hidden"));

  //fix required fields
  form.querySelectorAll("[required]").forEach(el => {
    el.required = false;
  });

  //empty any fileuploads
  form.querySelectorAll(".file_upload").forEach(el => el.value = "");

  //show hidden file uploads
  form.querySelectorAll(".upload-div.hidden").forEach(el => el.classList.remove("hidden"));

  //remove doc previews
  document.querySelectorAll(".document-preview .document").forEach(el => el.remove());
  if (form.querySelector(".formstep") != null) {
    //mark all circles as not active anymore
    form.querySelectorAll(".multi-step-controls .step.finish").forEach(el => el.classList.remove("finish"));
  }

  // Create a CustomEvent
  const customEvt = new CustomEvent('formReset', {
    bubbles: true,
    // Allows event to bubble up through the DOM
    cancelable: true // Allows event.preventDefault()
  });

  // Dispatch the event
  form.dispatchEvent(customEvt);
}

/**
 * Prepares for form validation by unrequiring elements that are not visiible
 * @param {node} wrapper 	The Wrapper element of the inputs to be prepared
 */
function prepareForValidation(wrapper) {
  // make all inputs required that should be
  wrapper.querySelectorAll("[required], .required input, .required textarea, .required select").forEach(el => el.required = true);

  //get all hidden required inputs and unrequire them
  wrapper.querySelectorAll(".hidden [required], select[required], .nice-select-search[required], .step-hidden [required]").forEach(el => {
    el.required = false;
  });

  // Get all multi-text inputs with a value and unrequire the main element
  wrapper.querySelectorAll(`.list-selection-list > .list-selection:first-child`).forEach(list => list.closest(".option-wrapper").querySelectorAll(`:required`).forEach(el => el.required = false));

  // enable disabled fields so it gets included and warnings are shown
  wrapper.querySelectorAll("[disabled][required]").forEach(el => {
    el.disabled = false;
    el.classList.add("was-disabled");
  });
}
async function submitForm(target, url, extraData = "") {
  let form = target.closest("form");
  let validity = true;
  prepareForValidation(form);
  validity = form.reportValidity();
  if (!validity) {
    form.querySelectorAll(":invalid").forEach(el => {
      if (el.validationMessage != undefined) {
        (0,_tsjippy_display_message__WEBPACK_IMPORTED_MODULE_1__.displayMessage)(`${el.name} has an error:\n${el.validationMessage}`, "error");
        form.querySelectorAll(".formstep:not(.step-hidden)").forEach(formstep => formstep.classList.add("step-hidden"));
        if (el.closest(".step-hidden") != null) {
          el.closest(".step-hidden").classList.remove("step-hidden");
        }
        el.focus();
        el.scrollIntoView();
        el.focus();
      }
    });
    return false;
  }

  // Disable the submit button
  target.disabled = true;

  /**
   * Adjust the submit button text
   */
  let buttonText = target.innerText.trim();

  // get the first word
  let text = buttonText.split(" ")[0];
  let vowels = ["a", "e", "i", "o", "u"];
  let lastChar = text.charAt(text.length - 1);
  let secondLatChar = text.charAt(text.length - 2);
  if (lastChar == "e") {
    // replace ie with y
    if (secondLatChar == "i") {
      text = text.substring(0, text.length - 2) + "y";
    }

    // remove the e
    else {
      text = text.substring(0, text.length - 1);
    }
  }

  // duplicate the last letter if needed
  else if (secondLatChar != lastChar && vowels.includes(secondLatChar) && !vowels.includes(lastChar)) {
    text = text + text.substring(text.length - 1, text.length);
  }
  text = text + "ing...";
  target.innerHTML = (0,_tsjippy_show_loader__WEBPACK_IMPORTED_MODULE_0__.showLoader)(null, false, 20, text, true, true);

  //save any tinymce forms
  if (typeof tinymce !== "undefined") {
    tinymce.get().forEach(tn => {
      // only save when the visual tab is active
      if (!tn.hidden) {
        tn.save();
      }
    });
  }

  // change the name of multiselects if needed
  document.querySelectorAll("select[multiple]:not([name$=\\[\\]])").forEach(el => {
    console.error(`Multi select ${el.name} should have [] at the end. I have fixed it for now`);
    el.name = el.name + "[]";
  });
  let formData = new FormData(form);
  if (extraData != "") {
    formData.append("extra", extraData);
  }

  // disable fields again
  form.querySelectorAll(".was-disabled").forEach(el => {
    el.disabled = true;
    el.classList.remove("was-disabled");
  });
  if (form.dataset.addEmpty == true) {
    //also append at least one off all checkboxes
    form.querySelectorAll('input[type="checkbox"]:not(:checked)').forEach(checkbox => {
      //if no checkbox with this name exist yet
      if (!formData.has(checkbox.name)) {
        formData.append(checkbox.name, "");
      }
    });
  }

  // also add get params
  try {
    location["search"].split("?")[1].split("&").forEach(param => {
      let split = param.split("=");
      if (split[0] != "formbuilder" && split[0] != "main-tab" && split[0] != "second-tab") {
        formData.append(split[0], split[1]);
      }
    });
  } catch {
    //pass
  }
  let response = await fetchRestApi(url, formData);

  // Reset button
  target.innerHTML = buttonText;
  target.disabled = false;
  if (form.dataset.reset == undefined) {
    markComplete();
  }
  return response;
}

/**
 * Set the default values to the current values so to not trigger a page leave warning
 */
function markComplete() {
  // pending
  document.querySelectorAll("[data-pending]").forEach(el => {
    el.removeAttribute("data-pending");
  });

  // check all inputs
  document.querySelectorAll("form input:not([type=radio], [type=checkbox]), form textarea").forEach(el => {
    el.defaultValue = el.value;
  });

  // check all checkboxes and radio
  document.querySelectorAll("form input[type=radio], form input[type=checkbox]").forEach(el => {
    if (el.checked) {
      el.defaultChecked = true;
    }
  });

  // check all dropdowns
  document.querySelectorAll("form select").forEach(el => {
    if (el.selectedIndex != -1 && el.options[el.selectedIndex] != undefined) {
      el.options[el.selectedIndex].defaultSelected = true;
    }
  });
}
async function fetchRestApi(url, formData = "", showErrors = true) {
  const data = JSON.parse(document.getElementById('wp-script-module-data-@tsjippy/nonce_script').textContent);
  if (formData == "") {
    formData = new FormData();
  }
  formData.append("_wpnonce", data.restNonce);
  let result;
  try {
    result = await fetch(`${data.baseUrl}/wp-json/tsjippy/v2/${url}`, {
      method: "POST",
      credentials: "same-origin",
      body: formData
    });
  } catch (error) {
    console.error(error);
  }
  let response = await result.text();
  try {
    let json = JSON.parse(response);
    if (result.ok) {
      return json;
    } else if (json.code == "rest_cookie_invalid_nonce") {
      (0,_tsjippy_display_message__WEBPACK_IMPORTED_MODULE_1__.displayMessage)("Please refresh the page and try again!", "error");
      return false;
    } else {
      if (json.data == null || json.data.status == 403) {
        (0,_tsjippy_display_message__WEBPACK_IMPORTED_MODULE_1__.displayMessage)(json.message, "error", 2000);
      } else {
        (0,_tsjippy_display_message__WEBPACK_IMPORTED_MODULE_1__.displayMessage)(json.message + "\n" + JSON.stringify(json.data), "error");
      }
      return false;
    }
  } catch (error) {
    console.error(error);
    console.error(result);
    console.error(response);
    if (result.ok) {
      if (showErrors) {
        (0,_tsjippy_display_message__WEBPACK_IMPORTED_MODULE_1__.displayMessage)(`Problem parsing the json, refresh the page or try again.`, "error");
      }
      console.error(`${data.baseUrl}/wp-json/tsjippy/v2/${url}`);
      console.error(response);
    } else {
      let modal = `<div id='response-details' class="modal hidden" style='z-index:9999999999;'>`;
      modal += `<div class="modal-content" style='max-width: min(1000px,100%);'>`;
      modal += `<span class="close">&times;</span>`;
      modal += response;
      modal += `</div>`;
      modal += `</div>`;
      document.querySelector("body").insertAdjacentHTML("afterBegin", modal);
      (0,_tsjippy_display_message__WEBPACK_IMPORTED_MODULE_1__.displayMessage)(`Error loading url ${data.baseUrl}/wp-json/tsjippy/v2/${url}<br><button class='button small' id='error-details' onclick='(function(){ document.getElementById("response-details").classList.remove("hidden"); })();'>Details</button><br><div class='hidden response-message'>${response}</div>`, "error");
    }
    return false;
  }
}
__webpack_async_result__();
} catch(e) { __webpack_async_result__(e); } });

/***/ },

/***/ "./src/formstep/view.js"
/*!******************************!*\
  !*** ./src/formstep/view.js ***!
  \******************************/
(module, __webpack_exports__, __webpack_require__) {

__webpack_require__.a(module, async (__webpack_handle_async_dependencies__, __webpack_async_result__) => { try {
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _tsjippy_form_submit_functions__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @tsjippy/form_submit_functions */ "../js/modules/form_submit_functions.js");
/* harmony import */ var _tsjippy_display_message__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @tsjippy/display_message */ "../../tsjippy-shared-functionality/js/modules/display_message.js");
var __webpack_async_dependencies__ = __webpack_handle_async_dependencies__([_tsjippy_form_submit_functions__WEBPACK_IMPORTED_MODULE_0__]);
var __webpack_async_dependencies_result__ = (__webpack_async_dependencies__.then ? (await __webpack_async_dependencies__)() : __webpack_async_dependencies__);
_tsjippy_form_submit_functions__WEBPACK_IMPORTED_MODULE_0__ = __webpack_async_dependencies_result__[0];



/**
 * Show the next or previous formstep
 * @param {int} formstep index
 * @param {*}   The parent form element
 * @returns
 */
function nextPrev(n, target) {
  let form = target.closest(`form`);
  let x = form.querySelectorAll(".wp-block-tsjippy-forms-formstep");
  let stepIndicators = form.querySelectorAll(".step");
  let currentTab = 0;
  let valid = true;

  // Find the current active tab
  x.forEach((el, index) => {
    if (!el.matches(".step-hidden")) {
      currentTab = index;
    }
  });

  //Check validity of this step if going forward
  if (n > 0) {
    // Prepare the elements on this tab
    (0,_tsjippy_form_submit_functions__WEBPACK_IMPORTED_MODULE_0__.prepareForValidation)(x[currentTab]);

    // Report validity of each required field
    let elements = x[currentTab].querySelectorAll("input[required], textarea[required], select[required]");
    for (const element of elements) {
      element.required = true;
      valid = element.reportValidity();
      if (!valid) {
        break;
      }
    }
    if (!valid) return;

    //mark the last step as finished
    stepIndicators[currentTab].classList.add("finish");
  } else {
    //mark the last step as unfinished
    stepIndicators[currentTab].classList.remove("finish");
  }

  //loop over all the formsteps to hide stepindicators of them if needed
  Array.from(x).forEach((formstep, index) => {
    if (formstep.classList.contains("hidden")) {
      //hide the corresponding circle
      stepIndicators[index].classList.add("hidden");
    }
  });

  // Increase or decrease the current tab by 1:
  currentTab = currentTab + n;

  //check if the next tab is hidden
  while (x[currentTab].classList.contains("hidden")) {
    //go to the next tab
    currentTab = currentTab + n;
    if (currentTab >= x.length) {
      break;
    }
  }

  // if you have reached the end of the form... :
  if (currentTab >= x.length) {
    return false;
  }
  // Otherwise, display the correct tab:
  showFormStep(currentTab, form);
  return true;
}

/**
 * show a next form step
 * @param {number}  n    - the form step index to show
 * @param {Element} form - the form contaning the form steps
 */
function showFormStep(n, form) {
  if (typeof form != "undefined") {
    if (n == 0) {
      // Hide any loaders
      form.querySelectorAll(".loader-wrapper:not(.hidden), .loader-image-trigger").forEach(loader => loader.remove());

      //show form controls
      form.querySelectorAll(".multi-step-controls.hidden").forEach(el => el.classList.remove("hidden"));
    }

    //hide all formsteps
    form.querySelectorAll(".wp-block-tsjippy-forms-formstep:not(.step-hidden)").forEach(step => step.classList.add("step-hidden"));

    // Show the specified formstep of the form ...
    let x = form.getElementsByClassName("wp-block-tsjippy-forms-formstep");
    if (x.length == 0) {
      return;
    }

    //scroll back to top
    let y = x[n].offsetTop - document.querySelector("#masthead").offsetHeight;
    window.scrollTo({
      top: y,
      behavior: "auto"
    });

    //show
    x[n].classList.remove("step-hidden");

    // This function removes the "active" class of all steps...
    form.querySelectorAll(".step.active").forEach(el => {
      el.classList.remove("active");
    });

    //... and adds the "active" class to the current step:
    x = form.getElementsByClassName("step");
    try {
      x[n].classList.add("active");
    } catch (err) {
      console.log(x);
      console.log(n);
      console.error(err.message);
    }

    // ... and fix the Previous/Next buttons:
    if (n == 0) {
      form.querySelector("button.previous-button").classList.add("hidden");
    } else {
      form.querySelector("button.previous-button").classList.remove("hidden");
    }
    if (n == x.length - 1) {
      form.querySelector("button.next-button").classList.add("hidden");
      form.querySelector(".form-submit").classList.remove("hidden");
    } else {
      form.querySelector("button.next-button").classList.remove("hidden");
      form.querySelector(".form-submit").classList.add("hidden");
    }
  } else {
    console.log("no form defined");
  }
}

/**
 * Updates the amount of step circles
 * Updates the visibility of the prev and next buttons if needed
 *
 * @param {*} form
 */
function updateMultiStepControls(form) {
  // get active formsteps amount
  let formsteps = form.querySelectorAll(".wp-block-tsjippy-forms-formstep");
  let visibleFormsteps = form.querySelectorAll(".wp-block-tsjippy-forms-formstep:not(.hidden)");
  let stepIndicators = form.querySelectorAll(".multi-step-controls-wrapper .step");

  // show all step circles
  stepIndicators.forEach(el => el.classList.remove("hidden"));

  // hide some step circles if needed
  for (let x = visibleFormsteps.length; x < formsteps.length; x++) {
    stepIndicators[x].classList.add("hidden");
  }

  // Add some step circles if needed
  for (let x = stepIndicators.length; x < formsteps.length; x++) {
    let step = document.createElement("span");
    step.classList.add("step");
    form.querySelectorAll(`.step-wrapper`).forEach(el => el.appendChild(step));
  }

  // check if this is the last visible
  let currentFormstep = form.querySelector(".wp-block-tsjippy-forms-formstep:not(.step-hidden)");
  if (visibleFormsteps[visibleFormsteps.length - 1] == currentFormstep) {
    // make the submit button visible
    form.querySelector(".next-button").classList.add("hidden");
    form.querySelector(".form-submit ").classList.remove("hidden");
  } else {
    form.querySelector(".next-button").classList.remove("hidden");
    form.querySelector(".form-submit ").classList.add("hidden");
  }
}

/**
 * Tracks formstep visibily changes and calls updateMultiStepControls if needed
 */
function onClassChange(formstep) {
  let lastClassList = new Set(formstep.classList);
  const mutationObserver = new MutationObserver(mutationList => {
    for (const item of mutationList) {
      // The class got changed
      if (item.attributeName === "class") {
        const classList = new Set(formstep.classList);
        const changed = lastClassList.symmetricDifference(classList);

        // Only do a re-render of the contols we if we added or removed the hidden class
        if (changed.has("hidden")) {
          updateMultiStepControls(formstep.closest("form"));
          lastClassList = classList;
          break;
        }
      }
    }
  });
  mutationObserver.observe(formstep, {
    attributes: true
  });
  return mutationObserver;
}

// Show the first tab
console.log("Formstep js loaded");

// Display the first tab
document.querySelectorAll(`form[data-formname]`).forEach(form => {
  showFormStep(0, form);

  // Reset to first formstep on form reset
  form.addEventListener('formReset', ev => {
    showFormStep(0, form);
  });
});

// Add visibility listener
document.querySelectorAll(`.wp-block-tsjippy-forms-formstep`).forEach(formstep => {
  onClassChange(formstep);
});

// Run on node creation
document.addEventListener("nodeAdded", function (event) {
  let newNode = event.target;

  // Only run for formsteps
  if (!newNode.matches(".wp-block-tsjippy-forms-formstep")) {
    return;
  }

  // hide the new clone
  newNode.classList.add("step-hidden");

  // Update the formstep controls
  let form = newNode.closest("form");
  if (form != null && form.querySelector(".multi-step-controls-wrapper") != null) {
    updateMultiStepControls(form);
  }
  let text = newNode.querySelector(".add.button").textContent.replace("Add ", "");
  (0,_tsjippy_display_message__WEBPACK_IMPORTED_MODULE_1__.displayMessage)(`Succesfully added an extra ${text}<br>Its added as the next page.`);
});

// Run on node deletion
document.addEventListener("nodeRemoved", function (event) {
  let node = event.target;
  let newFormstep = null;
  let parentNode = node.closest(".clone-divs-wrapper");

  // Only run for formsteps
  if (!node.matches(".wp-block-tsjippy-forms-formstep")) {
    return;
  }

  // if there is a next clonable formstep, show that one
  let nextFormstep = parentNode.querySelector(`.wp-block-tsjippy-forms-formstep[data-div-id='${parseInt(node.dataset.divId) + 1}']`);
  if (nextFormstep != null) {
    newFormstep = nextFormstep;
  } else {
    //try the previous one
    let prevFormstep = parentNode.querySelector(`.wp-block-tsjippy-forms-formstep[data-div-id='${parseInt(node.dataset.divId) - 1}']`);
    if (prevFormstep != null) {
      newFormstep = prevFormstep;
    }
  }
  if (newFormstep != null) {
    //check if we need to update the multi step controls
    let form = node.closest("form");
    if (form != null && form.querySelector(".multi-step-controls-wrapper") != null) {
      updateMultiStepControls(form);

      // find the next visible formstep index
      form.querySelectorAll(".wp-block-tsjippy-forms-formstep").forEach((formstep, index) => {
        if (formstep == newFormstep) {
          //show the next visible formstep
          showFormStep(index, form);
        }
      });
    }
  }
});
document.addEventListener("click", ev => {
  let target = ev.target;
  if (target.matches(`.button.next-button`)) {
    nextPrev(1, target);
  } else if (target.matches(`.button.previous-button`)) {
    nextPrev(-1, target);
  }
});
__webpack_async_result__();
} catch(e) { __webpack_async_result__(e); } });

/***/ },

/***/ "@tsjippy/nonce_script"
/*!****************************************!*\
  !*** external "@tsjippy/nonce_script" ***!
  \****************************************/
(module) {

module.exports = import("@tsjippy/nonce_script");;

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
/******/ 	/* webpack/runtime/async module */
/******/ 	(() => {
/******/ 		const webpackQueues = Symbol("webpack queues");
/******/ 		const webpackExports = Symbol("webpack exports");
/******/ 		const webpackError = Symbol("webpack error");
/******/ 		
/******/ 		const resolveQueue = (queue) => {
/******/ 			if(queue?.d < 1) {
/******/ 				queue.d = 1;
/******/ 				queue.forEach((fn) => (fn.r--));
/******/ 				queue.forEach((fn) => (fn.r-- ? fn.r++ : fn()));
/******/ 			}
/******/ 		}
/******/ 		const wrapDeps = (deps) => (deps.map((dep) => {
/******/ 			if(dep !== null && typeof dep === "object") {
/******/ 		
/******/ 				if(dep[webpackQueues]) return dep;
/******/ 				if(dep.then) {
/******/ 					const queue = [];
/******/ 					queue.d = 0;
/******/ 					dep.then((r) => {
/******/ 						obj[webpackExports] = r;
/******/ 						resolveQueue(queue);
/******/ 					}, (e) => {
/******/ 						obj[webpackError] = e;
/******/ 						resolveQueue(queue);
/******/ 					});
/******/ 					const obj = {};
/******/ 		
/******/ 					obj[webpackQueues] = (fn) => (fn(queue));
/******/ 					return obj;
/******/ 				}
/******/ 			}
/******/ 			const ret = {};
/******/ 			ret[webpackQueues] = x => {};
/******/ 			ret[webpackExports] = dep;
/******/ 			return ret;
/******/ 		}));
/******/ 		__webpack_require__.a = (module, body, hasAwait) => {
/******/ 			let queue;
/******/ 			hasAwait && ((queue = []).d = -1);
/******/ 			const depQueues = new Set();
/******/ 			const exports = module.exports;
/******/ 			let currentDeps;
/******/ 			let outerResolve;
/******/ 			let reject;
/******/ 			const promise = new Promise((resolve, rej) => {
/******/ 				reject = rej;
/******/ 				outerResolve = resolve;
/******/ 			});
/******/ 			promise[webpackExports] = exports;
/******/ 			promise[webpackQueues] = (fn) => (queue && fn(queue), depQueues.forEach(fn), promise["catch"](x => {}));
/******/ 			module.exports = promise;
/******/ 			const handle = (deps) => {
/******/ 				currentDeps = wrapDeps(deps);
/******/ 				let fn;
/******/ 				const getResult = () => (currentDeps.map((d) => {
/******/ 		
/******/ 					if(d[webpackError]) throw d[webpackError];
/******/ 					return d[webpackExports];
/******/ 				}))
/******/ 				const promise = new Promise((resolve) => {
/******/ 					fn = () => (resolve(getResult));
/******/ 					fn.r = 0;
/******/ 					const fnQueue = (q) => (q !== queue && !depQueues.has(q) && (depQueues.add(q), q && !q.d && (fn.r++, q.push(fn))));
/******/ 					currentDeps.forEach((dep) => (dep[webpackQueues](fnQueue)));
/******/ 				});
/******/ 				return fn.r ? promise : getResult();
/******/ 			}
/******/ 			const done = (err) => ((err ? reject(promise[webpackError] = err) : outerResolve(exports)), resolveQueue(queue))
/******/ 		
/******/ 			body(handle, done);
/******/ 			queue?.d < 0 && (queue.d = 0);
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	// define getter/value functions for harmony exports
/******/ 	__webpack_require__.d = (exports, definition) => {
/******/ 		for(var key in definition) {
/******/ 			if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 				Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 			}
/******/ 		}
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	__webpack_require__.o = (obj, prop) => (Object.hasOwn(obj, prop));
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = (exports) => {
/******/ 		Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module used 'module' so it can't be inlined
/******/ 	let __webpack_exports__ = __webpack_require__("./src/formstep/view.js");
/******/ 	
/******/ })()
;
//# sourceMappingURL=view.js.map