/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "../../tsjippy-shared-functionality/js/partials/field_value.js"
/*!*********************************************************************!*\
  !*** ../../tsjippy-shared-functionality/js/partials/field_value.js ***!
  \*********************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getDataListValue: () => (/* binding */ getDataListValue),
/* harmony export */   getFieldValue: () => (/* binding */ getFieldValue)
/* harmony export */ });
function getRadioValue(form, selector) {
  let el = form.querySelector(`${selector}:checked`);

  //There is no radio selected currently
  if (el == null) {
    //return an empty value
    return "";
  }
  return el.value;
}
function getCheckboxValue(form, selector, compareValue, element) {
  let value = "";
  let elements = "";

  //we are dealing with a specific checkbox
  if (element.type == "checkbox" && compareValue != null) {
    if (element.checked) {
      return element.value;
    }
    return "";
  }

  //we should find the checkbox with this value and check if it is checked
  if (compareValue != null) {
    elements = form.querySelector(`${selector}[value="${compareValue}" i]:checked`);
    if (elements != null) {
      value = compareValue;
    }
    //no compare value give just return all checked values
  } else {
    elements = form.querySelectorAll(`${selector}:checked`);
    value = [];
    elements.forEach(el => {
      value.push(el.value);
      /* if(value != ''){
      value += ', ';
      }
      value += el.value; */
    });
  }
  return value;
}
function getMultiValue(form, el) {
  let value = [];
  form.querySelectorAll(`[name="${el.name}"]`).forEach(elem => value.push(elem.value));
  return value;
}
function getDataListValue(el) {
  let value = "";
  let origInput = el.list.querySelector(`[value='${el.value}' i]`);
  if (origInput == null) {
    value = el.value;
  } else {
    value = origInput.dataset.value;
  }
  return value;
}
function getFieldValue(elementOrSelector, form, checkDatalist = true, compareValue = null, lowercase = false) {
  let el = "";
  let name = "";
  let value = "";
  let selector = "";

  //name is not a name but a node
  if (elementOrSelector instanceof Element) {
    el = elementOrSelector;
    //check if valid input type
    if (el.tagName != "INPUT" && el.tagName != "TEXTAREA" && el.tagName != "SELECT" && el.closest(".nice-select-dropdown") == null) {
      el = el.querySelector("input, select, textarea");
    }
    if (el == null) {
      el = elementOrSelector;
    }
    name = el.name;
    // We should look for an id
  } else {
    selector = elementOrSelector;
    el = form.querySelector(selector);
    name = el.name;
  }
  if (el == null) {
    console.trace();
    console.log("cannot find element with name " + name);
    return value;
  }
  if (el.type == "radio") {
    value = getRadioValue(form, selector);
  } else if (el.type == "checkbox") {
    value = getCheckboxValue(form, selector, compareValue, el);
  } else if (el.closest(".nice-select-dropdown") != null && el.dataset.value != undefined) {
    //nice select
    value = el.dataset.value;
  } else if (el.list != null && el.value != "" && checkDatalist) {
    value = getDataListValue(el);
  } else if (el.name != undefined && el.name.endsWith("[]")) {
    value = getMultiValue(form, el);
  } else if (el.value != null && el.value != "undefined") {
    value = el.value;
  }
  if (lowercase) {
    return value.toLowerCase();
  }
  return value;
}

/***/ },

/***/ "../../tsjippy-shared-functionality/js/partials/load_assets.js"
/*!*********************************************************************!*\
  !*** ../../tsjippy-shared-functionality/js/partials/load_assets.js ***!
  \*********************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addScripts: () => (/* binding */ addScripts),
