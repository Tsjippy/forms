var user_visa = new function(){
		console.log('Dynamic user_visa forms js loaded');
	document.addEventListener('DOMContentLoaded', function() {
		FormFunctions.tidyMultiInputs();
		let forms = document.querySelectorAll(`[data-formid="13"]`);
		forms.forEach(form=>{
			form.querySelectorAll(`select, input, textarea`).forEach(
				el=>user_visa.processFields(el)
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

		user_visa.processFields(el);
	};

	window.addEventListener('click', listener);
	window.addEventListener('input', listener);

	this.processFields    = function(el){
		var elName = el.getAttribute('name');

		var form	= el.closest('form');
		if(elName == 'visa_info[permit_type][]'){
			var value_1 = FormFunctions.getFieldValue('visa_info[permit_type][]', form, true, 'greencard', true);
			var value_3 = FormFunctions.getFieldValue('visa_info[permit_type][]', form, true, 'accompanying', true);

			if(value_1 == 'greencard' || value_3 == 'accompanying'){
				form.querySelectorAll(`[name="greencard_expiry_date_label"], [name="visa_info[passport_name]"], [name="visa_info[nin]"], [name="upload_your_passport_and_greencard_copy_in_one_file_label"], [name="visa_info[passport_and_greencard_files]_files[]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_visa.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'greencard' && value_3 != 'accompanying'){
				form.querySelectorAll(`[name="greencard_expiry_date_label"], [name="visa_info[passport_name]"], [name="visa_info[nin]"], [name="upload_your_passport_and_greencard_copy_in_one_file_label"], [name="visa_info[passport_and_greencard_files]_files[]"]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_visa.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 == 'greencard'){
				form.querySelectorAll(`[name="quota_position_label"], [name="visa_info[quota_position]"], [name="positions"], [name="upload_any_qualifications_you_have_label"], [name^="visa_info[qualifications]_files[]["]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('remove', el, user_visa.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}

			if(value_1 != 'greencard'){
				form.querySelectorAll(`[name="quota_position_label"], [name="visa_info[quota_position]"], [name="positions"], [name="upload_any_qualifications_you_have_label"], [name^="visa_info[qualifications]_files[]["]`).forEach(el=>{
						//Make sure we only do each wrapper once by adding a temp class
						if(!el.closest('.inputwrapper').matches('.action-processed')){
							el.closest('.inputwrapper').classList.add('action-processed');
							FormFunctions.changeVisibility('add', el, user_visa.processFields);
						}
				});
				document.querySelectorAll('.action-processed').forEach(el=>{el.classList.remove('action-processed')});
			}
		}

	};
};

// Loop over the element which value is given in the url;
if(typeof(urlSearchParams) == 'undefined'){
	window.urlSearchParams = new URLSearchParams(window.location.search.replaceAll('&amp;', '&'));
}
Array.from(urlSearchParams).forEach(array => document.querySelectorAll(`[name^='${array[0]}']`).forEach(el => FormFunctions.changeFieldValue(el, array[1], user_visa.processFields, el.closest('form'), )));

