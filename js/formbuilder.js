var reorderingBusy, formWrapper, formElementWrapper, modal;

console.log("Formbuilder.js loaded");

/* FUNCTIONS */

/**
 * Sets the min and max values of the window date inputs and the max of the reminder amount input
 * @param {*} target 
 * @returns 
 */
function updateReminderMinMax(target){
	let form 				= target.closest('form');
	let frequency 			= form.querySelector(`[name="frequency"]`);
	let periodType 			= form.querySelector(`#period:checked`);
	let windowStart 		= form.querySelector(`[name="window-start"]`);
	let windowEnd 			= form.querySelector(`[name="window-end"]`);
	let reminderStart		= form.querySelector(`[name="reminder-startdate"]`);
	let reminderAmount		= form.querySelector(`[name="reminder-amount"]`);

	// these two should have a value
	if(frequency == null || frequency.value == '' || periodType == null){
		return;
	}
	frequency				= +frequency.value;
	periodType				= periodType.value;
	let min 				= new Date();
	let max 				= new Date();
	let maxAmount;
	
	if(periodType == 'years'){
		let curYear = min.getFullYear();
		min.setFullYear(curYear - frequency);
		max.setFullYear(curYear + frequency);

		maxAmount	= 52 * frequency - 2;
	}else if(periodType == 'months'){
		let curMonth = min.getMonth();
		min.setMonth(curMonth - frequency);
		max.setMonth(curMonth + frequency);
		maxAmount	= 4 * frequency - 1;
	}else if(periodType == 'days'){
		let curDay = min.getDay();
		min.setDate(curDay - frequency);
		max.setDate(curDay + frequency);

		maxAmount	= 1 * frequency - 1;
	}

	min	= min.toLocaleDateString('en-CA');
	max	= max.toLocaleDateString('en-CA');
	
	windowStart.min = min;
	windowStart.max = max;
	windowEnd.min = min;
	windowEnd.max = max;

	// Max value for the reminder amount
	reminderAmount.max	= maxAmount;

	if( windowStart.value != '' ){
		reminderStart.min	= windowStart.value;
	}

	if(
		reminderAmount.value != '' && 
		form.querySelector(`#reminder-period:checked`) != null && 
		windowStart.value != '' &&
		windowEnd.value != ''
	){
		let reminderPeriod		= (parseInt(reminderAmount.value) + 1);
		let reminderPeriodType	= form.querySelector(`#reminder-period:checked`).value;
		if(reminderPeriodType == 'week'){
			reminderPeriod		= reminderPeriod * 7;
		}

		// Max value for the reminder start date
		max 				= windowEnd.valueAsDate;
		let curDay 			= max.getDay();
		max.setDate(curDay - reminderPeriod);

		reminderStart.max	= max;
	}
}
	
function clearFormInputs(){
	try {
		//Loop to clear the modalform
		modal.querySelectorAll('input:not([type=hidden]), select, textarea, [name=insert-after], [name=element-id]').forEach(function(el){
			if(el.type == 'checkbox'){
				el.checked = false;
			}else{
				el.value = '';
			}

			if(el.type == 'textarea' && el.id != ''){
				let editor = tinyMCE.get(el.id);
				if(editor != null){
					editor.setContent('');
				}
			}
			
			if(el.type == "select-one"){
				FormFunctions.removeDefaultSelect(el);
				
				if(el._niceSelect != undefined){
					el._niceSelect.clear();
					el._niceSelect.update();
				}
			}
		});
		
		hideConditionalfields(modal.querySelector('form'));
	}
	catch(err) {
		console.error(err);
	}
}

function fixElementNumbering(form){
	form.querySelectorAll('.form-element-wrapper').forEach((el, index)=>{
		el.dataset.priority = index+1;
	});
}

//edit existing or add new element
async function requestEditElementData(target, requestNew=false){
	target.classList.add('clicked');

	let elementId		= -1;
	if(!requestNew){
		elementId	= formElementWrapper.dataset.elementId;
	}

	let formId					= target.dataset.formId;
	if(formId == undefined){
		if(target.closest('.form-element-wrapper') != null){
			formId = target.closest('.form-element-wrapper').dataset.formId;
		}else{
			formId = document.querySelector('input[type=hidden][name="form-id"').value;
		}
	}

	let editButton		= target.outerHTML;

	let loader			= Main.showLoader(target);

	loader.querySelector('.loader').style.margin = '5px 19px 0px 19px';
	loader.classList.add('clicked');
	
	let formData = new FormData();
	formData.append('element-id', elementId);
	formData.append('form-id', formId);
	
	let response = await FormSubmit.fetchRestApi('forms/request_form_element', formData);

	if(!response){
		return;
	}

	//fill the form after we have clicked the edit button
	document.getElementById(`element-builder`).innerHTML = response.elementForm;

	//activate tiny mce's
	modal.querySelectorAll('.wp-editor-area').forEach(el =>{
		tinymce.execCommand( 'mceRemoveEditor', false, el.id );
		tinymce.execCommand( 'mceAddEditor', false, el.id );
	});

	// Add nice selects
	modal.querySelectorAll('.condition-select').forEach(function(select){
		Main.attachNiceSelect(select);
	});

	showCondionalFields(modal.querySelector('[name="formfield[type]"]').value, modal.querySelector('[name="add-form-element-form"]'));

	//fill the element conditions tab
	modal.querySelector('.element-conditions-wrapper').innerHTML = response.conditionsHtml;

	// Add nice selects
	modal.querySelectorAll('select').forEach(function(select){
		Main.attachNiceSelect(select);
	});
	
	Main.showModal(modal);

	// Scroll to top of the modal
	modal.querySelector(`[name='formfield[type]']`).scrollIntoView({block: "center"});
	
	//show edit button again
	loader.outerHTML	= editButton;
}

