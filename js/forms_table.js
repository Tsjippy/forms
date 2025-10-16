
console.log("Formstable.js loaded");

async function showHiddenColumns(target){
	// store as preference
	let formData	= new FormData();
	formData.append('form-id', target.dataset.formId);

	let response	= await FormSubmit.fetchRestApi('forms/delete_table_prefs', formData);

	if(response){
		Main.displayMessage(response);
		location.reload();
	}
}

async function saveColumnSettings(target){
	let response = await FormSubmit.submitForm(target, 'forms/save_column_settings');

	if(response){
		Main.displayMessage(response);
		location.reload();
	}
}

async function saveTableSettings(target){
	let response = await FormSubmit.submitForm(target, 'forms/save_table_settings');

	if(response){
		Main.displayMessage(response);
	}
}

async function askConfirmation(text){
	let options	= {
		title: 'Are you sure?',
		text: `Are you sure you want to ${text} this?`,
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: "#bd2919",
		cancelButtonColor: '#d33',
		confirmButtonText: `Yes, ${text} it!`,
	};

	if(document.fullscreenElement != null){
		options['target']	= document.fullscreenElement;
	}

	let result	= await Swal.fire(options);


	document.fullscreenElement

	return result.isConfirmed;
}

async function removeSubmission(target){
	if(await askConfirmation('delete')){		
		let submissionId	= target.closest('tr').dataset.id;
		let table			= target.closest('table');

		let formData = new FormData();
		formData.append('form-id', table.dataset.formId);
		formData.append('submission-id', submissionId);
		
		//display loading gif
		Main.showLoader(target);

		let response	= await FormSubmit.fetchRestApi('forms/remove_submission', formData);

		if(response){
			table.querySelectorAll(`.table-row[data-id="${submissionId}"]`).forEach(
				row=>row.remove()
			);
		}
	}
}

async function archiveSubmission(target){	
	let table			= target.closest('table');
	let tableRow		= target.closest('tr');
	let submissionId	= tableRow.dataset.id;
	let showSwal		= true;
	let action			= target.value;
	let response;

	let formData 		= new FormData();
	formData.append('form-id', table.dataset.formId);
	formData.append('submission-id', submissionId);
	formData.append('action', action);
	
	// Ask whether to archive one piece or the whole
	if(tableRow.dataset.subid != undefined){
		showSwal = false;

		let options	= {
			title: `What do you want to ${action}?`,
			text: `Do you want to ${action} just this one or the whole request?`,
			icon: 'question',
			showDenyButton: true,
			showCancelButton: true,
			confirmButtonText: 'Just this one',
			denyButtonText: 'The whole request',
			confirmButtonColor: "#bd2919"
		};
	
		if(document.fullscreenElement != null){
			options['target']	= document.fullscreenElement;
		}
		
		response = await Swal.fire(options);

		if (response.isConfirmed) {
			formData.append('subid', tableRow.dataset.subid);
		}
		
		// skip if denied
		if(response.isDismissed){
			return;
		}
	}
	
	if(showSwal){
		if(!await askConfirmation(action)){
			return;
		}
	}

	//display loading gif
	Main.showLoader(target);
	
	response	= await FormSubmit.fetchRestApi('forms/archive_submission', formData);

	if(response){
		let params = new Proxy(new URLSearchParams(window.location.search), {
			get: (searchParams, prop) => searchParams.get(prop),
		});

		// Create a custom event so others can listen to it.
		// Used by bookings uploads
		const event = new Event('submissionArchived');
		
		// Delete all
		if(formData.get('subid') == null){
			table.querySelectorAll(`[data-id="${submissionId}"]`).forEach(row=>{
				row.dispatchEvent(event);

				// just change the button name
				if(params.archived){
					let element;
					if(action == 'archive'){
						element = row.querySelector(`.loader-wrapper, .archive`);
					}else{
						element = row.querySelector(`.loader-wrapper, .unarchive`);
					}
					changeArchiveButton(element, action);
				}else{
					row.remove();
				}
			});
		// Only delete subid
		}else{
			table.querySelectorAll(`.table-row[data-id="${submissionId}"][data-subid="${tableRow.dataset.subid}"]`).forEach(row=>{
				row.dispatchEvent(event);
				
				// just change the button name
				if(params.archived){
					let loader = row.querySelector('.loader-wrapper');
					
					changeArchiveButton(loader, action);
				}else{
					row.remove();
				}
			}
			);
		}
	}
}

