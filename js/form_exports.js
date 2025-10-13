import { getFieldValue } from  '../../../plugins/sim-plugin/includes/js/field_value.js';

export function removeDefaultSelect(el){
	Array.from(el.options).forEach(function(option){
		option.defaultSelected = false;
	});
}

let tinymceSettings = [];
function prepareForCloning(originalNode){
	//also remove any tinymce's
	if(typeof(tinymce) != 'undefined'){
		originalNode.querySelectorAll('.wp-editor-area').forEach(el =>{
			let tn = tinymce.get(el.id);
			if(tn != null){
				tinymceSettings[el.id] = tn.settings
				tn.save();
				tn.remove();
			}
		});
	}
}

export function cloneNode(originalNode, clear=true){
	prepareForCloning(originalNode);
	
	//make a clone
	let newNode = originalNode.cloneNode(true);

	// remove niceselect drop down from clone
	newNode.querySelectorAll('.nice-select').forEach(dropdown => dropdown.remove());
	
	//add tinymce's again
	originalNode.querySelectorAll('.wp-editor-area').forEach(el =>{
		if(tinymceSettings[el.id] != undefined){
			tinymce.init(tinymceSettings[el.id]);
		}
	});
	
	//clear values in the clone
	if(clear){
		newNode.querySelectorAll('input,select,textarea').forEach(input => {
			if(input.type == 'checkbox' || input.type == 'radio'){
				input.checked = false;
			}else if(!input.matches('.no-reset') ){
				input.value = "";
			}
			
			//if this is a select
			if(input.type == "select-one"){
				//remove any defaults
				removeDefaultSelect(input);
			}
		});
	}

	newNode.querySelectorAll('select').forEach(select => {
		//remove any defaults
		removeDefaultSelect(select);

		Main.attachNiceSelect(select);
	});
	
	return newNode;
}

export function copyFormInput(originalNode){
	let newNode = cloneNode(originalNode);
	
	//update the data index
	newNode.querySelectorAll('.upload-files').forEach(function(uploadButton){
		uploadButton.dataset.index = nodeNr;
	})
	
	//Clear contents of any document preview divs.
	newNode.querySelectorAll('.documentpreview').forEach(function(previewDiv){
		previewDiv.innerHTML = '';
	});

	//Select
	let i = 0;
	newNode.querySelectorAll('select').forEach(select => {
		//Find the value of the select we have cloned
		let previousVal = originalNode.getElementsByTagName('select')[i].selectedIndex;
		
		//Hide the value in the clone
		if(select.options[previousVal] != undefined){
			select.options[previousVal].style.display = 'none';
		}
		
		//Add nice select
		Main.attachNiceSelect(select);
		
		i++;
	});
	
	//Add remove buttons if they are not there
	if(originalNode.querySelector('.remove') == null && newNode.querySelector('.remove') == null){
 		let html = `<button type="button" class="remove button" style="flex: 1;">-</button>`;

		//Add minus button to the first div
		originalNode.querySelector('.button-wrapper').insertAdjacentHTML('beforeend', html);

		//Add minus button to the second div
		newNode.querySelector('.button-wrapper').insertAdjacentHTML('beforeend', html)
	}	

	// process tab buttons
	if(originalNode.matches('.tabcontent')){
		// Hide original
		originalNode.classList.add('hidden');

		// Add button for the new one
		let orgButton	= originalNode.closest('.clone-divs-wrapper').querySelector(`.tablink.active`);
		let newButton	= cloneNode(orgButton);

		// make the org butto inactive
		orgButton.classList.remove('active');

		//Insert the clone
		orgButton.parentNode.insertBefore(newButton, orgButton.nextSibling);
	}
	
	//Insert the clone
	originalNode.parentNode.insertBefore(newNode, originalNode.nextSibling);
	
	return newNode;
}