/* harmony export */   addStyles: () => (/* binding */ addStyles),
/* harmony export */   afterScriptsLoaded: () => (/* binding */ afterScriptsLoaded),
/* harmony export */   scripts: () => (/* binding */ scripts)
/* harmony export */ });
let scripts;
let afterScriptsLoaded = function (attachTo) {
  // load
  if (typeof tinymce != "undefined") {
    tinymce.remove();

    // Activate tinyMce's again
    document.querySelectorAll(".entry-content .wp-editor-area").forEach(el => {
      window.tinyMCE.execCommand("mceAddEditor", false, el.id);
    });
  }

  // invoke dom content loaded events
  window.document.dispatchEvent(new Event("DOMContentLoaded", {
    bubbles: true,
    cancelable: true
  }));

  // Somehow the visual/text switch does not work, manually fix it
  document.querySelectorAll(".wp-switch-editor").forEach(el => el.addEventListener("click", ev => {
    let area = el.closest(".wp-editor-wrap").querySelector(".wp-editor-area");
    let panel = el.closest(".wp-editor-wrap").querySelector(".mce-panel");
    area.style.visibility = "unset";
    if (ev.target.matches(".switch-tmce")) {
      panel.style.display = "block";
      area.style.display = "none";
    } else {
      panel.style.display = "none";
      area.style.display = "block";
    }
  }));

  //add niceselects
  document.querySelectorAll("select:not(.nonice)").forEach(function (select) {
    Main.attachNiceSelect(select);
  });
  const ev = new Event("scriptsloaded");
  attachTo.dispatchEvent(ev);
};
let addStyles = function (response, attachTo) {
  scripts = [];
  let temp = document.createElement("div");

  // parse inline scripts
  temp.innerHTML = response.html;
  scripts = Array.prototype.slice.call(temp.getElementsByTagName("script"));
  if (response.js) {
    temp.innerHTML = response.js;
    scripts = [...temp.children].concat(scripts);
    addScripts(attachTo);
  }
  if (response.css) {
    temp.innerHTML = response.css;
    [...temp.children].forEach(el => {
      document.head.appendChild(el);
    });
  }
};
let addScripts = function (attachTo) {
  if (attachTo == undefined) {
    return;
  }
  if (scripts.length == 0) {
    afterScriptsLoaded(attachTo);
  }
  let el = scripts.shift();
  if (el == undefined) {
    afterScriptsLoaded(attachTo);
    return;
  }

  // only add if needed
  if (el.tagName == "SCRIPT" && document.getElementById(el.id) == null) {
    var s = document.createElement("script");
    if (el.type == "") {
      s.type = "text/javascript";
    } else {
      s.type = el.type;
    }
    if (el.src != "") {
      s.src = el.src;
    } else {
      s.innerHTML = el.innerHTML;
    }
    s.id = el.id;
    try {
      document.head.appendChild(s);
    } catch (error) {
      console.error(error);
    }
    if (scripts.length > 0) {
      if (el.src != "") {
        s.addEventListener("load", addScripts.bind(null, attachTo));
      } else {
        addScripts(attachTo);
      }
    } else {
      if (el.src != "") {
        s.addEventListener("load", afterScriptsLoaded.bind(null, attachTo));
      } else {
        afterScriptsLoaded(attachTo);
      }
    }
  } else if (scripts.length > 0) {
    // get next script
    addScripts(attachTo);
  } else {
    afterScriptsLoaded(attachTo);
  }
};

/***/ },

/***/ "../js/form_exports.js"
/*!*****************************!*\
  !*** ../js/form_exports.js ***!
  \*****************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   changeFieldProperty: () => (/* binding */ changeFieldProperty),
/* harmony export */   changeFieldValue: () => (/* binding */ changeFieldValue),
/* harmony export */   changeVisibility: () => (/* binding */ changeVisibility),
/* harmony export */   cloneNode: () => (/* binding */ cloneNode),
/* harmony export */   copyFormInput: () => (/* binding */ copyFormInput),
/* harmony export */   fixNumbering: () => (/* binding */ fixNumbering),
/* harmony export */   nextPrev: () => (/* binding */ nextPrev),
/* harmony export */   removeDefaultSelect: () => (/* binding */ removeDefaultSelect),
/* harmony export */   removeNode: () => (/* binding */ removeNode),
/* harmony export */   showFormStep: () => (/* binding */ showFormStep),
/* harmony export */   tidyMultiInputs: () => (/* binding */ tidyMultiInputs),
/* harmony export */   updateMultiStepControls: () => (/* binding */ updateMultiStepControls)
/* harmony export */ });
/* harmony import */ var _tsjippy_shared_functionality_js_partials_field_value_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../tsjippy-shared-functionality/js/partials/field_value.js */ "../../tsjippy-shared-functionality/js/partials/field_value.js");