// Add a new element
async function addFormElement(target, copying=false){
	let adding		= true;
	let editing		= false;

	let form		= target.closest('form');
	if(!copying){
		if(form.querySelector('[name="element-id"]').value != ''){
			editing		= true;
			adding		= false;
		}
	}
	
	let wrapper;

	let referenceNode	= document.querySelector('.form-elements .clicked');
	
	// we are adding the first element to the form 
	if(referenceNode == null){
		// reload if we are not in the editing screen yet
		if( !window.location.href.includes('formbuilder=true')){
			let combine	= '&';
			if( !window.location.href.includes('?')){
				combine = '?';
			}
			window.location.href = window.location.href+combine+'formbuilder=true';
			
			return;
		}

		wrapper				= document.querySelector('.form-elements');

		priority			= 1;
	}else{
		wrapper				= referenceNode.closest('.form-element-wrapper');

		priority			= parseInt(wrapper.dataset.priority) + 1;
	}

	// Show loader
	if(!editing){
		let loader 					= Main.showLoader(wrapper, false);
		loader.dataset.priority		= priority;
		loader.classList.add('form-element-wrapper');
		loader.dataset.elementId	= -1;

		// make sure all priorities are correct
		fixElementNumbering(referenceNode.closest('form'));

		var indexes	= {};
		document.querySelectorAll(`.form-element-wrapper`).forEach(el => {
			indexes[el.dataset.elementId]	= el.dataset.priority;
		})
		
		indexes						= JSON.stringify(indexes);
	}

	let response;
	if(copying){
		let formData			= new FormData();
		formData.append('element-id', wrapper.dataset.elementId);
		formData.append('form-id', wrapper.dataset.formId);
		formData.append('order', indexes);
		formData.append('insert-after', formElementWrapper.dataset.priority);

		response	= await FormSubmit.fetchRestApi('forms/copy_form_element', formData);
	}else if(editing){
		response	= await FormSubmit.submitForm(target, 'forms/add_form_element');
	}else{
		response	= await FormSubmit.submitForm(target, 'forms/add_form_element', indexes);
	}

	if(response){
		// New Element
		if(editing){
			referenceNode.closest('.form-element-wrapper').outerHTML = response.html;
		}else{
			//First clear any previous input
			clearFormInputs();
			
			// Replace loader with element
			document.querySelectorAll(`.loader-wrapper`).forEach(el=>el.outerHTML = response.html);

			//add resize listener
			form.querySelectorAll('.resizer').forEach(el=>{resizeOb.observe(el);});
		}

		Main.hideModals();

		Main.displayMessage(response.message);

		if(referenceNode != null){
			referenceNode.classList.remove('clicked');
		}
	}

	// Remove loaders
	document.querySelectorAll(`.loader-wrapper`).forEach(el=>el.remove());
}

async function sendElementSize(el, widthPercentage){
	if(widthPercentage != el.dataset.widthPercentage && Math.abs(widthPercentage - el.dataset.widthPercentage)>5){
		el.dataset.widthPercentage = widthPercentage;
		
		//send new width over AJAX
		let formData = new FormData();
		formData.append('form-id', el.closest('.form-element-wrapper').dataset.formId);
		formData.append('elementid', el.closest('.form-element-wrapper').dataset.elementId);
		formData.append('new-width', widthPercentage);
		
		let response = await FormSubmit.fetchRestApi('forms/edit_formfield_width', formData);

		if(response){
			Main.hideModals();

			Main.displayMessage(response);
		}
	}
}

