<?php
namespace SIM\FORMS;

use ParagonIE\Sodium\Core\Curve25519\Ge\P2;
use SIM;
use WP_Error;

class DisplayFormResults extends DisplayForm{
	use ExportFormResults;

	public $shortcodeTable;
	public $enriched;
	public $excelContent;
	public $currentPage;
	public $total;
	public $submission;
	public $submissions;
	public $splittedSubmissions;
	public $pageSplittedSubmissions;
	public $hiddenColumns;
	public $columnSettings;
	public $tableSettings;
	public $ownData;
	public $formEditPermissions;
	public $tableViewPermissions;
	public $tableEditPermissions;
	public $sortColumn;
	public $sortDirection;
	public $sortColumnFound;
	public $spliced;

	public function __construct($atts=[]){
		global $wpdb;
		
		$this->shortcodeTable			= $wpdb->prefix . 'sim_form_shortcodes';
		$this->enriched					= false;
		$this->sortColumn				= false;
		$this->sortDirection			= 'ASC';
		$this->spliced					= false;
		
		// call parent constructor
		parent::__construct($atts);

		$this->elementHtmlBuilder		= new ElementHtmlBuilder($this);

		wp_enqueue_style('sim_formtable_style');

		$family							= new SIM\FAMILY\Family();
		$this->user->partnerId			= $family->getPartner($this->user->ID);

		//Get personal visibility
		$this->hiddenColumns			= get_user_meta($this->user->ID, 'hidden_columns_'.$this->formData->id, true);
		
		if(function_exists('is_user_logged_in') && is_user_logged_in()){
			$this->userRoles[]	= 'everyone';//used to indicate view rights on permissions
			$this->excelContent	= [];
		}

		$this->enrichColumnSettings();

		$this->loadTableSettings();
	}

	public function getMetaKeyFormSubmissions($userId=null, $all=false){
		global $wpdb;

		// also check the users table
		$colNames	= $wpdb->get_results( "DESC {$wpdb->users}" );
		$usedCols	= [];
		foreach($colNames as &$desc){
			$desc	= $desc->Field;
		}
		
		$query				= "SELECT * FROM {$wpdb->usermeta} WHERE ";

		if(is_numeric($userId)){
			$query				= "user_id = $userId ";
		}

		$counter	= 0;
		foreach($this->formElements as $element){
			if(!in_array($element->type, $this->nonInputs) && $element->id >= 0){
				$name			= trim($element->name, '[]');

				if(in_array($name, $colNames)){
					$usedCols[]	= $name;
				}else{
					if($counter > 0){
						$query			.= "OR ";
					}


					$query			.= "meta_key = '$name' ";

					$counter++;
				}
			}
		}

		$query					= apply_filters('sim_formdata_retrieval_query', $query, $userId, $this);

		// Get results
		$result		= $wpdb->get_results($query);

		// parse results to merge based on userId
		$results	= [];
		$counter	= 0;
		foreach($result as $r){
			if(!isset($results[$r->user_id])){
				$results[$r->user_id]					= new \stdClass();
				$results[$r->user_id]->id				= $counter;
				$results[$r->user_id]->form_id			= $this->formData->id;
				$results[$r->user_id]->timecreated		= get_userdata($r->user_id)->user_registered;
				$results[$r->user_id]->timelastedited	= get_userdata($r->user_id)->user_registered;
				$results[$r->user_id]->userid			= $r->user_id;
				$results[$r->user_id]->formresults		= [
					'submissiontime'	=> $results[$r->user_id]->timecreated,
					'edittime'			=> $results[$r->user_id]->timelastedited
				];

				$counter++;
			}

			$results[$r->user_id]->formresults[$r->meta_key]	= maybe_unserialize($r->meta_value);
		}

		// now also add result from the users table
		if(!empty($usedCols)){
			$selectQuery	= 'ID,'.implode(',', $usedCols);
			$result		= $wpdb->get_results("Select $selectQuery from {$wpdb->users}");

			foreach($result as $r){	
				foreach($usedCols as $col){
					$results[$r->ID]->formresults[$col]	= maybe_unserialize($r->$col);
				}
			}
		}

		// Get the total
		$this->total			= count($results);

		// Limit the amount to 100
		if(!$all && isset($_REQUEST['page-number']) && is_numeric($_REQUEST['page-number']) && $this->total > $this->pageSize){
			$this->currentPage	= $_REQUEST['page-number'];

			if(isset($_POST['prev'])){
				$this->currentPage--;
			}
			if(isset($_POST['next'])){
				$this->currentPage++;
			}
			$start			= $this->currentPage * $this->pageSize;

			$results		= array_slice($results, $start, $this->pageSize);

			$this->spliced	= true;
		}else{
			$this->currentPage	= 0;
		}

		// sort colomn
		$this->sortColumnFound	= false;
		if($this->sortColumn){
			if($this->sortDirection != 'ASC'){
				$this->sortDirection	= 'DESC';
			}

			
		}	

		foreach($results as &$r){
			$r->formresults	= maybe_unserialize($r->formresults);
		}

		return apply_filters('sim_retrieved_formdata', $results, $userId, $this);
	}

	/**
	 * Get formresults of the current form
	 *
	 * @param	int		$userId			Optional the user id to get the results of. Default null
	 * @param	int		$submissionId	Optional a specific id. Default null
	 *
	 * @return	array					array of results
	 */
	public function getSubmissions($userId=null, $submissionId=null, $all=false){
		global $wpdb;

		if(isset($_REQUEST['all'])){
			$all	= true;
		}

		// return an already loaded submission
		if(is_numeric($submissionId) && !empty($this->submissions)){
			foreach($this->submissions as $submission){
				if($submission->id == $submissionId){
					return [$submission];
				}
			}
		}

		if($this->formData->save_in_meta){
			return $this->getMetaKeyFormSubmissions($userId, $all);
		}

		$query				= "SELECT * FROM {$this->submissionTableName} WHERE ";

		if(empty($submissionId) && !empty($_REQUEST['id'])){
			$submissionId	= $_REQUEST['id'];
		}
		
		if(is_numeric($submissionId)){
			$query .= "id='$submissionId'";
		}elseif(isset($this->formData->id)){
			$query	.= "form_id={$this->formData->id}";
		}else{
			$query	.= "1=1";
		}
		
		if(!$this->showArchived && $submissionId == null){
			$query .= " and archived=0";
		}

		// Limit the amount to 100
		if(isset($_REQUEST['page-number']) && is_numeric($_REQUEST['page-number'])){
			$this->currentPage	= $_REQUEST['page-number'];

			if(isset($_POST['prev'])){
				$this->currentPage--;
			}
			if(isset($_POST['next'])){
				$this->currentPage++;
			}
			$start	= $this->currentPage * $this->pageSize;
		}else{
			$start				= 0;
			$this->currentPage	= 0;
		}

		// sort colomn
		$this->sortColumnFound	= false;
		if($this->sortColumn){
			$colNames	= $wpdb->get_results( "DESC $this->submissionTableName" );
			foreach ( $colNames as $name ) {
				if ( $name->Field === $this->sortColumn ) {
					$this->sortColumnFound	= true;
				}
			}
			if($this->sortDirection != 'ASC'){
				$this->sortDirection	= 'DESC';
			}

			if($this->sortColumnFound){
				$query	.= " ORDER BY `$this->sortColumn` $this->sortDirection";
			}else{
				// have to get all results to be able to sort them later
				$all		= true;
			}
		}

		$query					= apply_filters('sim_formdata_retrieval_query', $query, $userId, $this);

		// Get the total
		$this->total			= $wpdb->get_var(str_replace('*', 'count(*) as total', $query));

		// add the limit only if we are not querying everything, or for a specific user or start is larger than the total
		if(!$all && empty($userId) && $start < $this->total){
			$this->spliced	= true;
			$query	.= " LIMIT $start, $this->pageSize";
		}

		// Get results
		$result	= $wpdb->get_results($query);

		foreach($result as &$r){
			$r->formresults	= unserialize($r->formresults);
		}

		$result	= apply_filters('sim_retrieved_formdata', $result, $userId, $this);

		if(is_numeric($userId)){
			// find the user id element
			$userIdKey	= $this->findUserIdElementName();

			// Form does not contain an user id field, run the query against the user who submitted the form
			if(!$userIdKey){
				$query = explode(' LIMIT', $query)[0]." and userid='$userId'";
				if(!$all){
					$query	.= " LIMIT $start, $this->pageSize";
				}
				
				$result	= $wpdb->get_results($query);
				
				if($wpdb->last_error !== ''){
					SIM\printArray($wpdb->print_error());
				}else{
					foreach($result as &$r){
						$r->formresults	= unserialize($r->formresults);
					}

					$result	= apply_filters('sim_retrieved_formdata', $result, $userId, $this);
				}
		
				return $result;
			}
		}

		// unserialize
		$keptCount		= 0;
		foreach($result as $index=>&$submission){
			if(
				is_numeric($userId)									&& 	// We only want results af a particular user
				(
					(
						isset($submission->formresults[$userIdKey])		&& 	// There is an user id in the result
						$submission->formresults[$userIdKey] != $userId		// But not the right one
					)													||
					(
						!isset($submission->formresults[$userIdKey])	&& 	// There is no user id in the result
						$submission->userid != $userId						// this user did not submit this form
					)
				)
			){
				// delete the result if we only want to keep results of a certain user and is not this user
				$shouldRemove	= apply_filters('sim_remove_formdata', true, $userId, $submission);
				if($shouldRemove){
					unset($result[$index]);

					$this->total--;
				}else{
					$keptCount++;

					// check if we have enough results left
					if($keptCount == $start + $this->pageSize){
						$result	= array_splice($result, $keptCount);
						break;
					}
				}
			}
		}

		if($wpdb->last_error !== ''){
			SIM\printArray($wpdb->print_error());
		}

		return $result;
	}