function removeDefaultSelect(el) {
  Array.from(el.options).forEach(function (option) {
    option.defaultSelected = false;
  });
}
let tinymceSettings = [];
function prepareForCloning(originalNode) {
  //also remove any tinymce's
  if (typeof tinymce != "undefined") {
    originalNode.querySelectorAll(".wp-editor-area").forEach(el => {
      let tn = tinymce.get(el.id);
      if (tn != null) {
        tinymceSettings[el.id] = tn.settings;
        tn.save();
        tn.remove();
      }
    });
  }
}
function cloneNode(originalNode, clear = true) {
  prepareForCloning(originalNode);

  //make a clone
  let newNode = originalNode.cloneNode(true);

  // remove niceselect drop down from clone
  newNode.querySelectorAll(".nice-select").forEach(dropdown => dropdown.remove());

  //add tinymce's again
  originalNode.querySelectorAll(".wp-editor-area").forEach(el => {
    if (tinymceSettings[el.id] != undefined) {
      tinymce.init(tinymceSettings[el.id]);
    }
  });

  //clear values in the clone
  if (clear) {
    newNode.querySelectorAll("input,select,textarea").forEach(input => {
      if (input.type == "checkbox" || input.type == "radio") {
        input.checked = false;
      } else if (!input.matches(".no-reset")) {
        input.value = "";
      }

      //if this is a select
      if (input.type == "select-one") {
        //remove any defaults
        removeDefaultSelect(input);
      }
    });
  }
  newNode.querySelectorAll("select").forEach(select => {
    //remove any defaults
    removeDefaultSelect(select);
    Main.attachNiceSelect(select);
  });
  return newNode;
}
function copyFormInput(originalNode) {
  if (originalNode == null) {
    return false;
  }
  originalNode.querySelectorAll(".remove.hidden").forEach(el => el.classList.remove("hidden"));
  let newNode = cloneNode(originalNode);

  //update the data index
  newNode.querySelectorAll(".upload-files").forEach(function (uploadButton) {
    uploadButton.dataset.index = nodeNr;
  });

  //Clear contents of any document preview divs.
  newNode.querySelectorAll(".document-preview").forEach(function (previewDiv) {
    previewDiv.innerHTML = "";
  });

  //Select
  let i = 0;
  newNode.querySelectorAll("select").forEach(select => {
    //Find the value of the select we have cloned
    let previousVal = originalNode.getElementsByTagName("select")[i].selectedIndex;

    //Hide the value in the clone
    if (select.options[previousVal] != undefined) {
      select.options[previousVal].style.display = "none";
    }

    //Add nice select
    Main.attachNiceSelect(select);
    i++;
  });

  // process tab buttons
  if (originalNode.matches(".tabcontent")) {
    // Hide all
    originalNode.closest(`.clone-divs-wrapper`).querySelectorAll(`:scope > .tabcontent:not(.hidden)`).forEach(el => el.classList.add("hidden"));

    // Show this one
    newNode.classList.remove("hidden");
    let isDummy = true;

    // Add button for the new one
    let orgButton = originalNode.closest(".clone-divs-wrapper").querySelector(`.tablink.dummy`);
    if (orgButton == null) {
      // Clone the tablink button belonging to this tabcontent
      orgButton = originalNode.closest(".clone-divs-wrapper").querySelector(`.tablink[data-target="${originalNode.id}"]`);
      isDummy = false;
    }
    if (orgButton != null) {
      let newButton = cloneNode(orgButton);

      // make the org button inactive
      newButton.classList.remove("dummy", "hidden");

      // remove the active class from the current active button
      originalNode.closest(".clone-divs-wrapper").querySelector(`.tablink.active`).classList.remove("active");

      // Make the new button the active one
      newButton.classList.add("active");

      // Change the name if it does not contain a number
      if (!/\d/.test(newButton.textContent)) {
        newButton.textContent = 'New';
      }

      //Insert the clone
      orgButton.parentNode.insertBefore(newButton, orgButton.nextSibling);
    }
  }

  //Insert the clone
  originalNode.parentNode.insertBefore(newNode, originalNode.nextSibling);

  // Process formsteps
  if (originalNode.matches(".formstep")) {
    // hide the new clone
    newNode.classList.add("step-hidden");

    // Update the formstep controls
    let form = originalNode.closest("form");
    if (form != null && form.querySelector(".multi-step-controls-wrapper") != null) {
      updateMultiStepControls(form);
    }
    let text = originalNode.querySelector(".add.button").textContent.replace("Add ", "");
    Main.displayMessage(`Succesfully added an extra ${text}<br>Its added as the next page.`);
  }
  return newNode;
}
function fixNumbering(wrapper) {
  // Run for clone divs
  wrapper.querySelectorAll(":scope > .clone-div").forEach(updateNumbers);

  // Run for tablinks seperately so the index starts over
  wrapper.querySelectorAll(":scope > .tablink").forEach(updateNumbers);
  function updateNumbers(clone, index) {
    //Update the new number

    // Update the ID
    if (clone.id != "") {
      clone.id = clone.id.replace(/[0-9]+(?!.*[0-9])/, index);
    }

    // Update the content
    if (clone.type == "button" && clone.textContent != "") {
      clone.textContent = clone.textContent.replace(/[0-9]+(?!.*[0-9])/, index + 1);
      clone.dataset.target = clone.dataset.target.replace(/[0-9]+(?!.*[0-9])/, index);
    }

    // Update the divid attribute
    if (clone.dataset.divId != null) {
      clone.dataset.divId = index;
    }

    //Update the title
    clone.querySelectorAll("h3, h4, legend :first-child").forEach(el => {
      el.textContent = el.textContent.replace(/[0-9]+(?!.*[0-9])/, index + 1);
    });

    //Update the legend
    /* clone.querySelectorAll('legend').forEach(legend => {
    legend.textContent = legend.textContent.replace(/[0-9]+(?!.*[0-9])/, ' '+(index+1));
    }); */

    //Update the elements
    clone.querySelectorAll("input,select,textarea").forEach(input => {
      //Do not copy nice selects
      if (!input.classList.contains("nice-select-search")) {
        //Update the id
        if (input.id != "" && input.id != undefined) {
          input.id = input.id.replace(/[0-9]+(?!.*[0-9])/, index);
        }

        //Update the name
        if (input.name != "" && input.name != undefined) {
          input.name = input.name.replace(/[0-9]+(?!.*[0-9])/, index);
        }

        //Reset the select to the default
        if (input.type == "button") {
          input.value = input.value.replace(/[0-9]+(?!.*[0-9])/, index);
        }
      }
    });
  }
}
function removeNode(target) {
  let node = target.closest(".clone-div");
  let parentNode = node.closest(".clone-divs-wrapper");
  let allCloneDivs = parentNode.querySelectorAll(".clone-div");

  //Check if we are removing the last element
  if (allCloneDivs[allCloneDivs.length - 1] == node) {
    let addElement = node.querySelector(".add");

    //Move the add button one up
    let prev = node.previousElementSibling;
    if (prev.querySelector(".button-wrapper") != null && prev.querySelector(".button-wrapper .add") == null) {
      prev.querySelector(".button-wrapper").appendChild(addElement);
    }
  }

  // check if we need to remove a corresponding tab button
  if (node.matches(".tabcontent")) {
    let buttonToRemove = parentNode.querySelector(`.tablink[data-target="${node.id}"]`);
    if (buttonToRemove != null) {
      //if the button is active, make the previous one active
      if (buttonToRemove.classList.contains("active")) {
        let prevButton = buttonToRemove.previousElementSibling;
        if (prevButton != null) {
          prevButton.classList.add("active");

          //show the corresponding tab
          Main.displayTab(prevButton);
        } else {
          //try the next one
          let nextButton = buttonToRemove.nextElementSibling;
          if (nextButton != null) {
            nextButton.classList.add("active");

            //show the corresponding tab
            Main.displayTab(nextButton);
          }
        }
      }

      //remove the button
      buttonToRemove.remove();
    }
  }

  // Check if this is a formstep
  if (node.matches(".formstep")) {
    let newFormstep = null;

    // if there is a next clonable formstep, show that one
    let nextFormstep = parentNode.querySelector(`.formstep[data-div-id='${parseInt(node.dataset.divId) + 1}']`);
    if (nextFormstep != null) {
      newFormstep = nextFormstep;
    } else {
      //try the previous one
      let prevFormstep = parentNode.querySelector(`.formstep[data-div-id='${parseInt(node.dataset.divId) - 1}']`);
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
        form.querySelectorAll(".formstep").forEach((formstep, index) => {
          if (formstep == newFormstep) {
            //show the next visible formstep
            showFormStep(index, form);
          }
        });
      }
    }
  }

  //Remove the node
  node.remove();

  //If there is only one div remaining, hide the remove button
  if (parentNode.querySelectorAll(".clone-div").length == 1) {
    let removeElement = parentNode.querySelector(".remove");
    removeElement.classList.add("hidden");
  }
  fixNumbering(parentNode);
}

