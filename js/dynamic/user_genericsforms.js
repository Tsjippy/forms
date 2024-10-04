var user_generics = new function(){
		console.log('Dynamic user_generics forms js loaded');
	document.addEventListener('DOMContentLoaded', function() {
		FormFunctions.tidyMultiInputs();
		let forms = document.querySelectorAll(`[data-formid="10"]`);
		forms.forEach(form=>{
			form.querySelectorAll(`select, input, textarea`).forEach(
				el=>user_generics.processFields(el)
			);
		});
	});
	var prevEl = '';

	var listener = function(event) {
		var el			= event.target;
		form			= el.closest('form');
		var elName		= el.getAttribute('name');

		if(elName == '' || elName == undefined){
			//el is a nice select
			if(el.closest('.nice-select-dropdown') != null && el.closest('.inputwrapper') != null){
				//find the select element connected to the nice-select
				el.closest('.inputwrapper').querySelectorAll('select').forEach(select=>{
					if(el.dataset.value == select.value){
						el	= select;
						elName = select.name;
					}
				});
			}else{
				return;
			}
		}

		//prevent duplicate event handling
		if(el == prevEl){
			return;
		}
		prevEl = el;

		//clear event prevenion after 100 ms
		setTimeout(function(){ prevEl = ''; }, 50);

		if(elName == 'nextBtn'){
			FormFunctions.nextPrev(1);
		}else if(elName == 'prevBtn'){
			FormFunctions.nextPrev(-1);
		}

		user_generics.processFields(el);
	};

	window.addEventListener('click', listener);
	window.addEventListener('input', listener);

	this.processFields    = function(el){
		var elName = el.getAttribute('name');

		var form	= el.closest('form');
		if(elName == 'nickname'){
			let nickname = FormFunctions.getFieldValue('nickname', form);

			if(elName == 'nickname'){
				FormFunctions.changeFieldValue('[name="first_name_label"]', nickname, user_generics.processFields, form, );
				FormFunctions.changeFieldValue('[name="display_name"]', nickname, user_generics.processFields, form, );
			}
		}

		if(elName == 'account-type'){
			var value_1 = FormFunctions.getFieldValue('account-type', form, true, 'positional', true);

			if(value_1 == 'positional'){
				form.querySelectorAll(`[name="Sending office"], [name="secondary_e-mail_address_label"], [name="email"], [name="sending_office"], [name="arrival_date_in_nigeria_label"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_generics.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
				FormFunctions.changeVisibility('remove', form.querySelector('[name="date_of_joining_sim_label"]').closest('.inputwrapper'), user_generics.processFields);
			}

			if(value_1 != 'positional'){
				form.querySelectorAll(`[name="Sending office"], [name="secondary_e-mail_address_label"], [name="email"], [name="sending_office"], [name="arrival_date_in_nigeria_label"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_generics.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
				FormFunctions.changeVisibility('add', form.querySelector('[name="date_of_joining_sim_label"]').closest('.inputwrapper'), user_generics.processFields);
			}
		}

		if(elName == 'financial_account_id'){
			var value_1 = FormFunctions.getFieldValue('financial_account_id', form, true, '', true);

			if(value_1 != ''){
				form.querySelectorAll(`[name="Financial statements"], [name="info_finance"], [name="financial_account_id"], [name="info_download"], [id^='E151']`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_generics.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 == ''){
				form.querySelectorAll(`[name="Financial statements"], [name="info_finance"], [name="financial_account_id"], [name="info_download"], [id^='E151']`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_generics.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
		}

		if(elName == 'advanced_options_button'){

			if(elName == 'advanced_options_button'){
				form.querySelectorAll(`[name="privacy_preferences_label"], [name="privacy_explain"], [name="privacy_preference[]"], [name="nickname_label"], [name="nickname"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('toggle', el, user_generics.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
		}

		if(elName == 'age_preference[]'){

			if(el.checked){
				FormFunctions.changeFieldValue('[name="privacy_preference[]"]', "hide_age", user_generics.processFields, form, );
			}
		}

	};
};

// Loop over the element which value is given in the url;
if(typeof(urlSearchParams) == 'undefined'){
	window.urlSearchParams = new URLSearchParams(window.location.search.replaceAll('&amp;', '&'));
}
Array.from(urlSearchParams).forEach(array => document.querySelectorAll(`[name^='${array[0]}']`).forEach(el => FormFunctions.changeFieldValue(el, array[1], user_generics.processFields, el.closest('form'), )));



//Show the position field when a ministry is checked
function changeVisibility(target) {
	target.closest('li').querySelectorAll('.ministryposition').forEach(label=>{
		label.classList.toggle('hidden');
		label.querySelectorAll('input').forEach(el=>el.value = '');
	});
}

async function addNewMinistry(target){
	var response = await FormSubmit.submitForm(target, 'user_management/add_ministry');
	
	if(response){
		var ministryName 		= target.closest('form').querySelector('[name="location_name"]').value;
		ministryName			= ministryName.charAt(0).toUpperCase() + ministryName.slice(1);

		var html = `
		<li style="list-style-type: none"> 
			<label>
				<input type="checkbox" class="ministry_option_checkbox" name="ministries[]" value="${response.postId}" checked>
				<span class="optionlabel">${ministryName}</span>
			</label>
			<label class="ministryposition" style="display:block;">
				<h4 class="labeltext">Position at ${ministryName}:</h4>
				<input type="text" id="justadded" name="jobs[${response.postId}]">
			</label>
		</li>`;
		
		document.querySelector("#ministries_list").insertAdjacentHTML('beforeEnd', html);
		
		//hide the SWAL window
		setTimeout(function(){document.querySelectorAll('.swal2-container').forEach(el=>el.remove());}, 1500);

		//focus on the newly added input
		document.getElementById('justadded').focus();
		document.getElementById('justadded').select();

		Main.displayMessage(response.html)
	}
	
	Main.hideModals();
}

//listen to all clicks
document.addEventListener('click', function(event) {
	var target = event.target;
	//show add ministry modal
	if(target.id == 'add-ministry-button'){
		//uncheck other and hide
		target.closest('li').querySelector('.ministry_option_checkbox').checked = false;
		target.closest('.ministryposition').classList.add('hidden');

		//Show the modal
		Main.showModal('add_ministry');
	}
	
	if(target.matches('.ministry_option_checkbox')){
		changeVisibility(target);
	}

	if(target.name == 'add_ministry'){
		addNewMinistry(target);
	}
});

function onBlur(ev){
	document.querySelectorAll(`.ministryposition input[type="text"][name="${ev.target.name}"]`).forEach(input => {
		// set value
		input.value = ev.target.value;

		// make visible
		input.closest('.ministryposition').classList.remove('hidden');

		// check the ministry
		input.closest('li').querySelector('.ministry_option_checkbox').checked	= true;
	});
}

document.addEventListener("DOMContentLoaded", function() {
	// Add the value to all inputs with the same name
	document.querySelectorAll('.ministryposition input[type="text"]').forEach(el => {
		el.addEventListener('blur', onBlur);
	});
});