	/**
	 * Sorts an array of subissions on the sortvalue
	 *
	 * @param	array	$submissions	Array of submissions past by reference
	 */
	public function sortSubmissions(&$submissions){
		// sort if needed
		if(!$this->sortColumn || $this->sortColumnFound  || empty($submissions)){
			// sorting not needed
			return;
		}

		$sortElement	= $this->getElementByName($this->sortColumn);

		if(empty($sortElement)){
			$defaultSortElement	= $this->tableSettings->default_sort;
			$sortElement		= $this->getElementById($defaultSortElement);
		}

		$sortElementType	= $sortElement->type;

		//Sort the array
		usort($submissions, function($a, $b) use ($sortElementType){
			// ascending
			if($this->sortDirection == 'ASC'){
				if($sortElementType == 'date'){
					return strtotime($a->formresults[$this->sortColumn]) <=> strtotime($b->formresults[$this->sortColumn]);
				}
				return $a->formresults[$this->sortColumn] > $b->formresults[$this->sortColumn];
			// Decending
			}else{
				if($sortElementType == 'date'){
					return strtotime($b->formresults[$this->sortColumn]) <=> strtotime($a->formresults[$this->sortColumn]);
				}
				return $b->formresults[$this->sortColumn] > $a->formresults[$this->sortColumn];
			}
		});
	}

	/**
	 * Set formresults of the current form
	 *
	 * @param	int		$userId			Optional the user id to get the results of. Default null
	 * @param	int		$submissionId	Optional a specific id. Default null
	 * @param	bool	$all			Whether to retrieve all submissions or paged
	 * @param	bool	$force			Whether to retrieve submissions even if already done
	 */
	public function parseSubmissions($userId=null, $submissionId=null, $all=false, $force=false){
		// no need to this again
		if(!empty($this->submissions) && !$force && empty($submissionId)){
			return;
		}

		if(empty($this->formData->split)){
			$this->submissions		= $this->getSubmissions($userId, $submissionId, $all);

			$this->sortSubmissions($this->submissions);
		}else{
			$this->submissions		= $this->getSubmissions($userId, $submissionId, true);
	
			$this->processSplittedData();

			if(empty($this->splittedSubmissions) && !empty($this->submissions)){
				$this->splittedSubmissions	= $this->submissions;
			}

			$this->sortSubmissions($this->splittedSubmissions);

			if(empty($this->splittedSubmissions)){
				$this->total	= 0;
			}else{
				$this->total	= count($this->splittedSubmissions);
			}
		}

		if(!empty($this->splittedSubmissions) && count($this->splittedSubmissions) == 1){
			$this->submission	= array_values($this->splittedSubmissions)[0];
		}elseif(count($this->submissions) == 1){
			$this->submission	= array_values($this->submissions)[0];
		}elseif(!empty($submissionId)){
			$this->submission	= $this->submissions;
		}
	}

	/**
	 * creates seperate entries for each sub-submission
	 *
	 * @param	string	$splitElementName	The name of the element the results should be split on
	 */
	public function splitArrayedSubmission($splitElementName){

		//loop over all submissions
		foreach($this->submissions as $this->submission){
			if(empty($this->submission->formresults[$splitElementName])){
				continue;
			}

			$this->submission->archivedsubs	= maybe_unserialize($this->submission->archivedsubs);

			// loop over all entries of the split key
			foreach($this->submission->formresults[$splitElementName] as $subKey=>$subSubmission){
				// Should always be an array
				if(!is_array($subSubmission)){
					continue;
				}

				// Check if it has data
				$hasData	= false;
				foreach($subSubmission as $value){
					if(!empty($value)){
						$hasData = true;
						break;
					}
				}

				if(!$hasData){
					continue;
				}

				// If it has data add as a seperate item to the submission data
				$newSubmission	= clone $this->submission;

				// Check if archived
				if(
					(
						is_array($this->submission->archivedsubs) && 
						in_array($subKey, $this->submission->archivedsubs)
					)	||
					$subSubmission['archived']
				){
					if($this->showArchived || isset($_REQUEST['id'])){
						// mark the entry as archived
						$newSubmission->archived	= true;
					}else{
						// do not add an archived sub value to the results
						continue;
					}
				}

				//Check if need to display
				if(!empty($_REQUEST['subid']) && is_numeric($_REQUEST['subid']) && $_REQUEST['subid'] != $subKey){
					continue;
				}

				// Add the array to the formresults array
				$newSubmission->formresults = array_merge($this->submission->formresults, $subSubmission);

				// remove the index value from the copy
				unset($newSubmission->formresults[$splitElementName]);

				// Add the subkey
				$newSubmission->subId			= $subKey;

				// Copy the entry
				$this->splittedSubmissions[]	= $newSubmission;
			}
		}
	}

	public function splitSubmission(){
		$splitNames	= [];
		foreach($this->formData->split as $id){
			$splitNames[] = str_replace('[]', '', $this->getElementById($id, 'name'));
		}

		//loop over all submissions
		foreach($this->submissions as $this->submission){
			// check how many entries we should make
			$count	= 1;
			foreach($splitNames as $splitName){
				if(!is_array($this->submission->formresults[$splitName])){
					$this->submission->formresults[$splitName]	= [$this->submission->formresults[$splitName]];
				}

				$c	= count($this->submission->formresults[$splitName]);

				if($c > $count){
					$count	= $c;
				}
			}

			// loop over
			for($x = 0; $x < $count; $x++){
				// create a new submission
				$newSubmission	= clone $this->submission;

				foreach($splitNames as $splitName){
					if(isset($newSubmission->formresults[$splitName][$x])){
						$newSubmission->formresults[$splitName]	= $newSubmission->formresults[$splitName][$x];
					}else{
						$newSubmission->formresults[$splitName]	= '';
					}
				}

				// Add the subkey
				$newSubmission->subId			= apply_filters('sim-formresults-split-subid', $x, $newSubmission, $this);

				//add the new submission
				$this->splittedSubmissions[]	= $newSubmission;
			}
		}
	}

	/**
	 * This function creates seperated entries from entries with an splitted value
	 */
	protected function processSplittedData(){
		if(empty($this->formData->split)){
			return;
		}

		// Get all the elements we should split the rows for
		$splitElements				= $this->formData->split;

		// Get the name of the first element
		$splitElementName			= $this->getElementById($splitElements[0], 'name');

		if(!$splitElementName){
			return;
		}

		// Check if we are dealing with an split element with form name[X]name
		preg_match('/(.*?)\[[0-9]\]\[.*?\]/', $splitElementName, $matches);

		$this->splittedSubmissions  = [];

		if($matches && isset($matches[1])){
			$splitElementName	= $matches[1];
			$this->splitArrayedSubmission($splitElementName);
		}else{
			$this->splitSubmission();
		}
	}
	
	protected function findSplittedElementName($element){
		// Do not continue if no split is defined
		if(empty($this->formData->split)){
			return $element;
		}

		//find the keyword followed by one or more numbers between [] followed by a  keyword between []
		$pattern	= "/.*?\[[0-9]+\]\[([^\]]+)\]/i";

		// The element does not match the pattern
		if( !preg_match($pattern, $element->name, $matches)){
			return $element;
		}
		
		//replace the name
		$element->name			= $matches[1];
		$element->nice_Name		= ucfirst($element->name);
		
		return $element;
	}

	/**
	 * Adds a new column setting for a new element
	 *
	 * @param object	$element	the element to check if column settings exists for
	 * 
	 * @return false|array			false if no column setting was added, array of column settings if added
	 */
	public function addColumnSetting($element){
		//do not show non-input elements
		if(in_array($element->type, $this->nonInputs)){
			return false;
		}

		$element	= $this->findSplittedElementName($element);

		$editRightRoles	= [];
		$viewRightRoles	= [];
		$show			= 1;

		if(isset($this->columnSettings[$element->id])){
			$show			= $this->columnSettings[$element->id]['show'];
			$editRightRoles	= $this->columnSettings[$element->id]['edit_right_roles'];
			$viewRightRoles = $this->columnSettings[$element->id]['view_right_roles'];
		}

		$this->columnSettings[$element->id] = [
			'name'				=> $element->name,
			'nice-name'			=> $element->niceName,
			'show'				=> $show,
			'edit_right_roles'	=> $editRightRoles,
			'view_right_roles'	=> $viewRightRoles
		];

		$this->elementMapper();

		// add to the form elements
		$this->formElements[]		= $element;

		//add the mappers
		$this->formData->elementMapping['id'][$element->id]			= count($this->formElements)-1;
		$this->formData->elementMapping['name'][$element->name][] 	= count($this->formElements)-1;
		$this->formData->elementMapping['type'][$element->type][] 	= count($this->formElements)-1;
		
		return $this->columnSettings[$element->id];
	}

