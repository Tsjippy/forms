console.log("Formsubmit loaded");

import {
  markComplete
} from "@tsjippy/form_submit_functions";

document
  .querySelectorAll("form.tsjippy-form-wrapper")
  .forEach((form) => form.addEventListener("submit", markComplete));

// check for unsaved formdata
window.addEventListener("beforeunload", (event) => {
  if (
    document.querySelector(".loader-wrapper:not(.hidden)") != null ||
    document.activeElement.type == "submit"
  ) {
    return;
  }

  // check all pending
  document.querySelectorAll("[data-pending]").forEach((el) => {
    console.log(el);
    console.log(`${el.defaultValue} - ${el.value}`);
    event.preventDefault();
  });

  // check all inputs
  document
    .querySelectorAll(
      "form.tsjippy-form-wrapper input:not([type=radio], [type=checkbox]), form textarea",
    )
    .forEach((el) => {
      if (el.defaultValue != el.value) {
        console.log(el);
        console.log(`${el.defaultValue} - ${el.value}`);
        event.preventDefault();
      }
    });

  // check all checkboxes and radio
  document
    .querySelectorAll(
      "form.tsjippy-form-wrapper input[type=radio], form input[type=checkbox]",
    )
    .forEach((el) => {
      if (el.defaultChecked != el.checked) {
        console.log(el);
        console.log(`${el.defaultChecked} - ${el.checked}`);
        event.preventDefault();
      }
    });

  // check all dropdowns
  document
    .querySelectorAll("form.tsjippy-form-wrapper select")
    .forEach((el) => {
      if (
        el.selectedIndex != 0 &&
        el.options[el.selectedIndex] != undefined &&
        !el.options[el.selectedIndex].defaultSelected
      ) {
        console.log(el);
        console.log(el.options[el.selectedIndex].defaultSelected);
        event.preventDefault();
      }
    });
});

document.addEventListener("focusout", (ev) => {
  if (ev.target.matches(`:invalid:not(select)`)) {
    ev.target.reportValidity();
  }
});

document.addEventListener("input", (ev) => {
  if (ev.target.matches(`:invalid`)) {
    //ev.target.reportValidity();
  }
});
