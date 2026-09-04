<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', function () {
    register_block_pattern_category(
        'tsjippy',
        [
            'label' => __('Tsjippy Forms', 'tsjippy'),
        ]
    );
});

/**
 * Accommodation form
 */
register_block_pattern(
    'tsjippy/accommodation-form',
    [
        'title'       => __('Accommodation Form', 'tsjippy'),
        'description' => __('Accommodation Booking Form', 'tsjippy'),
        'categories'  => ['tsjippy'],
        'content'     => <<<'HTML'
<!-- wp:tsjippy-forms/formbuilder {"postId":30236,"submission_message":"Successfully received your request","name":"Accommodation Reservations","auto_archive_element":"0","auto_archive_value":"%today%-3days","metadata":{"categories":["tsjippy"],"patternName":"tsjippy/accommodation-form","name":"Accommodation Form"},"blockId":"19"} -->
<form method="post" target="_self" autocomplete="true" data-formname="Accommodation Reservations" data-blockid="19" class="wp-block-tsjippy-forms-formbuilder"><input type="hidden" name="block-id" value="19"/><input type="hidden" name="post-id" value="30236"/><!-- wp:tsjippy-forms/label {"text":"Your name","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"295","formbuilderChild":true} -->
<label data-blockid="295"><h4 class="label-text">Your name</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"name","inputAttributes":{"list":"usernames"},"dynamic_value":"display_name","required":true,"blockId":"296","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="name" required data-blockid="296" autocomplete="on" list="usernames" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Your e-mail","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"email"},"blockId":"297","formbuilderChild":true} -->
<label data-blockid="297"><h4 class="label-text">Your e-mail</h4><br/><!-- wp:tsjippy-forms/input {"type":"email","name":"email","inputAttributes":{"list":"useremails"},"dynamic_value":"user_email","blockId":"298","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="email" name="email" data-blockid="298" autocomplete="on" list="useremails" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Your phonenumber(s)","childAttr":{"multiple":true,"add_button_content":"+","remove_button_content":"-","type":"tel"},"blockId":"299","formbuilderChild":true} -->
<label data-blockid="299"><h4 class="label-text">Your phonenumber(s)</h4><br/><!-- wp:tsjippy-forms/input {"type":"tel","name":"phonenumbers","dynamic_value":"phonenumbers","multiple":true,"required":true,"blockId":"0ff0cd7d-d927-4d37-a22c-04e07fd49e04","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input option-wrapper"><ul class="list-selection-list">%value-placeholder%</ul><div class="multi-text-input-wrapper"><input class="wp-block-tsjippy-forms-input" type="tel" name="phonenumbers" required data-blockid="0ff0cd7d-d927-4d37-a22c-04e07fd49e04" autocomplete="on" value="%value-placeholder%"/><button type="button" class="small add-list-selection hidden">add</button></div></div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:heading {"level":4,"blockId":"301","formbuilderChild":true} -->
<h4 class="wp-block-heading">Which accommodation do you want to book?<br>(Select a location to display a calendar of availabilities)</h4>
<!-- /wp:heading -->

<!-- wp:tsjippy-bookings/accomodation {"bookingSubjects":[24522,24523,24524,24530],"name":"accommodation","blockId":"489","formbuilderChild":true} /-->

<!-- wp:tsjippy-forms/label {"text":"Number of adults","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"number"},"blockId":"309","formbuilderChild":true} -->
<label data-blockid="309"><h4 class="label-text">Number of adults</h4><br/><!-- wp:tsjippy-forms/input {"type":"number","name":"adults","required":true,"blockId":"310","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="number" name="adults" required data-blockid="310" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Number of children","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"number"},"blockId":"311","formbuilderChild":true} -->
<label data-blockid="311"><h4 class="label-text">Number of children</h4><br/><!-- wp:tsjippy-forms/input {"type":"number","name":"children","required":true,"blockId":"312","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="number" name="children" required data-blockid="312" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Any remarks","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"textarea"},"blockId":"317","formbuilderChild":true} -->
<label data-blockid="317"><h4 class="label-text">Any remarks</h4><br/><!-- wp:tsjippy-forms/input {"type":"textarea","name":"remarks","blockId":"318","formbuilderChild":true} -->
<textarea class="wp-block-tsjippy-forms-input" type="textarea" name="remarks" data-blockid="318" autocomplete="on">%value-placeholder%</textarea>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/input {"type":"radio","name":"payment status","options":[{"value":"paid","label":"Paid"},{"value":"not paid","label":"Not paid"},{"value":"free","label":"Free"},{"value":"Internal","label":"Internal"}],"blockId":"1433","hidden":true,"formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input checkbox-wrapper" data-blockid="1433"><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="payment status" value="paid" class="formbuilder" autocomplete="on" data-blockid="1433"/>Paid</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="payment status" value="not paid" class="formbuilder" autocomplete="on" data-blockid="1433"/>Not paid</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="payment status" value="free" class="formbuilder" autocomplete="on" data-blockid="1433"/>Free</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="payment status" value="Internal" class="formbuilder" autocomplete="on" data-blockid="1433"/>Internal</label>%options-placeholder%</div>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"payment_amount","blockId":"1434","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="payment_amount" data-blockid="1434" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"textarea","name":"payment_details","inputAttributes":{"value":"Please pay to:Account Name:   SIMAccount Number: 1010365516Bank:Â           Zenith bankDescription:    Stay of %name% in %accommodation% from %start-date% till %end-date%"},"dynamic_value":"Account Name:      SIMrnAccount Number:  1010365516rnBank:                         Zenith bankrnDescription:             Stay of %name% in %subject% %duration%","blockId":"1435","hidden":true,"formbuilderChild":true} -->
<textarea class="wp-block-tsjippy-forms-input" type="textarea" name="payment_details" data-blockid="1435" autocomplete="on">Please pay to:Account Name:   SIMAccount Number: 1010365516Bank:Â           Zenith bankDescription:    Stay of %name% in %accommodation% from %start-date% till %end-date%</textarea>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"price","blockId":"1436","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="price" data-blockid="1436" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/input {"type":"number","name":"user-id","dynamic_value":"user_id","blockId":"490","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="number" name="user-id" data-blockid="490" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/datalist {"id":"usernames","options_dynamic":"all_users","blockId":"491","formbuilderChild":true} -->
<datalist class="wp-block-tsjippy-forms-datalist" id="usernames" data-blockid="491">%options-placeholder%</datalist>
<!-- /wp:tsjippy-forms/datalist -->

<!-- wp:tsjippy-forms/datalist {"id":"useremails","options_dynamic":"emails","blockId":"492","formbuilderChild":true} -->
<datalist class="wp-block-tsjippy-forms-datalist" id="useremails" data-blockid="492">%options-placeholder%</datalist>
<!-- /wp:tsjippy-forms/datalist -->

<!-- wp:tsjippy-forms/datalist {"id":"userphones","options_dynamic":"All phonenumbers","blockId":"493","formbuilderChild":true} -->
<datalist class="wp-block-tsjippy-forms-datalist" id="userphones" data-blockid="493">%options-placeholder%</datalist>
<!-- /wp:tsjippy-forms/datalist --><div class="submit-wrapper"><button type="button" class="button form-submit">Submit Accommodation Reservations</button></div></form>
<!-- /wp:tsjippy-forms/formbuilder -->
HTML,
    ]
);

/**
 * Generics form
 */
register_block_pattern(
    'tsjippy/generics-form',
    [
        'title'       => __('Generics Form', 'tsjippy'),
        'description' => __('User Details Form', 'tsjippy'),
        'categories'  => ['tsjippy'],
        'content'     => <<<'HTML'
<!-- wp:tsjippy-forms/formbuilder {"postId":29989,"submission_message":"Succesfully saved your generic info","submission_id":false,"name":"Generic info","user_meta":true,"auto_archive_element":"0","blockId":"10"} -->
<form method="post" target="_self" autocomplete="true" data-formname="Generic info" data-blockid="10" class="wp-block-tsjippy-forms-formbuilder"><input type="hidden" name="block-id" value="10"/><input type="hidden" name="post-id" value="29989"/><!-- wp:tsjippy-forms/label {"text":"First Name","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"124","formbuilderChild":true} -->
<label data-blockid="124"><h4 class="label-text">First Name</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"first_name","required":true,"blockId":"125","formbuilderChild":true,"remindByEmail":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="first_name" required data-blockid="125" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Last Name","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"126","formbuilderChild":true} -->
<label data-blockid="126"><h4 class="label-text">Last Name</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"last_name","required":true,"blockId":"127","formbuilderChild":true,"remindByEmail":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="last_name" required data-blockid="127" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Primary E-mail Address","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"email"},"blockId":"128","formbuilderChild":true} -->
<label data-blockid="128"><h4 class="label-text">Primary E-mail Address</h4><br/><!-- wp:tsjippy-forms/input {"type":"email","name":"user_email","required":true,"blockId":"129","formbuilderChild":true,"notChild":true,"remindByEmail":true} -->
<input class="wp-block-tsjippy-forms-input" type="email" name="user_email" required data-blockid="129" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Secondary E-mail Address","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"email"},"blockId":"130","formbuilderChild":true} -->
<label data-blockid="130"><h4 class="label-text">Secondary E-mail Address</h4><br/><!-- wp:tsjippy-forms/input {"type":"email","name":"email","required":true,"blockId":"131","formbuilderChild":true,"notChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="email" name="email" required data-blockid="131" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Birthday","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"date"},"blockId":"132","formbuilderChild":true} -->
<label data-blockid="132"><h4 class="label-text">Birthday</h4><br/><!-- wp:tsjippy-forms/input {"type":"date","name":"birthday","inputAttributes":{"max":"%today%"},"required":true,"blockId":"133","formbuilderChild":true,"remindByEmail":true} -->
<input class="wp-block-tsjippy-forms-input" type="date" name="birthday" required data-blockid="133" autocomplete="on" max="%today%" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/input {"type":"checkbox","name":"age_preference","options":[{"value":"Hide_age","label":"Hide my age"}],"blockId":"134","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input checkbox-wrapper" data-blockid="134"><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="checkbox" name="age_preference" value="Hide_age" class="formbuilder" autocomplete="on" data-blockid="134"/>Hide my age</label>%options-placeholder%</div>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/label {"text":"Gender","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"radio"},"blockId":"135","formbuilderChild":true} -->
<label data-blockid="135"><h4 class="label-text">Gender</h4><br/><!-- wp:tsjippy-forms/input {"type":"radio","name":"gender","options":[{"value":"Male","label":"Male"},{"value":"Female","label":"Female"}],"onlyOnInherited":true,"blockId":"136","formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input checkbox-wrapper" data-blockid="136"><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="gender" value="Male" class="formbuilder" autocomplete="on" data-blockid="136"/>Male</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="radio" name="gender" value="Female" class="formbuilder" autocomplete="on" data-blockid="136"/>Female</label>%options-placeholder%</div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Phonenumber","childAttr":{"multiple":true,"add_button_content":"+","remove_button_content":"-","type":"tel"},"blockId":"138","formbuilderChild":true} -->
<label data-blockid="138"><h4 class="label-text">Phonenumber</h4><br/><!-- wp:tsjippy-forms/input {"type":"tel","name":"phonenumbers","inputAttributes":{"pattern":"+[0-9]{9,}"},"multiple":true,"required":true,"blockId":"74d89683-81a1-453f-99dc-ab34289ce334","formbuilderChild":true,"notChild":true,"remindByEmail":true} -->
<div class="wp-block-tsjippy-forms-input option-wrapper"><ul class="list-selection-list">%value-placeholder%</ul><div class="multi-text-input-wrapper"><input class="wp-block-tsjippy-forms-input" type="tel" name="phonenumbers" required data-blockid="74d89683-81a1-453f-99dc-ab34289ce334" autocomplete="on" pattern="+[0-9]{9,}" value="%value-placeholder%"/><button type="button" class="small add-list-selection hidden">add</button></div></div>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:button {"blockId":"154","formbuilderChild":true} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Advanced options</a></div>
<!-- /wp:button -->

<!-- wp:heading {"level":4,"blockId":"155","hidden":true,"formbuilderChild":true} -->
<h4 class="wp-block-heading">Privacy preferences</h4>
<!-- /wp:heading -->

<!-- wp:tsjippy-forms/input {"type":"checkbox","name":"privacy_preference","options":[{"value":"hide_profile_picture","label":"Hide my profile picture"},{"value":"hide_name","label":"Hide my name"},{"value":"hide_location","label":"Hide my address"},{"value":"hide_phone","label":"Hide my phone numbers"},{"value":"hide_ministry","label":"Hide my ministries"},{"value":"hide_birthday","label":"Hide my birthday"},{"value":"hide_age","label":"Hide my age"},{"value":"hide_anniversary","label":"Hide my SIM Nigeria anniversary"}],"blockId":"157","hidden":true,"formbuilderChild":true} -->
<div class="wp-block-tsjippy-forms-input checkbox-wrapper" data-blockid="157"><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="checkbox" name="privacy_preference" value="hide_profile_picture" class="formbuilder" autocomplete="on" data-blockid="157"/>Hide my profile picture</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="checkbox" name="privacy_preference" value="hide_name" class="formbuilder" autocomplete="on" data-blockid="157"/>Hide my name</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="checkbox" name="privacy_preference" value="hide_location" class="formbuilder" autocomplete="on" data-blockid="157"/>Hide my address</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="checkbox" name="privacy_preference" value="hide_phone" class="formbuilder" autocomplete="on" data-blockid="157"/>Hide my phone numbers</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="checkbox" name="privacy_preference" value="hide_ministry" class="formbuilder" autocomplete="on" data-blockid="157"/>Hide my ministries</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="checkbox" name="privacy_preference" value="hide_birthday" class="formbuilder" autocomplete="on" data-blockid="157"/>Hide my birthday</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="checkbox" name="privacy_preference" value="hide_age" class="formbuilder" autocomplete="on" data-blockid="157"/>Hide my age</label><label class="checkbox-wrapper-label" style="margin-right:5px"><input type="checkbox" name="privacy_preference" value="hide_anniversary" class="formbuilder" autocomplete="on" data-blockid="157"/>Hide my SIM Nigeria anniversary</label>%options-placeholder%</div>
<!-- /wp:tsjippy-forms/input -->

<!-- wp:tsjippy-forms/label {"text":"Nickname","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"blockId":"158","hidden":true,"formbuilderChild":true} -->
<label data-blockid="158"><h4 class="label-text">Nickname</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"nickname","blockId":"159","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="nickname" data-blockid="159" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/input {"type":"text","name":"display_name","blockId":"160","hidden":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="display_name" data-blockid="160" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --><div class="submit-wrapper"><button type="button" class="button form-submit">Submit Generic info</button></div></form>
<!-- /wp:tsjippy-forms/formbuilder -->
HTML,
    ]
);

/**
 * family form
 */
register_block_pattern(
    'tsjippy/family-form',
    [
        'title'       => __('Family Form', 'tsjippy'),
        'description' => __('Family Details Form', 'tsjippy'),
        'categories'  => ['tsjippy'],
        'content'     => <<<'HTML'
<!-- wp:tsjippy-forms/label {"text":"Family name","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"text"},"onlyOnInherited":true,"formbuilderChild":true} -->
<label><h4 class="label-text">Family name</h4><br/><!-- wp:tsjippy-forms/input {"type":"text","name":"family_name","dynamic_value":"last_name","onlyOnInherited":true,"formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="text" name="family_name" autocomplete="on" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/label {"text":"Upload a family picture","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":""},"onlyOnInherited":true,"formbuilderChild":true} -->
<label><h4 class="label-text">Upload a family picture</h4><br/><!-- wp:tsjippy-forms/file {"name":"family_picture","formbuilderChild":true} /--></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:tsjippy-forms/formbuilder {"postId":29991,"submission_message":"Succesfully saved your family info","submission_id":false,"name":"Family","user_meta":true,"auto_archive_element":"0","blockId":"12"} -->
<form method="post" target="_self" autocomplete="true" data-formname="Family" data-blockid="12" class="wp-block-tsjippy-forms-formbuilder"><input type="hidden" name="block-id" value="12"/><input type="hidden" name="post-id" value="29991"/><!-- wp:heading {"level":4,"onlyOnInherited":true,"blockId":"171","formbuilderChild":true} -->
<h4 class="wp-block-heading">Spouse</h4>
<!-- /wp:heading -->

<!-- wp:tsjippy-forms/select {"name":"partner","options_dynamic":"Potential spouses","dynamic_selected_value":"partner","onlyOnInherited":true,"blockId":"172","formbuilderChild":true} -->
<select class="wp-block-tsjippy-forms-select" name="partner" data-blockid="172"><option value="">Select an option</option><option></option>%options-placeholder%</select>
<!-- /wp:tsjippy-forms/select -->

<!-- wp:tsjippy-forms/label {"text":"Married since","childAttr":{"multiple":false,"add_button_content":"+","remove_button_content":"-","type":"date"},"onlyOnInherited":true,"blockId":"173","hidden":true,"formbuilderChild":true} -->
<label data-blockid="173"><h4 class="label-text">Married since</h4><br/><!-- wp:tsjippy-forms/input {"type":"date","name":"weddingdate","inputAttributes":{"max":"%today%"},"blockId":"174","formbuilderChild":true} -->
<input class="wp-block-tsjippy-forms-input" type="date" name="weddingdate" data-blockid="174" autocomplete="on" max="%today%" value="%value-placeholder%"/>
<!-- /wp:tsjippy-forms/input --></label>
<!-- /wp:tsjippy-forms/label -->

<!-- wp:heading {"level":4,"onlyOnInherited":true,"blockId":"175","formbuilderChild":true} -->
<h4 class="wp-block-heading">Child</h4>
<!-- /wp:heading -->

<!-- wp:tsjippy-forms/select {"name":"children","options_dynamic":"Potential children","multiple":true,"dynamic_selected_value":"children","onlyOnInherited":true,"blockId":"176","formbuilderChild":true} -->
<select class="wp-block-tsjippy-forms-select" name="children" multiple data-blockid="176"><option value="">Select an option</option><option></option>%options-placeholder%</select>
<!-- /wp:tsjippy-forms/select -->

<!-- wp:heading {"level":4,"onlyOnInherited":true,"blockId":"1412","formbuilderChild":true} -->
<h4 class="wp-block-heading">Siblings</h4>
<!-- /wp:heading -->

<!-- wp:tsjippy-forms/select {"name":"siblings","options_dynamic":"all_users","multiple":true,"dynamic_selected_value":"siblings","onlyOnInherited":true,"blockId":"1413","formbuilderChild":true} -->
<select class="wp-block-tsjippy-forms-select" name="siblings" multiple data-blockid="1413"><option value="">Select an option</option><option></option>%options-placeholder%</select>
<!-- /wp:tsjippy-forms/select --><div class="submit-wrapper"><button type="button" class="button form-submit">Submit Family</button></div></form>
<!-- /wp:tsjippy-forms/formbuilder -->
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