	/**
	 * Adds extra column settings and also adds the virtual elements
	 *
	 * @return		array	The form elements
	 */
	public function addColumnSettings(){
		if(isset($this->columnSettings[-1]) && isset($this->formData->elementMapping['id'][-1])){
			return $this->formElements;
		}

		//also add the id
		if(!isset($this->columnSettings[-1])){
			$this->columnSettings[-1] = [
				'name'				=> 'id',
				'nice-name'			=> 'ID',
				'show'				=> 1,
				'edit_right_roles'	=> [],
				'view_right_roles'	=> []
			];
		}

		if(!isset($this->columnSettings[-2])){
			//also add the submitted by
			$this->columnSettings[-2] = [
				'name'				=> 'userid',
				'nice-name'			=> 'Submitted By',
				'show'				=> 1,
				'edit_right_roles'	=> [],
				'view_right_roles'	=> []
			];
		}

		if(!isset($this->columnSettings[-3])){
			//also add the submission date
			$this->columnSettings[-3] = [
				'name'				=> 'submissiontime',
				'nice-name'			=> 'Submission date',
				'show'				=> 1,
				'edit_right_roles'	=> [],
				'view_right_roles'	=> []
			];
		}

		if(!isset($this->columnSettings[-4])){
			//also add the last edited date
			$this->columnSettings[-4] = [
				'name'				=> 'edittime',
				'nice-name'			=> 'Last edit time',
				'show'				=> 1,
				'edit_right_roles'	=> [],
				'view_right_roles'	=> []
			];
		}
		
		//also add the subid
		if(!isset($this->columnSettings[-5]) && !empty($this->formData->split)){
			$this->columnSettings[-5] = [
				'name'				=> 'subid',
				'nice-name'			=> 'Sub-Id',
				'show'				=> 1,
				'edit_right_roles'	=> [],
				'view_right_roles'	=> []
			];
		}

		$this->elementMapper();

		// add column settings as elements
		foreach($this->columnSettings as $key => $setting){
			if(!is_array($setting)){
				continue;
			}

			if($key > -1){
				continue;
			}
			$element 			= new \stdClass();

			$element->id 		= $key;
			$element->type		= 'text';
			$element->name		= $setting['name'];
			$element->nicename	= $setting['nice-name'];

			// add to the form elements
			$this->formElements[]		= $element;

			//add the mappers
			$this->formData->elementMapping['id'][$element->id]			= count($this->formElements)-1;
			$this->formData->elementMapping['name'][$element->name][] 	= count($this->formElements)-1;
			$this->formData->elementMapping['type'][$element->type][] 	= count($this->formElements)-1;
		}

		return $this->columnSettings;
	}

	/**
	 * Updates column settings with missing columns
	 */
	protected function enrichColumnSettings(){
		if($this->enriched){
			return;
		}

		if(empty($this->formData) || is_numeric($this->shortcodeId)){
			$result	= $this->loadShortcodeData();

			if(is_wp_error($result)){
				return $result;
			}

			$this->getForm($this->tableSettings->form_id);

			if(empty($this->tableSettings)){
				$this->tableSettings	= new \stdClass();
			}

			if(empty($this->tableSettings->edit_right_roles)){
				$this->tableSettings->edit_right_roles	= $this->formData->full_right_roles;
			}
		}

		$this->enriched	= true;
		$elementIds		= [];
		
		$this->addColumnSettings();
		
		//loop over all elements to build a new array
		foreach ($this->formElements as $element){
			$elementIds[]	= $element->id;

			//check if the element is in the array, if not add it
			if(!isset($this->columnSettings[$element->id])){
				$this->addColumnSetting($element);
			}else{
				if(!isset($this->columnSettings[$element->id]['edit_right_roles'])){
					$this->columnSettings[$element->id]['edit_right_roles']	= [];
				}
				if(!isset($this->columnSettings[$element->id]['view_right_roles'])){
					$this->columnSettings[$element->id]['view_right_roles']	= [];
				}
			}
		}
		
		//check for removed elements
		foreach(array_diff(array_keys((array)$this->columnSettings), $elementIds) as $condition){
			//only unset elements
			if(is_numeric($condition) && $condition > -1){
				unset($this->columnSettings[$condition]);
			}
		}

		//Add a row for each table action as well
		$actions	= [];
		foreach($this->formData->actions as $action){
			$actions[]	= $action;
		}

		$actions = apply_filters('sim_form_actions', $actions);
		foreach($actions as $action){
			if(!isset($this->columnSettings[$action]) || !is_array($this->columnSettings[$action])){
				$this->columnSettings[$action] = [
					'name'				=> $action,
					'nice-name'			=> $action,
					'show'				=> 1,
					'edit_right_roles'	=> [],
					'view_right_roles'	=> []
				];
			}
		}
		
		$names	= [];
		//put hidden columns on the end and do not show same names twice
		foreach($this->columnSettings as $key => $setting){
			if(!is_array($setting)){
				continue;
			}
			
			if(in_array($setting['name'], $names)){
				//remove the duplicate element: same name but different id
				unset($this->columnSettings[$key]);
			}

			$names[]	= $setting['name'];

			if(!$setting['show']){
				
				//remove the element
				unset($this->columnSettings[$key]);

				//add it again, at the end of the array
				$this->columnSettings[$key] = $setting;
			}
		}
	}

	protected function getRowContents($values, $subId){
		$rowContents	= '';
		$excelRow		= [];

		$userIdElName   = $this->findUserIdElementName();

		if($values[$userIdElName] == $this->user->ID || $values[$userIdElName] == $this->user->partnerId){
			$ownEntry	= true;
		}else{
			$ownEntry	= false;
		}

		// Get the names of fields the data is splitted on
		$splitNames	= [];
		if(is_array($this->formData->split)){
			foreach($this->formData->split as $id){
				$element	= $this->getElementById($id);

				if(!$element){
					continue;
				}

				// Check if we are dealing with an split element with form name[X]name
				preg_match('/(.*?)\[[0-9]\]\[.*?\]/', $element->name, $matches);

				if($matches && isset($matches[1])){
					$splitNames[] = $matches[1];
				}else{
					$splitNames[] = $element->name;
				}
			}
		}

		$rowHasContents	= false;

		foreach($this->columnSettings as $id => $columnSetting){
			if(!is_array($columnSetting)){
				continue;
			}

			$value			= '';
			$subIdString	= '';
			$orgFieldValue	= $value;

			//If the column is hidden, do not show this cell
			if(!$columnSetting['show'] || !is_numeric($id)){
				continue;
			}

			if(!isset($columnSetting['view_right_roles'])){
				$columnSetting['view_right_roles']	= [];
			}

			if(!isset($columnSetting['edit_right_roles'])){
				$columnSetting['edit_right_roles']	= [];
			}
			
			//if we lack view permission, do not show this cell
			if(
				(
					!$ownEntry ||
					(																						//not our own entry
						$ownEntry &&																		//or it is our own
						!in_array('own', (array)$columnSetting['view_right_roles'])							//but we are not allowed to see it
					)
				)	&&
				!$this->tableEditPermissions &&															//no permission to edit the table and
				!empty($columnSetting['view_right_roles']) && 											// there are view right permissions defined
				!array_intersect($this->userRoles, $columnSetting['view_right_roles'])			// and we do not have the view right role
			){
				//later on there will be a row with data in this column
				if($this->ownData && in_array('own', $columnSetting['view_right_roles'])){
					$value = 'X';
				}else{
					continue;
				}
			}
			
			//if this row has no value in this column remove the row
			if(
				!empty($this->tableSettings->hide_row) &&												// There is a column defined
				$columnSetting['name'] == $this->tableSettings->hide_row && 							// We are currently checking a cell in that column
				(
					(
						empty($values[$this->tableSettings->hide_row]) && 								// The cell has no value
						empty($values[trim($this->tableSettings->hide_row, '[]')])						// also check the name without []
					)
				) && 									
				!array_intersect($this->userRoles, (array)$columnSetting['edit_right_roles'])	&&		// And we have no right to edit this specific column
				!$this->tableEditPermissions															// and we have no right to edit all table data
			){
				return '';
			}

			if(
				in_array('own', $columnSetting['edit_right_roles']) &&
				$ownEntry ||
				array_intersect($this->userRoles, $columnSetting['edit_right_roles']) ||
				$this->tableEditPermissions
			){
				$elementEditRights = true;
			}else{
				$elementEditRights = false;
			}
					
			/*
					Write the content to the cell, convert to something if needed
			*/
			$elementName 	= str_replace('[]', '', $columnSetting['name']);
			$class 			= $columnSetting['name'];

			//add field value if we are allowed to see it
			if($value != 'X'){
				$rowHasContents	= true;

				//Get the field value from the array
				if(!isset($values[$elementName])){
					$value	= 'X';
				}else{
					$value	= $values[$elementName];
				}
					
				// Add sub id if this is an sub value
				if($subId > -1){
					$element	= $this->getElementById($id);
					preg_match('/(.*?)\[[0-9]\]\[.*?\]/', $element->name, $matches);
					$name	= $element->name;

					if($matches && isset($matches[1])){
						$name	= $matches[1];
					}
					
					if(!empty($splitNames) && in_array($name, $splitNames)){
						$subIdString = "data-subid='$subId'";
					}
				}

				if($value === null){
					$value = '';
				}
				
				//transform if needed
				$orgFieldValue	= $value;

				$value 			= apply_filters('sim-form-result-table-value', $value, $columnSetting, $values, $this);
				$value 			= $this->transformInputData($value, $elementName, (object)$values);
				
				//show original email in excel
				if(gettype($value) == 'string' && str_contains($value, '@')){
					$excelRow[]		= $orgFieldValue;
				}elseif(gettype($value) == 'string' && str_contains($value, '<a href=') && str_contains($value, 'form_upload')){
					// add the url to excel
					preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $value, $match);

					if(!empty($match[0][0])){
						$excelRow[]		= $match[0][0];
					}else{
						$excelRow[]		= $orgFieldValue;
					}
				}else{
					$excelRow[]		= wp_strip_all_tags($value);
				}

				//Display an X if there is nothing to show
				if (empty($value) && $value !== 0){
					$value = "X";
				}
				
				//Limit url cell width, for strings with a visible length of more then 30 characters
				if(strlen(strip_tags($value)) > 30 && !str_contains($value, 'https://')){
					$class .= ' limit-length';
				}
			}

			//Add classes to the cell
			if($elementName == "displayname"){
				$class .= ' sticky';
			}

			if(!empty($this->hiddenColumns[$columnSetting['name']])){
				$class	.= ' hidden';
			}

			if(isset($columnSetting['copy'])){
				$class	.= ' copy-wrapper';
			}
			
			//if the user has one of the roles defined for this element
			if($elementEditRights && $elementName != 'id'){
				$class	.= ' edit-forms-table';
				$class	= trim($class);
				$class	= " class='$class' data-name='$elementName'";
			}elseif(!empty($class)){
				$class	= trim($class);
				$class = " class='$class'";
			}
			
			//Convert underscores to spaces, but not in urls
			if(!str_contains($value, 'href=')){
				$value	= str_replace('_',' ',$value);
			}

			$oldValue		= json_encode($orgFieldValue);

			// Use the indexed name to get the element, otherwise we might get the wrong
			if(isset($values['elementindex']) && $this->getElementByName($name.'['.$values['elementindex'].']['.$elementName.']')){
				$element		= $this->getElementByName( $name.'['.$values['elementindex'].']['.$elementName.']');
			}else{			
				$element		= $this->getElementByName($elementName);
			}

			$style			= '';
			if(!empty($columnSetting['width'])){
				$style	= "style='max-width:{$columnSetting['width']}px;width:{$columnSetting['width']}px;min-width:{$columnSetting['width']}px;text-wrap: balance;'";
			}

			if(!$element){
				$cellOpeningTag	= "<td $class";
			}else{
				$cellOpeningTag	= "<td $class data-id='$element->id' data-oldvalue='$oldValue'";
			}

			$cellOpeningTag	= apply_filters('sim-formresult-cell-opening-tag', $cellOpeningTag.' '. $subIdString, $this, $columnSetting, $values);

			// Add a copy option to the value
			$copy	= "";
			if(isset($columnSetting['copy'])){
				$copy	= "<img class='copy' src='".SIM\pathToUrl(MODULE_PATH.'/pictures/copy.png')."' width='20' height='20' loading='lazy' title='Click to copy cell contents'>";
			}
			
			$rowContents .= "$cellOpeningTag $style>$copy$value</td>";
		}

