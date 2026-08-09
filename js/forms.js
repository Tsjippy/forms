import { addStyles } from "../../tsjippy-shared-functionality/js/partials/load_assets.js";
import {
  removeDefaultSelect,
  cloneNode,
  copyFormInput,
  fixNumbering,
  removeNode,
  tidyMultiInputs,
  changeFieldValue,
  changeFieldProperty,
} from "./form_exports.js";
import { getFieldValue } from "../../tsjippy-shared-functionality/js/partials/field_value.js";
export {
  getFieldValue,
  removeDefaultSelect,
  cloneNode,
  copyFormInput,
  fixNumbering,
  removeNode,
  tidyMultiInputs,
  changeFieldValue,
  changeFieldProperty,
};

console.log("Forms.js is loaded");

async function saveFormInput(target) {
  let form = target.closest("form");

  // make all inputs required if needed
  form
    .querySelectorAll(
      ".required:not(hidden) input, .required:not(hidden) textarea, .required:not(hidden) select",
    )
    .forEach((el) => {
      // do not make nice select inputs nor file uploads required
      if (
        el.closest("div.nice-select") == null &&
        (el.type != "file" ||
          el
            .closest(".file-upload-wrap")
            .querySelector(".document-preview input") == null)
      ) {
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

function addNode(target){
  let wrapper = target.closest(".clone-divs-wrapper");
  let orgNode = target.closest(".clone-div");

  // Check if the orgNode is still in the wrapper, if not, find the last clone-div in the wrapper
  if (orgNode == null || wrapper.contains(orgNode) == false) {
    orgNode = wrapper.querySelector(`:scope >.clone-div:last-child`);
  }

  let newNode = copyFormInput(orgNode);

  // Fix in nodes
  fixNumbering(wrapper);

  //add tinymce's can only be done when node is inserted and id is unique
  newNode.querySelectorAll(".wp-editor-area").forEach((el, index) => {
    // find org node settings
    let tn = tinymce.get(
      orgNode.querySelectorAll(".wp-editor-area")[index].id,
    );
    if (tn != null) {
      let settings = tn.settings;

      // update the settings for the clone
      for (const key in settings) {
        console.log(`${key}: ${settings[key]}`);

        if (typeof settings[key] == "string") {
          settings[key] = settings[key].replace(
            /(.*)([0-9])/,
            (match, prefix, nr) => {
              const newNumber = parseInt(nr) + 1;
              return prefix + newNumber;
            },
          );
        }
      }

      tinymce.init(settings);
    }else{
      tinymce.execCommand("mceRemoveEditor", false, el.id);
      tinymce.execCommand("mceAddEditor", false, el.id);
    }
  });

  //target.remove();
}

//we are online again
window.addEventListener("online", function () {
  document.querySelectorAll(".form-submit").forEach((btn) => {
    btn.disabled = false;
    btn.querySelectorAll(".offline").forEach((el) => el.remove());
  });
});

//prevent form submit when offline
window.addEventListener("offline", function () {
  document.querySelectorAll(".form-submit").forEach((btn) => {
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
  else if (target.matches(".remove")) {
    //Remove node clicked
    removeNode(target);
  }

  else if (target.matches('.wp-block-tsjippy-forms-formbuilder .button.form-submit')) {
    event.stopPropagation();

    saveFormInput(target);
  }
});

document.addEventListener("change", (ev) => {
  // select all elements with a datalist attached
  if (ev.target.matches("input[list]") && ev.target.name.includes("[")) {
    ev.stopImmediatePropagation();

    let el = ev.target.list.querySelector(`[value="${ev.target.value}" i]`);

    if (el != null) {
      // find the dataset value of the given element value
      let value = el.dataset.value;

      if (value != undefined) {
        // change the value to create extra inputs if necessary
        changeFieldValue(ev.target, value, ev.target.closest("form"));
      }
    }
  }
});
