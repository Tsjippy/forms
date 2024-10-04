var prayers = new function(){
		console.log('Dynamic prayers forms js loaded');
	document.addEventListener('DOMContentLoaded', function() {
		FormFunctions.tidyMultiInputs();
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

		prayers.processFields(el);
	};

	window.addEventListener('click', listener);
	window.addEventListener('input', listener);

	this.processFields    = function(el){
		var elName = el.getAttribute('name');

		var form	= el.closest('form');
		if(elName == 'name'){
			let name = FormFunctions.getFieldValue('name', form);

			if(elName == 'name'){
				FormFunctions.changeFieldValue('[name="user_id"]', name, prayers.processFields, form, );
			}
		}

	};
};

// Loop over the element which value is given in the url;
if(typeof(urlSearchParams) == 'undefined'){
	window.urlSearchParams = new URLSearchParams(window.location.search.replaceAll('&amp;', '&'));
}
Array.from(urlSearchParams).forEach(array => document.querySelectorAll(`[name^='${array[0]}']`).forEach(el => FormFunctions.changeFieldValue(el, array[1], prayers.processFields, el.closest('form'), )));