		// none of the cells in this row has a value, only X
		if(!$rowHasContents){
			return '';
		}

		$this->excelContent[] = $excelRow;

		return $rowContents;
	}
	
	/**
	 * Writes a row of the table to the screen
	 *
	 * @param	array	$values			Array containing all the values of a form submission
	 * @param	int		$subId			The subid of a submission. Default -1 for none
	 */
	protected function writeTableRow($values, $subId){
		//If this row should be written and it is the first cell then write
		if($subId > -1){
			$subIdString = "data-subid='$subId'";
		}else{
			$subIdString = "";
		}
		
		$rowContents	= $this->getRowContents($values, $subId);

		$buttonCell		= '';

		//if there are actions
		if(!empty($this->formData->actions)){
			//loop over all the actions
			$buttonsHtml	= [];
			$buttons		= '';
			foreach($this->formData->actions as $action){
				if(
					$action == 'archive' && 
					(
						$this->showArchived ||
						isset($_REQUEST['id'])					// if we are requesting a specific id, we are showing archived ones even if not set
					) && 
					(
						$this->submission->archived ||
						$this->submission->formresults['archived']
					)
				){
					$action = 'unarchive';
				}
				$buttonsHtml[$action]	= "<button class='$action button forms-table-action' name='{$action}-action' value='$action'/>".ucfirst($action)."</button>";
			}
			$buttonsHtml = apply_filters('sim_form_actions_html', $buttonsHtml, $values, $subId, $this);
			
			//we have te html now, check for which one we have permission
			foreach($buttonsHtml as $action=>$button){
				if(
					$this->tableEditPermissions || 																		//if we are allowed to do all actions
					$values['userid'] == $this->user->ID || 															//or this is our own entry
					(isset($this->columnSettings[$action]['edit_right_roles']) && array_intersect($this->userRoles, (array)$this->columnSettings[$action]['edit_right_roles']))		//or we have permission for this specific button
				){
					$buttons .= $button;
				}
			}
			if(!empty($buttons)){
				$buttonCell	= "<td>$buttons</td>";
			}
		}

		if(!empty($rowContents)){
			echo "<tr class='table-row' data-id='{$values['id']}' $subIdString>";
				echo $rowContents;
				echo $buttonCell;
			echo '</tr>';

			return true;
		}

		return false;
	}
	
	/**
	 * Get shortcode settings from db
	 */
	public function loadShortcodeData(){
		global $wpdb;

		if(!is_numeric($this->shortcodeId)){
			if(!empty($_POST['shortcode-id']) && is_numeric($_POST['shortcode-id'])){
				$this->shortcodeId	= $_POST['shortcode-id'];
			}else{
				return new WP_Error('forms', 'no shortcoode id');
			}
		}
		
		$query						= "SELECT * FROM {$this->shortcodeTable} WHERE id = '{$this->shortcodeId}'";
		$this->tableSettings 		= $wpdb->get_results($query)[0];
		foreach($this->tableSettings as $key => &$value){
			$value	= maybe_unserialize($value);
		}

		$this->columnSettings		= [];
		$query						= "SELECT * FROM {$this->shortcodeColumnSettingsTable} WHERE shortcode_id = '{$this->shortcodeId}' ORDER BY `priority` ASC";
		$results 					= $wpdb->get_results($query, ARRAY_A);

		foreach($results as $setting){
			// do not add if the element does not exist anymore
			if(
				is_numeric($setting['element_id']) && 
				$setting['element_id']	> -1 &&
				!isset($this->formData->elementMapping['id'][$setting['element_id']])
			){
				continue;
			}

			//unserialize the values
			foreach($setting as &$value){
				$value	= maybe_unserialize($value);
			}

			$this->columnSettings[$setting['element_id']] = $setting;
		}

		return true;
	}

	protected function columnSettingsForm($class, $viewRoles, $editRoles){
		?>
		<div class="tabcontent <?php echo $class;?>" id="column-settings-<?php echo $this->shortcodeId;?>">
			<form class="sortable-column-settings-rows">
				<input type='hidden' class='no-reset' class='shortcode-settings' name='shortcode-id'	value='<?php echo $this->shortcodeId;?>'>
				
				<table class='sim-table' style='display:table'>
					<thead class="column-setting-wrapper">
						<tr>
							<th class="columnheading formfield-button">Sort</th>
							<th class="columnheading column-settings" style="width: 145px;">Field name</th>
							<th class="columnheading column-settings">Display name</th>
							<th style="width: 30px;"></th>
							<th class="columnheading column-settings" style='max-width:200px;'>Display permissions</th>
							<th class="columnheading column-settings" style='max-width:200px;'>Edit permissions</th>
							<th class="columnheading column-settings" style="width: 60px;">Max Width</th>
							<th class="columnheading column-settings">Copy</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($this->columnSettings as $elementIndex => $columnSetting){
							if(!isset($columnSetting['name'])){
								continue;
							}

							$niceName	= $columnSetting['nice_name'];
							if(empty($niceName)){
								$niceName = ucfirst(str_replace('_', ' ', $columnSetting['name']));
							}

							$width		= empty($columnSetting['width']) ? 200 : $columnSetting['width'];
							
							if(!$columnSetting['show']){
								$visibility	= 'invisible';
							}else{
								$visibility	= 'visible';
							}
							$icon			= "<img class='visibility-icon $visibility' src='".SIM\PICTURESURL."/$visibility.png' width='20px' loading='lazy' style='min-width:20px;'>";
							
							?>
							<tr class="column-setting-wrapper" data-element-id="<?php echo $elementIndex;?>">
								<input type="hidden" class="no-reset" name="column-settings[<?php echo $elementIndex;?>][column-id]"	value="<?php echo $columnSetting['id'];?>">
								<input type="hidden" class="no-reset" name="column-settings[<?php echo $elementIndex;?>][name]"		value="<?php echo $columnSetting['name'];?>">
								<td>
									<span class="movecontrol formfield-button" aria-hidden="true">:::</span>
								</td>
								<td>
									<span class="column-settings" style="margin-right:0px;">
										<?php echo $columnSetting['name'];?>
									</span>
								</td>
								<td>
									<input type="text" class="column-settings" name="column-settings[<?php echo $elementIndex;?>][nice-name]" value="<?php echo $niceName;?>" style="margin-right:0px;">
								</td>
								<td>
									<input type="hidden" class="no-reset" name="column-settings[<?php echo $elementIndex;?>][show]" value="<?php echo $columnSetting['show'];?>">
									<span class="visibility-icon">
										<?php echo $icon;?>
									</span>
								</td>
								<?php
								//only add view permission for numeric elements others are buttons
								if(is_numeric($elementIndex)){
									?>
									<td style='max-width:200px;text-wrap: auto; text-align: left;'>
										<select class='column-settings inline' name='column-settings[<?php echo $elementIndex;?>][view-right-roles][]' multiple='multiple' style="margin-right:0px;">
											<?php
											foreach($viewRoles as $key => $roleName){
												if(isset($columnSetting['view_right_roles']) && in_array($key, (array)$columnSetting['view_right_roles'])){
													$selected = 'selected="selected"';
												}else{
													$selected = '';
												}
												echo "<option value='$key' $selected>$roleName</option>";
											}
											?>
										</select>
									</td>
									<?php
								}else{
									?>
									<td class='column-settings'></td>
									<?php
								}
								?>
								<td style='max-width:200px;text-wrap: auto; text-align: left;'>
									<select class='column-settings inline' name='column-settings[<?php echo $elementIndex;?>][edit-right-roles][]' multiple='multiple' style="margin-right:0px;">
										<?php
										foreach($editRoles as $key=>$roleName){
											if(isset($columnSetting['edit_right_roles']) && @in_array($key, (array)$columnSetting['edit_right_roles'])){
												$selected = 'selected="selected"';
											}else{
												$selected = '';
											}
											echo "<option value='$key' $selected>$roleName</option>";
										}
										?>
									</select>
								</td>
								<td>
									<input type="number" class="column-settings" name="column-settings[<?php echo $elementIndex;?>][width]" value="<?php echo $width;?>" placeholder="200" min="100" style="max-width: 80px; margin-right:0px;">px
								</td>
								<td>
									<input type="checkbox" class="column-settings" name="column-settings[<?php echo $elementIndex;?>][copy]" value="1" <?php if(isset($columnSetting['copy'])){echo 'checked';}?> style="max-width: 40px; margin-right:0px;">
								</td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<?php
				echo SIM\addSaveButton('submit_column_setting','Save table column settings');
				?>
			</form>
		</div>
		<?php
	}

	protected function tableSettingsForm($class, $viewRoles, $editRoles){
		?>
		<div class="tabcontent <?php echo $class;?>" id="table-rights-<?php echo $this->shortcodeId;?>">
			<form>
				<input type='hidden' class='no-reset' class='shortcode-settings' name='shortcode-id'	value='<?php echo $this->shortcodeId;?>'>
				<input type='hidden' class='no-reset' class='shortcode-settings' name='form-id'		value='<?php echo $this->formData->id;?>'>
				
				<h4>Set the title for the results table</h4>
				<input type='text' name="table-settings[title]" value='<?php echo $this->tableSettings->title;?>' style='width:500px;'>

				<div class="table-rights-wrapper">
					<h4>Select the default column the table is sorted on</h4>
					<select name="table-settings[default-sort]">
						<?php
						if($this->tableSettings->default_sort == ''){
							?><option value='' selected>---</option><?php
						}else{
							?><option value=''>---</option><?php
						}
						
						foreach($this->columnSettings as $key => $columnSetting){
							if(!is_array($columnSetting)){
								continue;
							}

							$name = $columnSetting['nice-name'];
							
							//Check which option is the selected one
							if($this->tableSettings->default_sort != '' && $this->tableSettings->default_sort == $key){
								$selected = 'selected="selected"';
							}else{
								$selected = '';
							}
							echo "<option value='$key' $selected>$name</option>";
						}
						?>
					</select>

					<h4>Select the sort direction</h4>
					<label>
						<input type='radio' name='table-settings[sort-direction]' id='sort-direction' value='asc' <?php if($this->tableSettings->sort_direction == 'asc'){echo 'checked';}?>>
						Ascending
					</label>
					<label>
						<input type='radio' name='table-settings[sort-direction]' id='sort-direction' value='dsc' <?php if($this->tableSettings->sort_direction == 'dsc'){echo 'checked';}?>>
						Decending
					</label>
				</div>
				<br>
				<div class="table-filters-wrapper" style='margin-top:10px;'>
					<h4>Select the fields the table can be filtered on</h4>
					<table class='clone-divs-wrapper' style='border: none;'>
						<?php
						$filters	= $this->tableSettings->filter;

						if(!is_array($this->tableSettings->filter)){
							$this->tableSettings->filter	= [];
							$filters	= [''];
						}

						foreach($filters as $index=>$filter){
							echo "<tr class='clone-div' data-div-id='$index' style='border: none;'>";
								echo "<td style='border: none;'>";
									echo "<select name='table-settings[filter][$index][element]' class='inline'>";
										foreach($this->columnSettings as $key=>$columnSetting){

											if(!is_array($columnSetting)){
												continue;
											}

											$name = $columnSetting['nice-name'];
											
											//Check which option is the selected one
											if($this->tableSettings->filter[$index]['element'] == $key){
												$selected = 'selected="selected"';
											}else{
												$selected = '';
											}
											echo "<option value='$key' $selected>$name</option>";
										}
									echo "</select>";
								echo "</td>";

								echo "<td style='border: none;'>";
									echo "   filter type";
									echo "<select name='table-settings[filter][$index][type]' class='inline'>";
										foreach(['>=', '<', '==', 'like'] as $type){
											if($this->tableSettings->filter[$index]['type'] == $type){
												$selected = 'selected="selected"';
											}else{
												$selected = '';
											}
											echo "<option value='$type' $selected>$type</option>";
										}
									echo "</select>";
								echo "</td>";

								echo "<td style='border: none;'>";
									echo "   Filter name  ";
									echo "<input name='table-settings[filter][$index][name]' value='{$this->tableSettings->filter[$index]['name']}'>";
								echo "</td>";
								echo "<td style='border: none;'><button type='button' class='add button'>+</button></td>";
								echo "<td style='border: none;'><button type='button' class='remove button'>-</button></td>";
							echo "</tr>";
						}
						?>
					</table>
				</div>
				
				<div class="table-rights-wrapper">
					<h4>Select a column which determines if a row should be shown.</h4>
					<label>
						The row will be hidden if a cell in this column has no value and the viewer has no right to edit.
					</label>
					<select name="table-settings[hide-row]">
					<?php
					if($this->tableSettings->hide_row == ''){
						?><option value='' selected>---</option><?php
					}else{
						?><option value=''>---</option><?php
					}
					
					foreach($this->columnSettings as $key =>$columnSetting){
						if(!is_array($columnSetting)){
							continue;
						}

						$name = $columnSetting['nice-name'];
						
						//Check which option is the selected one
						if($this->tableSettings->hide_row == $columnSetting['name']){
							$selected = 'selected="selected"';
						}else{
							$selected = '';
						}
						echo "<option value='{$columnSetting['name']}' $selected>$name</option>";
					}
					?>
					</select>
				</div>

				<div class="table-rights-wrapper">
					<h4>Select which results to display</h4>
					<select name="table-settings[result-type]">
						<option value="personal" <?php if($this->tableSettings->result_type == 'personal'){echo 'selected';}?>>Only personal</option>
						<option value="all" <?php if($this->tableSettings->result_type == 'all'){echo 'selected';}?>>All the viewer has permission for</option>
					</select>
					<br>
					<label>
						<input type='checkbox' name='table-settings[split-table]' value='yes' <?php if(isset($this->tableSettings->split_table) && $this->tableSettings->split_table){echo 'checked';}?>>
						Split the results in own entries and others entries
					</label>

				</div>
				
				<div class="table-rights-wrapper">
					<h4 class="label">Select if you want to view archived results by default</h4>
					<?php
					if($this->tableSettings->archived){
						$checked1	= 'checked';
						$checked2	= '';
					}else{
						$checked1	= '';
						$checked2	= 'checked';
					}
					?>
					<label>
						<input type="radio" name="table-settings[archived]" value="1" <?php echo $checked1;?>>
						Yes
					</label>
					<label>
						<input type="radio" name="table-settings[archived]" value="0" <?php echo $checked2;?>>
						No
					</label>
				</div>
				
				<!-- We can define auto archive field both on table and on form settings-->
				<div class="table-rights-wrapper">
					<h4 class="label">Auto archive results</h4>
					<?php
					if($this->formData->autoarchive){
						$checked1	= 'checked';
						$checked2	= '';
					}else{
						$checked1	= '';
						$checked2	= 'checked';
					}
					?>
					<label>
						<input type="radio" name="form-settings[autoarchive]" value="1" <?php echo $checked1;?>>
						Yes
					</label>
					<label>
						<input type="radio" name="form-settings[autoarchive]" value="0" <?php echo $checked2;?>>
						No
					</label>
					<br>
					<br>
					<div class='auto-archive-logic <?php if($checked1 == ''){echo 'hidden';}?>'>
						Auto archive a (sub) entry when field<br>
						<select name="form-settings[autoarchive-el]" class='inline' style="margin-right:10px;">
							<?php
							if(empty($this->formData->autoarchive_el)){
								?><option value='' selected>---</option><?php
							}else{
								?><option value=''>---</option><?php
							}
							
							foreach($this->columnSettings as $key=>$columnSetting){
								if(!is_array($columnSetting)){
									continue;
								}

								$name = $columnSetting['nice-name'];
								
								//Check which option is the selected one
								if($this->formData->autoarchive_el != '' && $this->formData->autoarchive_el == $key){
									$selected = 'selected="selected"';
								}else{
									$selected = '';
								}
								echo "<option value='$key' $selected>$name</option>";
							}
							?>
						</select>
						<label style="margin:0 10px;">equals</label>
						<input type='text' class='wide' name="form-settings[autoarchive-value]" value="<?php echo $this->formData->autoarchive_value;?>" style='max-width:200px;'>
						
						<?php
						echo $this->infoBoxHtml('info', "You can use placeholders like '%today%+3days' for a value");
						?>
					</div>
				</div>

				<?php
				do_action('sim-formstable-after-table-settings', $this);
				?>
				
				<div style='margin-top:10px;'>
					<button class='button table-permissions-rights-form' type='button'>Advanced</button>
					<div class='permission-wrapper hidden'>
						<?php
							// Splitted fields
							$foundElements = [];
							foreach($this->formElements as $key=>$element){
								$pattern = "/([^\[]+)\[[0-9]*\]/i";
								
								if(
									preg_match($pattern, $element->name, $matches)	&&		// preg match was succesfull
									!in_array($matches[1], $foundElements)					// the match is not yet in the found elements
								){
									$foundElements[$element->id]	= $matches[1];
								}
							}

							if(!empty($foundElements)){
								?>
								<div class="table-rights-wrapper">
									<h4>Select fields where you want to create seperate rows for</h4>
									<?php

									foreach($foundElements as $id=>$element){
										$name	= ucfirst(strtolower(str_replace('_', ' ', $element)));
										
										//Check which option is the selected one
										if(is_array($this->formData->split) && in_array($id, $this->formData->split)){
											$checked = 'checked';
										}else{
											$checked = '';
										}
										echo "<label>";
											echo "<input type='checkbox' name='form-settings[split][]' value='$id' $checked>   ";
											echo $name;
										echo "</label><br>";
									}
									?>
								</div>
								<?php
							}
							?>
						<div class="table-rights-wrapper">
							<h4>Select roles with permission to VIEW the table, finetune it per column on the 'column settings' tab</h4>
							
							<select name='table-settings[view-right-roles][]' multiple>
								<option value=''>---</option>
								<?php
								foreach($viewRoles as $key=>$roleName){
									if(in_array($key, (array)$this->tableSettings->view_right_roles)){
										$selected = 'selected';
									}else{
										$selected = '';
									}
									echo "<option value='$key' $selected>$roleName</option>";
								}
								?>
							</select>

							<br>
							<h4>Select users with permission to VIEW the table</h4>
							<?php
							echo SIM\userSelect('', true, false, '', "table-settings[view-right-roles][]", [], $this->tableSettings->view_right_roles, [1], 'select', '', true);
							?>

							<h4>Select roles with permission to edit ALL form submission data</h4>

							<select name='table-settings[edit-right-roles][]' multiple>
								<option value=''>---</option>
								<?php
								foreach($viewRoles as $key => $roleName){
									if(in_array($key, (array)$this->tableSettings->edit_right_roles)){
										$selected = 'selected';
									}else{
										$selected = '';
									}
									echo "<option value='$key' $selected>$roleName</option>";
								}
								?>
							</select>

							<br>
							<h4>Select users with permission to EDIT the table</h4>
							<?php
							echo SIM\userSelect('', true, false, '', "table-settings[edit-right-roles][]", [], $this->tableSettings->edit_right_roles, [1], 'select', '', true);
							?>
						</div>
					</div>
				</div>
			<?php
			echo SIM\addSaveButton('submit_table_setting','Save table settings');
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Print the modal to change table settings to the screen
	 */
	protected function addShortcodeSettingsModal(){
		global $wp_roles;
		
		//Get all available roles
		$userRoles 					= $wp_roles->role_names;
		
		$viewRoles					= $userRoles;
		$viewRoles['everyone']		= 'Everyone';
		$viewRoles['own']	 		= 'Own entries';
		
		$editRoles					= $userRoles;
		$editRoles['own']	 		= 'Own entries';
		
		//Sort the roles
		asort($viewRoles);
		asort($editRoles);
		
		//Table rights active
		if(empty($this->tableSettings)){
			$active1	= '';
			$active2	= 'active';
			$class1		= "hidden";
			$class2		= '';
		//Column settings active
		}else{
			$active1	= 'active';
			$active2	= '';
			$class1		= "";
			$class2		= "hidden";
		}

		ob_start();
		?>
		<div class="modal form-shortcode-settings hidden">
			<!-- Modal content -->
			<div class="modal-content" style='max-width:100vw;min-width:90vw;'>
				<span id="modal-close" class="close">&times;</span>
				
				<button id="column-settings" class="button tablink <?php echo $active1;?>" data-target="column-settings-<?php echo $this->shortcodeId;?>">Column settings</button>
				<button id="table-settings" class="button tablink <?php echo $active2;?>" data-target="table-rights-<?php echo $this->shortcodeId;?>">Table settings</button>
				
				<?php
				$this->columnSettingsForm($class1, $viewRoles, $editRoles);

				$this->tableSettingsForm($class2, $viewRoles, $editRoles);
				?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Processed the table settings
	 */
	protected function loadTableSettings(){		
		if($this->tableSettings->archived || $this->showArchived){
			$this->showArchived = true;
		}else{
			$this->showArchived = false;
		}
		
		//check if we have rights on this form
		if(!isset($this->formEditPermissions) || !$this->formEditPermissions){
			if(
				array_intersect(														// We have full rights to the forms
					(array)$this->userRoles, 
					array_keys((array)$this->formData->full_right_roles)
				)	||
				(
					isset($this->tableSettings->full_right_roles) &&					// we have full rights to the table
					array_intersect(
						(array)$this->userRoles, 
						array_keys((array)$this->tableSettings->full_right_roles)
					)
				)	||
				$this->editRights														// we have edit rights on the form
			){
				$this->formEditPermissions = true;
			}else{
				$this->formEditPermissions = false;
			}
		}
		
		//check if we have rights on this table
		if(!isset($this->tableEditPermissions) || !$this->tableEditPermissions){
			if(
				array_intersect($this->userRoles, (array)$this->tableSettings->edit_right_roles) ||
				in_array($this->userId, (array)$this->tableSettings->edit_right_roles)
			){
				$this->tableEditPermissions = true;
			}else{
				$this->tableEditPermissions = false;
			}

			$this->tableEditPermissions	= apply_filters('sim-table-edit-permissions', $this->tableEditPermissions, $this);
		}
		
		$this->tableViewPermissions	= true;
		if(
			$this->onlyOwn											||
			(
				$this->tableSettings->result_type == 'personal'	&&
				!$this->all
			)	||
			!$this->tableEditPermissions							&&
			!array_intersect($this->userRoles, (array)$this->tableSettings->view_right_roles) &&
			!in_array($this->userId, (array)$this->tableSettings->view_right_roles) &&
			!wp_doing_cron()
		){
			$this->tableViewPermissions 	= false;
		}

		$this->tableViewPermissions	= apply_filters('sim-table-view-permissions', $this->tableViewPermissions, $this);
	}

	/**
	 * Filters the given submissions according to the filter values in POST
	 *
	 * @param	array		$submissions	The submissions to be filtered
	 *
	 * @return	array						The filtered submissions
	 */
	function filterSubmissions($submissions){
		$filteredCount	= 0;
		foreach($this->tableSettings->filter as $filter){
			$filterElement	= $this->getElementById($filter['element']);
			$filterValue	= '';
			$filterKey		= strtolower($filter['name']);
			if(!empty($_POST[$filterKey])){
				$filterValue	= $_POST[$filterKey];
			}

			$exploded		= explode('[', $filterElement->name);

			$name			= str_replace(']', '', end($exploded));

			// Filter the current submission data
			if(!empty($filterValue)){

				foreach($submissions as $key=>$submission){
					if(
						!isset($submission->formresults[$name])	||													// The filter value is not set at all
						!$this->compareFilterValue($submission->formresults[$name], $filter['type'], $filterValue)	// The filter value does not match the value
					){
						unset($submissions[$key]);
						$filteredCount++;
					}
				}
			}
		}

		// total minus the filtered out submissions
		$this->total	= $this->total - $filteredCount;

		// Get the submissions we need
		if(isset($this->splittedSubmissions)){
			$this->splittedSubmissions	= array_chunk($submissions, $this->pageSize)[$this->currentPage];
		}else{
			$this->submissions			= array_chunk($submissions, $this->pageSize)[$this->currentPage];
		}

		$this->spliced	= true;

		return $submissions;
	}

	/**
	 * Renders the table filter html
	 *
	 * @return string	The html
	 */
	protected function renderFilterForm(){
		$html	= '';

		if(!empty($this->tableSettings->filter)){
			$filterOption	= '';
			foreach($this->tableSettings->filter as $filter){
				$filterElement	= $this->getElementById($filter['element']);
				$filterValue	= '';
				$filterKey		= strtolower($filter['name']);

				if(!$filterElement || empty($filterKey)){
					continue;
				}

				if(!empty($_POST[$filterKey])){
					$filterValue	= $_POST[$filterKey];
				}
	
				$elementHtml	= $this->elementHtmlBuilder->getElementHtml($filterElement, $filterValue);
				
				// make sure the name is not the element name but the filtername
				$elementHtml	= str_replace("name='{$filterElement->name}'", "name='$filterKey'", $elementHtml);
	
				$filterOption	.= "<span class='filter-option'>";
					$filterOption	.= "<label>".ucfirst($filterKey).": </label>";
					$filterOption	.= $elementHtml;
				$filterOption	.= "</span>";
			}

			if(!empty($filterOption)){
				$html	= "<form method='post' class='filter-options'>";
					$html	.= "<div class='filter-wrapper'>";
						$html	.= $filterOption;
						$html	.= "<button class='button' style='height: fit-content;'>Filter</button>";
					$html	.= "</div>";
				$html	.= "</form>";
			}
		}

		return $html;
	}
	
	/**
	 * Renders the table buttons html
	 *
	 * @return string	The html
	 */
	public function renderTableButtons(){
		$html	= "<div class='table-buttons-wrapper'>";
			//Show form properties button if we have form edit permissions
			if($this->tableEditPermissions){
				$html	.= "<button class='button small edit-formshortcode-settings'>Edit settings</button>";
				$html	.= $this->addShortcodeSettingsModal();
			}

			// Archived button
			if($this->showArchived){
				$html	.= "<button class='button sim small archive-switch-hide'>Hide archived entries</button>";
			}else{
				$html	.= "<button class='button sim small archive-switch-show'>Show archived entries</button>";
			}

			// Only own button
			if(
				$this->tableViewPermissions &&
				$this->onlyOwn || 
				( $this->tableSettings->result_type == 'personal' && !$this->all)
			){
				$html	.= "<button class='button sim small only-own-switch-all'>Show all entries</button>";
			}elseif(
				$this->tableViewPermissions &&
				(
					!$this->onlyOwn	||
					$this->all		||
					$this->tableSettings->result_type != 'personal'
				)
			){
				$html	.= "<button class='button sim small only-own-switch-on'>Show only my own entries</button>";
			}

			$html	.= "<button type='button' class='button small show fullscreenbutton'>Show full screen</button>";
			
			$hidden	= '';
			if(empty($this->hiddenColumns)){
				$hidden	= 'hidden';
			}
			$html	.= "<button type='button' class='button small reset-col-vis $hidden' data-form-id='{$this->formData->id}'>Reset visibility</button>";
		$html	.= "</div>";

		$html	.= $this->renderFilterForm();
		
		return $html;
	}

	/**
	 * Compares 2 values according to a given comparison string
	 */
	protected function compareFilterValue ($var1, $op, $var2) {
		if(empty($var1) && empty($var2)){
			return true;
		}

		if(empty($var1) || empty($var2)){
			return false;
		}

		if(is_array($var1) && $op == 'like'){
			if(is_array($var2)){
				return array_intersect($var1, $var2);
			}

			return in_array($var2, $var1);
		}

		switch ($op) {
			case "=":  		return $var1 == $var2;
			case "!=": 		return $var1 != $var2;
			case ">=": 		return $var1 >= $var2;
			case "<=": 		return $var1 <= $var2;
			case ">":  		return $var1 >  $var2;
			case "<":  		return $var1 <  $var2;
			case "like":	return str_contains(strtolower($var2), strtolower($var1));
			default:       return true;
		}
	}

	/**
	 * Checks if a given submssion belongs to a given user
	 *
	 * @param	object		$submission			The submission to check
	 * @param	int			$userId				The user id
	 * @param	bool		$includingPartner	Also return true if the partner submitted
	 *
	 * @return	bool							True if the submission belongs to the user, false otherwise
	 */
	public function ownSubmission($submission, $userId, $includingPartner){
		$submissionUserId	= $submission->userid;

		$userIdElementName	= $this->findUserIdElementName();

		if(isset($submission->formresults[$userIdElementName])){
			$submissionUserId	= $submission->formresults['userid'];
		}

		return $submissionUserId == $userId;
	}

	/**
	 * creates the main table html
	 *
	 * @param	string		$type			Either 'own', 'others' or 'all'
	 * @param	array		$submissions	Array of Submissions
	 *
	 * @return	bool						If there are submissions or not
	 */
	public function theTable($type, $submissions){
		$userIdKey			= $this->findUserIdElementName();

		if($this->spliced){
			// only use the submissions for this page
			$submissions	= array_splice($submissions, ($this->currentPage*$this->pageSize), $this->pageSize);
		}

		?>
		<style>
			.name{
				max-width: 50px;
    			white-space: normal;
			}
		</style>
		<table class='sim-table form-data-table' data-form-id='<?php echo $this->formData->id;?>' data-shortcode-id='<?php echo $this->shortcodeId;?>' data-type='<?php echo $type;?>' data-page='<?php echo $this->currentPage;?>' style='position: relative;z-index: 999;'>
			<?php
			$this->resultTableHead($type);
			?>
			
			<tbody class="table-body">
				<?php
				$allRowsEmpty	= true;
				foreach($submissions as $this->submission){
					$values				= $this->submission->formresults;

					$values['id']		= $this->submission->id;
					$values['userid']	= $this->submission->userid;

					// Skip if needed
					if($type == 'others' && $values[$userIdKey] == $this->user->ID){
						continue;
					}

					$subId	= -1;
					if(isset($this->submission->subId)){
						$subId				= $this->submission->subId;
						if($subId > -1){
							$values['subid']= $this->submission->subId;
						}
					}
						
					if($this->writeTableRow($values, $subId)){
						// this row has contents
						$allRowsEmpty	= false;
					}
				}

				if($allRowsEmpty){
					?>
					<td>No records found</td>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php

		return $allRowsEmpty;
	}

	/**
	 * Writes a result table to the screen
	 *
	 * @param	string		$type		Either 'own', 'others' or 'all'
	 * @param	bool		$justTable	Only give the table not the heading and filter form
	 * @param	bool		$force		Whether to retrieve submissions even if already done
	 * @param	bool		$all		Retrieve all bookings or paged, default false for paged
	 *
	 * @return	bool					True on no records found, false on data found
	 */
	public function renderTable($type, $justTable=false, $force=false, $all	= false){
		$userId	= null;

		// Check permissions
		if(
			$this->onlyOwn || 
			!$this->tableViewPermissions || 
			isset($_REQUEST['only-own']) && $_REQUEST['only-own']
		){
			// we do not have permission to view someone elses submissions
			if($type == 'others'){
				return true;
			}
			$type		= 'own';
		}

		$shouldShow	= apply_filters('sim-formstable-should-show', true, $this, $type);

		if($shouldShow !== true){
			echo 	$shouldShow;
			return  false;
		}
		
		// get submissions for the current user only
		if($type == 'own'){
			$userId	= get_current_user_id();

			if(!$userId){
				if(!empty($_REQUEST['hash']) && $_REQUEST['hash'] == wp_hash($_REQUEST['id'])){
					$userId		= $_REQUEST['hash'];
				}else{
					return true;
				}
			}
		}

		// Check if we should sort the data
		if($this->tableSettings->default_sort || isset($_REQUEST['sortcol'])){
			if(isset($_REQUEST['sortcol'])){
				$this->sortColumn	= $_REQUEST['sortcol'];
			}else{
				$defaultSortElement	= $this->tableSettings->default_sort;
				$sortElement		= $this->getElementById($defaultSortElement);

				if($sortElement){
					$exploded			= explode('[', $sortElement->name);
					$sort				= str_replace(']', '', end($exploded));

					$this->sortColumn	= $sort;
				}
			}
		}

		if(isset($_REQUEST['sortdir'])){
			$this->sortDirection	= $_REQUEST['sortdir'];
		}

		if(isset($this->tableSettings->sort_direction)){
			$this->sortDirection	= strtoupper($this->tableSettings->sort_direction);
		}

		if(isset($_REQUEST['export_pfd']) || isset($_REQUEST['export-xls'])){
			$all	= true;
		}

		$this->parseSubmissions($userId, null, $all, $force);

		$submissions		= $this->submissions;
		if(isset($this->splittedSubmissions)){
			$submissions	= $this->splittedSubmissions;
		}

		$submissions	= $this->filterSubmissions($submissions);

		// do not write anything if empty 
		if($type != 'all' && empty($submissions)){
			return true;
		}

		/*
			Write the header row of the table
		*/
		//first check if the data contains data of our own
		$this->ownData	= false;

		if($type != 'others'){
			foreach($submissions as $submission){				
				//Our own entry or one of our partner
				if(
					!empty($submission->userid) &&
					(
						$submission->userid == $this->user->ID || 
						$submission->userid == $this->user->partnerId
					)
				){
					$this->ownData = true;
					break;
				}
			}
		}
		
		ob_start();

		if($justTable){
			$this->theTable($type, $submissions);

			return ob_get_clean();
		}

		echo "<div class='form-results-wrapper'>";
			if($type == 'own'){
				echo "<h4>Your own submissions</h4>";
			}elseif($type == 'others'){
				$type	= 'others';
				echo "<h4>Submissions of others</h4>";
			}

			if($this->total > $this->pageSize){
				$pageCount	=  ceil($this->total / $this->pageSize);

				if(isset($_GET['page-number'])){
					unset($_GET['page-number']);
				}
				$html	= "<div class='form-result-navigation'>";
					// include a back button if we are not on the first page
					$class = 'hidden';
					if($this->currentPage > 0){
						$class = '';
					}
					$html	.= "<button class='button small prev $class' name='prev' value='prev'>← Previous</button>";
					//show page numbers
					$html	.= "<span class='page-number-wrapper'>";
						for ($x = 0; $x < $pageCount; $x++) {
							$pageNr	= $x+1;

							$class	= '';
							if($this->currentPage == $x){
								$class	= "current";
							}
							$html	.= "<span class='page-number $class' data-nr='$x'>$pageNr</span> ";
						}
					$html	.= "</span>";

					// Include a next button if we are not on the last page
					$class = 'hidden';
					if($this->total > $this->pageSize && $this->currentPage != $pageCount-1){
						$class = '';
					}
					$html	.= "<button class='button small next $class' name='next' value='next'>Next →</button>";

					// Adjust page size
					$get	= $_REQUEST;
					unset($get['_wpnonce']);
					unset($get['form-id']);
					unset($get['shortcode-id']);

					$get['pagesize']	= '';
					$get	= '?'.http_build_query($get);

					$html	.= "<select onchange=location.href='{$get}'+this.value>";
						foreach([1000, 500, 200, 100, 50, 40, 20, 10] as $size){
							$selected	= '';
							if(
								(
									isset($_GET['pagesize']) && 
									$_GET['pagesize'] == $size
								) ||
								(
									empty($_GET['pagesize']) && 
									$size == 100
								)
							){
								$selected	= 'selected';
							}
							$html	.= "<option $selected>$size</option>";
						}
					$html	.= "</select>";
				$html	.= "</div>";

				echo $html;
			}

			$allRowsEmpty	= $this->theTable($type, $submissions);
		echo "</div>";
			
		$this->printTableFooter();
		
		echo ob_get_clean();

		return $allRowsEmpty;
	}

	private function printTableFooter(){
		?>
		<div class='sim-table-footer'>
			<p id="table-remark">Click on any cell with <span class="edit-forms-table">underlined text</span> to edit its contents.<br>Click on any header to sort the column.</p>
			
			<?php
			//Add excel export button if allowed
			//if($this->tableEditPermissions){
				?>
				<div>
					<form method="post" class="export-form" id="export-xls">
						<button class="button button-primary" type="submit" name="export-xls">Export data to excel</button>
					</form>
					<?php
					if(SIM\getModuleOption('pdf', 'enable')){
						?>
						<form method="post" class="export-form" id="export-pdf">
							<button class="button button-primary" type="submit" name="export-pdf">Export data to pdf</button>
						</form>
						<?php
					}
					?>
				</div>
				<?php
			//}
		?>
		</div>
		<?php
	}

	/**
	 * Creates the formresult table html
	 *
	 * @param	bool	$split	Whether or not to split in two tables, default table settings	
	 * @param	bool	$all	Retrieve all bookings or paged, default false for paged	
	 *
	 * @return	string|WP_Error			The html or error on failure
	 */
	public function showFormresultsTable($split = null, $all = false){
		// first render the table so we now how many results we have
		ob_start();
		$allRowsEmpty	= true;
		if(
			(
				$split === null	&&									// we should use the table settings
				$this->tableSettings->split_table					// and we should split						
			) ||
			$split == true											// we should always split
		){
			$buttons		= $this->renderTableButtons();
			$result1		= $this->renderTable('own', false, true, $all);

			$buttons		= $this->renderTableButtons();
			$result2		= $this->renderTable('others', false, true, $all);
			$allRowsEmpty	= $result1 && $result2;
		}else{
			$buttons		= $this->renderTableButtons();
			$allRowsEmpty	= $this->renderTable('all', false, false, $all);
		}
		$tableHtml			= ob_get_clean();

		ob_start();
		//process any $_GET acions
		do_action('sim_formtable_GET_actions');
		do_action('sim_formtable_POST_actions');
		
		//Load js
		wp_enqueue_script('sim_forms_table_script');

		?>
		<div class='form table-wrapper'>
			<div class='form table-head'>
				<h2 class="table-title"><?php echo esc_html($this->tableSettings->title); ?></h2><br>
				<?php
					echo $buttons;
				?>
			</div>
			<?php
			
			if($allRowsEmpty){
				?>
				<table class='sim-table form-data-table' data-form-id='<?php echo $this->formData->id;?>' data-shortcode-id='<?php echo $this->shortcodeId;?>'>
					<td>No records found</td>
				</table>
				<?php

				return ob_get_clean().'</div>';
			}else{
				echo $tableHtml;
			}
			?>
		</div>
		<?php
		
		//now we have rendered all the content we can export the excel if requested
		if(isset($_POST['export-xls'])){
			$this->exportExcel();
		}

		//now we have rendered all the content we can export the pdf if requested
		if(isset($_POST['export-pdf'])){
			echo $this->exportPdf();
		}
		
		$html	= ob_get_clean();
		
		return apply_filters('sim-forms-form-results-html', $html, $this);
	}

	/**
	 * Prints the results table head
	 *
	 * @param	string		$type		Either 'own', 'others' or 'all'
	 */
	private function resultTableHead($type){
		$excelRow	= [];
		?>
		<thead>
			<tr>
				<?php

				//add normal fields
				foreach($this->columnSettings as $elementId => $columnSetting){					
					if(!isset($columnSetting['view_right_roles']) || !is_array($columnSetting['view_right_roles'])){
						$columnSetting['view_right_roles']	= [];
					}

					if(
						!is_numeric($elementId)				||
						!$columnSetting['show']				||												//hidden column
						(
							!$this->ownData				|| 													//The table does not contain data of our own
							(
								$this->ownData			&& 													//or it does contain our own data but
								!in_array('own', $columnSetting['view_right_roles'])							//we are not allowed to see it
							)
						) &&
						!$this->tableEditPermissions 				&&										//no permission to edit the table and
						!empty($columnSetting['view_right_roles']) 	&& 										// there are view right permissions defined
						!array_intersect($this->userRoles, $columnSetting['view_right_roles'])				// and we do not have the view right role and
					){
						continue;
					}
					
					$niceName			= $columnSetting['nice_name'];
					
					if($this->tableSettings->default_sort	== $elementId){
						$class	= "defaultsort";
					}else{
						$class	= "";
					}

					if($this->sortColumn == $columnSetting['name']){
						$class	= strtolower($this->sortDirection). ' defaultsort';
					}

					if(!empty($this->hiddenColumns[$columnSetting['name']])){
						$class	.= ' hidden';
					}
					$icon			= "<img class='visibility-icon visible' src='".SIM\PICTURESURL."/visible.png' width=20 height=20 loading='lazy' >";
					
					//Add a heading for each column
					$style			= '';
					if(!empty($columnSetting['width'])){
						$style	= "style='max-width:{$columnSetting['width']}px;width:{$columnSetting['width']}px;min-width:{$columnSetting['width']}px;text-wrap: balance;'";
					}

					echo "<th class='$class' id='{$columnSetting['name']}' data-nice-name='$niceName' $style>$niceName $icon</th>";
					
					$excelRow[]	= $niceName;
				}
				
				//write header to excel
				$this->excelContent[] = $excelRow;
				
				//add a Actions heading if needed
				$actions = [];
				foreach($this->formData->actions as $action){
					$actions[]	= $action;
				}
				$actions = apply_filters('sim_form_actions', $actions);

				//we have full permissions on this table
				$addHeading	= false;
				if($this->tableEditPermissions && !empty($actions)){
					$addHeading	= true;
				}else{
					foreach($actions as $action){
						//we have permission for this specific button
						if(isset($this->columnSettings[$action]['edit_right_roles']) && array_intersect($this->userRoles, (array)$this->columnSettings[$action]['edit_right_roles'])){
							$addHeading	= true;
						}elseif($type != 'others'){
							//Loop over all submissions to see if the current user has permission for them
							foreach($this->submissions as $submission){
								//we have permission on this row for this button
								if(
									(
										isset($submission->formresults['userid']) &&				// formresults contains a userid
										$submission->formresults['userid'] == $this->user->ID		// userid is the current user
									) ||
									(
										!isset($submission->formresults['userid']) &&				// formresults don't contain a userid
										$submission->userid == $this->user->ID						// current user submitted the form
									)
								){
									$addHeading	= true;
								}
							}
						}
					}
				}

				if($addHeading){
					echo "<th id='actions' data-nice-name='Actions'>Actions</th>";
				}
				?>
			</tr>
		</thead>
		<?php
	}

	/**
	 * New form results table
	 *
	 * @param	int		$formId		the id of the form
	 *
	 * @return	int					The id of the new formtable
	 */
	public function insertInDb($formId){
		global $wpdb;

		//add new row in db
		$wpdb->insert(
			$this->shortcodeTable,
			array(
				'form_id'			=> $formId,
			)
		);

		return $wpdb->insert_id;
	}

	/**
	 * check for any formresults shortcode and add an id if needed
	 *
	 * @param	array	$data	The post data
	 *
	 * @return	array			The filtered post data
	 */
	public function checkForFormShortcode($data) {
		global $wpdb;
		
		//find any formresults shortcode
		$pattern = "/\[formresults([^\]]*formname=(.*)[^\]]*)\]/s";
		
		//if there are matches
		if(preg_match_all($pattern, $data['post_content'], $matches)) {
			//loop over all the matches
			foreach($matches[1] as $key=>$shortcodeAtts){
				//this shortcode has no id attribute
				if (!str_contains($shortcodeAtts, ' id=')) {
					$shortcode		= $matches[0][$key];
					
					$this->formName = $matches[2][$key];

					$this->getForm();
					
					$shortcodeId	= $this->insertInDb($this->formData->id);

					$newShortcode	= str_replace('formresults', "formresults id=$shortcodeId", $shortcode);
					
					//replace the old shortcode with the new one
					$pos = strpos($data['post_content'], $shortcode);
					if ($pos !== false) {
						$data['post_content'] = substr_replace($data['post_content'], $newShortcode, $pos, strlen($shortcode));
					}
				}
			}
		}
		
		return $data;
	}
}
