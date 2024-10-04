var user_medical = new function(){
		console.log('Dynamic user_medical forms js loaded');
	document.addEventListener('DOMContentLoaded', function() {
		FormFunctions.tidyMultiInputs();
		let forms = document.querySelectorAll(`[data-formid="14"]`);
		forms.forEach(form=>{
			form.querySelectorAll(`select, input, textarea`).forEach(
				el=>user_medical.processFields(el)
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

		user_medical.processFields(el);
	};

	window.addEventListener('click', listener);
	window.addEventListener('input', listener);

	this.processFields    = function(el){
		var elName = el.getAttribute('name');

		var form	= el.closest('form');
		if(elName == 'medical[vaccinations][]'){
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'hepatitis_a', true);

			if(value_1 == 'hepatitis_a'){
				form.querySelectorAll(`[name="hepatitis_a_expiry_date_label"], [name="medical[never_hepatitis_a][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'hepatitis_a'){
				form.querySelectorAll(`[name="hepatitis_a_expiry_date_label"], [name="medical[never_hepatitis_a][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'hepatitis_b', true);

			if(value_1 == 'hepatitis_b'){
				form.querySelectorAll(`[name="hepatitus_b_expiry_date_label"], [name="medical[never_hepatitis_b][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'hepatitis_b'){
				form.querySelectorAll(`[name="hepatitus_b_expiry_date_label"], [name="medical[never_hepatitis_b][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'rabies', true);

			if(value_1 == 'rabies'){
				form.querySelectorAll(`[name="rabies_vaccination_expiry_date_label"], [name="medical[never_rabies][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'rabies'){
				form.querySelectorAll(`[name="rabies_vaccination_expiry_date_label"], [name="medical[never_rabies][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'meningitis', true);

			if(value_1 == 'meningitis'){
				FormFunctions.changeVisibility('remove', form.querySelector('[name="meningitis_vaccination_expiry_date_label"]').closest('.inputwrapper'), user_medical.processFields);
			}

			if(value_1 != 'meningitis'){
				FormFunctions.changeVisibility('add', form.querySelector('[name="meningitis_vaccination_expiry_date_label"]').closest('.inputwrapper'), user_medical.processFields);
			}
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'poliomyelitis', true);

			if(value_1 == 'poliomyelitis'){
				form.querySelectorAll(`[name="poliomyelitis_vaccination_expiry_date_label"], [name="medical[poliomyelitis_expiry_date]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'poliomyelitis'){
				form.querySelectorAll(`[name="poliomyelitis_vaccination_expiry_date_label"], [name="medical[poliomyelitis_expiry_date]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'diphtheria_tetanus_pertussis_diteper', true);

			if(value_1 == 'diphtheria_tetanus_pertussis_diteper'){
				form.querySelectorAll(`[name="diteper_vaccination_expiry_date_label"], [name="medical[diteper_expiry_date]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'diphtheria_tetanus_pertussis_diteper'){
				form.querySelectorAll(`[name="diteper_vaccination_expiry_date_label"], [name="medical[diteper_expiry_date]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'measles_mumps_rubella_mmr', true);

			if(value_1 == 'measles_mumps_rubella_mmr'){
				form.querySelectorAll(`[name="mmr_vaccination_expiry_date_label"], [name="medical[never_mmr][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'measles_mumps_rubella_mmr'){
				form.querySelectorAll(`[name="mmr_vaccination_expiry_date_label"], [name="medical[never_mmr][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'chickenpox', true);

			if(value_1 == 'chickenpox'){
				form.querySelectorAll(`[name="chickenpox_vaccination_expiry_date_label"], [name="medical[never_chickenpox][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'chickenpox'){
				form.querySelectorAll(`[name="chickenpox_vaccination_expiry_date_label"], [name="medical[never_chickenpox][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'typhoid', true);

			if(value_1 == 'typhoid'){
				form.querySelectorAll(`[name="typhoid_vaccination_expiry_date_label"], [name="medical[typhoid_expiry_date]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'typhoid'){
				form.querySelectorAll(`[name="typhoid_vaccination_expiry_date_label"], [name="medical[typhoid_expiry_date]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'cholera', true);

			if(value_1 == 'cholera'){
				form.querySelectorAll(`[name="cholera_vaccination_expiry_date_label"], [name="medical[never_cholera][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'cholera'){
				form.querySelectorAll(`[name="cholera_vaccination_expiry_date_label"], [name="medical[never_cholera][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'covid', true);

			if(value_1 == 'covid'){
				form.querySelectorAll(`[name="which_covid_vaccination_did_you_get?_label"], [name="medical[covid_vaccintype][]"], [name="medical[covid_date_1]"], [name="medical[covid_date_2]"], [name="medical[covid_2_not_needed][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'covid'){
				form.querySelectorAll(`[name="which_covid_vaccination_did_you_get?_label"], [name="medical[covid_vaccintype][]"], [name="medical[covid_date_1]"], [name="medical[covid_date_2]"], [name="medical[covid_2_not_needed][]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_medical.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
			var value_1 = FormFunctions.getFieldValue('medical[vaccinations][]', form, true, 'other', true);

			if(value_1 == 'other'){
				FormFunctions.changeVisibility('remove', form.querySelector('[name="othervaccinationstart"]').closest('.inputwrapper'), user_medical.processFields);
			}

			if(value_1 != 'other'){
				FormFunctions.changeVisibility('add', form.querySelector('[name="othervaccinationstart"]').closest('.inputwrapper'), user_medical.processFields);
			}
		}

	};
};

// Loop over the element which value is given in the url;
if(typeof(urlSearchParams) == 'undefined'){
	window.urlSearchParams = new URLSearchParams(window.location.search.replaceAll('&amp;', '&'));
}
Array.from(urlSearchParams).forEach(array => document.querySelectorAll(`[name^='${array[0]}']`).forEach(el => FormFunctions.changeFieldValue(el, array[1], user_medical.processFields, el.closest('form'), )));