function changeArchiveButton(element, action){
	let text;

	if(action == 'archive'){
		action 	= 'unarchive';
		text	= 'Unarchive';
	}else{
		action 	= 'archive';
		text	= 'Archive';
	}
	element.outerHTML = `<button class="${action} button forms-table-action" name="${action}-action" value="${action}">${text}</button>`;
}

async function getInputHtml(target){
	let table			= target.closest('table');

	// First make sure we have processed all others
	document.querySelectorAll('td.active').forEach(td=>{
		if(td != target){
			processFormsTableInput(td);
		}
	});

	// There can only be one active cell per page
	target.classList.add('active');

	let formId			= table.dataset.formId;
    let submissionId	= target.closest('tr').dataset.id;
	let data			= target.dataset;
	let oldText			= target.textContent;
    
    Main.showLoader(target.firstChild);
	
	target.dataset.oldtext	 	= oldText;

	let formData = new FormData();
    formData.append('form-id', formId);
    formData.append('submission-id', submissionId);

	for( var d in data){
		formData.append(d, data[d]);
	}

	let response	= await FormSubmit.fetchRestApi('forms/get_input_html', formData);

	if(response){
		target.innerHTML	 		= `<div class='input-wrapper'>${response}</div>`;

		addFormsTableInputEventListeners(target);

		target.querySelectorAll('.file-upload-wrap').forEach(el=>el.addEventListener('uploadfinished', uploadFinished));

		// show calendar by default
		target.querySelectorAll('input').forEach(el=>el.showPicker());
	}else{
		target.innerHTML	= target.dataset.oldtext;
		target.classList.remove('active');
	}
}

function updatePageNav(navWrapper, pageNr){

	navWrapper.querySelector('.current').classList.remove('current');

	navWrapper.querySelector(`[data-nr="${pageNr}"]`).classList.add('current');

	// hide prev button
	if(pageNr == 0){
		navWrapper.querySelector('.prev').classList.add('hidden');
	}else{
		navWrapper.querySelector('.prev').classList.remove('hidden');
	}

	// hide next button
	if(pageNr == navWrapper.querySelectorAll('.page-number').length -1){
		navWrapper.querySelector('.next').classList.add('hidden');
	}else{
		navWrapper.querySelector('.next').classList.remove('hidden');
	}
}

async function getNextPage(target){
	let wrapper			= target.closest('.form.table-wrapper');
	let navWrapper		= target.closest('.form-result-navigation');
	let tableWrapper	= target.closest('.form-results-wrapper');
	let table			= tableWrapper.querySelector('.form-data-table:not(.hidden)');

	table.classList.add('hidden');

	// get the requested page number
	let	curPage			= parseInt(navWrapper.querySelector(".page-number-wrapper .current").dataset.nr);
	let page;
	if(target.matches('.next')){
		page	= curPage + 1;
	}else if(target.matches('.prev')){
		page	= curPage - 1;
	}else{
		page	= target.dataset.nr;
	}
	
	updatePageNav(navWrapper, page);

	// check if the requested page is already loaded
	let loadedPage	= tableWrapper.querySelector(`[data-page="${page}"]`);
	
	if(loadedPage != null){
		console.log(loadedPage);
		loadedPage.classList.remove('hidden');

		return;
	}

	// request page over ajax
	let formId			= table.dataset.formId;

	let loader			= Main.showLoader(table, false);
	let formData;
	if(wrapper.querySelector(".filter-options") == null){
		formData		= new FormData();
	}else{
		formData		= new FormData(wrapper.querySelector(".filter-options"));
	}

    formData.append('form-id', formId);
    formData.append('page-number', page);
	formData.append('shortcode-id', table.dataset.shortcodeId);
    formData.append('type', table.dataset.type);

	let params = new Proxy(new URLSearchParams(window.location.search), {
		get: (searchParams, prop) => searchParams.get(prop),
	});

	let archived;
	if(params['archived'] == null){
		archived	= false;
	}else{
		archived	= true;
	}

	let onlyOwn;
	if(params['onlyOwn'] == null){
		onlyOwn	= false;
	}else{
		onlyOwn	= true;
	}
	formData.append('archived', archived);
	formData.append('only-own', onlyOwn);
	
	if(tableWrapper.dataset.sortcol){
		formData.append('sortcol', tableWrapper.dataset.sortcol);
	}

	if(tableWrapper.dataset.sortdir){
		formData.append('sortdir', tableWrapper.dataset.sortdir);
	}

	let response	= await FormSubmit.fetchRestApi('forms/get_page', formData);

	if(response){
		loader.outerHTML	= response;
	}else{
		// restore prev data
		table.classList.remove('hidden');

		loader.remove();

		updatePageNav(navWrapper, curPage);
	}
}