export function fixNumbering(wrapper){
	wrapper.querySelectorAll(':scope > .clone-div').forEach(updateNumbers);

	wrapper.querySelectorAll(':scope > .tablink').forEach(updateNumbers);

	function updateNumbers(clone, index){
		//Update the new number	

		// Update the ID
		if(clone.id != ''){
			clone.id = clone.id.replace(/[0-9]+(?!.*[0-9])/, index);
		}
		
		// Update the content
		if(clone.type == 'button' && clone.textContent != ''){
			clone.textContent 		= clone.textContent.replace(/[0-9]+(?!.*[0-9])/, index + 1);

			clone.dataset.target	= clone.dataset.target.replace(/[0-9]+(?!.*[0-9])/, index);
		}

		// Update the divid attribute
		if(clone.dataset.divid  != null){
			clone.dataset.divid = index;
		}
		
		//Update the title
		clone.querySelectorAll('h3, h4, legend :first-child').forEach(el => {
			el.textContent = el.textContent.replace(/[0-9]+(?!.*[0-9])/, index + 1);
		});
		
		//Update the legend
		/* clone.querySelectorAll('legend').forEach(legend => {
			legend.textContent = legend.textContent.replace(/[0-9]+(?!.*[0-9])/, ' '+(index+1));
		}); */
		
		//Update the elements
		clone.querySelectorAll('input,select,textarea').forEach(input => {
			//Do not copy nice selects
			if(!input.classList.contains('nice-select-search')){
				//Update the id
				if(input.id != '' && input.id != undefined){
					input.id = input.id.replace(/[0-9]+(?!.*[0-9])/, index);
				}
				//Update the name
				if(input.name != '' && input.name != undefined){
					input.name = input.name.replace(/[0-9]+(?!.*[0-9])/, index);
				}
				
				//Reset the select to the default
				if(input.type == 'button'){
					input.value = input.value.replace(/[0-9]+(?!.*[0-9])/, index);
				}
			}
		});
	}
}

export function removeNode(target){
	let node			= target.closest(".clone-div");
	let parentNode		= node.closest('.clone-divs-wrapper');
	let allCloneDivs	= parentNode.querySelectorAll('.clone-div');
	
	//Check if we are removing the last element
	if(allCloneDivs[allCloneDivs.length-1] == node){
		let addElement = node.querySelector(".add");
		
		//Move the add button one up
		let prev = node.previousElementSibling;
		if(prev.querySelector('.button-wrapper .add') == null){
			prev.querySelector('.button-wrapper').appendChild(addElement);
		}
	}

	// check if we need to remove a corresponding tab button
	if(node.matches('.tabcontent')){
		let buttonToRemove	= parentNode.querySelector(`.tablink[data-target="${node.id}"]`);
		if(buttonToRemove != null){
			//if the button is active, make the previous one active
			if(buttonToRemove.classList.contains('active')){
				let prevButton	= buttonToRemove.previousElementSibling;
				if(prevButton != null){
					prevButton.classList.add('active');
					
					//show the corresponding tab
					Main.displayTab(prevButton);
				}else{
					//try the next one
					let nextButton	= buttonToRemove.nextElementSibling;
					if(nextButton != null){
						nextButton.classList.add('active');
						
						//show the corresponding tab
						Main.displayTab(nextButton);
					}
				}
			}
			
			//remove the button
			buttonToRemove.remove();
		}
	}

	
	//Remove the node
	node.remove();

	//If there is only one div remaining, remove the remove button
	if(parentNode.querySelectorAll('.clone-div').length == 1){
		let removeElement = parentNode.querySelector('.remove');
		removeElement.remove();
	}

	fixNumbering(parentNode)
}

/* 
	FUNCTIONS USED BY DYNAMIC FORMS JS
 */
export function tidyMultiInputs(){
	//remove unnecessary buttons on inputs with multiple values
	document.querySelectorAll('.clone-divs-wrapper').forEach( function(div){
		let cloneDivArr	= div.querySelectorAll(':scope > .clone-div');
		
		if(cloneDivArr.length == 1){
			cloneDivArr[0].querySelectorAll('.remove').forEach(el=>el.remove());
		}
		
		cloneDivArr.forEach(function(cloneDiv, index, array){
			//update dataset
			cloneDiv.dataset.divid = index;
			
			//remove add button for all but the last
			if(index != array.length - 1){
				// Select all add buttons but not the any nested buttons
				cloneDiv.querySelectorAll('.add:not(:scope .clone-divs-wrapper .add)').forEach(el=>el.remove());
			}
		})
	});
}

export function updateMultiStepControls(form){
	// get active formsteps amount
	let formsteps			= form.querySelectorAll('.formstep');
	let visibleFormsteps	= form.querySelectorAll('.formstep:not(.hidden)');
	let stepIndicators		= form.querySelectorAll('.multi-step-controls-wrapper .step');

	// show all step circles
	stepIndicators.forEach(el => el.classList.remove('hidden'));

	// hide some step circles if needed
	for(let x = visibleFormsteps.length; x < formsteps.length; x++){
		stepIndicators[x].classList.add('hidden');
	}

	// check if this is the last visible
	let activeFormstep		= form.querySelector('.formstep:not(.step-hidden)');
	if(visibleFormsteps[visibleFormsteps.length-1] == activeFormstep){
		// make the submit button visible
		form.querySelector('.nextBtn').classList.add('hidden');
		form.querySelector('.form-submit ').classList.remove('hidden');
	}else{
		form.querySelector('.nextBtn').classList.remove('hidden');
		form.querySelector('.form-submit ').classList.add('hidden');
	}
}
	