/* 
	FUNCTIONS USED BY DYNAMIC FORMS JS
 */
function tidyMultiInputs() {
  //remove unnecessary buttons on inputs with multiple values
  document.querySelectorAll(".clone-divs-wrapper").forEach(function (div) {
    let cloneDivArr = div.querySelectorAll(":scope > .clone-div");
    if (cloneDivArr.length == 1) {
      cloneDivArr[0].querySelectorAll(".remove").forEach(el => el.remove());
    }
    cloneDivArr.forEach(function (cloneDiv, index, array) {
      //update dataset
      cloneDiv.dataset.divId = index;

      //remove add button for all but the last
      if (index != array.length - 1) {
        // Select all add buttons but not the any nested buttons
        cloneDiv.querySelectorAll(".add:not(:scope .clone-divs-wrapper .add)").forEach(el => el.remove());
      }
    });
  });
}
function updateMultiStepControls(form) {
  // get active formsteps amount
  let formsteps = form.querySelectorAll(".formstep");
  let visibleFormsteps = form.querySelectorAll(".formstep:not(.hidden)");
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
  let currentFormstep = form.querySelector(".formstep:not(.step-hidden)");
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
 * show a next form step
 * @param {number} n - the form step index to show
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
    form.querySelectorAll(".formstep:not(.step-hidden)").forEach(step => step.classList.add("step-hidden"));

    // Show the specified formstep of the form ...
    let x = form.getElementsByClassName("formstep");
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
      form.querySelector('[name="previous-button"]').classList.add("hidden");
    } else {
      form.querySelector('[name="previous-button"]').classList.remove("hidden");
    }
    if (n == x.length - 1) {
      form.querySelector('[name="next-button"]').classList.add("hidden");
      form.querySelector(".form-submit").classList.remove("hidden");
    } else {
      form.querySelector('[name="next-button"]').classList.remove("hidden");
      form.querySelector(".form-submit").classList.add("hidden");
    }
  } else {
    console.log("no form defined");
  }
}