async function getSortedPage(target){
	
	let wrapper			= target.closest('.form.table-wrapper');
	let tableWrapper	= target.closest('.form-results-wrapper');

	// always start at page 1 again after sorting
	let navWrapper		= tableWrapper.querySelector('.form-result-navigation');
	updatePageNav(navWrapper, 0);

	let table			= tableWrapper.querySelector('.form-data-table:not(.hidden)');
	let formId			= table.dataset.formId;
	let sortCol			= target.id;
	let sortDir			= target.classList.contains('desc') ? 'DESC' : 'ASC';

	tableWrapper.dataset.sortcol	= sortCol;
	tableWrapper.dataset.sortdir	= sortDir;

	let loader			= Main.showLoader(table, true, 50, 'Loading sorted data');
	let formData;
	if(wrapper.querySelector(".filter-options") == null){
		formData		= new FormData();
	}else{
		formData		= new FormData(wrapper.querySelector(".filter-options"));
	}

    formData.append('form-id', formId);
    formData.append('page-number', 0);
	formData.append('shortcode-id', table.dataset.shortcodeId);
    formData.append('type', table.dataset.type);

	let params = new Proxy(new URLSearchParams(window.location.search), {
		get: (searchParams, prop) => searchParams.get(prop),
	});

	let archived;
	if(params['archived'] == null){
		archived	= false;
	}else{
		archived	= true;
	}

	let onlyOwn;
	if(params['onlyOwn'] == null){
		onlyOwn	= false;
	}else{
		onlyOwn	= true;
	}
	formData.append('archived', archived);
	formData.append('only-own', onlyOwn);
	formData.append('sortcol', sortCol);
	formData.append('sortdir', sortDir);

	let response	= await FormSubmit.fetchRestApi('forms/get_page', formData);

	if(response){
		loader.outerHTML	= response;
	}else{
		// restore prev data
		table.classList.remove('hidden');

		loader.remove();
	}
}

function addFormsTableInputEventListeners(cell){
	let inputs		= cell.querySelectorAll('input,select,textarea');
		
	inputs.forEach((inputNode)=>{
		if(inputNode.type == 'select-one'){
			Main.attachNiceSelect(select);
		}
		
		if((inputNode.type != 'radio' && inputNode.type != 'checkbox') || inputs.length == 1){

			if(inputNode.type == 'date'){
				inputNode.addEventListener("blur", processFormsTableInput);
			}
			
			inputNode.focus();
		}
	});
}

function uploadFinished(event){
	if(event.target.closest('td') != null){
		//remove as soon as we come here
		document.removeEventListener('uploadfinished', uploadFinished);
		processFormsTableInput(document.querySelector('[data-oldtext]'));
	}
}

