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
    FormSubmit.prepareForValidation(x[currentTab]);

    // Report validity of each required field
    let elements = x[currentTab].querySelectorAll(
      "input[required], textarea[required], select[required]",
    );
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
      form
        .querySelectorAll(".loader-wrapper:not(.hidden), .loader-image-trigger")
        .forEach((loader) => loader.remove());

      //show form controls
      form
        .querySelectorAll(".multi-step-controls.hidden")
        .forEach((el) => el.classList.remove("hidden"));
    }

    //hide all formsteps
    form
      .querySelectorAll(".wp-block-tsjippy-forms-formstep:not(.step-hidden)")
      .forEach((step) => step.classList.add("step-hidden"));

    // Show the specified formstep of the form ...
    let x = form.getElementsByClassName("wp-block-tsjippy-forms-formstep");

    if (x.length == 0) {
      return;
    }

    //scroll back to top
    let y = x[n].offsetTop - document.querySelector("#masthead").offsetHeight;
    window.scrollTo({ top: y, behavior: "auto" });

    //show
    x[n].classList.remove("step-hidden");

    // This function removes the "active" class of all steps...
    form.querySelectorAll(".step.active").forEach((el) => {
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
  let stepIndicators = form.querySelectorAll(
    ".multi-step-controls-wrapper .step",
  );

  // show all step circles
  stepIndicators.forEach((el) => el.classList.remove("hidden"));

  // hide some step circles if needed
  for (let x = visibleFormsteps.length; x < formsteps.length; x++) {
    stepIndicators[x].classList.add("hidden");
  }

  // Add some step circles if needed
  for (let x = stepIndicators.length; x < formsteps.length; x++) {
    let step = document.createElement("span");
    step.classList.add("step");

    form
      .querySelectorAll(`.step-wrapper`)
      .forEach((el) => el.appendChild(step));
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

  const mutationObserver = new MutationObserver((mutationList) => {
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

  mutationObserver.observe(formstep, { attributes: true });

  return mutationObserver;
}

// Show the first tab
console.log("Formstep js loaded");

// Display the first tab
document.querySelectorAll(`form[data-formname]`).forEach((form) => {
  showFormStep(0, form);
});

// Add visibility listener
document
  .querySelectorAll(`.wp-block-tsjippy-forms-formstep`)
  .forEach((formstep) => {
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

  if (
    form != null &&
    form.querySelector(".multi-step-controls-wrapper") != null
  ) {
    updateMultiStepControls(form);
  }

  let text = newNode
    .querySelector(".add.button")
    .textContent.replace("Add ", "");

  Main.displayMessage(
    `Succesfully added an extra ${text}<br>Its added as the next page.`,
  );
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
  let nextFormstep = parentNode.querySelector(
    `.wp-block-tsjippy-forms-formstep[data-div-id='${parseInt(node.dataset.divId) + 1}']`,
  );

  if (nextFormstep != null) {
    newFormstep = nextFormstep;
  } else {
    //try the previous one
    let prevFormstep = parentNode.querySelector(
      `.wp-block-tsjippy-forms-formstep[data-div-id='${parseInt(node.dataset.divId) - 1}']`,
    );

    if (prevFormstep != null) {
      newFormstep = prevFormstep;
    }
  }

  if (newFormstep != null) {
    //check if we need to update the multi step controls
    let form = node.closest("form");
    if (
      form != null &&
      form.querySelector(".multi-step-controls-wrapper") != null
    ) {
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

document.addEventListener("click", (ev) => {
  let target = ev.target;

  if (target.matches(`.button.next-button`)) {
    nextPrev(1, target);
  } else if (target.matches(`.button.previous-button`)) {
    nextPrev(-1, target);
  }
});