//next form step clicked
function nextPrev(n, form) {
  // This function will figure out which tab to display
  let x = form.querySelectorAll(".formstep");
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
    FormSubmit.prepareForValidation(x[currentTab]);

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
function changeFieldValue(selector, value, functionRef, form, addition = "", forceValue = false) {
  if (value == undefined) {
    return;
  }
  let name = "";
  let target = "";
  if (selector instanceof Element) {
    target = selector;
    name = target.name;
    if (target.id == "") {
      selector = `[name^="${target.name}" i]`;
    } else {
      selector = `[id^=${target.id}]`;
    }
  } else {
    target = form.querySelector(selector);
    try {
      name = target.name;
    } catch {
      console.log(target);
    }
  }
  let oldValue = (0,_tsjippy_shared_functionality_js_partials_field_value_js__WEBPACK_IMPORTED_MODULE_0__.getFieldValue)(target, form, false, value);
  // nothing to change
  if (oldValue == value) {
    return;
  }

  // Check if we are dealing with a multi input field
  if (target == null) {
    let targets = form.querySelectorAll(`.clone-div [name^="${name}" i]`);
    if (targets.length === 0) {
      return;
    } else if (targets.length == 1) {
      target = targets[0];
      targets = "";
    } else {
      target = targets[0];
      targets.forEach((el, index) => {
        if (index == 0) {
          changeFieldValue(el, "", "", form);
        } else {
          removeNode(el);
        }
      });
    }
  }

  // calculate the new value
  if (addition != "") {
    // check if a date
    if (/\d{4}-\d{2}-\d{2}/.test(value)) {
      let date = new Date(value);
      date.setDate(date.getDate() + parseInt(addition));
      value = date.toISOString().split("T")[0];
    } else {
      value = value + parseInt(addition);
    }
  }
  if (target.type == "radio" || target.type == "checkbox") {
    // uncheck all
    if (value == "") {
      if (selector != "") {
        form.querySelectorAll(selector).forEach(el => el.checked = false);
      }
    } else {
      // Check if the current target is the one we need to check
      if (target.value.toLowerCase() == value.toLowerCase()) {
        target.checked = true;
      } else {
        // find the element with the given value and check it
        let targets = form.querySelectorAll(`[name="${name}" i]`);
        for (const element of targets) {
          if (element.value.toLowerCase() == value.toLowerCase()) {
            element.checked = true;
          }
        }
      }
    }
    //the target has a list attached to it
  } else if (target.type == "date") {
    target.value = value;

    // convert a date to the right format
    if (!/\d{4}-\d{2}-\d{2}/.test(value)) {
      let splitted = "";
      if (value.split("-").length == 3) {
        splitted = value.split("-");
      } else if (value.split("/").length == 3) {
        splitted = value.split("/");
      }
      if (splitted != "") {
        let year;
        let month;
        let day;
        splitted.forEach(nr => {
          if (nr.length == 4) {
            year = nr;
          } else if (nr.length == 2) {
            if (nr > 12) {
              day = nr;
            } else {
              // does not have a value yet
              if (month == undefined) {
                month = nr;
              } else {
                day = nr;
              }
            }
          }
        });
        if (day != undefined && month != undefined && year != undefined) {
          target.value = `${year}-${month}-${day}`;
        }
      }
    }
  } else if (target.list != null) {
    let dataListOption = target.list.querySelector(`[data-value="${value}" i]`);

    //we found a match
    if (dataListOption != null) {
      // We found a cloned field, add as many inputs as needed
      if (target.closest(".clone-div") != null) {
        // mark the existing ones for deletion, we can delete right now as we need to copy the existing ones first
        target.closest(".clone-divs-wrapper").querySelectorAll(".clone-div").forEach(el => el.classList.add("shouldremove"));
        let clone;
        dataListOption.value.split(";").forEach(val => {
          clone = copyFormInput(target.closest(".clone-div"));
          clone.classList.remove("shouldremove");
          changeFieldValue(clone.querySelector(target.tagName), val, "", form, "", true);
        });
        fixNumbering(target.closest(".clone-divs-wrapper"));

        // delete the old ones
        target.closest(".clone-divs-wrapper").querySelectorAll(".shouldremove").forEach(el => el.remove());
      } else {
        target.value = dataListOption.value;
      }
      // We did not find a match, we are filling in the given value
    } else if (forceValue) {
      target.value = value;
      // We did not find a match, empty value
    } else {
      target.value = "";
    }
  } else {
    target.value = value;
  }

  //create a new event
  let evt = new Event("input");
  //attach the target
  target.dispatchEvent(evt);

  //run the originating function with this event
  if (typeof functionRef == "function") {
    functionRef(target);
  }
}
function changeVisibility(action, el, functionRef) {
  let wrapper = el.closest(".input-wrapper");
  if (wrapper == null) {
    wrapper = el;
  }
  if (action == "add") {
    if (wrapper.matches(".hidden")) {
      return;
    }
    wrapper.classList.add("hidden");
  } else {
    if (!wrapper.matches(".hidden")) {
      return;
    }
    wrapper.classList.remove("hidden");
  }

  //create a new event
  let evt = new Event("input");
  //attach the target
  wrapper.dispatchEvent(evt);

  //run the originating function with this event
  if (typeof functionRef == "function") {
    functionRef(el);
  }
}
function changeFieldProperty(selector, att, value, functionRef, form, addition = "") {
  //first change the value
  let target = form.querySelector(selector);

  // calculate the new value
  if (addition != "") {
    // check if a date
    if (/\d{4}-\d{2}-\d{2}/.test(value)) {
      let date = new Date(value);
      date.setDate(date.getDate() + parseInt(addition));
      value = date.toISOString().split("T")[0];
    } else {
      value = value + parseInt(addition);
    }
  }
  target[att] = value;

  //create a new event
  let evt = new Event("input");

  //attach the target
  target.dispatchEvent(evt);

  //run the originating function with this event
  functionRef(target);
}

/***/ },