async function removeElement(target){
	let parent			= target.parentNode;
	let elementWrapper	= target.closest('.form-element-wrapper');
	let formId			= elementWrapper.dataset.formId;
	let elementIndex 	= elementWrapper.dataset.elementId;
	let form			= target.closest('form');

	Main.showLoader(target);
	let loader			= parent.querySelector('.loader-wrapper');
	loader.style.paddingRight = '10px';
	loader.classList.remove('loader-wrapper');

	let formData = new FormData();
	formData.append('form-id', formId);

	formData.append('elementindex', elementIndex);
	
	let response = await FormSubmit.fetchRestApi('forms/remove_element', formData);

	if(response){
		//remove the formelement row
		elementWrapper.remove();
		
		fixElementNumbering(form);

		Main.displayMessage(response);
	}
}

 //Fires after element reorder
async function reorderformelements(event){
	if(!reorderingBusy){
		reorderingBusy = true;

		fixElementNumbering(event.item.closest('form'));

		let formData = new FormData();
		formData.append('form-id', event.item.dataset.formId);
		formData.append('el-id', event.item.dataset.elementId);

		let indexes	= {};
		document.querySelectorAll(`.form-element-wrapper`).forEach(el => {
			indexes[el.dataset.elementId]	= el.dataset.priority;
		})
		
		formData.append('indexes', JSON.stringify(indexes));
		
		let response	= await FormSubmit.fetchRestApi('forms/reorder-form-elements', formData);

		if(response){
			reorderingBusy = false;

			Main.displayMessage(response);
		}
	}else{
		let options = {
			icon: 'error',
			title: 'ordering already in progress, please wait',
			confirmButtonColor: "#bd2919",
		};

		if(document.fullscreenElement != null){
			options['target']	= document.fullscreenElement;
		}

		Swal.fire(options);
	}
}

async function saveFormConditions(target){
	let response	= await FormSubmit.submitForm(target, 'forms/save_element-conditions');

	if(response){
		Main.hideModals();

		Main.displayMessage(response);
	}
}

async function saveFormSettings(target){
	let response	= await FormSubmit.submitForm(target, 'forms/save_form_settings');

	if(response){
		Main.displayMessage(response);
	}
}

async function saveFormReminder(target){
	let response	= await FormSubmit.submitForm(target, 'forms/save_form_reminder');

	if(response){
		Main.displayMessage(response);
	}
}

async function saveFormEmails(target){
	let response	= await FormSubmit.submitForm(target, 'forms/save_form_emails');

	if(response){
		Main.displayMessage(response);
	}
}

//listen to element size changes
var doit;
const resizeOb = new ResizeObserver(function(entries) {
	let element = entries[0].target;
	if(element.parentNode != undefined){
		var width	= entries[0].contentRect.width
		var widthPercentage = Math.round(width/element.parentNode.offsetWidth * 100);
		
		//Show percentage on screen
		let el	= element.querySelector('.width-percentage');
		if(widthPercentage < 99){
			el.textContent = widthPercentage + '%';
		}else if(el != null){
			el.textContent = '';
		}
		
		clearTimeout(doit);
		doit = setTimeout(sendElementSize, 500, element, widthPercentage);
	}
});

//show conditional fields based on on the element type
function showCondionalFields(type, form){
	hideConditionalfields(form);

	// hide all elements who do not belong to this type
	form.querySelectorAll(`.element-option:not(.${type}, .reverse), .element-option.not-${type}`).forEach(el=>el.classList.replace('shouldhide', 'hidden'));
	form.querySelectorAll(`.element-option.${type}`).forEach(el=>el.classList.replace('hidden', 'shouldhide'));

	// Check if this is a multi answer element
	if(document.querySelector(`.clicked`) != null && document.querySelector(`.clicked`).closest(`.form-element-wrapper.multi-answer-element`) != null){
		form.querySelectorAll(`.element-option.multi-answer-element`).forEach(el=>el.classList.replace('hidden', 'shouldhide'));
	}

	switch(type) {
		case 'button':
		case 'formstep':
		case 'label':
			form.querySelector('[name="label-text"] .element-type').textContent = type;
			break;
		case 'info':
			form.querySelector('[name="infotext"] .type').textContent = 'info-box';
			break;
		case 'hcaptcha':
		case 'recaptcha':
		case 'turnstile':
			modal.querySelectorAll(".shouldhide").forEach(el=>el.classList.replace('shouldhide', 'hidden'));
			modal.querySelectorAll(`[name="elementname"]`).forEach(el=>el.classList.replace('hidden', 'shouldhide'));
			break;
		case 'radio':
		case 'select':
		case 'checkbox':
		case 'datalist':
			break;
		case 'p':
			form.querySelector('[name="infotext"] .type').textContent = 'Paragraph';
			break;
		case 'php':
			break;
		case 'file':
		case 'image':
			form.querySelectorAll('.filetype').forEach(el=>{el.textContent = type;});
			break;
		default:
			break;
	} 
}

function hideConditionalfields(form){
	form.querySelectorAll(`.element-option.reverse`).forEach(el=>el.classList.replace('hidden', 'shouldhide'));
}