//function to get the temp input value and save it over AJAX
var running = false;
async function processFormsTableInput(target){
	// target is an event
	if(target.target != undefined){
		target = target.target;
	}
	
	if(running == target || target.value == '' || target.matches('.nice-select-search')){
		return;
	}
	running = target;
	
	setTimeout(function(){ running = false;}, 500);	

	let table			= target.closest('table');
	let formId			= table.dataset.formId;
	let submissionId	= target.closest('tr').dataset.id;
	let cell			= target.closest('td');
	cell.classList.remove('active');
    let data			= cell.dataset;
	let value			= FormFunctions.getFieldValue(target, cell, false);
	let shortcodeId		= '';

	if(target.closest('[data-shortcode-id]') != null){
		shortcodeId	= target.closest('[data-shortcode-id]').dataset.shortcodeId;
	}

	//Only update when needed
	if (value != JSON.parse(cell.dataset.oldvalue)){
		Main.showLoader(cell.querySelector('.input-wrapper'));
		
		// Submit new value and receive the filtered value back
		let formData = new FormData();
		formData.append('form-id', formId);
		formData.append('submission-id', submissionId);

		for(d in data){
			formData.append(d, data[d]);
		}
		formData.append('new-value', JSON.stringify(value));

		if(shortcodeId != ''){
			formData.append('shortcode-id', shortcodeId);
		}
		
		let response	= await FormSubmit.fetchRestApi('forms/edit_value', formData);
	
		if(response){
			let newValue = response['new-value'];
			
			if(typeof(newValue) == 'string'){
				newValue.replace('_', ' ');
			}

			//Replace the input element with its value
			if(newValue == ""){
				newValue = "X";
			}
	
			//Update all occurences of this field
			if(data['subid'] == undefined){
				let targets	= table.querySelectorAll(`tr[data-id="${submissionId}"] td[data-id="${data['id']}"]`);
				targets.forEach(td=>{td.innerHTML = newValue;});
			}else{
				cell.innerHTML = newValue;
			}

			cell.dataset.oldvalue	 	= JSON.stringify(newValue);
	
			Main.displayMessage(response.message.replace('_', ' '));
		}
		
		// Something went wrong restore the old text
		else{
			cell.innerHTML = cell.dataset.oldtext;
		}
	}else{
		cell.innerHTML = cell.dataset.oldtext;
	}

	addFormsTableInputEventListeners(target);

	if(target.dataset.oldtext != undefined){
		delete target.dataset.oldtext;
	}
}

// Add a change button when changing form values
function addChangeButton(target){
	let activeCell	= target.closest('td.active');
	
	if(activeCell == null){
		return;
	}

	let button = document.createElement('button');
    button.innerHTML = 'Save changes';
    button.classList.add('button');
    button.classList.add('small');
    button.classList.add('save');

	button.addEventListener('click', ev=>processFormsTableInput(ev.target.closest('td').querySelector('input')));

	// only add the button once
	if(activeCell.querySelector('.save') == null){
		activeCell.querySelector('.input-wrapper').appendChild(button)
	}
}

const copyContent = async (target) => {
	let text	= target.closest('td').innerText;
    try {
		let options = {
			icon: 'success',
			title: `Copied '${text}'`,
			showConfirmButton: false,
			timer: 3000
		};

		Swal.fire(options);

		navigator.clipboard.writeText(text);
    } catch (err) {
		Main.displayMessage('Failed to copy: '+err, 'error');
    }
}

const editCellValue	= async(event, td) => {
	let target	= event.target;
	if( target.matches('td.edit-forms-table')){
		event.stopPropagation();
		getInputHtml(target);
	}else if(td.matches('td.edit-forms-table') && target.tagName != 'INPUT' && target.tagName != 'A' && target.tagName != 'TEXTAREA' && !target.closest('.nice-select') && !target.matches('.button.save')){
		console.log(target)
		event.stopPropagation();
		getInputHtml(target.closest('td'));
	}
}

const hideColumn	= async (target) => {
	if(target.tagName == 'SPAN'){
		target = target.querySelector('img');
	}

	// Table itself
	if(target.parentNode.matches('th')){
		let cell 	= target.parentNode;

		// Hide the column
		var table		= cell.closest('table');

		// store as preference
		var formData	= new FormData();
		formData.append('form-id', table.dataset.formId);
		formData.append('column-name', cell.id);
		
		await FormSubmit.fetchRestApi('forms/save_table_prefs', formData);
	// Table settings
	}else{
		if(target.classList.contains('visible')){
			target.classList.replace('visible', 'invisible');
			target.src	= target.src.replace('visible.png', 'invisible.png');
			target.closest('td').querySelector('input').value = 0;
		}else{
			target.classList.replace('invisible','visible');
			target.src	= target.src.replace('invisible.png','visible.png');
			target.closest('td').querySelector('input').value = 1;
		}
	}
}