/***/ "../js/forms.js"
/*!**********************!*\
  !*** ../js/forms.js ***!
  \**********************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   changeFieldProperty: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.changeFieldProperty),
/* harmony export */   changeFieldValue: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.changeFieldValue),
/* harmony export */   changeVisibility: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.changeVisibility),
/* harmony export */   cloneNode: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.cloneNode),
/* harmony export */   copyFormInput: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.copyFormInput),
/* harmony export */   fixNumbering: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.fixNumbering),
/* harmony export */   getFieldValue: () => (/* reexport safe */ _tsjippy_shared_functionality_js_partials_field_value_js__WEBPACK_IMPORTED_MODULE_2__.getFieldValue),
/* harmony export */   nextPrev: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.nextPrev),
/* harmony export */   removeDefaultSelect: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.removeDefaultSelect),
/* harmony export */   removeNode: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.removeNode),
/* harmony export */   showFormStep: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.showFormStep),
/* harmony export */   tidyMultiInputs: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.tidyMultiInputs),
/* harmony export */   updateMultiStepControls: () => (/* reexport safe */ _form_exports_js__WEBPACK_IMPORTED_MODULE_1__.updateMultiStepControls)
/* harmony export */ });
/* harmony import */ var _tsjippy_shared_functionality_js_partials_load_assets_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../tsjippy-shared-functionality/js/partials/load_assets.js */ "../../tsjippy-shared-functionality/js/partials/load_assets.js");
/* harmony import */ var _form_exports_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./form_exports.js */ "../js/form_exports.js");
/* harmony import */ var _tsjippy_shared_functionality_js_partials_field_value_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../tsjippy-shared-functionality/js/partials/field_value.js */ "../../tsjippy-shared-functionality/js/partials/field_value.js");