function showOrHideIds(target){

	const url 		= new URL(window.location);

	if(target.dataset.action == 'show'){
		url.searchParams.set('show-id', 1);

		formWrapper.querySelectorAll('.element-id.hidden').forEach(el=>el.classList.remove('hidden'));
		
		target.textContent 		= 'Hide ids';
		target.dataset.action	= 'hide';
	}else{
		url.searchParams.delete('show-id');

		formWrapper.querySelectorAll('.element-id:not(.hidden)').forEach(el=>el.classList.add('hidden'));
		
		target.textContent 		= 'Show ids';
		target.dataset.action	= 'show';
	}
	
	window.history.pushState({}, '', url);
}

function showOrHideName(target){
	const url 		= new URL(window.location);

	if(target.dataset.action == 'show'){
		url.searchParams.set('show-name', 1);

		formWrapper.querySelectorAll('.element-name.hidden').forEach(el=>el.classList.remove('hidden'));
		
		target.textContent 		= 'Hide names';
		target.dataset.action	= 'hide';
	}else{
		url.searchParams.delete('show-id');

		formWrapper.querySelectorAll('.element-name:not(.hidden)').forEach(el=>el.classList.add('hidden'));
		
		target.textContent 		= 'Show names';
		target.dataset.action	= 'show';
	}

	window.history.pushState({}, '', url);
}

function maybeRemoveElement(target){
	if(typeof(Swal)=='undefined'){
		removeElement(target);
	}else{
		let options = {
			title: 'Are you sure?',
			text: "This will remove this element",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, delete it!'
		}

		if(document.fullscreenElement != null){
			options['target']	= document.fullscreenElement;
		}

		Swal.fire(options).then((result) => {
			if (result.isConfirmed) {
				removeElement(target);
			}
		})
	}
}

function showOrHideConditionFields(target){
	//show default conditional field
	target.closest('.rule-row').querySelector('[name*="conditional-value"]').classList.remove('hidden');
	
	//hide extra fields
	target.closest('.rule-row').querySelectorAll('.equation-2, .conditional-field-2').forEach(function(el){
		el.classList.add('hidden');
	});
	
	//show both extra fields
	if(target.dataset.value == '-' || target.dataset.value == '+'){
		target.closest('.rule-row').querySelectorAll('.equation-2, .conditional-field-2').forEach(function(el){
			el.classList.remove('hidden');
		});
	//show extra conditional field
	}else if(target.dataset.value != undefined && target.dataset.value.includes('value')){
		target.closest('.rule-row').querySelector('.conditional-field-2').classList.remove('hidden');

		//hide normal conditional value field
		target.closest('.rule-row').querySelector('[name*="conditional-value"]').classList.add('hidden');
	}else if(target.dataset.value != undefined && (
			target.dataset.value.includes('changed') || 
			target.dataset.value.includes('clicked') ||
			target.dataset.value.includes('visible') ||
			target.dataset.value.includes('invisible') ||
			target.dataset.value.includes('checked')
	)){
		target.closest('.rule-row').querySelector('[name*="conditional-value"]').classList.add('hidden');
	}
}

async function addConditionRule(target){
	let condition		= target.closest('.condition-row');
	let row				= target.closest('.rule-row');
	let activeButton	= row.querySelector('.active');
	
	if(
		(
			activeButton == null && 													// there is no active button
			condition.querySelectorAll('.rule-row').length > 1 && 						// but there are more than one rules
			row.dataset.ruleIndex != condition.querySelectorAll('.rule-row').length -1	// and this is not the last rule
		) ||
		(
			activeButton != null && 				// there is an active button
			!target.classList.contains('active')	// We clicked on the button which was not active
		)
	){	
		let current		= 'OR';
		let opposite	= 'AND';
		let makeActive	= true;
		
		if(activeButton != null){
			if(activeButton.textContent == 'AND'){
				current		= 'AND';
				opposite	= 'OR';
			}

			let options = {
				title: 'What do you want to do?',
				showDenyButton: true,
				showCancelButton: true,
				confirmButtonText: `Change ${current} to ${opposite}`,
				denyButtonText: 'Add a new rule',
			};
	
			if(document.fullscreenElement != null){
				options['target']	= document.fullscreenElement;
			}
			
			result	= await Swal.fire(options)

			//swap and/or
			if (result.isConfirmed) {
				//make other button inactive
				activeButton.classList.remove('active');
			//add new rule after this one
			}else if (result.isDenied) {
				addRuleRow(row);
				makeActive	= false;
			}
		}

		if(makeActive){
			//make the button active
			target.classList.add('active');
			
			//store action 
			row.querySelector('.combinator').value = target.textContent;
		}
			
	//add new rule at the end
	}else{
		addRuleRow(row);
		
		//make the button active
		target.classList.add('active');
		
		//store action 
		row.querySelector('.combinator').value = target.textContent;
	}
}

