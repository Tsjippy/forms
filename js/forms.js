import { addStyles } from '../../../plugins/sim-plugin/includes/js/partials/load_assets.js';
import { removeDefaultSelect, cloneNode, copyFormInput, fixNumbering, removeNode, tidyMultiInputs, updateMultiStepControls, showFormStep, nextPrev, changeFieldValue, changeVisibility, changeFieldProperty } from './form_exports.js';
import { getFieldValue } from  '../../../plugins/sim-plugin/includes/js/field_value.js';
export { getFieldValue, removeDefaultSelect, cloneNode, copyFormInput, fixNumbering, removeNode, tidyMultiInputs, updateMultiStepControls, showFormStep, nextPrev, changeFieldValue, changeVisibility, changeFieldProperty };

console.log('Forms.js is loaded');

async function saveFormInput(target){
	let form		= target.closest('form');

	// make all inputs required if needed
	form.querySelectorAll('.required:not(hidden) input, .required:not(hidden) textarea, .required:not(hidden) select').forEach(el=>{
		// do not make nice select inputs nor file uploads required
		if(el.closest('div.nice-select') == null && (el.type != 'file' || el.closest('.file-upload-wrap').querySelector('.document-preview input') == null)){
			el.required	= true;
		}
	});

	let response	= await FormSubmit.submitForm(target, 'forms/save_form_input');

	if(response){
		Main.displayMessage(response);

		if(form.dataset.reset){
			FormSubmit.formReset(form);
		}
	}
}

async function formbuilderSwitch(target){
	let wrapper	= target.closest('.sim-form-wrapper');
	let button	= target.outerHTML;

	let formData = new FormData();
	let formId;

	const url 		= new URL(window.location);
	if(target.matches('.formbuilder-switch')){
		formData.append('formbuilder', true);
		url.searchParams.set('formbuilder', true);

		formId	= wrapper.querySelector('form.sim-form-wrapper').dataset.formId;
	}else{
		url.searchParams.delete('formbuilder');
		formId	= wrapper.querySelector('[name="form-id"]').value;
	}
	window.history.pushState({}, '', url);

	formData.append('form-id', formId);

	let loader	= Main.showLoader(target, false, 50, 'Requesting form...');
	wrapper.innerHTML	= loader.outerHTML;

	let response = await FormSubmit.fetchRestApi('forms/form_builder', formData);

	if(response){
		wrapper.innerHTML	= response.html;

		addStyles(response, document);

		// Activate tinyMce's again
	/* 	wrapper.querySelectorAll('.wp-editor-area').forEach(el =>{
			window.tinyMCE.execCommand('mceAddEditor', false, el.id);
		});

		wrapper.querySelectorAll('select').forEach(function(select){
			Main.attachNiceSelect(select);
		}); */
	}else{
		loader.outerHTML	= button;
	}
}

async function requestNewFormResults(target){
	let wrapper		= target.closest('.form.table-wrapper');
	let button		= target.outerHTML;

	let formData 	= new FormData();
	let formId		= wrapper.querySelector('.sim-table.form-data-table').dataset.formId;
	let shortcodeId	= wrapper.querySelector('.sim-table.form-data-table').dataset.shortcodeId;

	formData.append('form-id', formId);
	formData.append('shortcode-id', shortcodeId);

	const url 		= new URL(window.location);
	if(url.searchParams.get('only-own')){
		formData.append('only-own', true);
	}

	if(url.searchParams.get('all')){
		formData.append('all', true);
	}

	if(url.searchParams.get('archived')){
		formData.append('archived', true);
	}

	let loader	= Main.showLoader(target, false, 50, 'Requesting form results...');
	wrapper.innerHTML	= loader.outerHTML;

	let response = await FormSubmit.fetchRestApi('forms/show_form_results', formData);

	if(response){
		wrapper.innerHTML	= response;
	}else{
		loader.outerHTML	= button;
	}
}

async function archivedEntriesSwitch(target){
	const url 		= new URL(window.location);
	if(target.matches('.archive-switch-show')){
		url.searchParams.set('archived', true);
	}else{
		url.searchParams.delete('archived');
	}
	window.history.pushState({}, '', url);

	requestNewFormResults(target);
}

async function onlyOwnSwitch(target){
	const url 		= new URL(window.location);
	if(target.matches('.only-own-switch-on')){
		url.searchParams.set('only-own', true);
		url.searchParams.delete('all', true);
	}else{
		url.searchParams.set('all', true);
		url.searchParams.delete('only-own');
	}
	window.history.pushState({}, '', url);

	requestNewFormResults(target);
}

//we are online again
window.addEventListener('online', function(){
	document.querySelectorAll('.form-submit').forEach(btn=>{
		btn.disabled = false
		btn.querySelectorAll('.offline').forEach(el=>el.remove());
	});
});

//prevent form submit when offline
window.addEventListener('offline', function(){
	document.querySelectorAll('.form-submit').forEach(btn=>{
		btn.disabled = true;
		if(btn.querySelector('.online') == null){
			btn.innerHTML = '<div class="online">'+btn.innerHTML+'</div>';
		}
		btn.innerHTML += '<div class="offline">You are offline</div>'
	});
});

document.addEventListener('click', function(event) {
	let target = event.target;
	
	//add element
	if(target.matches('.add')){
		let orgNode	= target.closest(".clone-div");

		let newNode = copyFormInput(orgNode);

		// Fix in nodes
		fixNumbering(target.closest('.clone-divs-wrapper'));

		//add tinymce's can only be done when node is inserted and id is unique
		newNode.querySelectorAll('.wp-editor-area').forEach((el, index) =>{
			// find org node settings
			let tn = tinymce.get(orgNode.querySelectorAll('.wp-editor-area')[index].id);
			if(tn != null){
				let settings	= tn.settings;

				// update the settings for the clone
				for (const key in settings) {
					console.log(`${key}: ${settings[key]}`);

					if(typeof(settings[key]) == 'string'){
						settings[key]	= settings[key].replace(
							/(.*)([0-9])/, 
							(match, prefix, nr) => {
								const newNumber = parseInt(nr) + 1;
								return prefix + newNumber;
							}
						);
					}
				}

				tinymce.init(settings);
				//window.tinyMCE.execCommand('mceAddEditor', false, el.id);
			}
		});

		target.remove();
	}
	
	//remove element
	if(target.matches('.remove')){
		//Remove node clicked
		removeNode(target);
	}

	if(target.matches('.sim-form-wrapper [name="submit-form"]')){
		event.stopPropagation();
		
		saveFormInput(target);
	}

	if(target.matches('.formbuilder-switch') || target.matches('.formbuilder-switch-back')){
		formbuilderSwitch(target);
	}

	if(target.matches('.archive-switch-hide') || target.matches('.archive-switch-show')){
		archivedEntriesSwitch(target);
	}

	if(target.matches('.only-own-switch-all') || target.matches('.only-own-switch-on')){
		onlyOwnSwitch(target);
	}
});

document.addEventListener('change', ev=>{
	// select all elements with a datalist attached
	if(ev.target.matches('input[list]') && ev.target.name.includes('[')){
		let el		= ev.target.list.querySelector(`[value="${ev.target.value}" i]`);

		if(el != null){
			// find the dataset value of the given element value
			let value	= el.dataset.value;

			if(value != undefined){
				// change the value to create extra inputs if necessary
				changeFieldValue(ev.target, value, '', ev.target.closest('form'));
			}
		}
	}
})