document.addEventListener("click", event=>{
	let target = event.target;

	if(target.name == 'submit_column_setting'){
		saveColumnSettings(target);
		event.stopPropagation();
	}else if(target.name == 'submit_table_setting'){
		saveTableSettings(target);
	}else if(target.name == 'form-settings[autoarchive]'){
		//show auto archive fields
		let el = target.closest('.table-rights-wrapper').querySelector('.auto-archive-logic');
		if(target.value == '1'){
			el.classList.remove('hidden');
		}else{
			el.classList.add('hidden');
		}
	}else if(target.closest('.form-result-navigation') != null && (target.matches('.next') || target.matches('.prev') || target.matches('.page-number'))){
		getNextPage(target);
		event.stopPropagation();
	}

	if(target.tagName == 'TH' && target.closest(".form-results-wrapper").querySelector('.form-result-navigation') != null){
		// get a sorted table over AJAX
		getSortedPage(target);
	}

	// copy cell contents
	if(target.matches('.copy')){
		copyContent(target);
	}

	//Actions
	if(target.matches('.delete.forms-table-action')){
		removeSubmission(target);
	}

	if(target.matches('.archive.forms-table-action, .unarchive.forms-table-action')){
		archiveSubmission(target);
	}

	if(target.matches('.print.forms-table-action')){
		window.location.href = window.location.href.split('?')[0]+"?print=true&table_id="+table.dataset.id+"&submission_id="+table_row.querySelector("[id='id' i]").textContent;
	}
	
	//Open settings modal
	if(target.classList.contains('edit-formshortcode-settings')){
		Main.showModal(document.querySelector('.modal.form-shortcode-settings'));
	}
	
	//Edit data
	let td = target.closest('td');
	if(!target.matches('.copy') && td && !td.matches('.active')){
		editCellValue(event, td);
	}

	// If we clicked somewhere and there is an active cell
	let activeCell	= document.querySelector('td.active');
	if(activeCell != null && td == null && activeCell.closest('.schedule') == null){
		if((target.type != 'checkbox') || target.length == 1){
			console.log(target);
			processFormsTableInput(activeCell);
		}
	}

	if(target.matches('form .table-permissions-rights-form')){
		target.closest('div').querySelector('.permission-wrapper').classList.toggle('hidden');
	}

	//Hide column
	if(target.classList.contains('visibility-icon')){
		hideColumn(target);
	}

	if(target.matches('.reset-col-vis')){
		showHiddenColumns(target);
	}
});

document.addEventListener("DOMContentLoaded", function() {
	
	if(typeof(Sortable) != 'undefined'){
		//Make the sortable-column-settings-rows div sortable
		let options = {
			handle: '.movecontrol',
			animation: 150,
		};

		document.querySelectorAll('.sortable-column-settings-rows tbody').forEach(el=>{
			Sortable.create(el, options);
		});
	}

	document.querySelectorAll('.form-data-table th').forEach(cell=>{
		cell.style.minWidth		= parseFloat(window.getComputedStyle(cell).width) + 20 + 'px';
	});
});

document.addEventListener('change', event=>{
	let target = event.target;

	if(target.id == 'sim-forms-selector'){
		document.querySelectorAll('.main-form-wrapper').forEach(el=>{
			el.classList.add('hidden');
		});

		document.getElementById(target.value).classList.remove('hidden');

		// position table
		SimTableFunctions.positionTable();
	}

	if(target.closest('td.active') != null){
		if(target.type != 'file' && target.type != 'date' && target.type != 'checkbox'){
			// Check if there are multiple inputs
			names=[];
			target.closest('td.active').querySelectorAll('input').forEach(el=>{
				if(!names.includes(el.type)){
					names.push(el.type);
				}
			});

			if(names.length == 1){
				processFormsTableInput(target);
			}else{
				addChangeButton(target);
			}
		}else if(target.type != 'file'){
			addChangeButton(target);
		}
	}
});

//Add a keyboard listener
document.addEventListener("keyup", function(event){
	if (['Enter', 'NumpadEnter'].includes(event.key) && keysPressed.Shift == undefined) {

		let activeCell	= document.querySelector('td.active');
		if(activeCell != null){
			processFormsTableInput(activeCell);
		}
	}

	delete keysPressed[event.key];
});


// Keep track of which keys are pressed
let keysPressed = {};
document.addEventListener('keydown', (event) => {
   keysPressed[event.key] = true;
});