console.log("Forms.js is loaded");
async function saveFormInput(target) {
  let form = target.closest("form");

  // make all inputs required if needed
  form.querySelectorAll(".required:not(hidden) input, .required:not(hidden) textarea, .required:not(hidden) select").forEach(el => {
    // do not make nice select inputs nor file uploads required
    if (el.closest("div.nice-select") == null && (el.type != "file" || el.closest(".file-upload-wrap").querySelector(".document-preview input") == null)) {
      el.required = true;
    }
  });
  let response = await FormSubmit.submitForm(target, "forms/save_form_input");
  if (response) {
    Main.displayMessage(response);
    if (form.dataset.reset) {
      FormSubmit.formReset(form);
    }
  }
}
async function formbuilderSwitch(target) {
  let wrapper = target.closest(".tsjippy-form-wrapper");
  let button = target.outerHTML;
  const url = new URL(window.location);
  let searchParams = new URLSearchParams(window.location.search);
  if (target.matches(".formbuilder-switch")) {
    searchParams.set("formbuilder", true);
  } else {
    searchParams.delete("formbuilder");
  }
  window.location.search = searchParams.toString();
}
async function requestNewFormResults(target) {
  let wrapper = target.closest(".form.table-wrapper");
  let button = target.outerHTML;
  let formData = new FormData();
  let formId = wrapper.querySelector(".tsjippy.table.form-data.table").dataset.formId;
  let shortcodeId = wrapper.querySelector(".tsjippy.table.form-data.table").dataset.shortcodeId;
  formData.append("form-id", formId);
  formData.append("shortcode-id", shortcodeId);
  const url = new URL(window.location);
  if (url.searchParams.get("only-own")) {
    formData.append("only-own", true);
  }
  if (url.searchParams.get("all")) {
    formData.append("all", true);
  }
  if (url.searchParams.get("archived")) {
    formData.append("archived", true);
  }
  let loader = Main.showLoader(target, false, 50, "Requesting form results...");
  wrapper.innerHTML = loader.outerHTML;
  let response = await FormSubmit.fetchRestApi("forms/show_form_results", formData);
  if (response) {
    wrapper.innerHTML = response;
  } else {
    loader.outerHTML = button;
  }
}
async function archivedEntriesSwitch(target) {
  const url = new URL(window.location);
  if (target.matches(".archive-switch-show")) {
    url.searchParams.set("archived", true);
  } else {
    url.searchParams.delete("archived");
  }
  window.history.pushState({}, "", url);
  requestNewFormResults(target);
}
async function onlyOwnSwitch(target) {
  const url = new URL(window.location);
  if (target.matches(".only-own-switch-on")) {
    url.searchParams.set("only-own", true);
    url.searchParams.delete("all", true);
  } else {
    url.searchParams.set("all", true);
    url.searchParams.delete("only-own");
  }
  window.history.pushState({}, "", url);
  requestNewFormResults(target);
}
function addNode(target) {
  let wrapper = target.closest(".clone-divs-wrapper");
  let orgNode = target.closest(".clone-div");

  // Check if the orgNode is still in the wrapper, if not, find the last clone-div in the wrapper
  if (orgNode == null || wrapper.contains(orgNode) == false) {
    orgNode = wrapper.querySelector(`:scope >.clone-div:last-child`);
  }
  let newNode = (0,_form_exports_js__WEBPACK_IMPORTED_MODULE_1__.copyFormInput)(orgNode);

  // Fix in nodes
  (0,_form_exports_js__WEBPACK_IMPORTED_MODULE_1__.fixNumbering)(wrapper);

  //add tinymce's can only be done when node is inserted and id is unique
  newNode.querySelectorAll(".wp-editor-area").forEach((el, index) => {
    // find org node settings
    let tn = tinymce.get(orgNode.querySelectorAll(".wp-editor-area")[index].id);
    if (tn != null) {
      let settings = tn.settings;

      // update the settings for the clone
      for (const key in settings) {
        console.log(`${key}: ${settings[key]}`);
        if (typeof settings[key] == "string") {
          settings[key] = settings[key].replace(/(.*)([0-9])/, (match, prefix, nr) => {
            const newNumber = parseInt(nr) + 1;
            return prefix + newNumber;
          });
        }
      }
      tinymce.init(settings);
    } else {
      tinymce.execCommand("mceRemoveEditor", false, el.id);
      tinymce.execCommand("mceAddEditor", false, el.id);
    }
  });

  //target.remove();
}