//show a next form step
export function showTab(n, form) {
	if(typeof(form) != 'undefined'){
		if(n == 0){
			let loader = form.querySelector('.loader-wrapper:not(.hidden)' );
			//hide loader
			if(loader != null){
				loader.classList.add('hidden');
			
				//show form controls
				form.querySelector('.multistepcontrols').classList.remove('hidden');
			}
		}
		
		//hide all formsteps
		form.querySelectorAll('.formstep:not(.step-hidden)').forEach(step=>step.classList.add('step-hidden'));
		
		// Show the specified formstep of the form ...
		let x = form.getElementsByClassName("formstep");
		
		if(x.length == 0){
			return;
		}
		
		//scroll back to top
		let y = x[n].offsetTop - document.querySelector("#masthead").offsetHeight
		window.scrollTo({ top: y, behavior: 'auto'});
		
		//show
		x[n].classList.remove('step-hidden');

		// This function removes the "active" class of all steps...
		form.querySelectorAll(".step.active").forEach(el=>{el.classList.remove('active');});

		//... and adds the "active" class to the current step:
		x = form.getElementsByClassName("step");
		try{
			x[n].classList.add("active");
		}catch(err) {
			console.log(x);
			console.log(n);
		  	console.error(err.message);
		}
		

		// ... and fix the Previous/Next buttons:
		if (n == 0) {
			form.querySelector('[name="prevBtn"]').classList.add('hidden');
		} else {
			form.querySelector('[name="prevBtn"]').classList.remove('hidden');
		}

		if (n == (x.length - 1)) {
			form.querySelector('[name="nextBtn"]').classList.add('hidden');
			form.querySelector('.form-submit').classList.remove('hidden');
		} else {
			form.querySelector('[name="nextBtn"]').classList.remove('hidden');
			form.querySelector('.form-submit').classList.add('hidden');
		}
	}else{
		console.log('no form defined');
	}
		
}

