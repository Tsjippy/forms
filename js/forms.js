import {
  copyFormInput,
  fixNumbering,
  removeNode,
  changeFieldValue,
} from "@tsjippy/form_exports";

import{
  submitForm,
  formReset
} from "@tsjippy/form_submit_functions";

import { 
  displayMessage 
} from "@tsjippy/display_message";

import {
  attachAll
} from "@tsjippy/multi_input";

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

  let response = await submitForm(target, "forms/save_form_input");

  if (response) {
    displayMessage(response);

    if (form.dataset.reset) {
      formReset(form);
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

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Scroll to input
   */
  if (window.location.hash) {
    var hash = window.location.hash.replace("#", "");

    var hashElement = document.querySelector(`[name^="${hash}"]`);

    if (hashElement != null) {
      hashElement.classList.add("highlight");
      hashElement.focus();
      hashElement.scrollIntoView({ block: "center" });
    }
  }

  /**
   * Unrequire all required inputs if it is a meta form
   */
  document.querySelectorAll(`form[data-meta]`).forEach((form) => {
    form.querySelectorAll(`:required`).forEach((input) => {
      input.required  = false;
    });
  });

  /**
   * Add multi htmls
   */
  attachAll();
});

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
    ev.stopPropagation();

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

document.addEventListener("DOMContentLoaded", () => {
  console.log('Custom js block');

  document.querySelectorAll('.wp-block-tsjippy-forms-formbuilder .button.next-button').forEach( button => {
    button.addEventListener("click", ev => {
      /**
       * Check how many steps to go
       */
      const wrapper = ev.target.closest(`.multi-step-controls-wrapper`);
      if(wrapper.querySelectorAll(`.step:not(.hidden`).length - wrapper.querySelectorAll(`.step.finish:not(.hidden`).length > 2){
        return;
      }

      const form      = ev.target.closest('form');
      const formData  = new FormData(form);

      let toBeUpdated     = {};
      let data            = {};
      let maxOutwardIndex = 1;
      let maxReturnIndex  = 1;
      let prevIndex       = 0;

      for (const [key, value] of formData.entries()) {
        if(key.includes('travel[') && value){
            const match = key.match(/\[(\d+)\]\[([a-z]*)\]/);

            if (match) {
              const index = parseInt(match[1], 10);

              // outwardJourney
              if(index < 10){

                // Set the destination of the previous leg to the starting point of this leg
                if(prevIndex > 0 && match[2] == 'from'){
                  toBeUpdated[`travel[${prevIndex}][to]`]  = value;
                  data[prevIndex]['to'] = value;
                }
                maxOutwardIndex       = index;
              }else{

                // Set the destination of the previous leg to the starting point of this leg
                if(prevIndex > 10 && match[2] == 'from'){
                  toBeUpdated[`travel[${prevIndex}][to]`]  = value;
                  data[prevIndex]['to'] = value;
                }

                maxReturnIndex        = index;
              }

              // Store for use in the overview table
              if(data[index] == undefined){
                data[index] = {};
              }
              data[index][match[2]] = value;

              prevIndex = index;
            }
        }
      }

      toBeUpdated[`travel[${maxOutwardIndex}][to]`] = Object.fromEntries(formData)['final_destination_1'];
      data[maxOutwardIndex]['to']                   = Object.fromEntries(formData)['final_destination_1'];
      toBeUpdated[`travel[${maxReturnIndex}][to]`]  = Object.fromEntries(formData)['final_destination_2'];

      if(data[maxReturnIndex] != undefined){
        data[maxReturnIndex]['to']                    = Object.fromEntries(formData)['final_destination_2'];
      }

      /**
       * Store the updated values
       */
      Object.entries(toBeUpdated).forEach(([key, value]) => {
        form.querySelector(`[name="${key}"]`).value = value;
      });

      /**
       * Create an overview
       */
      const table     = document.createElement("table");
      table.classList.add('tsjippy-table', 'table', 'no-border');

      // Create table header
      const thead     = document.createElement("thead");
      thead.style.textAlign = "left";
      const headerRow = document.createElement("tr");

      ["Date", "From", "To"].forEach(text => {
        const th = document.createElement("th");
        th.textContent = text;
        headerRow.appendChild(th);
      });

      thead.appendChild(headerRow);
      table.appendChild(thead);

      // add tbody
      const tbody = document.createElement("tbody");

      Object.values(data).forEach((rowData) => {
        const row = document.createElement("tr");

        ["date", "from", "to"].forEach(key => {
          const td  = document.createElement("td");

          let text  = rowData[key];

          /**
           * Convert date to browser formated string
           */
          if(key == 'date'){
            let date = new Date(rowData[key]);
            
            text  = date.toLocaleDateString(undefined, {
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            });
          }

          td.textContent = text;
          row.appendChild(td);
        });

        tbody.appendChild(row);
      });

      table.appendChild(thead);

      table.appendChild(tbody);

      const container = document.createElement("div");
      container.classList.add('travel-overview-wrapper');

      const paragraph = document.createElement("h4");
      paragraph.textContent = "Summary of your travel request";

      container.appendChild(paragraph);

      container.appendChild(table);

      let tabs    = form.querySelectorAll('.wp-block-tsjippy-forms-formstep');
      let lastTab = tabs[tabs.length - 1];

      // Remove prev container first
      lastTab.querySelectorAll(`.travel-overview-wrapper`).forEach(el => el.remove());

      const h3 = lastTab.querySelector('h3');

      h3.parentNode.insertBefore(container, h3.nextSibling);
    });
  });
});