function addRuleRow(row){
	//Insert a new rule row
	let clone		= FormFunctions.cloneNode(row);
	
	let cloneIndex	= parseInt(row.dataset.ruleIndex) + 1;

	clone.dataset.ruleIndex = cloneIndex;

	row.parentNode.insertBefore(clone, row.nextSibling);
	
	fixRuleNumbering(row.closest('.condition-row'));
}

function addOppositeCondition(clone, target){
	//Set values to opposite
	clone.querySelectorAll('.element-condition').forEach(function(el){
		if(el.matches('select:not(.nonice,.swal2-select)')){
			Main.attachNiceSelect(select);
		}
		
		if(el.tagName == 'SELECT' && el.classList.contains('equation')){
			//remove all default selected
			FormFunctions.removeDefaultSelect(el);
			
			//get the original value which was lost during cloning
			let originalSelect 	= target.closest('.condition-row').querySelector('.equation');
			let selIndex		= originalSelect.selectedIndex;
			
			if(selIndex){
				//if odd the select the next one
				if(selIndex % 2){
					el.options[(selIndex + 1)].defaultSelected = true;
				}else{
					el.options[(selIndex - 1)].defaultSelected = true;
				}
			}
			
			//reflect changes in niceselect
			if(el._niceSelect != undefined){
				el._niceSelect.update();
			}
		}else if(el.type == 'radio' && el.classList.contains('element-condition') && (el.value == 'show' || el.value == 'hide')){
			if(!el.checked){
				el.checked = true;
			}else{
				el.checked = false;
			}
		}
	});

	return clone;
}

function addCondition(target){
	let clone;
	let row = target.closest('.condition-row');
	
	if(target.classList.contains('opposite')){
		clone = FormFunctions.cloneNode(row, false);
		clone = addOppositeCondition(clone, target);
	}else{
		clone = FormFunctions.cloneNode(row);
	}
	
	let cloneIndex	= parseInt(row.dataset.conditionIndex) + 1;

	clone.dataset.conditionIndex = cloneIndex;
	
	if(!target.classList.contains('opposite')){
		clone.querySelectorAll('.active').forEach(function(el){
			el.classList.remove('active');
		});
	}	
	
	//store radio values
	let radioValues = {};
	row.querySelectorAll('input[type="radio"]:checked').forEach(input=>{
		radioValues[input.value]	= input.value;
	});
		
	//insert in page
	row.parentNode.insertBefore(clone, row.nextSibling);
	
	if(!target.classList.contains('opposite')){
		//remove unnecessy rulerows works only after html insert
		let ruleCount = clone.querySelectorAll('.rule-row').length;
		clone.querySelectorAll('.rule-row').forEach(function(ruleRow){
			//only keep the last rule (as that one has the + button)
			if(ruleRow.dataset.ruleIndex != ruleCount - 1){
				ruleRow.remove();
			}else{
				fixRuleNumbering(ruleRow.closest('.condition-row'));
			}
		});
	}
	
	//fix numbering
	fixConditionNumbering();
	
	//fix radio's as they get unselected due to same names on insert
	row.querySelectorAll('input[type="radio"]').forEach(input=>{
		if(radioValues[input.value] != undefined){
			input.checked = true;
		}
	});
	
	
	//hide button
	target.style.display = 'none';
}

function removeConditionRule(target){
	let conditionRow = target.closest('.condition-row');
	
	//count rule rows in this condition row
	if(conditionRow.querySelectorAll('.rule-row').length > 1){
		let options = {
			title: 'What do you want to remove?',
			showDenyButton: true,
			showCancelButton: true,
			confirmButtonText: `One condition rule`,
			denyButtonText: `The whole condition`,
			confirmButtonColor: "#bd2919",
		};

		if(document.fullscreenElement != null){
			options['target']	= document.fullscreenElement;
		}

		Swal.fire(options).then((result) => {
			//remove a rule rowe
			if (result.isConfirmed) {
				//get the current row
				let ruleRow		= target.closest('.rule-row');
				
				//get the current row index
				let ruleRowIndex	= parseInt(ruleRow.dataset.ruleIndex);
				
				//Get previous row
				let prevRow		= conditionRow.querySelector('[data-rule-index="'+(ruleRowIndex - 1)+'"]');
				
				if(prevRow != null){
					//remove the active class from row above if there is no row after this one
					if(conditionRow.querySelector(`[data-rule-index="${ruleRowIndex + 1}"]`) == null){
						prevRow.querySelectorAll('.active').forEach(el=>el.classList.remove('active'));
					}
					
					//clear the hidden input
					prevRow.querySelector('.combinator').value	= '';
				}
				
				target.closest('.rule-row').remove();
				
				fixRuleNumbering(conditionRow);
			} else if (result.isDenied) {
				conditionRow.remove();
				fixConditionNumbering();
			}
		});
	}else{
		let options = {
			title: 'Are you sure?',
			text: "This will remove this condition",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: "#bd2919",
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, delete it!'
		};

		if(document.fullscreenElement != null){
			options['target']	= document.fullscreenElement;
		}

		Swal.fire(options).then((result) => {
			if (result.isConfirmed) {
				conditionRow.remove();
				fixConditionNumbering();
			}
		});
	}
}