//next form step clicked
export function nextPrev(n, form) {
	// This function will figure out which tab to display
	let x 				= form.querySelectorAll(".formstep");
	let stepIndicators	= form.querySelectorAll(".step");
	let currentTab		= 0;
	let valid			= true;

	// Find the current active tab
	x.forEach((el, index)=>{if(!el.matches('.step-hidden')){currentTab = index}});
	
	//Check validity of this step if going forward
	if(n>0){
		// Report validity of each required field
		let elements	= x[currentTab].querySelectorAll('.required:not(.hidden) input, .required:not(.hidden) textarea, .required:not(.hidden) select');
		for(const element of elements) {
			if(
				element.closest('.hidden')	== null	&&
				element.closest('div.nice-select') == null && 
				(
					element.type != 'file' || 
					element.closest('.file-upload-wrap').querySelector('.documentpreview input') == null
				)
			){
				element.required	= true;
				valid				= element.reportValidity();
				if(!valid){
					break;
				}
			}
		}

		if(!valid) return;
		
		//mark the last step as finished
		stepIndicators[currentTab].classList.add("finish");
	}else{
		//mark the last step as unfinished
		stepIndicators[currentTab].classList.remove("finish");
	}
	
	//loop over all the formsteps to hide stepindicators of them if needed
	Array.from(x).forEach((formstep,index) =>{
		if(formstep.classList.contains('hidden')){
			//hide the corresponding circle
			stepIndicators[index].classList.add('hidden');
		}
	});

	// Increase or decrease the current tab by 1:
	currentTab = currentTab + n;
	
	//check if the next tab is hidden
	while(x[currentTab].classList.contains('hidden')){
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
	showTab(currentTab,form);

	return true;
}

export function changeFieldValue(selector, value, functionRef, form, addition='', forceValue=false){
	if(value == undefined){
		return;
	}

	let name		= '';
	let target		= '';

	if(selector instanceof Element){
		target		= selector;
		name		= target.name;
		if(target.id == ''){
			selector	= `[name^="${target.name}" i]`;
		}else{
			selector	= `[id^=${target.id}]`;
		}
		
	}else{
		target 		= form.querySelector(selector);

		try{
			name		= target.name;
		}catch{
			console.log(target);
		}
	}

	let oldValue	= getFieldValue(target, form, false, value);
	// nothing to change
	if(oldValue == value){
		return;
	}

	// Check if we are dealing with a multi input field
	if(target == null){
		let targets 	= form.querySelectorAll(`.clone-div [name^="${name}" i]`);
		if(targets.length === 0){
			return;
		}else if(targets.length == 1){
			target	= targets[0];
			targets	= '';
		}else{
			target	= targets[0];

			targets.forEach((el, index) =>{
				if(index == 0){
					changeFieldValue(el, '', '', form);
				}else{
					removeNode(el);
				}
			});
		}
	}

	// calculate the new value
	if(addition != ''){
		// check if a date
		if (/\d{4}-\d{2}-\d{2}/.test(value)){
			let date	= new Date(value);
			date.setDate(date.getDate() + parseInt(addition));
			value	= date.toISOString().split('T')[0];
		}else{
			value	= value + parseInt(addition);
		}
	}
	
	if(target.type == 'radio' || target.type == 'checkbox'){
		// uncheck all
		if(value == ''){
			if(selector != ''){
				form.querySelectorAll(selector).forEach(el=>el.checked=false);
			}
		}else{
			// Check if the current target is the one we need to check
			if(target.value.toLowerCase() == value.toLowerCase()){
				target.checked = true;
			}else{
				// find the element with the given value and check it
				let targets = form.querySelectorAll(`[name="${name}" i]`);
				for (const element of targets) {
					if(element.value.toLowerCase() == value.toLowerCase()){
						element.checked = true;
					}
				}
			}
		}
	//the target has a list attached to it
	}else if(target.type == 'date'){
		target.value = value;
		
		// convert a date to the right format
		if (!/\d{4}-\d{2}-\d{2}/.test(value)){
			let splitted = '';
			if(value.split('-').length == 3){
				splitted	= value.split('-');
			}else if(value.split('/').length == 3){
				splitted	= value.split('/');
			}

			if(splitted != ''){
				let year;
				let month;
				let day;
				splitted.forEach(nr=>{
					if(nr.length == 4){
						year	= nr;
					}else if(nr.length == 2){
						if(nr > 12){
							day	= nr;
						}else{
							// does not have a value yet
							if(month == undefined){
								month	= nr;
							}else{
								day	= nr;
							}
						}
					}
				});

				if(day != undefined && month != undefined && year != undefined){
					target.value = `${year}-${month}-${day}`;
				}
			}
		}
	}else if(target.list != null){
		let dataListOption = target.list.querySelector(`[data-value="${value}" i]`);

		//we found a match
		if(dataListOption != null){
			// We found a cloned field, add as many inputs as needed
			if(target.closest('.clone-div') != null){
				// mark the existing ones for deletion, we can delete right now as we need to copy the existing ones first
				target.closest('.clone-divs-wrapper').querySelectorAll('.clone-div').forEach(el=>el.classList.add('shouldremove'));

				let clone;
				dataListOption.value.split(';').forEach(val=>{
					clone = copyFormInput(target.closest('.clone-div'));

					clone.classList.remove('shouldremove');

					changeFieldValue(clone.querySelector(target.tagName), val, '', form, '', true);
				});

				fixNumbering(target.closest('.clone-divs-wrapper'));

				// delete the old ones
				target.closest('.clone-divs-wrapper').querySelectorAll('.shouldremove').forEach(el=>el.remove());
			}else{
				target.value = dataListOption.value;
			}
		// We did not find a match, we are filling in the given value
		}else if(forceValue){
			target.value = value;
		// We did not find a match, empty value
		}else{
			target.value = '';
		}
	}else{
		target.value = value;
	}
	
	//create a new event
	let evt = new Event('input');
	//attach the target
	target.dispatchEvent(evt);
	
	//run the originating function with this event
	if(typeof functionRef == 'function'){
		functionRef(target);
	}
}

export function changeVisibility(action, el, functionRef){
	let wrapper	= el.closest('.input-wrapper');
	if(wrapper == null){
		wrapper	= el
	}
	
	if(action == 'add'){
		if(wrapper.matches('.hidden')){
			return;
		}
		wrapper.classList.add('hidden');
	}else{
		if(!wrapper.matches('.hidden')){
			return;
		}
		wrapper.classList.remove('hidden');
	}

	//create a new event
	let evt = new Event('input');
	//attach the target
	wrapper.dispatchEvent(evt);
	
	//run the originating function with this event
	if(typeof functionRef == 'function'){
		functionRef(el);
	}
}

export function changeFieldProperty(selector, att, value, functionRef, form, addition=''){
	//first change the value
	let target = form.querySelector(selector);

	// calculate the new value
	if(addition != ''){
		// check if a date
		if (/\d{4}-\d{2}-\d{2}/.test(value)){
			let date	= new Date(value);
			date.setDate(date.getDate() + parseInt(addition));
			value	= date.toISOString().split('T')[0];
		}else{
			value	= value + parseInt(addition);
		}
	}

	target[att] = value;
	
	//create a new event
	let evt = new Event('input');

	//attach the target
	target.dispatchEvent(evt);

	//run the originating function with this event
	functionRef(target);
}