//Load after page load
document.addEventListener("DOMContentLoaded", () => {
  let html = Main.showLoader("", false, 100, "Please wait...", true);
  document.querySelectorAll(`.form-load-trigger`).forEach(async el => {
    el.innerHTML = html;
    let formId = el.dataset.formId;
    let shortcodeId = el.dataset.shortcodeId;
    let formData = new FormData();
    let response = false;
    if (formId != null) {
      formData.append("form-id", formId);
      response = await FormSubmit.fetchRestApi("forms/load_form", formData);
    } else {
      formData.append("shortcode-id", shortcodeId);
      response = await FormSubmit.fetchRestApi("forms/load_form_results", formData);
    }
    if (response) {
      el.innerHTML = response;
    }
  });
});

//we are online again
window.addEventListener("online", function () {
  document.querySelectorAll(".form-submit").forEach(btn => {
    btn.disabled = false;
    btn.querySelectorAll(".offline").forEach(el => el.remove());
  });
});

//prevent form submit when offline
window.addEventListener("offline", function () {
  document.querySelectorAll(".form-submit").forEach(btn => {
    btn.disabled = true;
    if (btn.querySelector(".online") == null) {
      btn.innerHTML = '<div class="online">' + btn.innerHTML + "</div>";
    }
    btn.innerHTML += '<div class="offline">You are offline</div>';
  });
});
document.addEventListener("click", function (event) {
  let target = event.target;

  //add element
  if (target.matches(".add")) {
    addNode(target);
  }

  //remove element
  if (target.matches(".remove")) {
    //Remove node clicked
    (0,_form_exports_js__WEBPACK_IMPORTED_MODULE_1__.removeNode)(target);
  }
  if (target.matches('.tsjippy-form-wrapper [name="submit-form"]')) {
    event.stopPropagation();
    saveFormInput(target);
  }
  if (target.matches(".formbuilder-switch") || target.matches(".formbuilder-switch-back")) {
    formbuilderSwitch(target);
  }
  if (target.matches(".archive-switch-hide") || target.matches(".archive-switch-show")) {
    archivedEntriesSwitch(target);
  }
  if (target.matches(".only-own-switch-all") || target.matches(".only-own-switch-on")) {
    onlyOwnSwitch(target);
  }
});
document.addEventListener("change", ev => {
  // select all elements with a datalist attached
  if (ev.target.matches("input[list]") && ev.target.name.includes("[")) {
    ev.stopImmediatePropagation();
    let el = ev.target.list.querySelector(`[value="${ev.target.value}" i]`);
    if (el != null) {
      // find the dataset value of the given element value
      let value = el.dataset.value;
      if (value != undefined) {
        // change the value to create extra inputs if necessary
        (0,_form_exports_js__WEBPACK_IMPORTED_MODULE_1__.changeFieldValue)(ev.target, value, "", ev.target.closest("form"));
      }
    }
  }
});

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
/*!*********************************!*\
  !*** ./src/formbuilder/view.js ***!
  \*********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _js_forms_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../../../js/forms.js */ "../js/forms.js");
/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */



/* eslint-disable no-console */
console.log('Hello World! (from formbuilder-my-block block)');
/* eslint-enable no-console */
})();

/******/ })()
;
//# sourceMappingURL=view.js.map