function fixRuleNumbering(conditionRow){
	let ruleRows	= conditionRow.querySelectorAll('.element-conditions-wrapper .rule-row');
	let i = 0;
	
	//loop over all rules in the condition
	ruleRows.forEach(ruleRow =>{
		ruleRow.dataset.ruleIndex = i;
		
		ruleRow.querySelectorAll('.element-condition').forEach(function(el){
			//fix numbering
			if(el.id != undefined){
				el.id = el.id.replace(/([0-9]+\]\[rules\]\[)[0-9]+/g,"$1"+i);
			}
				
			if(el.name != undefined){
				el.name = el.name.replace(/([0-9]+\]\[rules\]\[)[0-9]+/g,"$1"+i);
			}
		});
		
		i++;
	});
}

function fixConditionNumbering(){
	let conditionRows	= modal.querySelectorAll('.element-conditions-wrapper .condition-row');
	for (let i = 0; i < conditionRows.length; i++) {
		conditionRows[i].dataset.conditionIndex = i;
		
		conditionRows[i].querySelectorAll('.element-condition').forEach(function(el){
			//fix numbering
			if(el.id != undefined){
				el.id = el.id.replace(/[0-9]+(\]\[rules\]\[[0-9]+)/g,i+"$1")
				el.id.replace(/[0-9]+(\]\[action\])/g,i+"$1");
				el.id = el.id.replace(/(element-conditions\[)[0-9]+\]\[+/g,"$1"+i+'][');
			}
			if(el.name != undefined){
				el.name = el.name.replace(/[0-9]+(\]\[rules\]\[[0-9]+)/g,i+"$1");
				el.name = el.name.replace(/[0-9]+(\]\[action\])/g,i+"$1");
				el.name = el.name.replace(/(element-conditions\[)[0-9]+\]\[+/g,"$1"+i+'][');
			}
		});
	}
}

function focusFirst(){
	modal.scrollTo(0,0);
	
	console.log('scrolling')
	modal.querySelector('[name="add-form-element-form"] .nice-select').focus();
}

reorderingBusy = false;
document.addEventListener("DOMContentLoaded",function() {

	//Make the form-elements div sortable
	let options = {
		handle: '.movecontrol',
		animation: 150,
		onEnd: reorderformelements
	};
	
	document.querySelectorAll('.form-elements').forEach(el=>{Sortable.create(el, options);});
	
	//Listen to resize events on form-element-wrappers
	document.querySelectorAll('.resizer').forEach(el=>{resizeOb.observe(el);});
});

function fromEmailClicked(target){
	let div1 = target.closest('.clone-div').querySelector('.emailfromfixed');
	let div2 = target.closest('.clone-div').querySelector('.emailfromconditional');

	if(target.value == 'fixed'){
		div1.classList.remove('hidden');
		div2.classList.add('hidden');
	}else{
		div2.classList.remove('hidden');
		div1.classList.add('hidden');
	}
}

function toEmailClicked(target){
	let div1 = target.closest('.clone-div').querySelector('.email-tofixed');
	let div2 = target.closest('.clone-div').querySelector('.email-toconditional');

	if(target.value == 'fixed'){
		div1.classList.remove('hidden');
		div2.classList.add('hidden');
	}else{
		div2.classList.remove('hidden');
		div1.classList.add('hidden');
	}
}

function placeholderSelect(target){
	let value = '';
	if(target.classList.contains('placeholders')){
		value = target.textContent;
	}else if(target.value != ''){
		value = target.value;
		target.selectedIndex = '0';
	}
	
	if(value != ''){
		let options = {
			icon: 'success',
			title: 'Copied '+value,
			showConfirmButton: false,
			timer: 1500
		};

		if(document.fullscreenElement != null){
			options['target']	= document.fullscreenElement;
		}

		Swal.fire(options);

		navigator.clipboard.writeText(value);
	}
}

function copyWarningCondition(target){
	//copy the row
	let newNode = FormFunctions.cloneNode(target.closest('.warning-conditions'));

	target.closest('.conditions-wrapper').insertAdjacentElement('beforeEnd', newNode);

	//add the active class
	target.classList.add('active');

	//store the value
	target.closest('.warning-conditions').querySelector('.combinator').value = target.value;

	fixWarningConditionNumbering(target.closest('.conditions-wrapper'));
}

