# Changelog
## [Unreleased] - yyyy-mm-dd

### Added

### Changed

### Fixed

### Updated

## [11.8.7] - 2026-07-15


### Changed
- no need to serilize before insertInDb

### Fixed
- form submit bug

## [11.8.6] - 2026-07-15


## [11.8.5] - 2026-07-14


## [11.8.4] - 2026-07-14


### Fixed
- load form when no form is given

## [11.8.3] - 2026-07-13


### Fixed
- bug

## [11.8.2] - 2026-07-13


## [11.8.1] - 2026-07-13


## [11.8.0] - 2026-07-12


## [11.7.9] - 2026-07-12


### Changed
- uses %TEXTDOMAIN% as placeholder for translation domain

### Fixed
- bug

## [11.7.8] - 2026-07-11


## [11.7.7] - 2026-07-11


### Changed
- select roles and users in one dropdown

### Fixed
- form results block

## [11.7.6] - 2026-07-10


### Changed
- default page content

### Fixed
- block name change
- check if edit screen

## [11.7.5] - 2026-07-10


## [11.7.4] - 2026-07-10


### Added
- block icon

### Changed
- directly print form to screen

### Fixed
- forms selector
- event listener in dynamic js

## [11.7.3] - 2026-07-06


### Changed
- blocks to php only blocks
- abspath to get_home_path()

### Updated
- css

## [11.7.2] - 2026-07-03


### Changed
- add default pages when not present

## [11.7.1] - 2026-07-03


### Changed
- rewrite element html builder

## [11.7.0] - 2026-07-02


### Added
- no-border class to table

## [11.6.9] - 2026-07-02


### Added
- attribute escaping
- usage of get_edit_profile_url
- activate shared code function
- support for tab buttons outside clone-divs

### Changed
- plugin tested up to 7.0
- replaced in_array with isset
- use array_intersect_key
- select users and roles in one drop down

### Fixed
- submission bug
- isset bug
- str_starts_with error
- bugs

## [11.6.8] - 2026-06-26


### Changed
- sanitize post on original function
- print close buttont in funtion

## [11.6.7] - 2026-06-25


### Changed
- some extra db caching

## [11.6.6] - 2026-06-24


### Fixed
- conditional email senders

## [11.6.5] - 2026-06-24


## [11.6.4] - 2026-06-23


### Changed
- hooks

## [11.6.3] - 2026-06-23


### Changed
- order form elements logic

### Fixed
- addElement code

## [11.6.2] - 2026-06-23


### Changed
- message timer

## [11.6.1] - 2026-06-23


### Changed
- append table to parent node
- implemented db caching
- implemented db caching
- implemented db caching
- replaced wpdb->update with updateDbFunction
- find phonenumber by slug phone_number

### Fixed
- archive whole splitted submission
- addElement

## [11.5.9] - 2026-06-21


## [11.5.8] - 2026-06-20


### Fixed
- get all user_meta including family_meta

## [11.5.7] - 2026-06-19


### Added
- request sanitazion

## [11.5.6] - 2026-06-18


### Changed
- hook and filter name update
- hook and filter name update
- code layout

### Fixed
- admin date page

## [11.5.5] - 2026-06-15


### Fixed
- problem when adding an attrbute with an empty value

## [11.5.4] - 2026-06-15


## [11.5.3] - 2026-06-15


### Fixed
- do not prefix family meta keys
- set value of multiple elements

## [11.5.2] - 2026-06-15


### Fixed
- unserialize conditions

## [11.5.1] - 2026-06-15


### Fixed
- typo
- bug when next element is null

## [11.5.0] - 2026-06-15


### Added
- 'tsjippy-file-upload-delete-permission' filter

### Changed
- file uploads are now only done of form submit when not saving in user meta

### Fixed
- add raw html

## [11.4.9] - 2026-06-13


## [11.4.8] - 2026-06-13


### Changed
- implemented plugin check remmendations 1
- do not use  in classes
- nice page selector
- moved phonenumber transform to signal plugin
- better handling of storing meta form submissions

### Fixed
- shared code loader
- activation hook
- do not show data tab i not needed admin menu
- use correct shortcodes on auto created pages
- prefix data stored in meta table
- export forms
- unmatched ob_start() call

## [11.4.7] - 2026-06-11


