# Changelog
## [Unreleased] - yyyy-mm-dd

### Added

### Changed

### Fixed

### Updated

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
- 'sim-table-view-permissions' filter
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
