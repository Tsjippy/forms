<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Travel form
 */
register_block_pattern(
    'tsjippy/travel-form',
    [
        'title'       => __('Travel Form', 'tsjippy'),
        'description' => __('Travel request form', 'tsjippy'),
        'categories'  => ['tsjippy'],
        'content'     => <<<'HTML'
HTML,
    ]
);

/**
 * Travel form
 */
register_block_pattern(
    'tsjippy/travel-form',
    [
        'title'       => __('Travel Form', 'tsjippy'),
        'description' => __('Travel request form', 'tsjippy'),
        'categories'  => ['tsjippy'],
        'content'     => <<<'HTML'
<!-- wp:tsjippy-forms/formbuilder {"postId":30002,"submission_message":"Succesfully submitted your request","name":"travelform","actions":["print"],"auto_archive_element":"625","auto_archive_value":"%today%-2days","split_blocks":["623"],"step_amount":4,"blockId":"52"} -->
<form method="post" target="_self" autocomplete="true" data-formname="travelform" data-blockid="52" class="wp-block-tsjippy-forms-formbuilder"><input type="hidden" name="block-id" value="52"/><input type="hidden" name="post-id" value="30002"/><!-- wp:tsjippy-forms/formstep {"text":"General Information","onlyOnInherited":true,"blockId":"600","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-formstep step-hidden" data-blockid="600"><h3>General Information</h3><!-- wp:tsjippy-forms/input {"type":"number","name":"user-id","dynamic_value":"user_id","blockId":"601","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="number" name="user-id" data-blockid="601" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/label {"text":"Your name","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"602","formbuilderChild":true} -->
<label data-blockid="602"><h4 class="label-text">Your name</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"name","inputAttributes":{"list":"users"},"dynamic_value":"display_name","required":true,"blockId":"603","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="name" required data-blockid="603" autocomplete="on" list="users" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/datalist {"id":"users","options_dynamic":"all_users","blockId":"604","formbuilderChild":true} -->
<datalist class="wp-block-tsjippy-forms-datalist" id="users" data-blockid="604">%options-placeholder%</datalist>
<!-- /wp:tsjippy-forms/datalist -->

<!-- wp:tsjippy-forms/datalist {"id":"emails","options_dynamic":"emails","blockId":"607","formbuilderChild":true} -->
<datalist class="wp-block-tsjippy-forms-datalist" id="emails" data-blockid="607">%options-placeholder%</datalist>
<!-- /wp:tsjippy-forms/datalist -->

<!-- wp:tsjippy-forms/label {"text":"Your E-mail","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"email"},"blockId":"ae437824-ba6e-4177-99f3-b884f0814d18","formbuilderChild":true} -->
<label data-blockid="ae437824-ba6e-4177-99f3-b884f0814d18"><h4 class="label-text">Your E-mail</h4><br/><!-- wp:tsjippy-forms/input {"type":"email","name":"email","inputAttributes":{"list":"emails"},"dynamic_value":"user_email","required":true,"blockId":"606","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="email" name="email" required data-blockid="606" autocomplete="on" list="emails" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Your Phonenumbers","childAttr":{"multiple":true,"add_button_content":"+","remove_button_content":"-","type":"tel"},"blockId":"03583644-2523-4f3b-93ae-b69fb32fba03","formbuilderChild":true} -->
<label data-blockid="03583644-2523-4f3b-93ae-b69fb32fba03"><h4 class="label-text">Your Phonenumbers</h4><br/><!-- wp:tsjippy-forms/input {"type":"tel","name":"phone_number","inputAttributes":{"list":"phones"},"dynamic_value":"phonenumbers","multiple":true,"required":true,"blockId":"610","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input option-wrapper"><ul class="list-selection-list">%value-placeholder%</ul><div class="multi-text-input-wrapper"><input class="wp-block-tsjippy-forms-input" type="tel" name="phone_number" required data-blockid="610" autocomplete="on" list="phones" value="%value-placeholder%"/><button type="button" class="small add-list-selection hidden">add</button></div></div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/datalist {"id":"phones","options_dynamic":"All phonenumbers","blockId":"609","formbuilderChild":true} -->
<datalist class="wp-block-tsjippy-forms-datalist" id="phones" data-blockid="609">%options-placeholder%</datalist>
<!-- /wp:tsjippy-forms/datalist -->

<!-- wp:tsjippy-forms/label {"text":"Indicate people travelling with you","childAttr":{"multiple":true,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"611","formbuilderChild":true} -->
<label data-blockid="611"><h4 class="label-text">Indicate people travelling with you</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"passengers","inputAttributes":{"list":"users"},"multiple":true,"blockId":"614","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input option-wrapper"><ul class="list-selection-list">%value-placeholder%</ul><div class="multi-text-input-wrapper"><input class="wp-block-tsjippy-forms-input" type="text" name="passengers" data-blockid="614" autocomplete="on" list="users" value="%value-placeholder%"/><button type="button" class="small add-list-selection hidden">add</button></div></div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Part of the journey is by plane","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"radio"},"blockId":"615","formbuilderChild":true} -->
<label data-blockid="615"><h4 class="label-text">Part of the journey is by plane</h4><br/><!-- wp:tsjippy-forms/input {"type":"radio","name":"travelmode","options":[{"value":"plane","label":"Yes"},{"value":"car","label":"No"}],"required":true,"blockId":"616","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input checkbox-wrapper" data-blockid="616"><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="travelmode" value="plane" class="formbuilder" autocomplete="on" data-blockid="616" required/>Yes</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="travelmode" value="car" class="formbuilder" autocomplete="on" data-blockid="616" required/>No</label>%options-placeholder%</div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label --></div>
<!-- /wp:tsjippy-forms/formstep -->

<!-- wp:tsjippy-forms/formstep {"text":"Journey One","onlyOnInherited":true,"blockId":"617","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-formstep step-hidden" data-blockid="617"><h3>Journey One</h3><!-- wp:tsjippy-forms/label {"text":"Starting Country","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"radio"},"blockId":"618","hidden":true,"formbuilderChild":true} -->
<label data-blockid="618"><h4 class="label-text">Starting Country</h4><br/><!-- wp:tsjippy-forms/input {"type":"radio","name":"startingpoint","options":[{"value":"Nigeria","label":"Nigeria"},{"value":"Abroad","label":"Abroad"}],"blockId":"619","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input checkbox-wrapper" data-blockid="619"><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="startingpoint" value="Nigeria" class="formbuilder" autocomplete="on" data-blockid="619"/>Nigeria</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="startingpoint" value="Abroad" class="formbuilder" autocomplete="on" data-blockid="619"/>Abroad</label>%options-placeholder%</div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/datalist {"id":"guesthouses","options":[{"value":"Esters, Abuja","label":"Esters, Abuja"},{"value":"Baptist Guesthouse, Abuja","label":"Baptist Guesthouse, Abuja"},{"value":"ECWA Guesthouse, Jos","label":"ECWA Guesthouse, Jos"},{"value":"ECWA Guesthouse, Kano","label":"ECWA Guesthouse, Kano"}],"blockId":"620","formbuilderChild":true} -->
<datalist class="wp-block-tsjippy-forms-datalist" id="guesthouses" data-blockid="620"><option value="Esters, Abuja" label="Esters, Abuja"></option><option value="Baptist Guesthouse, Abuja" label="Baptist Guesthouse, Abuja"></option><option value="ECWA Guesthouse, Jos" label="ECWA Guesthouse, Jos"></option><option value="ECWA Guesthouse, Kano" label="ECWA Guesthouse, Kano"></option>%options-placeholder%</datalist>
<!-- /wp:tsjippy-forms/datalist -->

<!-- wp:group {"blockId":"621","hidden":true,"formbuilderChild":true,"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:tsjippy-forms/label {"text":"Arrival Airport","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"radio"},"blockId":"622","formbuilderChild":true} -->
<label data-blockid="622"><h4 class="label-text">Arrival Airport</h4><br/><!-- wp:tsjippy-forms/input {"type":"radio","name":"travel[1][from]","options":[{"value":"Abuja airport","label":"Abuja"},{"value":"Kano airport","label":"Kano"},{"value":"Lagos airport","label":"Lagos"},{"value":"Jos airport","label":"Jos"}],"blockId":"623","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input checkbox-wrapper" data-blockid="623"><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="travel[1][from]" value="Abuja airport" class="formbuilder" autocomplete="on" data-blockid="623"/>Abuja</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="travel[1][from]" value="Kano airport" class="formbuilder" autocomplete="on" data-blockid="623"/>Kano</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="travel[1][from]" value="Lagos airport" class="formbuilder" autocomplete="on" data-blockid="623"/>Lagos</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="travel[1][from]" value="Jos airport" class="formbuilder" autocomplete="on" data-blockid="623"/>Jos</label>%options-placeholder%</div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Date of arrival","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"date"},"blockId":"624","formbuilderChild":true} -->
<label data-blockid="624"><h4 class="label-text">Date of arrival</h4><br/><!-- wp:tsjippy-forms/input {"type":"date","name":"travel[1][date]","inputAttributes":{"min":"today"},"blockId":"625","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="date" name="travel[1][date]" data-blockid="625" autocomplete="on" min="today" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Arrival Time","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"time"},"blockId":"626","formbuilderChild":true} -->
<label data-blockid="626"><h4 class="label-text">Arrival Time</h4><br/><!-- wp:tsjippy-forms/input {"type":"time","name":"travel[1][time]","blockId":"627","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="time" name="travel[1][time]" data-blockid="627" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Flight Number","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"628","formbuilderChild":true} -->
<label data-blockid="628"><h4 class="label-text">Flight Number</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"travel[1][flightnr]","inputAttributes":{"pattern":"","oninvalid":"this.setCustomValidity('Please add your flight number in the format AA1234')","oninput":"this.setCustomValidity('')","value":"^[a-zA-Z]{1,2}\u005cs*[0-9]{1,5}$"},"onlyOnInherited":true,"blockId":"629","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[1][flightnr]" data-blockid="629" autocomplete="on" pattern="" oninvalid="this.setCustomValidity('Please add your flight number in the format AA1234')" oninput="this.setCustomValidity('')" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[1][to]","blockId":"635","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[1][to]" data-blockid="635" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></div>
<!-- /wp:group -->

<!-- wp:group {"blockId":"637","hidden":true,"formbuilderChild":true,"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:tsjippy-forms/label {"text":"When do you continue your journey","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"date"},"onlyOnInherited":true,"blockId":"641","formbuilderChild":true} -->
<label data-blockid="641"><h4 class="label-text">When do you continue your journey</h4><br/><!-- wp:tsjippy-forms/input {"type":"date","name":"travel[2][date]","onlyOnInherited":true,"blockId":"642","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="date" name="travel[2][date]" data-blockid="642" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[2][to]","onlyOnInherited":true,"blockId":"646","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[2][to]" data-blockid="646" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></div>
<!-- /wp:group -->

<!-- wp:group {"blockId":"648","hidden":true,"formbuilderChild":true,"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:tsjippy-forms/label {"text":"Starting address including state","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"649","formbuilderChild":true} -->
<label data-blockid="649"><h4 class="label-text">Starting address including state</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"travel[3][from]","inputAttributes":{"placeholder":"1 some street, some state","pattern":".{3,},.{3,}","oninvalid":"this.setCustomValidity('Please add your streetname, then a comma, then your statename')","oninput":"this.setCustomValidity('')"},"onlyOnInherited":true,"blockId":"650","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[3][from]" data-blockid="650" autocomplete="on" placeholder="1 some street, some state" pattern=".{3,},.{3,}" oninvalid="this.setCustomValidity('Please add your streetname, then a comma, then your statename')" oninput="this.setCustomValidity('')" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[3][flightnr]","blockId":"658","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[3][flightnr]" data-blockid="658" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"time","name":"travel[3][time]","blockId":"659","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="time" name="travel[3][time]" data-blockid="659" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[3][to]","blockId":"660","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[3][to]" data-blockid="660" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></div>
<!-- /wp:group -->

<!-- wp:group {"blockId":"679","hidden":true,"formbuilderChild":true,"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:tsjippy-forms/label {"text":"Airport","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"radio"},"blockId":"680","formbuilderChild":true} -->
<label data-blockid="680"><h4 class="label-text">Airport</h4><br/><!-- wp:tsjippy-forms/input {"type":"radio","name":"airport1","options":[{"value":"Abuja airport","label":"Abuja"},{"value":"Kano airport","label":"Kano"},{"value":"Jos airport","label":"Jos"},{"value":"Lagos airport","label":"Lagos"},{"value":"Other","label":"Other"}],"blockId":"681","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input checkbox-wrapper" data-blockid="681"><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="airport1" value="Abuja airport" class="formbuilder" autocomplete="on" data-blockid="681"/>Abuja</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="airport1" value="Kano airport" class="formbuilder" autocomplete="on" data-blockid="681"/>Kano</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="airport1" value="Jos airport" class="formbuilder" autocomplete="on" data-blockid="681"/>Jos</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="airport1" value="Lagos airport" class="formbuilder" autocomplete="on" data-blockid="681"/>Lagos</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="airport1" value="Other" class="formbuilder" autocomplete="on" data-blockid="681"/>Other</label>%options-placeholder%</div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Flight Number","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"682","formbuilderChild":true} -->
<label data-blockid="682"><h4 class="label-text">Flight Number</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"flightnr1","inputAttributes":{"pattern":"","oninvalid":"this.setCustomValidity('Please add your flight number in the format AA1234')","oninput":"this.setCustomValidity('')"},"blockId":"683","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="flightnr1" data-blockid="683" autocomplete="on" pattern="" oninvalid="this.setCustomValidity('Please add your flight number in the format AA1234')" oninput="this.setCustomValidity('')" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Flight  date","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"date"},"blockId":"684","formbuilderChild":true} -->
<label data-blockid="684"><h4 class="label-text">Flight  date</h4><br/><!-- wp:tsjippy-forms/input {"type":"date","name":"flightdate1","blockId":"685","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="date" name="flightdate1" data-blockid="685" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Flight time","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"time"},"blockId":"686","formbuilderChild":true} -->
<label data-blockid="686"><h4 class="label-text">Flight time</h4><br/><!-- wp:tsjippy-forms/input {"type":"time","name":"flighttime1","blockId":"687","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="time" name="flighttime1" data-blockid="687" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label --></div>
<!-- /wp:group -->

<!-- wp:group {"blockId":"689","hidden":true,"formbuilderChild":true,"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:tsjippy-forms/label {"text":"Where will you spend the night?","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"690","formbuilderChild":true} -->
<label data-blockid="690"><h4 class="label-text">Where will you spend the night?</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"travel[5][from]","inputAttributes":{"placeholder":"1 some street, some state","pattern":".{3,},.{3,}","oninvalid":"this.setCustomValidity('Please add your streetname, then a comma, then your statename')","oninput":"this.setCustomValidity('')"},"blockId":"691","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[5][from]" data-blockid="691" autocomplete="on" placeholder="1 some street, some state" pattern=".{3,},.{3,}" oninvalid="this.setCustomValidity('Please add your streetname, then a comma, then your statename')" oninput="this.setCustomValidity('')" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/input {"type":"date","name":"travel[5][date]","blockId":"692","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="date" name="travel[5][date]" data-blockid="692" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[5][flightnr]","blockId":"696","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[5][flightnr]" data-blockid="696" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[5][to]","blockId":"697","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[5][to]" data-blockid="697" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"time","name":"travel[5][time]","blockId":"698","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="time" name="travel[5][time]" data-blockid="698" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></div>
<!-- /wp:group -->

<!-- wp:tsjippy-forms/label {"text":"Final destination","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"700","hidden":true,"formbuilderChild":true} -->
<label data-blockid="700"><h4 class="label-text">Final destination</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"final_destination_1","inputAttributes":{"placeholder":"1 some street, some state","pattern":".{3,},.{3,}","oninvalid":"this.setCustomValidity('Please add your streetname, then a comma, then your statename')","oninput":"this.setCustomValidity('')"},"blockId":"701","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="final_destination_1" data-blockid="701" autocomplete="on" placeholder="1 some street, some state" pattern=".{3,},.{3,}" oninvalid="this.setCustomValidity('Please add your streetname, then a comma, then your statename')" oninput="this.setCustomValidity('')" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Do you want to submit a return journey as well","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"radio"},"blockId":"702","hidden":true,"formbuilderChild":true} -->
<label data-blockid="702"><h4 class="label-text">Do you want to submit a return journey as well</h4><br/><!-- wp:tsjippy-forms/input {"type":"radio","name":"return","options":[{"value":"Yes","label":"Yes"},{"value":"No","label":"No"}],"blockId":"703","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input checkbox-wrapper" data-blockid="703"><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="return" value="Yes" class="formbuilder" autocomplete="on" data-blockid="703"/>Yes</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="return" value="No" class="formbuilder" autocomplete="on" data-blockid="703"/>No</label>%options-placeholder%</div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label --></div>
<!-- /wp:tsjippy-forms/formstep -->

<!-- wp:tsjippy-forms/formstep {"text":"Return Journey","onlyOnInherited":true,"blockId":"704","hidden":true,"formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-formstep step-hidden" data-blockid="704"><h3>Return Journey</h3><!-- wp:group {"blockId":"705","hidden":true,"formbuilderChild":true,"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:tsjippy-forms/label {"text":"Arrival Airport","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"radio"},"blockId":"706","formbuilderChild":true} -->
<label data-blockid="706"><h4 class="label-text">Arrival Airport</h4><br/><!-- wp:tsjippy-forms/input {"type":"radio","name":"travel[11][from]","options":[{"value":"Abuja airport","label":"Abuja"},{"value":"Kano airport","label":"Kano"},{"value":"Lagos airport","label":"Lagos"},{"value":"Jos airport","label":"Jos"}],"blockId":"707","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input checkbox-wrapper" data-blockid="707"><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="travel[11][from]" value="Abuja airport" class="formbuilder" autocomplete="on" data-blockid="707"/>Abuja</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="travel[11][from]" value="Kano airport" class="formbuilder" autocomplete="on" data-blockid="707"/>Kano</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="travel[11][from]" value="Lagos airport" class="formbuilder" autocomplete="on" data-blockid="707"/>Lagos</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="travel[11][from]" value="Jos airport" class="formbuilder" autocomplete="on" data-blockid="707"/>Jos</label>%options-placeholder%</div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Date of arrival","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"date"},"blockId":"708","formbuilderChild":true} -->
<label data-blockid="708"><h4 class="label-text">Date of arrival</h4><br/><!-- wp:tsjippy-forms/input {"type":"date","name":"travel[11][date]","blockId":"709","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="date" name="travel[11][date]" data-blockid="709" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Arrival Time","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"time"},"blockId":"710","formbuilderChild":true} -->
<label data-blockid="710"><h4 class="label-text">Arrival Time</h4><br/><!-- wp:tsjippy-forms/input {"type":"time","name":"travel[11][time]","blockId":"711","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="time" name="travel[11][time]" data-blockid="711" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Flight Number","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"712","formbuilderChild":true} -->
<label data-blockid="712"><h4 class="label-text">Flight Number</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"travel[11][flightnr]","inputAttributes":{"pattern":"","oninvalid":"this.setCustomValidity('Please add your flight number in the format AA1234')","oninput":"this.setCustomValidity('')"},"onlyOnInherited":true,"blockId":"713","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[11][flightnr]" data-blockid="713" autocomplete="on" pattern="" oninvalid="this.setCustomValidity('Please add your flight number in the format AA1234')" oninput="this.setCustomValidity('')" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[11][to]","blockId":"719","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[11][to]" data-blockid="719" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></div>
<!-- /wp:group -->

<!-- wp:group {"blockId":"721","hidden":true,"formbuilderChild":true,"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:tsjippy-forms/label {"text":"Where will you spend the night","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"onlyOnInherited":true,"blockId":"723","formbuilderChild":true} -->
<label data-blockid="723"><h4 class="label-text">Where will you spend the night</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"travel[12][from]","inputAttributes":{"placeholder":"1 some street, some state","pattern":".{3,},.{3,}","oninvalid":"this.setCustomValidity('Please add your streetname, then a comma, then your statename')","oninput":"this.setCustomValidity('')","list":"guesthouses"},"blockId":"724","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[12][from]" data-blockid="724" autocomplete="on" placeholder="1 some street, some state" pattern=".{3,},.{3,}" oninvalid="this.setCustomValidity('Please add your streetname, then a comma, then your statename')" oninput="this.setCustomValidity('')" list="guesthouses" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"When do you continue your journey","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"date"},"onlyOnInherited":true,"blockId":"725","formbuilderChild":true} -->
<label data-blockid="725"><h4 class="label-text">When do you continue your journey</h4><br/><!-- wp:tsjippy-forms/input {"type":"date","name":"travel[12][date]","onlyOnInherited":true,"blockId":"726","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="date" name="travel[12][date]" data-blockid="726" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[12][to]","onlyOnInherited":true,"blockId":"730","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[12][to]" data-blockid="730" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></div>
<!-- /wp:group -->

<!-- wp:group {"blockId":"732","hidden":true,"formbuilderChild":true,"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:tsjippy-forms/label {"text":"Starting address including state","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"733","formbuilderChild":true} -->
<label data-blockid="733"><h4 class="label-text">Starting address including state</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"travel[13][from]","inputAttributes":{"placeholder":"1 some street, some state","pattern":".{3,},.{3,}","oninvalid":"this.setCustomValidity('Please add your streetname, then a comma, then your statename')","oninput":"this.setCustomValidity('')"},"onlyOnInherited":true,"blockId":"734","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[13][from]" data-blockid="734" autocomplete="on" placeholder="1 some street, some state" pattern=".{3,},.{3,}" oninvalid="this.setCustomValidity('Please add your streetname, then a comma, then your statename')" oninput="this.setCustomValidity('')" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"How many bags will you bring","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"number"},"blockId":"739","hidden":true,"formbuilderChild":true} -->
<label data-blockid="739"><h4 class="label-text">How many bags will you bring</h4><br/><!-- wp:tsjippy-forms/input {"type":"number","name":"travel[13][bags]","blockId":"740","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="number" name="travel[13][bags]" data-blockid="740" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[13][to]","blockId":"742","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[13][to]" data-blockid="742" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[13][flightnr]","blockId":"743","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[13][flightnr]" data-blockid="743" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"time","name":"travel[13][time]","blockId":"744","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="time" name="travel[13][time]" data-blockid="744" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></div>
<!-- /wp:group -->

<!-- wp:group {"blockId":"763","hidden":true,"formbuilderChild":true,"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:tsjippy-forms/label {"text":"Airport","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"radio"},"blockId":"764","formbuilderChild":true} -->
<label data-blockid="764"><h4 class="label-text">Airport</h4><br/><!-- wp:tsjippy-forms/input {"type":"radio","name":"airport2","options":[{"value":"Abuja airport","label":"Abuja"},{"value":"Kano airport","label":"Kano"},{"value":"Lagos airport","label":"Lagos"},{"value":"Jos airport","label":"Jos"}],"blockId":"765","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input checkbox-wrapper" data-blockid="765"><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="airport2" value="Abuja airport" class="formbuilder" autocomplete="on" data-blockid="765"/>Abuja</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="airport2" value="Kano airport" class="formbuilder" autocomplete="on" data-blockid="765"/>Kano</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="airport2" value="Lagos airport" class="formbuilder" autocomplete="on" data-blockid="765"/>Lagos</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="airport2" value="Jos airport" class="formbuilder" autocomplete="on" data-blockid="765"/>Jos</label>%options-placeholder%</div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Flight Number","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"766","formbuilderChild":true} -->
<label data-blockid="766"><h4 class="label-text">Flight Number</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"flightnr2","inputAttributes":{"pattern":"^[a-zA-Z]{2}u005cs*[0-9]{1,4}u005cs*$","oninvalid":"this.setCustomValidity('Please add your flight number in the format AA1234')","oninput":"this.setCustomValidity('')"},"blockId":"767","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="flightnr2" data-blockid="767" autocomplete="on" pattern="^[a-zA-Z]{2}u005cs*[0-9]{1,4}u005cs*$" oninvalid="this.setCustomValidity('Please add your flight number in the format AA1234')" oninput="this.setCustomValidity('')" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Flight date","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"date"},"blockId":"768","formbuilderChild":true} -->
<label data-blockid="768"><h4 class="label-text">Flight date</h4><br/><!-- wp:tsjippy-forms/input {"type":"date","name":"flightdate2","blockId":"769","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="date" name="flightdate2" data-blockid="769" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Flight time","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"time"},"blockId":"770","formbuilderChild":true} -->
<label data-blockid="770"><h4 class="label-text">Flight time</h4><br/><!-- wp:tsjippy-forms/input {"type":"time","name":"flighttime2","blockId":"771","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="time" name="flighttime2" data-blockid="771" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label --></div>
<!-- /wp:group -->

<!-- wp:group {"blockId":"773","hidden":true,"formbuilderChild":true,"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"blockId":"774","formbuilderChild":true} -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:tsjippy-forms/label {"text":"Where will you spend the night?","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"775","formbuilderChild":true} -->
<label data-blockid="775"><h4 class="label-text">Where will you spend the night?</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"travel[15][from]","inputAttributes":{"placeholder":"1 some street, some state","pattern":".{3,},.{3,}","oninvalid":"this.setCustomValidity('Please add your streetname, then a comma, then your statename')","oninput":"this.setCustomValidity('')"},"blockId":"776","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[15][from]" data-blockid="776" autocomplete="on" placeholder="1 some street, some state" pattern=".{3,},.{3,}" oninvalid="this.setCustomValidity('Please add your streetname, then a comma, then your statename')" oninput="this.setCustomValidity('')" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/input {"type":"date","name":"travel[15][date]","blockId":"777","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="date" name="travel[15][date]" data-blockid="777" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[15][flightnr]","blockId":"781","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[15][flightnr]" data-blockid="781" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"time","name":"travel[15][time]","blockId":"782","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="time" name="travel[15][time]" data-blockid="782" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"travel[15][to]","blockId":"783","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="travel[15][to]" data-blockid="783" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></div>
<!-- /wp:group -->

<!-- wp:tsjippy-forms/label {"text":"Final destination","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"785","hidden":true,"formbuilderChild":true} -->
<label data-blockid="785"><h4 class="label-text">Final destination</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"final_destination_2","inputAttributes":{"placeholder":"1 some street, some state","pattern":".{3,},.{3,}","oninvalid":"this.setCustomValidity('Please add your streetname, then a comma, then your statename')","oninput":"this.setCustomValidity('')"},"blockId":"786","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="final_destination_2" data-blockid="786" autocomplete="on" placeholder="1 some street, some state" pattern=".{3,},.{3,}" oninvalid="this.setCustomValidity('Please add your streetname, then a comma, then your statename')" oninput="this.setCustomValidity('')" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label --></div>
<!-- /wp:tsjippy-forms/formstep -->

<!-- wp:tsjippy-forms/formstep {"text":"Final","onlyOnInherited":true,"blockId":"1349","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-formstep step-hidden" data-blockid="1349"><h3>Final</h3><!-- wp:tsjippy-forms/label {"text":"Any final remarks?","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"textarea"},"onlyOnInherited":true,"blockId":"1352","formbuilderChild":true} -->
<label data-blockid="1352"><h4 class="label-text">Any final remarks?</h4><br/><!-- wp:tsjippy-forms/input {"type":"textarea","name":"notes","onlyOnInherited":true,"blockId":"1353","formbuilderChild":true} -->
<textarea class="wp-block-tsjippy-forms-input" type="textarea" name="notes" data-blockid="1353" autocomplete="on">%value-placeholder%</textarea>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label --></div>
<!-- /wp:tsjippy-forms/formstep --><div class="multi-step-controls"><div class="multi-step-controls-wrapper"><div style="flex:1px"><button type="button" class="button hidden previous-button">Previous</button></div><div class="step-wrapper" style="flex:1px;text-align:center;margin:auto"><span class="step active"></span><span class="step"></span><span class="step"></span><span class="step"></span></div><div style="flex:1px"><button type="button" class="button next-button">Next</button><div class="submit-wrapper"><button type="button" class="button form-submit hidden">Submit travelform</button></div></div></div></div></form>
<!-- /wp:tsjippy-forms/formbuilder -->
HTML,
    ]
);