### Added
- placeholder for textdomain
- user, post and rest_meta prefixing

### Changed
- prefixed post metas and shortcodes
- no submit button when not needed

### Fixed
- value

## [11.4.6] - 2026-06-09


### Added
- usage of wpdb->prepare for all queries
- shared functionality loader
- default value

### Changed
- comply to coding standards
- code layout
- namespaced all constants
- sanitize all posts and get vars
- html to domelement

### Fixed
- spacing problem
- foreach bug
- space before dot bug
- column setting initial name when indexed columnname
- form submision archiving

## [11.4.5] - 2026-06-03


### Added
- escaping functions
- echo escaping

### Changed
- addSaveButton with echo param

## [11.4.4] - 2026-06-01


### Changed
- merged hooks.md into readme.md

### Fixed
- added domain to __ function
- construction should have atts

## [11.4.3] - 2026-06-01


### Changed
- loading libraries is now done in shared-functionality plugin

## [11.4.2] - 2026-06-01


### Fixed
- empty array bug

## [11.4.1] - 2026-06-01


### Changed
- use named params for userSelect function

### Fixed
- cloning tabbed contents

## [11.4.0] - 2026-05-30


### Changed
- do not store get_plugin_data in global variable

## [11.3.9] - 2026-05-29


### Added
- wp_unslash

## [11.3.8] - 2026-05-28


### Fixed
- edit form element

## [11.3.7] - 2026-05-28


### Fixed
- store form slug

## [11.3.6] - 2026-05-28


### Fixed
- bug when cloning empty node

## [11.3.5] - 2026-05-28


### Fixed
- update actions
- ?? bug
- viewhash
- bugs when using invalid userid
- js creation bug

## [11.3.3] - 2026-05-27


### Fixed
- form loader

## [11.3.2] - 2026-05-27


### Fixed
- shortcode settings bug

## [11.3.1] - 2026-05-26


### Fixed
- html parsing errors

## [11.3.0] - 2026-05-26


### Fixed
- non exiting array index bug

## [11.2.9] - 2026-05-25


### Fixed
- form results constructor
- bug when column setting has no id
- error when not formdata id set

## [11.2.8] - 2026-05-24


### Fixed
- constructor bug

## [11.2.7] - 2026-05-23


### Fixed
- empty prev el error

## [11.2.6] - 2026-05-22


### Fixed
- bugs
- undeined error

## [11.2.5] - 2026-05-21


### Fixed
- test if objects are not null
- bugs

## [11.2.4] - 2026-05-20


### Fixed
- class attribute fixes

## [11.2.3] - 2026-05-16


### Fixed
- after update

## [11.2.2] - 2026-05-14


