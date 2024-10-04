var user_location = new function(){
		console.log('Dynamic user_location forms js loaded');
	document.addEventListener('DOMContentLoaded', function() {
		FormFunctions.tidyMultiInputs();
		let forms = document.querySelectorAll(`[data-formid="11"]`);
		forms.forEach(form=>{
			form.querySelectorAll(`select, input, textarea`).forEach(
				el=>user_location.processFields(el)
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

		user_location.processFields(el);
	};

	window.addEventListener('click', listener);
	window.addEventListener('input', listener);

	this.processFields    = function(el){
		var elName = el.getAttribute('name');

		var form	= el.closest('form');
		if(elName == 'location[preset]'){

			if(elName == 'location[preset]'){
				FormFunctions.changeVisibility('add', form.querySelector('[name="address_label"]').closest('.inputwrapper'), user_location.processFields);
			}
		}

	};
};

// Loop over the element which value is given in the url;
if(typeof(urlSearchParams) == 'undefined'){
	window.urlSearchParams = new URLSearchParams(window.location.search.replaceAll('&amp;', '&'));
}
Array.from(urlSearchParams).forEach(array => document.querySelectorAll(`[name^='${array[0]}']`).forEach(el => FormFunctions.changeFieldValue(el, array[1], user_location.processFields, el.closest('form'), )));



console.log("Location.js loaded");


function fillLocationFields(event){
	var target	= event.target;
	var form	= target.closest('form');
	
	var option	= target.options[target.selectedIndex];
	var value	= option.value;
	var name	= option.text;
	
	//Fill the fields based on the selected compound
	if(value == 'modal'){
		Main.showModal('add_location');
	}else if (name != ""){
		//Get the locations from the presets variable
		form.querySelector("[name='location[address]']").value		= name+' State';
		form.querySelector("[name='location[latitude]']").value		= locations.locations[value]["lat"];
		form.querySelector("[name='location[longitude]']").value	= locations.locations[value]["lon"];
	}
}

//dynamically load google maps script only when needed
function loadGoogleMapsScript(){
	if(document.getElementById('googlemaps') == null && typeof(mapsApi) == 'object' && typeof(initMap) != 'undefined'){
		const script	= document.createElement('script');
		script.id 		= 'googlemaps';
		script.src 		= `//maps.googleapis.com/maps/api/js?key=${mapsApi.key}&callback=initMap`;
		script.async 	= true;
		script.loading	= 'async';
		document.body.append(script);
	}
}

document.addEventListener("DOMContentLoaded", function() {
	loadGoogleMapsScript();
	
	// Add event listener to a state field
	let element = document.querySelector(`[name="location[preset]"]`);
	if (typeof(element) != 'undefined' && element != null){
		element.addEventListener('change', fillLocationFields);
	}

	//Add event listener to the latitude field
	element = document.querySelector(".latitude");
	if (typeof(element) != 'undefined' && element != null){
		element.addEventListener('keydown', setTimer);
	}
	
	//Add event listener to the longitude field
	element = document.querySelector(".longitude");
	if (typeof(element) != 'undefined' && element != null){
		element.addEventListener('keydown', setTimer);
	}
	
	//Only continue if typing has stopped for 1 second
	var timer = null;
	function setTimer() {
		clearTimeout(timer); 
		timer = setTimeout(setAddress, 1000)
	}
	
	//Reverse geocode coordinates to address, using the Google API
	function setAddress(){
		var lat = document.querySelector(".latitude").value;
		var lon = document.querySelector(".longitude").value;
		
		var geocoder = new google.maps.Geocoder();
		if (lat != "" && lon != ""){
			var latlng = { lat: parseFloat(lat), lng: parseFloat(lon) };
			
			geocoder.geocode({ location: latlng }, 
				function(results, status) {
					if (status === "OK") {
					  if (results[0]) {
						  document.querySelectorAll(".address").forEach(function(address){
							if(address.value == ''){
								address.value = results[0].formatted_address
							}
						});
					  }
					}
				}
			);
		}
	}
	
	//Only fill the coordinates after someone stopped typing in the address field
	timer 	= null;
	element = document.querySelector(".address");
	if (typeof(element) != 'undefined' && element != null){
		element.addEventListener('keydown', function(e) {
			clearTimeout(timer); 
			timer = setTimeout(setCoordinates, 1000,e)
		});
	}

	function setCoordinates(event){
		//Geocode address to coordinates
		var geocoder = new google.maps.Geocoder();
		var address = event.target.value;
		geocoder.geocode({ address: address}, 
			function(results, status) {
				if (status === "OK") {
					document.querySelectorAll(".latitude").forEach(function(field){field.value = (results[0].geometry.location.lat()).toFixed(7)});
					document.querySelectorAll(".longitude").forEach(function(field){field.value = (results[0].geometry.location.lng()).toFixed(7)});
				}
			}
		);
	}
	
	//If the current locationbutton is clicked, get the location, and fill the form
	let el 	= document.querySelector('.current-location');
	if(el != null){
		if (Main.isMobileDevice() && navigator.geolocation) {
			el.addEventListener('click', ev=>navigator.geolocation.getCurrentPosition(showPosition));
		} else { 
			el.classList.add('hide');
		}
	}

	function showPosition(position) {
		var lat = (position.coords.latitude).toFixed(7);
		var lon = (position.coords.longitude).toFixed(7);
		
		document.querySelectorAll(".latitude").forEach(function(field){field.value = lat});
		document.querySelectorAll(".longitude").forEach(function(field){field.value = lon});
		setAddress();
	}
});