function removeWarningCondition(target){
	let condition	= target.closest('.warning-conditions');

	// Remove the active class of the previous conditions
	if(condition.nextElementSibling == null){
		condition.previousElementSibling.querySelector('.active').classList.remove('active');

		//clear the value
		condition.previousElementSibling.querySelector('.combinator').value='';
	}

	// remove the condition
	condition.remove();

	fixWarningConditionNumbering(target.closest('.conditions-wrapper'));
}

//Catch click events
window.addEventListener("click", event => {
	let target = event.target;
	
	formWrapper				= target.closest('.sim-form-wrapper');
	formElementWrapper		= target.closest('.form-element-wrapper');
	
	if(formWrapper != null){
		modal = formWrapper.querySelector('.add-form-element-modal');
	}
	
	/* ELEMENT ACTIONS */
	
	//Show form edit controls
	if (target.name == 'show-id'){
		showOrHideIds(target);
	}else if (target.name == 'show-name'){
		showOrHideName(target);
	}else if(target.name == 'submit-form-element'){
		event.stopPropagation();
		addFormElement(target);
	}else if(target.name == 'submit-form-condition'){
		saveFormConditions(target);
	}else if(target.name == 'submit-form-setting'){
		saveFormSettings(target);
	}else if(target.name == 'submit-form-reminder'){
		saveFormReminder(target);
	}else if(target.name == 'submit-form-emails'){
		saveFormEmails(target);
	}else if(target.name == 'autoarchive'){
		let el = target.closest('.formsettings-wrapper').querySelector('.auto-archive-logic');
		if(target.value == 1){
			el.classList.remove('hidden');
		}else{
			el.classList.add('hidden');
		}
	}else if(target.name == 'save-in-meta'){
		target.closest('.sim-form.builder').querySelector('.recurring-submissions').classList.toggle('hidden');
	}else if(target.name == 'formfield[mandatory]' && target.checked){
		target.closest('div').querySelector('[name="formfield[recommended]"]').checked=true;
	}
	
	//request form values via AJAX
	else if (target.classList.contains('edit-form-element')){
		requestEditElementData(target);
	}

	else if (target.classList.contains('copy-form-element')){
		target.classList.add('clicked');

		addFormElement(target, true);
	}

	else if (target.classList.contains('remove-form-element')){
		maybeRemoveElement(target);
	}

	//open the modal to add an element
	else if (target.classList.contains('add-form-element') || target.name == 'createform'){
		if( !window.location.href.includes('formbuilder=true')){
			let combine	= '&';
			if( !window.location.href.includes('?')){
				combine = '?';
			}
			window.location.href = window.location.href+combine+'formbuilder=true';
			
			return;
		}
		requestEditElementData(target, true);
	}
	
	//actions on element type select
	else if (target.closest('.element-type') != null){
		showCondionalFields(target.dataset.value, target.closest('form'));
		
		//if label type is selected, wrap by default
		if(target.dataset.value == 'label'){
			target.closest('form').querySelector('[name="formfield[wrap]"]').checked		= true;
			target.closest('form').querySelector('[name="formfield[wrap]"]').defaultChecked	= true
		}
	}
	
	/* ELEMENT CONDITION ACTIONS */
	//actions on condition equation select
	else if (target.closest('.equation') != null){
		showOrHideConditionFields(target);
	}
	
	//add new conditions_rule
	else if (target.classList.contains('and-rule') || target.classList.contains('or-rule')){
		addConditionRule(target);
	}
	
	//add new condition row
	else if (target.classList.contains('add-condition')){
		addCondition(target);
	}
	
	//remove  condition row
	else if (target.classList.contains('remove-condition')){
		removeConditionRule(target);
	}
	
	//show copy fields
	else if (target.classList.contains('showcopyfields')){
		if(target.checked){
			target.closest('.copyfieldswrapper').querySelector('.copyfields').classList.remove('hidden');
		}else{
			target.closest('.copyfieldswrapper').querySelector('.copyfields').classList.add('hidden');
		}
	}
	
	else if(target.classList.contains('email-trigger')){
		let el = target.closest('.clone-div').querySelector('.conditional-field-wrapper');
		if(target.value == 'fieldchanged'){
			el.classList.remove('hidden');
		}else{
			el.classList.add('hidden');
		}

		el = target.closest('.clone-div').querySelector('.conditional-fields-wrapper');
		if(target.value == 'fieldschanged'){
			el.classList.remove('hidden');
		}else{
			el.classList.add('hidden');
		}

		el = target.closest('.clone-div').querySelector('.submitted-type');
		if(target.value == 'submittedcond'){
			el.classList.remove('hidden');
		}else{
			el.classList.add('hidden');
		}
	}
	
	else if(target.classList.contains('from-email')){
		fromEmailClicked(target);
	}
	
	else if(target.classList.contains('email-to')){
		toEmailClicked(target);
	}
	
	else if(target.classList.contains('placeholderselect') || target.classList.contains('placeholders')){
		placeholderSelect(target);
	}

	//copy warning-conditions row
	else if(target.matches('.warn-cond')){
		copyWarningCondition(target);
	}

	else if(target.matches('.remove-warn-cond')){
		removeWarningCondition(target)
	}

	else if(target.matches('.builder-permissions-rights-form')){
		target.closest('div').querySelector('.permission-wrapper').classList.toggle('hidden');
	}else{
		event.stopImmediatePropagation();
	}
});