### Changed
- date( to gmdate(

### Fixed
- initialization bug

## [11.2.1] - 2026-05-13


### Fixed
- initializing error

## [11.1.9] - 2026-05-12


## [11.1.8] - 2026-05-12


### Fixed
- empty formdata error

## [11.1.7] - 2026-05-12


### Changed
- permission callback for rest api

## [11.1.6] - 2026-05-11


## [11.1.5] - 2026-05-11


### Changed
- replaced sortable by np install
- removed soratbel dependicy
- defined type of class properties
- removed admin login for cron

### Fixed
- update comparisson
- hide columns that should be hidden
- do not load form on shortcode results table

### Updated
- css

## [11.1.4] - 2026-05-08


## [11.1.3] - 2026-05-08


### Fixed
- form import

## [11.1.2] - 2026-05-08


### Changed
- js update

### Fixed
- account page retrieval

## [11.1.1] - 2026-05-08


## [11.1.0] - 2026-05-08


### Fixed
- show submissions without split on slitted forms

## [11.0.9] - 2026-05-08


### Fixed
- update form submisison value

## [11.0.8] - 2026-05-07


### Changed
- replaced sweetalert

### Fixed
- reset table visibility

## [11.0.7] - 2026-05-06


### Changed
- do not use formData->name instead of ->formName
- ->formId to ->formData->id

### Fixed
- use slug and not name
- transform by slug

## [11.0.6] - 2026-05-06


## [11.0.5] - 2026-05-06


### Changed
- form to slug

## [11.0.4] - 2026-05-05


## [11.0.2] - 2026-05-03


### Changed
- removed the redirection at activation as it is done by the share plugin
- use shared github workflows

## [11.0.1] - 2026-05-01


### Added
- redirection to settings page on plugin activation

### Changed
- main plugin name from sim-base to tsjippy-shared-functionality
- sim prefix to tsjippy prefix
- base namespace to TSJIPPY
- filternames to include tsjippy
- block apt to version 3
- PLUGINCONSTANT value
- lib updates
- table columns
- open url in new tab
- exclude .vscode from releases
- updated github workflow versions

### Fixed
- show archived submissions

## [10.0.8] - 2026-04-16


### Changed
- faster value retrieval

## [10.0.7] - 2026-03-24


### Fixed
- bug in sql query when no splitted elements

## [10.0.6] - 2026-03-23


### Changed
- refactor sql queries

### Fixed
- formbuilder bugs
- archive sub-submissions

## [10.0.5] - 2026-03-11


### Fixed
- unserialization error

## [10.0.4] - 2026-03-05


### Fixed
- only update edittime if needed

## [10.0.3] - 2026-03-04


### Fixed
- edit form results of splitted values

## [10.0.2] - 2026-03-04


### Added
- get form by submission id

### Changed
- more efficient code

### Fixed
- error in retrieving submission when no toColumn
- repeated submisison when failure

## [10.0.1] - 2026-02-11


### Fixed
- allow submissions by non-logged in users
- form auto archiving

## [10.0.0] - 2026-01-24


### Changed
- all form data from one query

### Fixed
- sorting
- order on splitted columns

## [9.1.4] - 2026-01-12


### Fixed
- form results page retrieval
- table result sorting

## [9.1.3] - 2026-01-09


### Changed
- allow sim-formstable-should-show filter to echo to screen

## [9.1.1] - 2025-12-19


### Fixed
- form export and imports
- bug in formbuilder when multi html enabled

## [9.1.0] - 2025-12-18


### Fixed
- conditional e-mail adresses
- form builder form

## [9.0.9] - 2025-12-12


### Fixed
- replace element ids with element names

## [9.0.8] - 2025-12-12


### Changed
- sim_before_saving_formdata filter to sim_before_submitting_formdata

## [9.0.7] - 2025-12-12


### Changed
- clean up db

## [9.0.6] - 2025-12-11


### Fixed
- bugs

## [9.0.5] - 2025-12-08


### Fixed
- bugs

## [9.0.4] - 2025-12-02


### Fixed
- bug in e-mail settings

## [9.0.3] - 2025-12-01


### Fixed
- multiple nodes from raw htlm

## [9.0.2] - 2025-11-27


### Added
- logging

### Fixed
- user id wahala

## [9.0.1] - 2025-11-21


### Changed
- composer updated
- .gitignore settings

## [9.0.0] - 2025-11-21


### Added
- insert user id element on new forms
- support for multiple values with same key

### Changed
- store form submission values in seperate table
- formresults to submission
- split data on submission
- store values on element id not on name
- store form results by element id not name
- more efficient data filtering
- change table size over AJAX

### Fixed
- store-non-numeric element id
- nug with form submission dat which is an array
- update bug
- column settings for defaults
- bug with windows paths
- problemen when .local addresses
- bugs
- issuew ith formbuilder

## [8.9.6] - 2025-11-07


### Changed
- use wpdb->prepare
- bug fixes

### Fixed
- allow multiple selections when editing sub entry

## [8.9.5] - 2025-11-06


### Added
- async form loading

## [8.9.4] - 2025-11-04


### Fixed
- hide columns

## [8.9.3] - 2025-11-04


### Fixed
- copy form input

## [8.9.2] - 2025-11-04


### Changed
- only redirect to account page when not requesting another page

### Fixed
- get input html

## [8.9.1] - 2025-11-03


### Changed
- data-id to data-submission-id

## [8.9.0] - 2025-11-03


### Fixed
- make sure form result display element are always added

## [8.8.9] - 2025-11-03


### Changed
- js listeneres order
- removed redundant code

### Fixed
- some bugs

## [8.8.8] - 2025-11-01


### Changed
- code cleanup
- render loader image using js

### Fixed
- default selected option for checkboxes and radios

## [8.8.6] - 2025-10-30


### Added
- filter for form results table

### Changed
- removed form reset option
- remove db columns on update
- use new family class
- supply meta value to uploader

### Fixed
- creating dynamic js conditions with shortcodes
- do not serialize empty data
- bug when retrieving meta formdate for other user
- bug when retrieving non indexed meta value

## [8.8.5] - 2025-10-27


### Fixed
- potential bug when no element found

## [8.8.4] - 2025-10-25


### Changed
- replace _ with space

## [8.8.3] - 2025-10-23


### Added
- warning when having form reminders without defined e-mail

### Changed
- make recurring form submissions optional
- meta form reminders now possible

### Fixed
- check for empty arrays in conditions
- multi input valuaes
- bug in result view permissions
- multielement wrap with radios in it

## [8.8.2] - 2025-10-20


### Changed
- hide fields if needed

## [8.8.1] - 2025-10-20


### Added
- form reminder settings form

### Changed
- form reminders in seperate table
- adjusted form reminder logic

### Fixed
- show index in condition form when set
-  is an object

## [8.8.0] - 2025-10-17


### Changed
- code changes to make it simpler to maintain

### Fixed
- form reminder e-mails

## [8.7.9] - 2025-10-16


### Fixed
- order result table columns

## [8.7.8] - 2025-10-16


### Fixed
- bug
- bug in updating table values

## [8.7.7] - 2025-10-16


### Added
- support for splitting on multi wrap elements

### Fixed
- bug when clonable formstep is the last of a form
- make sure form html is balanced

## [8.7.6] - 2025-10-15


### Changed
- do not add names to non-input elements

## [8.7.5] - 2025-10-15


### Fixed
- issue with a multi element withing in a multi wrapper

## [8.7.4] - 2025-10-15


### Added
- table permission per user

### Changed
- scroll to modal top

### Fixed
- copying of info elements

## [8.7.3] - 2025-10-15


### Added
- support for clonable formsteps
- support for clonable formsteps
- extra form step circles if needed

## [8.7.2] - 2025-10-14


### Fixed
- user id value

## [8.7.1] - 2025-10-14


### Fixed
- non inputs

## [8.7.0] - 2025-10-13


### Changed
- data attribute names
- dataset names

### Fixed
- store boolean values
- bugs
- form submit bug

## [8.6.9] - 2025-10-06


### Added
- copy element in form builder

### Changed
- more _ to -
- make sure db formats are correct
- cleaner formbuilder interface

### Fixed
- insert new elements in right position

## [8.6.8] - 2025-09-26


### Fixed
- error in deleting submissions

## [8.6.7] - 2025-09-26


### Fixed
- formbuilder layout

## [8.6.6] - 2025-09-26


### Changed
- classnames replace _ with -
- submit loader

## [8.6.5] - 2025-09-25


### Changed
- js generated loader

### Fixed
- conditions form loader

## [8.6.4] - 2025-09-24


### Changed
- loader image

### Fixed
- hide loaders

## [8.6.3] - 2025-09-24


### Changed
- code changes
- form exports in seperate file
- loader image

### Fixed
- form builder nice selects

## [8.6.2] - 2025-09-10


### Changed
- minimize class calls

### Fixed
- issue with archiving splitted sub entires

## [8.6.1] - 2025-08-29


### Changed
- smaller conditional js
- better minification
- removed duplicated call of extra js filter

## [8.5.9] - 2025-08-26


### Added
- multi text input defaults

### Changed
- form permission settings redesign

## [8.5.7] - 2025-08-25


### Fixed
- wrapped labels for multi text inputs

## [8.5.6] - 2025-08-25


### Added
- process elements with a default value for conditional logic

## [8.5.5] - 2025-08-25


### Fixed
- export form error
- default values

## [8.5.4] - 2025-08-06


### Changed
- less niceselect code

## [8.5.3] - 2025-07-30


### Changed
- removed must logged in requirement

## [8.5.2] - 2025-07-25


### Changed
- niceselect instatiation

### Fixed
- content issues
- issue with non-numeric subId

## [8.5.1] - 2025-07-02


### Fixed
- activa tinymce

## [8.5.0] - 2025-06-26


### Fixed
- initial active e-mail form
- display submission data when no table settings defined yet

## [8.4.9] - 2025-06-26


### Changed
- url in missing form log
- form e-mails display

### Fixed
- attachment export
- underscore replacement issue

## [8.4.8] - 2025-05-08


### Fixed
- issue when form has no version number
- delete empty forms

## [8.4.7] - 2025-04-28


### Fixed
- bug when ajax returned an error

## [8.4.6] - 2025-04-27


## [8.4.5] - 2025-04-05


### Changed
- unserialize form results before filtering them

## [8.4.4] - 2025-04-05


### Fixed
- email on field changed

## [8.4.3] - 2025-03-27


### Fixed
- do not show form builder if not loged in
- issue with values not updated

## [8.4.2] - 2025-03-27


### Added
- form submit without being logged in

## [8.4.1] - 2025-03-27


### Changed
- code reordering

## [8.4.0] - 2025-03-21


### Changed
- removed signal messaging
- add form data to wp_mail args

## [8.3.9] - 2025-03-20


### Fixed
- issue with new form

## [8.3.8] - 2025-03-19


### Fixed
- issue with form attachments

## [8.3.7] - 2025-03-17


### Fixed
- bug in email headers
- multiple atachment to e-mail
- process user id element

## [8.3.6] - 2025-03-06


### Added
- administror has admin role by default
- trim to files array

### Fixed
- rare bug in splitted formdata

## [8.3.5] - 2025-02-13


### Added
- shortcode in replacement value for forms

## [8.3.4] - 2025-02-13


### Changed
- module hooks now include module slug

## [8.3.3] - 2025-02-12


### Fixed
- only remove unnesseray whitespaces, not new lines

## [8.3.2] - 2025-02-11


## [8.3.1] - 2025-02-11


### Changed
- sim_module_updated filter to new format

## [8.3.0] - 2025-02-10


### Added
- return false if submission not found

### Changed
- replace spaces with underscores in element names

## [8.2.9] - 2025-02-10


### Fixed
- bug in formbuilder

## [8.2.8] - 2025-02-10


## [8.2.7] - 2025-02-07


### Added
- newest js import version
- 'tsjippy-table-view-permissions' filter
- filter subid

### Changed
- code clean up

### Fixed
- loaded after form submission update

## [8.2.6] - 2025-02-04


### Added
- support for inline script over AJAX

## [8.2.5] - 2025-02-03


### Fixed
- form conditions
- loop when no multi-end element

## [8.2.4] - 2025-02-03


### Fixed
- default el as split el

## [8.2.3] - 2025-01-31


### Added
- double check form ordering

### Changed
- sim_form_extra_js filter

### Fixed
- bug while adding new elements

## [8.2.2] - 2025-01-31


### Added
- edit value which did not exist on form submission
- getSubmission function

## [8.2.1] - 2025-01-30


### Added
- find element functions

## [8.2.0] - 2025-01-30


### Fixed
- formbuilder loader removal after element update
- do not replace spaces with underscores in checkbox values

## [8.1.9] - 2025-01-28


### Fixed
- updating form results

## [8.1.8] - 2025-01-27


### Fixed
- element insertion

## [8.1.7] - 2025-01-25


### Fixed
- formstep indicators
- issue with column rights setting
- form import

## [8.1.6] - 2025-01-23


### Fixed
- form id setting
- bug in editing splitted data
- database creation
- error when ' in element name
- reordering of elements
- element reordering

## [8.1.4] - 2025-01-20


### Added
- max amount of reminders

### Fixed
- load js

## [8.1.3] - 2024-12-18


## [8.1.2] - 2024-12-18


### Changed
- after update hook

## [8.1.1] - 2024-12-17


### Added
- reminder amount

## [8.1.0] - 2024-11-22


### Changed
- removed anonymous functions

## [8.0.9] - 2024-11-19


### Changed
- remove anonymous functions

## [8.08] - 2024-11-12


### Added
- default sor direction

## [8.0.7] - 2024-10-24


### Added
- login redirect action

## [8.0.6] - 2024-10-22


### Added
- support for non-nice selects

## [8.05] - 2024-10-17


### Fixed
- global css reference

### Updated
- readme
- blocks

## [8.0.4] - 2024-10-11


### Changed
- removed reference to user pages

## [8.0.3] - 2024-10-11


### Changed
- redering of asset urls

## [8.0.2] - 2024-10-09


### Changed
- code clean up

### Fixed
- form reminders

### Updated
- deps
- deps

## [8.0.0] - 2024-10-04


## [8.0.0] - 2024-10-03