window.addEventListener('change', ev=>{
	let target	= ev.target;
	if(target.matches('.meta-key')){
		//if this option has a keys data value
		let metaIndexes	= target.list.querySelector(`[value='${target.value}' i]`);
		if(metaIndexes != null && metaIndexes.dataset.keys != undefined){
			parent	= target.closest('.warning-conditions').querySelector('.index-wrapper');
			//show the data key selector
			parent.classList.remove('hidden');
			
			//remove all options and add new ones
			let datalist	= parent.querySelector('.meta-key-index-list');
			for(let i=1; i<datalist.options.length; i++) {
				datalist.options[i].remove();
			}

			//add the new options
			metaIndexes.dataset.keys.split(',').forEach(key=>{
				let opt			= document.createElement('option');
				opt.value 		= key;
				datalist.appendChild(opt);
			});
		}
	}else if(target.matches(".condition-row [name*='[property-value]']")){
		let selectedElement	= document.querySelector(`.form-element-wrapper[data-element-id="${target.value}"]`);
		if(selectedElement == null){
			return;
		}

		let selectedElementType	= selectedElement.dataset.type;

		// if select element is of number type
		if(selectedElementType == 'number'){
			target.closest('.condition-form').querySelectorAll('.addition.hidden').forEach(el=>el.classList.remove('hidden'));
		}else{
			target.closest('.condition-form').querySelectorAll('.addition:not(.hidden)').forEach(el=>el.classList.add('hidden'));
		}
		
		// if select element is of date type
		if(selectedElementType == 'date'){
			target.closest('.condition-form').querySelectorAll('.addition .days.hidden').forEach(el=>el.classList.remove('hidden'));
		}else{
			target.closest('.condition-form').querySelectorAll('.addition .days:not(.hidden)').forEach(el=>el.classList.add('hidden'));
		}
	}else if(target.matches("[name*='[submitted-trigger][equation]']")){
		let parent	= target.closest('.submitted-trigger-type');
		let static	= parent.querySelector('.staticvalue');
		let dynamic	= parent.querySelector('div.dynamicvalue');

		if(dynamic != null){
		
			if(target.value == '==' || target.value == '!=' || target.value == '>' || target.value == '<'){
				static.classList.remove('hidden');
				dynamic.classList.add('hidden');
			}else if(target.value == 'checked' || target.value == '!checked'){
				dynamic.classList.add('hidden');
				static.classList.add('hidden');
			}else{
				dynamic.classList.remove('hidden');
				static.classList.add('hidden');
			}
		}
	}else if(target.name != undefined && target.name.includes('[equation')){
		let warningsConditions	= target.closest('.warning-conditions');

		if(warningsConditions != null){
			if( target.value == 'submitted'){
				warningsConditions.querySelector(`[name*='[conditional-value]']`).style.visibility = 'hidden';
			}else{
				warningsConditions.querySelector(`[name*='[conditional-value]']`).style.visibility = '';
			}
		}
	}else if(target.matches(`.switch [name='enable']`)){
		target.closest(`form`).querySelector(`.recurring-submissions`).classList.toggle('hidden');
	}else if(target.matches(`.frequency, #period, [name="window-start"], [name="window-end"], [name="reminder-startdate"], #reminder-period, [name="reminder-amount"]`)){
		updateReminderMinMax(target);
	}else{
		return;
	}

	event.stopImmediatePropagation();
});

function fixWarningConditionNumbering(parent){
	var warningConditions	= parent.querySelectorAll('.warning-conditions');
	var i = 0;
	//loop over all rules in the condition
	warningConditions.forEach(condition =>{
		condition.dataset.index = i;
		
		condition.querySelectorAll('.warning-condition').forEach(function(el){
			//fix numbering
			if(el.name != undefined){
				el.name	= el.name.replace(/([0-9])+/g,i);
			}

			if(el.id != undefined){
				el.id	= el.id.replace(/([0-9])+/g,i);
			}

			if(el.list != undefined){
				el.setAttribute('list', el.list.id.replace(/([0-9])+/g,i));
			}
		});
		
		i++;
	});
}

