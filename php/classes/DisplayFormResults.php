<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use stdClass;
use WP_Error;
use function TSJIPPY\addElement as addElement;

if (! defined('ABSPATH')) {
    exit;
}

class DisplayFormResults extends DisplayForm
{
    use ExportFormResults;

    public array    $columnSettings;
    public int      $currentPage;
    public bool     $enriched;
    public array    $excelContent;
    public array    $extraElements;
    public bool     $formEditPermissions;
    public array    $hiddenColumns;
    public bool     $ownData;
    public string   $shortcodeTable;
    public string   $sortColumn;
    public string   $sortDirection;
    public array    $sortElementIds;
    public bool     $spliced;
    public array    $subElements;
    public bool     $tableEditPermissions;
    public object   $tableSettings;
    public bool     $tableViewPermissions;
    public int|null $total;

    /**
     * Constructor for the DisplayFormResults class
     * @param array $atts The attributes passed to the shortcode
     */
    public function __construct($atts)
    {
        // call parent constructor
        unset($atts['shortcode-id']);
        parent::__construct($atts);

        global $wpdb;

        $this->columnSettings       = [];
        $this->currentPage          = 0;
        $this->enriched             = false;
        $this->excelContent         = [];
        $this->extraElements        = [];
        $this->formEditPermissions  = false;
        $this->ownData              = false;
        $this->shortcodeTable       = $wpdb->prefix . 'tsjippy_form_shortcodes';
        $this->sortColumn           = '';
        $this->sortDirection        = 'ASC';
        $this->sortElementIds       = [];
        $this->spliced              = false;
        $this->subElements          = [];
        $this->tableEditPermissions = false;
        $this->tableSettings        = new stdClass();
        $this->tableViewPermissions = false;
        $this->total                = 0;

        //Get personal visibility
        if (empty($this->formData)) {
            $this->hiddenColumns        = [];
        } elseif (!empty($this->formData->id)) {
            $hiddenColumns        = get_user_meta($this->user->ID, 'tsjippy_hidden_columns_' . $this->formData->id, true);
            if (empty($hiddenColumns)) {
                $this->hiddenColumns    = [];
            }
        } else {
            return new WP_Error('forms', 'No form data found for the given form results shortcode');
        }

        // add the elements filter before the parent construct, as that will apply the filter
        add_filter('tsjippy-forms-elements', [$this, 'addExtraElements'], 10, 3);

        wp_enqueue_style('tsjippy_formtable_style');

        $family                     = new TSJIPPY\FAMILY\Family();
        $this->user->partnerId      = $family->getPartner($this->user->ID);

        if (function_exists('is_user_logged_in') && is_user_logged_in()) {
            $this->userRoles[]      = 'everyone'; //used to indicate view rights on permissions
        }

        $result    = $this->enrichColumnSettings();
        if (is_wp_error($result)) {
            return $result;
        }

        $this->loadTableSettings();
    }

    /**
     * Function to add the elements for submission meta
     *
     * @param array $elements The current array of elements
     * @param object $object The form or shortcode object for which the elements are being retrieved
     * @param bool $force Whether to force adding the extra elements even if they already exist
     * @return array The updated array of elements with the extra elements added
     */
    public function addExtraElements($elements, $object, $force)
    {
        // Build the array of element details
        $this->extraElements    = [
            // -6 = archived indexes
            // -7 = hash
            -4 => [
                'slug'    => 'time_last_edited',
                'name'    => 'Last edit time',
                'type'    => 'date'
            ],
            -3 => [
                'slug'    => 'time_created',
                'name'    => 'Submission date',
                'type'    => 'date'
            ],
            -2 => [
                'slug'    => 'submitter_id',
                'name'    => 'Submitted By',
                'type'    => 'number'
            ],
            -1 => [
                'slug'    => 'id',
                'name'    => 'ID',
                'type'    => 'number'
            ]
        ];

        if (!empty($this->formData->split)) {
            $this->extraElements[-5] = [
                'slug'                => 'sub_id',
                'name'            => 'Sub-Id',
                'type'                => 'number'
            ];
        }

        foreach ($this->extraElements as $id => $newElement) {
            if (isset($this->elementMapping['id'][$id])) {
                continue;
            }
            $element                 = new \stdClass();

            $element->id             = $id;
            if (isset($newElement['type'])) {
                $element->type = $newElement['type'];
            } else {
                $element->type        = 'text';
            }
            $element->slug            = $newElement['slug'];
            $element->name            = $newElement['name'];

            // Add to the front of the array
            array_unshift($elements, $element);
        }

        return $elements;
    }

    /**
     * Retrieves all user metas and user data's and use them as submission data
     */
    public function getMetaKeyFormSubmissions($userId = null, $all = false)
    {
        global $wpdb;

        // also check the users table
        $colNames    = $wpdb->get_results("DESC {$wpdb->users}");
        $usedCols    = ['ID', 'user_registered'];
        foreach ($colNames as &$desc) {
            $desc    = $desc->Field;
        }

        $baseQuery            = "SELECT * FROM %i WHERE ";
        $where                = [];
        $values                = [$wpdb->usermeta];

        if (is_numeric($userId)) {
            $where[]            = "user_id = %d ";
            $values[]            = $userId;
        }

        $or    = [];

        // Loop over all form elements to see which metas/userdata we need to get
        foreach ($this->formElements as $element) {
            if (!in_array($element->type, $this->nonInputs) && $element->id >= 0) {
                $name            = trim($element->slug, '[]');

                if (in_array($name, $colNames)) {
                    $usedCols[]    = $name;
                } else {
                    $or[]            .= "%s";
                    $values[]         = $name;
                }
            }
        }

        $where[]    = 'meta_key IN (' . implode(',', $or) . ')';

        $submissions = [];
        /**
         * Build the base submission
         */
        $values    = [implode(',', $usedCols), $wpdb->users];

        $w    = '';
        if (is_numeric($userId)) {
            $w            = "where user_id = %d ";
            $values[]    = $userId;
        }
        $users        = $wpdb->get_results(
            $wpdb->prepare("select %s from %i $w", $values)
        );

        $counter    = 0;
        foreach ($users as $user) {
            $submission     = new \stdClass();

            $submission->id                    = $counter;
            $submission->form_id            = $this->formData->id;

            // Base submission data
            $submission->time_created        = $user->user_registered;
            $submission->time_last_edited        = $user->user_registered;
            unset($usedCols['user_registered']);

            $submission->user_id                = $user->ID;
            $submission->submitter_id        = $user->ID;
            unset($usedCols['ID']);

            // Add the remaining user data if any
            foreach ($usedCols as $col) {
                $submission->$col    = $user->$col;
            }

            $submissions[$user->ID]    = $submission;

            $counter++;
        }

        /**
         * Add the metas to the submissions
         */
        $filtered    = apply_filters(
            'tsjippy_formdata_retrieval_query',
            [
                'baseQuery'    => $baseQuery,
                'where'        => $where,
                'values'    => $values,
            ],
            $userId,
            $this
        );
        extract($filtered);

        $query    = $baseQuery . implode(' AND ', $where);

        if (count($values) != substr_count($query, '%')) {
            TSJIPPY\printArray($query);
            TSJIPPY\printArray($values);
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $metas        = $wpdb->get_results(
            $wpdb->prepare($query, $values)
        );

        // parse results to merge based on userId
        foreach ($metas as $meta) {
            $submissions[$meta->user_id]->{$meta->meta_key}    = maybe_unserialize($meta->meta_value);
        }

        // Get the total
        $this->total            = count($submissions);

        // Limit the amount to 100
        if (!$all && isset($_REQUEST['page-number']) && is_numeric($_REQUEST['page-number']) && $this->total > $this->pageSize) {
            $this->currentPage    = $_REQUEST['page-number'];

            if (isset($_POST['prev'])) {
                $this->currentPage--;
            }
            if (isset($_POST['next'])) {
                $this->currentPage++;
            }
            $start            = $this->currentPage * $this->pageSize;

            $submissions        = array_slice($submissions, $start, $this->pageSize);

            $this->spliced    = true;
        } else {
            $this->currentPage    = 0;
        }

        // sort colomn
        if (!empty($this->sortElementIds)) {
            if ($this->sortDirection != 'ASC') {
                $this->sortDirection    = 'DESC';
            }
        }

        return apply_filters('tsjippy_retrieved_formdata', $submissions, $userId, $this);
    }

    /**
     * Add filter querys
     *
     * @param array $where The array of where statements to add the filter querys to
     * @param array $values The array of values for the where statements to add the filter values to
     *
     * @return void
     */
    protected function addFilterQueries(&$where, &$values)
    {
        global $wpdb;

        if (empty($this->tableSettings->filter)) {
            return;
        }

        foreach ($this->tableSettings->filter as $filter) {

            $filterKey        = strtolower($filter['name']);

            // nothing to filter, continue
            if (empty($_POST[$filterKey])) {
                continue;
            }

            // Get the data for the current filter
            $filterValue    = TSJIPPY\sanitize($_POST[$filterKey]);

            $filterElement  = $this->getElementById($filter['element']);

            // Invalid filter element id
            if (!$filterElement) {
                continue;
            }

            /**
             * Check if we are filtering on a indexed element
             */
            $exploded            = explode('[', $filterElement->slug);
            if (count($exploded) > 1) {
                $filterIndex        = str_replace(']', '', end($exploded));

                $filterElementIds    = $wpdb->get_col(
                    $wpdb->prepare(
                        "SELECT id FROM %i WHERE `name` LIKE %s",
                        $this->elTableName,
                        "{$exploded[0]}[%][$filterIndex]"
                    )
                );
            } else {
                $filterElementIds    = [$filter['element']];
            }

            // Add the filter query
            if ($filter['type'] == '==') {
                $filter['type']    = '=';
            }

            if ($filter['type'] == 'like') {
                $filterValue    = "%$filterValue%";
            }

            $placeholders   = implode(', ', array_fill(0, count($filterElementIds), '%d'));

            $where[]    = "(V.element_id NOT IN ($placeholders) or LOWER(V.value) {$filter['type']} %s)";
            $values[]    = array_merge($values, $filterElementIds);
            $values[]    = strtolower($filterValue);
        }
    }

    /**
     * Queries for splitted form results
     * Transpose all splitted value rows to columns
     *
     * @param array $finalWhere            The array of where statements to add the sub_id where statement to
     * @param string $innerJoinString    The inner join string to add the splitted values inner join to
     *
     * @return string                    The updated Common Table Expressions string with the splitted values queries added
     */
    private function splittedValuesQueries(&$finalWhere, &$innerJoinString)
    {
        $splitElements        = $this->formData->split ?? [];
        if (empty($splitElements)) {
            return;
        }

        $innerJoinString    = "\n\tLEFT JOIN SubIdValues as V ON E.id = V.Sid";

        $ect                    = ",\nSubIdValues AS (\n\tSELECT \n\t\tid AS Sid, \n\t\tsub_id,\n\t\t";

        $splitColumns        = [];

        /**
         * Process split elements with the form base[index][key]
         */
        foreach ($this->findSplitElementIds() as $base) {
            foreach ($base as $columnName => $ids) {
                // Make the array of elements that share the same name a comma separated string for the query
                $implodedIds    = implode(", ", array_values($ids));

                // Store the other ids as well
                $splitElements    = array_merge($splitElements, array_values($ids));

                // Add the column to the query
                $splitColumns[] = "MAX(CASE WHEN element_id IN ($implodedIds) THEN value END) AS '$columnName'";

                // Make sure we sort on the $columnName if needed
                foreach ($this->sortElementIds as &$elementId) {
                    if (in_array($elementId, $ids)) {
                        $elementId = $columnName;
                    }
                }
                unset($elementId);
            }

            $this->sortElementIds    = array_unique($this->sortElementIds);
        }

        /**
         * Process simple base[index] splits
         */
        if (empty($splitColumns)) {
            foreach ($splitElements as $splitElement) {
                // Add the column to the query
                $splitColumns[]         = "MAX(CASE WHEN element_id = $splitElement THEN value END) AS '$splitElement'";
            }
        }

        $ect .= implode(",\n\t\t", $splitColumns);
        $ect .= ",\n\t\tMAX(CASE WHEN element_id = '-6' THEN sub_id END) AS 'sub_archived'";
        $ect .= "\n\tFROM Raw";
        $ect .= "\n\tWHERE sub_id IS NOT NULL";
        $ect .= "\n\tGROUP BY id, sub_id";
        $ect .= "\n)";

        if (!$this->showArchived) {
            $finalWhere[]            = "(sub_id <> sub_archived or sub_archived is null)";
        }

        return $ect;
    }

    /**
     * Builds the columns list for the SQL query
     *
     * @param     array    $where            The where conditions for the query
     * @param     string    $baseQuery        The base query to append the select statement to
     * @param     array    $values            The values for the where conditions
     *
     * @return string                    The built ect
     */
    private function columnsQuery($where, &$baseQuery, &$values)
    {
        /**
         * Build the Common Table Expressions (CTE) needed to make the pivot query
         */
        $splitElements        = (array) ($this->formData->split ?? []);
        $existingColumns    = ['id', 'form_id', 'time_created', 'time_last_edited', 'user_id', 'archived', 'submitter_id'];

        $columns            = $existingColumns;

        $columnsString        = implode(', S. ', $columns);

        $innerJoinString    = '';

        // Check which where statements should apply to the splitted values and add those to the inner join string
        $rawWhere            = [];
        $rawValues           = [];
        $finalWhere          = [];
        $valueIndex          = 0;
        foreach ($where as $whereStatement) {
            if (str_contains($whereStatement, 'S.')) {
                $rawWhere[]        = $whereStatement;

                if (str_contains($whereStatement, '%')) {
                    $rawValues[]    = $values[$valueIndex];

                    unset($values[$valueIndex]);
                }
            } else {
                $finalWhere[]    = $whereStatement;
            }

            // Keep track of the value index for the where statements
            if (str_contains($whereStatement, '%')) {
                $valueIndex++;
            }
        }

        // merge the value arrays back in the right order
        $values        = array_merge($rawValues, $values);

        $rawWhere    = implode(' AND ', $rawWhere);

        // ECT for all the values
        $ect                 = "-- Table with raw data on several rows, where only the element_id and value are unique\n"
            . "WITH Raw AS (\n\t"
            . "SELECT S.$columnsString, V.sub_id, V.element_id, V.value\n\tFROM %i as S\n\t"
            . "INNER JOIN %i as V ON S.id = V.submission_id \n\t"
            . "WHERE $rawWhere\n"
            . ")";

        // add the table names to the values table
        array_unshift($values, $this->submissionTableName, $this->submissionValuesTableName);

        $ect .= $this->splittedValuesQueries($finalWhere, $innerJoinString);

        /**
         * Transpose rows to columns for values with an empty sub_id (non splitted)
         */
        $columnsString        = implode(", \n\t\t", $columns);
        $ect               .= ", \n-- Table where the rows are transposed to columns\nEmptySubIdValues AS (\n\tSELECT \n\t\t$columnsString";
        $toColumn            = [];

        foreach ($this->formElements as $element) {
            // Negative element ids are from the submission table
            if ($element->id < 0 || in_array($element->id, $splitElements) || in_array($element->type, $this->nonInputs)) {
                continue;
            }

            $toColumn[]         = "MAX(CASE WHEN element_id = '$element->id' THEN value END) AS '$element->id'";
        }

        if (!empty($toColumn)) {
            $ect            .= ",\n\t\t" . implode(",\n\t\t", $toColumn);
        }
        $ect                .= "\n\tFROM Raw \n\tWHERE sub_id IS NULL \n\tGROUP BY id\n)";

        /**
         * The main ECT that joins the ect with the non-splitted values with the ect with the splitted values
         */
        $ect                .= ",\n -- the final submission table including sub-values \nSubmissions AS (\n\tSELECT * \n\tFROM EmptySubIdValues E $innerJoinString\n)\n\t\t";
        $baseQuery            .= "SELECT * FROM Submissions WHERE 1=1";

        if (!empty($finalWhere)) {
            $baseQuery .= " AND " . implode(' AND ', $finalWhere);
        }

        return $ect;
    }

    /**
     * Get formresults of the current form
     *
     * @param    int|array    $userId            Optional the user id to get the results of or an array of user ids. Default null
     * @param    int            $submissionId    Optional a specific submission id. Default null
     * @param    bool        $all            Whether to retrieve all submissions or paged. Default false
     * @param    array        $where            Optional array of where conditions. Default empty array
     * @param    array        $values            Optional array of values for the where conditions. Default empty array
     *
     * @return    array                        array of results
     */
    public function getSubmissions($userId = null, $submissionId = null, $all = false, $where    = [], $values = [])
    {
        global $wpdb;

        $userId    = apply_filters('tsjippy-forms-user_ids-to-retrieve', $userId, $this);

        if (isset($_REQUEST['all'])) {
            $all    = true;
        }

        // Submission id
        if (empty($submissionId) && !empty($_REQUEST['id'])) {
            $submissionId    = $_REQUEST['id'];
        }

        if (!empty($this->submissions) && is_numeric($submissionId)) {
            foreach ($this->submissions as $submission) {
                if ($submission->id == $submissionId) {
                    return [$submission];
                }
            }
        }

        // We want to see archived entries if a specific submission id is queried
        $this->showArchived = $this->showArchived || is_numeric($submissionId);

        // Check if a form is loaded
        if (empty($this->formData) && !empty($submissionId)) {
            // Load the form before loading the submission, because we need the form elements to load the submission data
            $this->getFormBySubmissionId($submissionId);
        }

        if (!empty($this->formData->save_in_meta)) {
            return $this->getMetaKeyFormSubmissions($userId, $all);
        }

        /**
         * Get the where statements
         */
        // Form Id
        if (isset($this->formData->id)) {
            $where[]    = "S.form_id=%d";
            $values[]    = $this->formData->id;
        }

        // Archived
        if (!$this->showArchived && $submissionId == null) {
            $where[]    =  "S.archived=0";
        }

        // Specific Submission
        if (is_numeric($submissionId)) {
            $where[]    = "S.id=%d";
            $values[]    = $submissionId;
        }

        /**
         * Specific Users
         */
        if (is_numeric($userId)) {
            $where[]    = "S.user_id=%d";
            $values[]    = $userId;
        }

        if (is_array($userId)) {
            $q    = [];
            foreach ($userId as $id) {
                if (is_numeric($id)) {
                    $q[]        = "S.user_id=%d";
                    $values[]    = $id;
                }
            }

            $where[]    = '(' . implode(' OR ', $q) . ')';
        }

        /**
         * Filters from frontend
         */
        $this->addFilterQueries($where, $values);

        /**
         * Apply filter to modify the query
         * @var string    $base        The base query
         * @var array    $where        Array of where statements
         * @var array    $values        Array of values for the where statements
         */
        $filtered    = apply_filters(
            'tsjippy_formdata_retrieval_query',
            [
                'query'        => '',
                'where'        => $where,
                'values'    => $values,
            ],
            $userId,
            $this
        );

        extract($filtered);

        /**
         * Build the main query
         */
        $ecd            = $this->columnsQuery($where, $query, $values);

        // Get the total
        $countQuery     = "$ecd\n\nSELECT COUNT(*) AS total FROM (\n\t$query\n) AS AllData;";
        $this->total    = $wpdb->get_var($wpdb->prepare($countQuery, ...$values));

        if (empty($this->total)) {
            return apply_filters('tsjippy_retrieved_formdata', [], $userId, $this);
        }

        /**
         * Pagination
         */
        // Limit the amount to 100
        if (isset($_REQUEST['page-number']) && is_numeric($_REQUEST['page-number'])) {
            $this->currentPage    = $_REQUEST['page-number'];

            if (isset($_POST['prev'])) {
                $this->currentPage--;
            }

            if (isset($_POST['next'])) {
                $this->currentPage++;
            }

            $start    = $this->currentPage * $this->pageSize;
        } else {
            $start                = 0;
            $this->currentPage    = 0;
        }

        /**
         * Sort column
         */
        if (!empty($this->sortElementIds)) {
            if ($this->sortDirection != 'ASC') {
                $this->sortDirection    = 'DESC';
            }

            $query        .= " \nORDER BY ";
            $sortables    = [];
            foreach ($this->sortElementIds as $elementId) {
                if ($elementId < 0) {
                    $elementId     = $this->extraElements[$elementId]['slug'];
                }

                $sortables[] = "`$elementId` $this->sortDirection";
            }

            $query    .= implode(', ', $sortables);
        }

        // add the limit only if we are not querying everything or start is larger than the total
        if (!$all && $start < $this->total) {
            $this->spliced    = true;
            $query           .= " LIMIT %d, %d";
            $values[]        = $start;
            $values[]        = $this->pageSize;
        }

        // Get the submissions
        $submissions    = $wpdb->get_results(
            $wpdb->prepare("$ecd\n\n$query", ...$values)
        );

        if ($wpdb->last_error !== '') {
            TSJIPPY\printArray($wpdb->print_error());
        }

        /**
         * Unserialize values
         */
        foreach ($submissions as &$submission) {
            foreach ($submission as $elementId => &$value) {
                if (!empty($nonSplittedValues[$submission->id]) && is_numeric($elementId)) {
                    $value    = $nonSplittedValues[$submission->id][$elementId];
                } else {
                    $value    = maybe_unserialize($value);
                }
            }
        }

        $submissions    = apply_filters('tsjippy_retrieved_formdata', $submissions, $userId, $this);

        return $submissions;
    }

    /**
     * Set formresults of the current form
     *
     * @param    int        $userId            Optional the user id to get the results of. Default null
     * @param    int        $submissionId    Optional a specific id. Default null
     * @param    bool    $all            Whether to retrieve all submissions or paged
     * @param    bool    $force            Whether to retrieve submissions even if already done
     */
    public function parseSubmissions($userId = null, $submissionId = null, $all = false, $force = false)
    {
        // no need to this again
        if (!empty($this->submissions) && !$force && empty($submissionId)) {
            return;
        }

        $this->submissions        = $this->getSubmissions($userId, $submissionId, $all);

        if (count($this->submissions) == 1) {
            $this->submission    = array_values($this->submissions)[0];
        } elseif (!empty($submissionId)) {
            $this->submission    = $this->submissions[0];
        }
    }

    /**
     * Adds a new column setting for a new element
     *
     * @param object    $element    the element to check if column settings exists for
     * @param array        $elementIds    optional array of element ids that belong to this column
     *
     * @return false|array            false if no column setting was added, array of column settings if added
     */
    public function addColumnSetting($element, $elementIds = [])
    {
        //do not show non-input elements
        if (in_array($element->type, $this->nonInputs)) {
            return false;
        }

        $this->columnSettings[$element->id] = [
            'slug'                => $element->slug,
            'name'                => empty($element->name) ? $element->slug : $element->name,
            'show'                => 1,
            'edit_right_roles'    => [],
            'view_right_roles'    => []
        ];

        // Only add element ids if available
        if (!empty($elementIds)) {
            $this->columnSettings[$element->id]['elementIds']    = $elementIds;
        }
    }

    /**
     * Updates column settings with missing columns
     */
    protected function enrichColumnSettings()
    {
        if ($this->enriched) {
            return;
        }

        if (empty($this->formData) || is_numeric($this->shortcodeId)) {
            $result    = $this->loadShortcodeData();

            if (is_wp_error($result)) {
                return $result;
            }

            $this->getForm($this->tableSettings->form_id);

            if (empty($this->tableSettings)) {
                $this->tableSettings    = new \stdClass();
            }

            if (empty($this->tableSettings->edit_right_roles)) {
                $this->tableSettings->edit_right_roles    = $this->formData->full_right_roles;
            }
        }

        $this->enriched    = true;

        /**
         * Get all splitted elements that share the same name and add the ids to the column settings
         */
        $elementIds    = $this->findSplitElementIds();
        $relatedIds    = [];
        if (!empty($elementIds)) {
            // loop over all base names that data should be splitted on
            foreach ($elementIds as $baseName => $names) {
                if (is_numeric($baseName)) {
                    if (isset($this->columnSettings[$names])) {
                        $this->columnSettings[$names]['elementIds']    = [$names];
                    }
                    continue;
                }

                // loop over all sub names
                foreach ($names as $name => $elIds) {
                    // create an array to lookup by elid
                    foreach ($elIds as $elId) {
                        $relatedIds[$elId]    = $elIds;
                    }

                    $id = array_values($elIds)[0];
                    if (!isset($this->columnSettings[$id])) {
                        // Use the generic name to create the column setting
                        $element    = $this->getElementById($id);
                        $element->slug = strtolower(str_replace(' ', '-', $name));
                        $element->name = ucfirst($name);

                        $this->addColumnSetting($element, $relatedIds[$id]);
                    }

                    $this->columnSettings[$id]['elementIds']    = $elIds;
                }
            }
        }

        //loop over all elements to build a new array
        foreach ($this->formElements as $element) {

            $id = $element->id;
            // If it has related ids, its already added above
            if (!empty($relatedIds[$id])) {
                continue;   
            }

            if (!empty($this->columnSettings[$id])) {
                // edit permissions
                if (!isset($this->columnSettings[$id]['edit_right_roles'])) {
                    $this->columnSettings[$id]['edit_right_roles']    = [];
                }

                // View permissions
                if (!isset($this->columnSettings[$id]['view_right_roles'])) {
                    $this->columnSettings[$id]['view_right_roles']    = [];
                }
            }

            //check if the element is in the array, if not add it
            if (!isset($this->columnSettings[$id])) {
                $this->addColumnSetting($element, $relatedIds[$id] ?? []);
            }
        }

        //Add a row for each table action as well
        $actions    = [];
        foreach ($this->formData->actions as $action) {
            $actions[]    = $action;
        }

        $actions = apply_filters('tsjippy_form_actions', $actions);
        foreach ($actions as $action) {
            if (!isset($this->columnSettings[$action]) || !is_array($this->columnSettings[$action])) {
                $this->columnSettings[$action] = [
                    'slug'                => $action,
                    'name'                => $action,
                    'show'                => 1,
                    'edit_right_roles'    => [],
                    'view_right_roles'    => []
                ];
            }
        }

        $names    = [];
        //put hidden columns on the end and do not show same names twice
        foreach ($this->columnSettings as $key => $setting) {
            if (!is_array($setting)) {
                continue;
            }

            if (in_array($setting['name'], $names)) {
                //remove the duplicate element: same name but different id
                unset($this->columnSettings[$key]);
            }

            $names[]    = $setting['name'];

            if (!$setting['show']) {

                //remove the element
                unset($this->columnSettings[$key]);

                //add it again, at the end of the array
                $this->columnSettings[$key] = $setting;
            }
        }
    }

    protected function getRowContents($tr)
    {
        $excelRow    = [];

        if (
            $this->submission->user_id == $this->user->ID ||
            $this->submission->user_id == $this->user->partnerId
        ) {
            $ownEntry    = true;
        } else {
            $ownEntry    = false;
        }

        $rowHasContents  = false;
        $iconUrl         = TSJIPPY\pathToUrl(PLUGINPATH . 'pictures/copy.png');

        foreach ($this->columnSettings as $elementId => $columnSetting) {

            if (
                !is_array($columnSetting) ||
                !$columnSetting['show'] ||
                !is_numeric($elementId)
            ) {
                continue;
            }

            $value         = '';
            $orgFieldValue = $value;

            //if we lack view permission, do not show this cell
            if (
                (
                    !$ownEntry ||
                    (                                                                           //not our own entry
                        $ownEntry &&                                                            //or it is our own
                        !in_array('own', (array)$columnSetting['view_right_roles'] ?? [])       //but we are not allowed to see it
                    )
                )    &&
                !$this->tableEditPermissions &&                                                 //no permission to edit the table and
                !empty($columnSetting['view_right_roles'] ?? []) &&                                   // there are view right permissions defined
                !array_intersect($this->userRoles, $columnSetting['view_right_roles'] ?? [])          // and we do not have the view right role
            ) {
                //later on there will be a row with data in this column
                if (
                    $this->ownData &&                                                           // we are only showing own data
                    in_array('own', $columnSetting['view_right_roles'] ?? [])                         // and this column can be viewed by owner
                ) {
                    $value = 'X'; // we cannot see this value, but we can see other values in this column
                } else {
                    continue;
                }
            }

            //if this row has no value in this column remove the row
            if (
                !empty($this->tableSettings->hide_row) &&                                        // There is a column defined
                $columnSetting['name'] == $this->tableSettings->hide_row &&                      // We are currently checking a cell in that column
                (
                    (
                        empty($values[$this->tableSettings->hide_row]) &&                        // The cell has no value
                        empty($values[trim($this->tableSettings->hide_row, '[]')])               // also check the name without []
                    )
                ) &&
                !array_intersect($this->userRoles, (array)$columnSetting['edit_right_roles'] ?? [])    &&        // And we have no right to edit this specific column
                !$this->tableEditPermissions                                                            // and we have no right to edit all table data
            ) {
                return;
            }

            if (
                in_array('own', $columnSetting['edit_right_roles'] ?? []) &&
                $ownEntry ||
                array_intersect($this->userRoles, $columnSetting['edit_right_roles'] ?? []) ||
                $this->tableEditPermissions
            ) {
                $elementEditRights = true;
            } else {
                $elementEditRights = false;
            }

            $attributes = [];

            /*
                Write the content to the cell, convert to something if needed
            */
            $class          = $columnSetting['slug'];

            $elementName    = $columnSetting['name'];

            //add field value if we are allowed to see it
            if ($value != 'X') {
                $rowHasContents    = true;

                /**
                 * Find splitted element values
                 */
                if (in_array($elementId, $columnSetting['elementIds'] ?? []) ) {
                    if(!empty($this->submission->sub_id)){
                        $attributes["data-subid"] = $this->submission->sub_id;
                    }

                    // Find the splitted value
                    foreach ($columnSetting['elementIds'] as $id) {
                        if (!empty($this->submission->{$id})) {
                            $value    = $this->submission->{$id};
                            break;
                        }
                    }
                }

                /**
                 * Find regular values
                 */
                if (isset($this->submission->{$elementId})) {
                    $value    = $this->submission->{$elementId};
                } elseif (isset($this->submission->{$elementName})) {
                    $value    = $this->submission->{$elementName};
                } elseif (isset($this->submission->{$class})) {
                    $value    = $this->submission->{$class};
                } elseif(empty($value)) {
                    $value    = 'X';
                }

                if ($value === null) {
                    $value = '';
                }

                //transform if needed
                $orgFieldValue    = $value;

                $value            = apply_filters('tsjippy-form-result-table-value', $value, $columnSetting, $this->submission, $this);
                $value            = $this->transformInputData($value, $class, $this->submission);

                //show original email in excel
                if (gettype($value) == 'string' && str_contains($value, '@')) {
                    $excelRow[]        = $orgFieldValue;
                } elseif (gettype($value) == 'string' && str_contains($value, '<a href=') && str_contains($value, 'form_upload')) {
                    // add the url to excel
                    preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $value, $match);

                    if (!empty($match[0][0])) {
                        $excelRow[]    = $match[0][0];
                    } else {
                        $excelRow[]    = $orgFieldValue;
                    }
                } else {
                    $excelRow[]        = wp_strip_all_tags($value);
                }

                //Display an X if there is nothing to show
                if ($value == '') {
                    $value = "X";
                }

                //Limit url cell width, for strings with a visible length of more then 30 characters
                if (strlen(wp_strip_all_tags($value)) > 30 && !str_contains($value, 'https://')) {
                    $class .= ' limit-length';
                }
            }

            //Add classes to the cell
            if ($elementName == "displayname") {
                $class .= ' sticky';
            }

            if (!empty($this->hiddenColumns[$columnSetting['slug']])) {
                $class    .= ' hidden';
            }

            if (isset($columnSetting['copy'])) {
                $class    .= ' copy-wrapper';
            }

            //if the user has one of the roles defined for this element
            if ($elementEditRights && $elementName != 'id') {
                $class    .= ' edit forms-table';
            }

            $attributes['class'] = trim($class);

            //Convert underscores to spaces, but not in urls
            if (!str_contains($value, 'href=')) {
                $value    = str_replace('_', ' ', $value);
            }

            if (!empty($columnSetting['width'])) {
                $attributes['style']    = "max-width:{$columnSetting['width']}px;width:{$columnSetting['width']}px;min-width:{$columnSetting['width']}px;text-wrap: balance;";
            }

            // for action buttons there is no element id
            if ($elementId) {
                $attributes['data-element-id'] = $elementId;
            }

            /**
             * Filters the cell attributes
             * 
             * @param   array   $attributes             The td attributes
             * @param   object  $displayFormResults     The current instance
             * @param   array   $columnSetting          The current column settings array
             * @param   array   $submission             The current submission
             */
            $attributes    = apply_filters('tsjippy-formresult-cell-attributes', $attributes, $this, $columnSetting, $this->submission);

            $td = addElement('td', $tr, $attributes);

            TSJIPPY\addRawHtml($value, $td);

            // Add a copy option to the value
            if (isset($columnSetting['copy'])) {
                addElement(
                    'img',
                    $td,
                    [
                        'class'   => 'copy',
                        'src'     => $iconUrl,
                        'width'   => '20',
                        'height'  => '20',
                        'loading' => 'lazy',
                        'title'   => 'Click to copy cell contents'
                    ],
                    '',
                    'afterBegin'
                );
            }
        }

        $this->excelContent[] = $excelRow;

        // none of the cells in this row has a value, only X
        if (!$rowHasContents) {
            return false;
        }

        return true;
    }

    /**
     * Action Buttons
     */
    protected function actionButtons($row)
    {
        if (empty($this->formData->actions)) {
            return;
        }

        $attributes = [];

        //loop over all the actions
        foreach ($this->formData->actions as $action) {
            if (
                !$this->tableEditPermissions                  &&      // if we are notallowed to do all actions
                $this->submission->user_id != $this->user->ID &&      //  this is not our own entry
                !array_intersect($this->userRoles, (array)$this->columnSettings[$action]['edit_right_roles'] ?? [])
            ) {
                continue;
            }

            /**
             * check if this submission is already archived, in that case make it an unarchive button
             */
            if (
                $action == 'archive' &&
                $this->showArchived &&
                (
                    $this->submission->archived ||
                    !empty($this->submission->archived)
                )
            ) {
                $action = 'unarchive';
            }

            $attributes[$action]    = [
                "class" => "$action button forms-table-action",
                "name"  => "{$action}-action",
                "value" => $action,
                "text"  => ucfirst($action)
            ];
        }

        /**
         * Filters the avaiable buttons and their attributes
         * 
         * @param   array   $attributes Array of arrays with attributes
         * @param   object  $submission The current submission
         * @param   object  $object     The current DisplayFormResults object
         */
        $attributes = apply_filters('tsjippy-formresults-row-actions', $attributes, $this->submission, $this);

        $cell       = addElement('td', $row);
        //we have the attributes now, check for which one we have permission
        foreach ($attributes as $action => $buttonAttributes) {
            $text   = $buttonAttributes['text'];
            unset($buttonAttributes['text']);
            addElement('button', $cell, $buttonAttributes, $text);
        }
    }

    /**
     * Writes a row of the table to the screen
     *
     */
    protected function writeTableRow($body)
    {
        $attributes = [
            'class'              => 'table-row',
            'data-submission-id' => $this->submission->id
        ];

        if (isset($this->submission->sub_id)) {
            $attributes['data-subid'] = $this->submission->sub_id;
        }

        $tr = addElement(
            'tr',
            $body,
            $attributes
        );


        if (!$this->getRowContents($tr)) {
            $tr->remove();
            return false;
        }

        $this->actionButtons($tr);

        return true;
    }

    /**
     * Get shortcode settings from db
     */
    public function loadShortcodeData()
    {
        global $wpdb;

        if (
            !isset($this->shortcodeId) ||
            !is_numeric($this->shortcodeId) ||
            $this->shortcodeId == -1
        ) {
            if (is_numeric($_POST['shortcode-id'] ?? '')) {
                $this->shortcodeId    = $_POST['shortcode-id'];
            } else {
                return new WP_Error('forms', 'no shortcoode id');
            }
        }

        $this->tableSettings         = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM %i WHERE id = %d",
                $this->shortcodeTable,
                $this->shortcodeId
            )
        )[0];

        foreach ($this->tableSettings as $key => &$value) {
            $value    = maybe_unserialize($value);
        }

        $this->columnSettings        = [];
        $results                     = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM %i WHERE shortcode_id = %d ORDER BY `priority` ASC",
                $this->shortcodeColumnSettingsTable,
                $this->shortcodeId
            ),
            ARRAY_A
        );

        foreach ($results as $setting) {
            // do not add if the element does not exist anymore
            if (
                is_numeric($setting['element_id']) &&
                $setting['element_id']    > -1 &&
                !isset($this->elementMapping['id'][$setting['element_id']])
            ) {
                continue;
            }

            //unserialize the values
            foreach ($setting as &$value) {
                $value    = maybe_unserialize($value);
            }

            $this->columnSettings[$setting['element_id']] = $setting;
        }

        return true;
    }

    /**
     * Show the column settings form
     *
     * @param string $class            Optional class to add to the form
     * @param array $viewRoles        Array of roles that can be selected for view permissions
     * @param array $editRoles        Array of roles that can be selected for edit permissions
     *
     * @return void
     */
    protected function columnSettingsForm($class, $viewRoles, $editRoles)
    {
    ?>
        <div class="tabcontent" id="column-settings-<?php echo esc_attr($this->shortcodeId); ?>">
            <form class="sortable-column-settings-rows">
                <input type='hidden' class='no-reset' name='shortcode-id' value='<?php echo esc_attr($this->shortcodeId); ?>'>
                <input type='hidden' class='no-reset' name='form-id' value='<?php echo esc_attr($this->formData->id); ?>'>

                <table class='tsjippy table' style='display:table'>
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
                        foreach ($this->columnSettings as $elementIndex => $columnSetting) {
                            if (!isset($columnSetting['slug'])) {
                                continue;
                            }

                            $name    = $columnSetting['name'];
                            if (empty($name)) {
                                $name = ucfirst(str_replace('-', ' ', $columnSetting['name']));
                            }

                            $width        = empty($columnSetting['width']) ? 200 : $columnSetting['width'];

                            if (!$columnSetting['show']) {
                                $visibility    = 'invisible';
                            } else {
                                $visibility    = 'visible';
                            }
                            $icon            = "<img class='visibility-icon $visibility' src='" . TSJIPPY\PICTURESURL . "/$visibility.png' width='20px' loading='lazy' style='min-width:20px;'>";

                        ?>
                            <tr class="column-setting-wrapper" data-element-id="<?php echo esc_attr($elementIndex); ?>">
                                <input type="hidden" class="no-reset" name="column-settings[<?php echo esc_attr($elementIndex); ?>][column-id]" value="<?php echo $columnSetting['id'] ?? -9; ?>">
                                <input type="hidden" class="no-reset" name="column-settings[<?php echo esc_attr($elementIndex); ?>][slug]" value="<?php echo $columnSetting['slug'] ?? ''; ?>">
                                <td>
                                    <span class="movecontrol formfield-button" aria-hidden="true">:::</span>
                                </td>
                                <td>
                                    <span class="column-settings" style="margin-right:0px;">
                                        <?php echo $columnSetting['slug']; ?>
                                    </span>
                                </td>
                                <td>
                                    <input type="text" class="column-settings" name="column-settings[<?php echo esc_attr($elementIndex); ?>][nice-name]" value="<?php echo $name; ?>" style="margin-right:0px;">
                                </td>
                                <td>
                                    <input type="hidden" class="no-reset" name="column-settings[<?php echo esc_attr($elementIndex); ?>][show]" value="<?php echo $columnSetting['show']; ?>">
                                    <span class="visibility-icon">
                                        <?php echo $icon; ?>
                                    </span>
                                </td>
                                <?php
                                //only add view permission for numeric elements others are buttons
                                if (is_numeric($elementIndex)) {
                                ?>
                                    <td style='max-width:200px;text-wrap: auto; text-align: left;'>
                                        <select class='column-settings inline' name='column-settings[<?php echo esc_attr($elementIndex); ?>][view-right-roles][]' multiple='multiple' style="margin-right:0px;">
                                            <?php
                                            foreach ($viewRoles as $key => $roleName) {
                                                if (isset($columnSetting['view_right_roles']) && in_array($key, (array)$columnSetting['view_right_roles'])) {
                                                    $selected = 'selected="selected"';
                                                } else {
                                                    $selected = '';
                                                }
                                                echo "<option value='$key' $selected>$roleName</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                <?php
                                } else {
                                ?>
                                    <td class='column-settings'></td>
                                <?php
                                }
                                ?>
                                <td style='max-width:200px;text-wrap: auto; text-align: left;'>
                                    <select class='column-settings inline' name='column-settings[<?php echo esc_attr($elementIndex); ?>][edit-right-roles][]' multiple='multiple' style="margin-right:0px;">
                                        <?php
                                        foreach ($editRoles as $key => $roleName) {
                                            if (isset($columnSetting['edit_right_roles']) && @in_array($key, (array)$columnSetting['edit_right_roles'])) {
                                                $selected = 'selected="selected"';
                                            } else {
                                                $selected = '';
                                            }
                                            echo "<option value='$key' $selected>$roleName</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="column-settings" name="column-settings[<?php echo esc_attr($elementIndex); ?>][width]" value="<?php echo esc_attr($width); ?>" placeholder="200" min="100" style="max-width: 80px; margin-right:0px;">px
                                </td>
                                <td>
                                    <input type="checkbox" class="column-settings" name="column-settings[<?php echo esc_attr($elementIndex); ?>][copy]" value="1" <?php if (isset($columnSetting['copy'])) {
                                                                                                                                                                        echo 'checked';
                                                                                                                                                                    } ?> style="max-width: 40px; margin-right:0px;">
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php
                TSJIPPY\addSaveButton('submit_column_setting', 'Save table column settings');
                ?>
            </form>
        </div>
    <?php
    }

    /**
     * Show the table settings form
     *
     * @param string $class            Optional class to add to the form
     * @param array $viewRoles        Array of roles that can be selected for view permissions
     * @param array $editRoles        Array of roles that can be selected for edit permissions
     *
     * @return void
     */
    protected function tableSettingsForm($class, $viewRoles, $editRoles)
    {
    ?>
        <div class="tabcontent <?php echo esc_attr($class); ?>" id="table-rights-<?php echo esc_attr($this->shortcodeId); ?>">
            <form>
                <input type='hidden' class='no-reset' class='shortcode-settings' name='shortcode-id' value='<?php echo $this->shortcodeId; ?>'>
                <input type='hidden' class='no-reset' class='shortcode-settings' name='form-id' value='<?php echo $this->formData->id; ?>'>

                <h4>Set the title for the results table</h4>
                <input type='text' name="table-settings[title]" value='<?php echo $this->tableSettings->title; ?>' style='width:500px;'>

                <div class="table-rights-wrapper">
                    <h4>Select the default column the table is sorted on</h4>
                    <select name="table-settings[default-sort]">
                        <?php
                        if ($this->tableSettings->default_sort == '') {
                            ?><option value='' selected>---</option><?php
                        } else {
                            ?><option value=''>---</option><?php
                        }

                        foreach ($this->columnSettings as $key => $columnSetting) {
                            if (!is_array($columnSetting)) {
                                continue;
                            }

                            $name = $columnSetting['name'];

                            //Check which option is the selected one
                            if ($this->tableSettings->default_sort != '' && $this->tableSettings->default_sort == $key) {
                                $selected = 'selected="selected"';
                            } else {
                                $selected = '';
                            }
                            echo "<option value='$key' $selected>$name</option>";
                        }
                            ?>
                    </select>

                    <h4>Select the sort direction</h4>
                    <label>
                        <input type='radio' name='table-settings[sort-direction]' id='sort-direction' value='asc' <?php if (($this->tableSettings->sort_direction ?? '') == 'asc') {
                                                                                                                        echo 'checked';
                                                                                                                    } ?>>
                        Ascending
                    </label>
                    <label>
                        <input type='radio' name='table-settings[sort-direction]' id='sort-direction' value='dsc' <?php if (($this->tableSettings->sort_direction ?? '') == 'dsc') {
                                                                                                                        echo 'checked';
                                                                                                                    } ?>>
                        Decending
                    </label>
                </div>
                <br>
                <div class="table-filters-wrapper" style='margin-top:10px;'>
                    <h4>Select the fields the table can be filtered on</h4>
                    <table class='clone-divs-wrapper' style='border: none;'>
                        <?php
                        $filters    = $this->tableSettings->filter;

                        if (!is_array($this->tableSettings->filter ?? '')) {
                            $this->tableSettings->filter    = [];
                            $filters    = [''];
                        }

                        foreach ($filters as $index => $filter) {
                        ?>
                            <tr class='clone-div' data-div-id='<?php echo esc_attr($index); ?>' style='border: none;'>
                                <td style='border: none;'>
                                    <select name='table-settings[filter][<?php echo esc_attr($index); ?>][element]' class='inline'>
                                        <?php
                                        foreach ($this->columnSettings as $key => $columnSetting) {

                                            if (!is_array($columnSetting)) {
                                                continue;
                                            }

                                            $name = $columnSetting['name'];

                                            //Check which option is the selected one
                                        ?>
                                            <option value='<?php echo esc_attr($key); ?>' <?php if ($this->tableSettings->filter[$index]['element'] == $key) {
                                                                                                echo 'selected="selected"';
                                                                                            } ?>>
                                                <?php echo esc_html($name); ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td style='border: none;'>
                                    filter type
                                    <select name='table-settings[filter][<?php echo esc_attr($index); ?>][type]' class='inline'>
                                        <?php
                                        foreach (['>=', '<', '==', 'like'] as $type) {
                                        ?>
                                            <option value='<?php echo esc_attr($type); ?>' <?php if ($this->tableSettings->filter[$index]['type'] == $type) {
                                                                                                echo 'selected="selected"';
                                                                                            } ?>>
                                                <?php echo esc_html($type); ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td style='border: none;'>
                                    filter type
                                    <select name='table-settings[filter][<?php echo esc_attr($index); ?>][type]' class='inline'>
                                        <?php
                                        foreach (['>=', '<', '==', 'like'] as $type) {
                                        ?>
                                            <option value='<?php echo esc_attr($type); ?>' <?php if ($this->tableSettings->filter[$index]['type'] == $type) {
                                                                                                echo 'selected="selected"';
                                                                                            } ?>>
                                                <?php echo esc_html($type); ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td style='border: none;'>
                                    Filter name
                                    <input name='table-settings[filter][<?php echo esc_attr($index); ?>][name]' value='<?php echo esc_attr($this->tableSettings->filter[$index]['name']); ?>'>
                                </td>
                                <td style='border: none;'>
                                    <button type='button' class='add button'>+</button>
                                </td>
                                <td style='border: none;'>
                                    <button type='button' class='remove button'>-</button>
                                </td>
                            </tr>
                        <?php
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
                        if (($this->tableSettings->hide_row ?? '') == '') {
                            ?><option value='' selected>---</option><?php
                        } else {
                            ?><option value=''>---</option><?php
                        }

                        foreach ($this->columnSettings as $key => $columnSetting) {
                            if (!is_array($columnSetting)) {
                                continue;
                            }

                            $name = $columnSetting['name'];

                            //Check which option is the selected one
                            if (($this->tableSettings->hide_row ?? '') == $columnSetting['name']) {
                                $selected = 'selected="selected"';
                            } else {
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
                        <option value="personal" <?php if (($this->tableSettings->result_type ?? '') == 'personal') {
                                                        echo 'selected';
                                                    } ?>>Only personal</option>
                        <option value="all" <?php if (($this->tableSettings->result_type ?? '') == 'all') {
                                                echo 'selected';
                                            } ?>>All the viewer has permission for</option>
                    </select>
                    <br>
                    <label>
                        <input type='checkbox' name='table-settings[split-table]' value='1' <?php if (isset($this->tableSettings->split_table) && $this->tableSettings->split_table) {
                                                                                                echo 'checked';
                                                                                            } ?>>
                        Split the results in own entries and others entries
                    </label>

                </div>

                <div class="table-rights-wrapper">
                    <h4 class="label">Select if you want to view archived results by default</h4>
                    <?php
                    if ($this->tableSettings->archived ?? false) {
                        $checked1    = 'checked';
                        $checked2    = '';
                    } else {
                        $checked1    = '';
                        $checked2    = 'checked';
                    }
                    ?>
                    <label>
                        <input type="radio" name="table-settings[archived]" value="1" <?php echo $checked1; ?>>
                        Yes
                    </label>
                    <label>
                        <input type="radio" name="table-settings[archived]" value="0" <?php echo $checked2; ?>>
                        No
                    </label>
                </div>

                <!-- We can define auto archive field both on table and on form settings-->
                <div class="table-rights-wrapper">
                    <h4 class="label">Auto archive results</h4>
                    <?php
                    if ($this->formData->autoarchive ?? false) {
                        $checked1    = 'checked';
                        $checked2    = '';
                    } else {
                        $checked1    = '';
                        $checked2    = 'checked';
                    }
                    ?>
                    <label>
                        <input type="radio" name="form-settings[autoarchive]" value="1" <?php echo $checked1; ?>>
                        Yes
                    </label>
                    <label>
                        <input type="radio" name="form-settings[autoarchive]" value="0" <?php echo $checked2; ?>>
                        No
                    </label>
                    <br>
                    <br>
                    <div class='auto-archive-logic <?php if ($checked1 == '') {
                                                        echo 'hidden';
                                                    } ?>'>
                        Auto archive a (sub) entry when field<br>
                        <select name="form-settings[autoarchive-el]" class='inline' style="margin-right:10px;">
                            <?php
                            if (empty($this->formData->autoarchive_el)) {
                                ?><option value='' selected>---</option><?php
                            } else {
                                ?><option value=''>---</option><?php
                            }

                            foreach ($this->columnSettings as $key => $columnSetting) {
                                if (!is_array($columnSetting)) {
                                    continue;
                                }

                                $name = $columnSetting['name'];

                                //Check which option is the selected one
                                if ($this->formData->autoarchive_el != '' && $this->formData->autoarchive_el == $key) {
                                    $selected = 'selected="selected"';
                                } else {
                                    $selected = '';
                                }
                                echo "<option value='$key' $selected>$name</option>";
                            }
                                ?>
                        </select>
                        <label style="margin:0 10px;">equals</label>
                        <input type='text' class='wide' name="form-settings[autoarchive-value]" value="<?php echo $this->formData->autoarchive_value ?? ''; ?>" style='max-width:200px;'>

                        <?php
                        echo $this->infoBoxHtml("You can use placeholders like '%today%+3days' for a value");
                        ?>
                    </div>
                </div>

                <?php
                do_action('tsjippy-formstable-after-table-settings', $this);
                ?>

                <div style='margin-top:10px;'>
                    <button class='button table-permissions-rights-form' type='button'>Advanced</button>
                    <div class='permission-wrapper hidden'>
                        <?php
                        // Splitted fields
                        $foundElements = [];
                        foreach ($this->formElements as $key => $element) {
                            $pattern = "/([^\[]+)\[[0-9]*\]/i";

                            if (
                                preg_match($pattern, $element->slug, $matches)    &&        // preg match was succesfull
                                !in_array($matches[1], $foundElements)                    // the match is not yet in the found elements
                            ) {
                                $foundElements[$element->id]    = $matches[1];
                            }
                        }

                        if (!empty($foundElements)) {
                        ?>
                            <div class="table-rights-wrapper">
                                <h4>Select fields where you want to create seperate rows for</h4>
                                <?php

                                foreach ($foundElements as $id => $element) {
                                    $name    = ucfirst(strtolower(str_replace('_', ' ', $element)));

                                    //Check which option is the selected one
                                    if (is_array($this->formData->split) && in_array($id, $this->formData->split)) {
                                        $checked = 'checked';
                                    } else {
                                        $checked = '';
                                    }
                                    echo "<label>";
                                    echo "<input type='checkbox' name='form-settings[split][]' value='$id' $checked>   ";
                                    esc_html($name);
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
                                foreach ($viewRoles as $key => $roleName) {
                                    if (in_array($key, (array)$this->tableSettings->view_right_roles)) {
                                        $selected = 'selected';
                                    } else {
                                        $selected = '';
                                    }
                                    echo "<option value='$key' $selected>$roleName</option>";
                                }
                                ?>
                            </select>

                            <br>
                            <h4>Select users with permission to VIEW the table</h4>
                            <?php
                            TSJIPPY\userSelect(onlyAdults: true, id: "table-settings[view-right-roles][]", userId: $this->tableSettings->view_right_roles, excludeIds: [1], multiple: true, echo: true);
                            ?>

                            <h4>Select roles with permission to edit ALL form submission data</h4>

                            <select name='table-settings[edit-right-roles][]' multiple>
                                <option value=''>---</option>
                                <?php
                                foreach ($viewRoles as $key => $roleName) {
                                    if (in_array($key, (array)$this->tableSettings->edit_right_roles)) {
                                        $selected = 'selected';
                                    } else {
                                        $selected = '';
                                    }
                                    echo "<option value='$key' $selected>$roleName</option>";
                                }
                                ?>
                            </select>

                            <br>
                            <h4>Select users with permission to EDIT the table</h4>
                            <?php
                            TSJIPPY\userSelect(onlyAdults: true, id: "table-settings[edit-right-roles][]", userId: $this->tableSettings->edit_right_roles ?? [], excludeIds: [1], multiple: true, echo: true);
                            ?>
                        </div>
                    </div>
                </div>
                <?php
                TSJIPPY\addSaveButton('submit_table_setting', 'Save table settings');
                ?>
            </form>
        </div>
    <?php
    }

    /**
     * Print the modal to change table settings to the screen
     */
    protected function addShortcodeSettingsModal()
    {
        global $wp_roles;

        //Get all available roles
        $userRoles                     = $wp_roles->role_names;

        $viewRoles                    = $userRoles;
        $viewRoles['everyone']        = 'Everyone';
        $viewRoles['own']             = 'Own entries';

        $editRoles                    = $userRoles;
        $editRoles['own']             = 'Own entries';

        //Sort the roles
        asort($viewRoles);
        asort($editRoles);

        //Table rights active
        if (empty($this->tableSettings)) {
            $active1    = '';
            $active2    = 'active';
            $class1        = "hidden";
            $class2        = '';
            //Column settings active
        } else {
            $active1    = 'active';
            $active2    = '';
            $class1        = "";
            $class2        = "hidden";
        }

        ob_start();
    ?>
        <div class="modal form-shortcode-settings hidden">
            <!-- Modal content -->
            <div class="modal-content" style='max-width:100vw;min-width:90vw;'>
                <span id="modal-close" class="close">&times;</span>

                <button id="column-settings" class="button tablink <?php echo $active1; ?>" data-target="column-settings-<?php echo $this->shortcodeId; ?>">Column settings</button>
                <button id="table-settings" class="button tablink <?php echo $active2; ?>" data-target="table-rights-<?php echo $this->shortcodeId; ?>">Table settings</button>

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
    protected function loadTableSettings()
    {
        if ($this->tableSettings->archived || $this->showArchived) {
            $this->showArchived = true;
        } else {
            $this->showArchived = false;
        }

        //check if we have rights on this form
        if (!isset($this->formEditPermissions) || !$this->formEditPermissions) {
            if (
                array_intersect(                                                       // We have full rights to the forms
                    (array)$this->userRoles,
                    array_keys((array)$this->formData->full_right_roles)
                )    ||
                (
                    isset($this->tableSettings->full_right_roles) &&                    // we have full rights to the table
                    array_intersect(
                        (array)$this->userRoles,
                        array_keys((array)$this->tableSettings->full_right_roles)
                    )
                )    ||
                $this->editRights                                                        // we have edit rights on the form
            ) {
                $this->formEditPermissions = true;
            } else {
                $this->formEditPermissions = false;
            }
        }

        //check if we have rights on this table
        if (!isset($this->tableEditPermissions) || !$this->tableEditPermissions) {
            if (
                array_intersect($this->userRoles, (array)$this->tableSettings->edit_right_roles) ||
                in_array($this->userId, (array)$this->tableSettings->edit_right_roles)
            ) {
                $this->tableEditPermissions = true;
            } else {
                $this->tableEditPermissions = false;
            }

            $this->tableEditPermissions    = apply_filters('tsjippy-table-edit-permissions', $this->tableEditPermissions, $this);
        }

        $this->tableViewPermissions    = true;
        if (
            $this->onlyOwn                                            ||
            (
                ($this->tableSettings->result_type ?? '') == 'personal'    &&
                !$this->all
            )    ||
            !$this->tableEditPermissions                            &&
            !array_intersect($this->userRoles, (array)$this->tableSettings->view_right_roles) &&
            !in_array($this->userId, (array)$this->tableSettings->view_right_roles) &&
            !wp_doing_cron()
        ) {
            $this->tableViewPermissions     = false;
        }

        $this->tableViewPermissions    = apply_filters('tsjippy-table-view-permissions', $this->tableViewPermissions, $this);
    }

    /**
     * Renders the table filter html
     *
     * @return string    The html
     */
    protected function renderFilterForm($parent = '')
    {
        $html    = '';

        // Filtering not enabled
        if (empty($this->tableSettings->filter)) {
            return $html;
        }

        $filterOption    = '';
        foreach ($this->tableSettings->filter as $filter) {
            $filterElement    = $this->getElementById($filter['element']);
            $filterValue    = false;
            $filterKey        = strtolower($filter['name']);

            if (!$filterElement || empty($filterKey)) {
                continue;
            }

            if (!empty($_POST[$filterKey])) {
                $filterValue    = TSJIPPY\sanitize($_POST[$filterKey]);
            }

            $elementHtml    = $this->getElementHtml($filterElement, $parent, $filterValue);

            // make sure the name is not the element name but the filtername
            $elementHtml    = str_replace("name=\"{$filterElement->slug}\"", "name='$filterKey'", $elementHtml);

            $filterOption    .= "<span class='filter-option'>";
            $filterOption    .= "<label>" . ucfirst($filterKey) . ": </label>";
            $filterOption    .= $elementHtml;
            $filterOption    .= "</span>";
        }

        if (!empty($filterOption)) {
            $html    = "<form method='post' class='filter-options'>";
            $html    .= "<div class='filter-wrapper'>";
            $html    .= $filterOption;
            $html    .= "<button class='button filter-results' type='button' style='height: fit-content;'>Filter</button>";
            $html    .= "</div>";
            $html    .= "</form>";
        }

        return $html;
    }

    /**
     * Renders the table buttons html
     *
     * @return string    The html
     */
    public function renderTableButtons()
    {
        $html    = "<div class='table-buttons-wrapper'>";
        //Show form properties button if we have form edit permissions
        if ($this->tableEditPermissions) {
            $html    .= "<button class='button small edit-formshortcode-settings'>Edit settings</button>";
            $html    .= $this->addShortcodeSettingsModal();
        }

        // Archived button
        if ($this->showArchived) {
            $html    .= "<button class='button tsjippy small archive-switch-hide'>Hide archived entries</button>";
        } else {
            $html    .= "<button class='button tsjippy small archive-switch-show'>Show archived entries</button>";
        }

        // Only own button
        if (
            $this->tableViewPermissions &&
            $this->onlyOwn ||
            (($this->tableSettings->result_type ?? '') == 'personal' && !$this->all)
        ) {
            $html    .= "<button class='button tsjippy small only-own-switch-all'>Show all entries</button>";
        } elseif (
            $this->tableViewPermissions &&
            (
                !$this->onlyOwn    ||
                $this->all        ||
                ($this->tableSettings->result_type ?? '') != 'personal'
            )
        ) {
            $html    .= "<button class='button tsjippy small only-own-switch-on'>Show only my own entries</button>";
        }

        $html    .= "<button type='button' class='button small show fullscreenbutton'>Show full screen</button>";

        $hidden    = '';
        if (empty($this->hiddenColumns)) {
            $hidden    = 'hidden';
        }
        $html    .= "<button type='button' class='button small reset-col-vis $hidden' data-form-id='{$this->formData->id}'>Reset visibility</button>";
        $html    .= "</div>";

        $html    .= $this->renderFilterForm();

        return $html;
    }

    /**
     * Gets an empty table
     */
    public function emptyTable($parent = '')
    {
        $table  = addElement(
            'table',
            $parent,
            [
                'class'             => 'tsjippy table form-data-',
                'data-form-id'      => $this->formData->id,
                'data-shortcode-id' => $this->shortcodeId
            ]
        );

        addElement('td', $table, [], 'No records found');

        if (empty($parent)) {
            return $table->ownerDocument->saveHTML();
        }
    }

    /**
     * creates the main table html
     *
     * @param    string        $type            Either 'own', 'others' or 'all'
     * @param    array        $submissions    Array of Submissions
     *
     * @return    bool                        If there are submissions or not
     */
    public function theTable($type, $submissions, $parent)
    {
        $table  = addElement(
            'table',
            $parent,
            [
                'class'             => 'tsjippy table form-data',
                'data-form-id'      => $this->formData->id,
                'data-shortcode-id' => $this->shortcodeId,
                'data-type'         => esc_attr($type),
                'data-page'         => $this->currentPage,
                'style'             => 'position: relative;z-index: 999;',
            ]
        );

        $this->resultTableHead($type, $table);

        $body   = addElement('tbody', $table, ['class' => "table-body"]);

        $allRowsEmpty    = true;
        foreach ($submissions as $this->submission) {
            // Skip if needed
            if ($type == 'others' && $this->submission->user_id == $this->user->ID) {
                continue;
            }

            if ($this->writeTableRow($body)) {
                // this row has contents
                $allRowsEmpty    = false;
            }
        }

        if ($allRowsEmpty) {
            $table->remove();

            $this->emptyTable($parent);
        }
    }

    /**
     * Render the navigation menu in case of multiple pages of results
     */
    public function navigationMenu($parent)
    {

        if ($this->total <= $this->pageSize) {
            return;
        }

        $pageCount =  ceil($this->total / $this->pageSize);

        $navigator  = addElement('div', $parent, ['class' => 'form-result-navigation']);

        // include a back button if we are not on the first page
        $class = 'hidden';
        if ($this->currentPage > 0) {
            $class = '';
        }

        $attributes = [
            'class' => 'button small prev',
            'name'  => 'prev',
            'value' => 'prev'
        ];

        if ($this->currentPage == 0) {
            $attributes['class']    .= ' hidden';
        }

        addElement('button', $navigator, $attributes, "← Previous");

        /**
         * show page numbers
         */
        addElement('span', $navigator, ['class' => 'page-number-wrapper']);

        for ($x = 0; $x < $pageCount; $x++) {
            $pageNr    = $x + 1;

            $class    = '';
            if ($this->currentPage == $x) {
                $class    = "current";
            }
            addElement('span', $navigator, ['class' => "page-number $class", 'data-nr' => '$x'], $pageNr);
        }

        // Include a next button if we are not on the last page
        $class = 'hidden';
        if ($this->total > $this->pageSize && $this->currentPage != $pageCount - 1) {
            $class = '';
        }

        addElement('button', $navigator, ['class' => "button small next $class", 'name' => 'next', 'value' => 'next'], "Next →");

        $pageSizeSelector    =  addElement("select", $parent, ['class' => 'page-size']);

        foreach ([1000, 500, 200, 100, 50, 40, 20, 10] as $size) {
            $attributes    = [];
            if ($this->pageSize == $size) {
                $attributes['selected']    = 'selected';
            }

            addElement('option', $pageSizeSelector, $attributes, $size);
        }
    }

    /**
     * Writes a result table to the screen
     *
     * @param    string        $type        Either 'own', 'others' or 'all'
     * @param    bool        $force        Whether to retrieve submissions even if already done
     * @param    bool        $all        Retrieve all bookings or paged, default false for paged
     *
     * @return    string|false            False on no records found, else the html
     */
    public function renderTable($type, $force = false, $all = false)
    {
        global $wpdb;

        $userId    = null;

        // Check permissions
        if (
            $this->onlyOwn ||
            !$this->tableViewPermissions ||
            isset($_REQUEST['only-own']) && $_REQUEST['only-own']
        ) {
            // we do not have permission to view someone elses submissions
            if ($type == 'others') {
                return 'You do not have permissions to see this. ';
            }
            $type        = 'own';
        }

        // Ob_start here in case the filter is echoing something
        ob_start();

        /**
         * Filter whether or not to show the table, this can be used to for example show a message instead of the table when there are no submissions or when the user has no permissions
         * @param    bool    $shouldShow    Whether or not to show the table, default true
         * @param    object    $this            The current instance of the form table class, can be used to get more information about the form and the user to decide whether or not to show the table
         * @param    string    $type            The type of results that would be shown, either 'own', 'others' or 'all'
         */
        $shouldShow    = apply_filters('tsjippy-formstable-should-show', true, $this, $type);

        ob_end_clean();
        if ($shouldShow !== true) {
            return     $shouldShow;
        }

        // get submissions for the current user only
        if ($type == 'own') {
            $userId    = get_current_user_id();

            if (!$userId) {
                if (($_REQUEST['hash'] ?? '') == wp_hash($_REQUEST['id'] ?? '')) {
                    $userId        = $_REQUEST['hash'];
                } else {
                    return $this->emptyTable();
                }
            }
        }

        // Check if we should sort the data
        if (($this->tableSettings->default_sort ?? '') != '' || isset($_REQUEST['sortcol'])) {
            // Get the sort column from $_POST
            if (isset($_REQUEST['sortcol'])) {
                $this->sortElementIds    = [$_REQUEST['sortcol']];
            }

            // Default sort elements
            else {
                $defaultSortElement     = $this->tableSettings->default_sort;
                $sortElement            = $this->getElementById($defaultSortElement);

                // check if this is an sub id, use all elements in that case
                if ($sortElement) {
                    $exploded            = explode('[', $sortElement->slug);

                    if (count($exploded) > 1) {
                        $sort                = str_replace(']', '', end($exploded));

                        $this->sortElementIds    = $wpdb->get_col(
                            $wpdb->prepare("SELECT id FROM %i WHERE `name` LIKE %s", $this->elTableName, "{$exploded[0]}[%][$sort]")
                        );
                    } else {
                        $this->sortElementIds    = [$defaultSortElement];
                    }
                }
            }
        }

        if (isset($this->tableSettings->sort_direction)) {
            $this->sortDirection    = strtoupper($this->tableSettings->sort_direction);
        }

        if (isset($_REQUEST['sortdir'])) {
            $this->sortDirection    = $_REQUEST['sortdir'];
        }

        if (isset($_REQUEST['export_pdf']) || isset($_REQUEST['export-xls'])) {
            $all    = true;
        }

        $this->parseSubmissions($userId, null, $all, $force);

        /*
            Write the header row of the table
        */
        //first check if the data contains data of our own
        $this->ownData    = false;

        if ($type != 'others') {
            foreach ($this->submissions as $submission) {
                //Our own entry or one of our partner
                if (
                    !empty($submission->user_id) &&
                    (
                        $submission->user_id == $this->user->ID ||
                        $submission->user_id == $this->user->partnerId
                    )
                ) {
                    $this->ownData = true;
                    break;
                }
            }
        }

        $wrapper    = addElement('div', '', ['class' => 'form-results-wrapper']);

        if ($type == 'own') {
            addElement('h4', $wrapper, [], "Your own submissions");
        } elseif ($type == 'others') {
            $type    = 'others';
            addElement('h4', $wrapper, [], "Submissions of others");
        }

        $this->navigationMenu($wrapper);

        $this->theTable($type, $this->submissions, $wrapper);

        $this->printTableFooter($wrapper);

        return $wrapper->ownerDocument->saveHtml();
    }

    private function printTableFooter($parent)
    {
        $footer = addElement('div', $parent, ['class' => 'tsjippy-table-footer']);

        $p      = addElement('p', $footer, ['id' => 'table-remark'], 'Click on any cell with ');

        addElement('span', $p, ['class' => "edit forms-table"], "underlined text");

        $p->append("to edit its contents.");

        addElement('br', $p);

        $p->append("Click on any header to sort the column.");

        $formWrapper    = addElement('div', $footer);

        $form           = addElement('form', $formWrapper, ['method' => "post", 'class' => "export-form", 'id' => "export-xls"]);

        addElement('button', $form, ['class' => "button button-primary", 'type' => "submit", 'name' => "export-xls"], 'Export data to excel');

        /**
         * Runs within the formwrapper div of the results table
         * 
         * @param   NodeElement $parent The parent node
         * @param   object      $object The DisplayFormResults instance
         */
        do_action('tsjippy-forms-results-table-footer', $formWrapper, $this);
    }

    /**
     * Creates the formresult table html
     *
     * @param    bool    $split    Whether or not to split in two tables, default table settings
     * @param    bool    $all    Retrieve all bookings or paged, default false for paged
     *
     * @return    string|WP_Error            The html or error on failure
     */
    public function showFormresultsTable($split = null, $all = false)
    {
        // first render the table so we now how many results we have
        $tableHtml    = '';
        if (
            (
                $split === null    &&                                    // we should use the table settings
                $this->tableSettings->split_table ?? false            // and we should split
            ) ||
            $split == true                                            // we should always split
        ) {
            $buttons        = $this->renderTableButtons();
            $tableHtml       .= $this->renderTable('own', true, $all);

            $buttons        = $this->renderTableButtons();
            $tableHtml       .= $this->renderTable('others', true, $all);
        } else {
            $buttons        = $this->renderTableButtons();
            $tableHtml        = $this->renderTable('all', false, $all);
        }

        ob_start();
        //process any $_GET acions
        do_action('tsjippy_formtable_GET_actions');
        do_action('tsjippy_formtable_POST_actions');

        //Load js
        wp_enqueue_script('tsjippy_forms_table_script');

        ?>
        <div class='form table-wrapper'>
            <div class='form table-head'>
                <h2 class="table-title"><?php echo esc_html($this->tableSettings->title ?? ''); ?></h2><br>
                <?php
                echo wp_kses_post($buttons);
                ?>
            </div>
            <?php
            echo $tableHtml;
            ?>
        </div>
        <?php

        //now we have rendered all the content we can export the excel if requested
        if (isset($_POST['export-xls'])) {
            $this->exportExcel();
        }

        //now we have rendered all the content we can export the pdf if requested
        if (isset($_POST['export-pdf'])) {
            echo $this->exportPdf();
        }

        $html    = ob_get_clean();

        return apply_filters('tsjippy-forms-form-results-html', $html, $this);
    }

    /**
     * Prints the results table head
     *
     * @param    string        $type        Either 'own', 'others' or 'all'
     */
    private function resultTableHead($type, $table)
    {
        $excelRow = [];
        $thead    = addElement('thead', $table);
        $tr       = addElement('tr', $thead);

        // Loop over the column settings
        foreach ($this->columnSettings as $elementId => $columnSetting) {

            if (
                !is_numeric($elementId)                    ||
                !$columnSetting['show']                    ||                          //hidden column
                (
                    !$this->ownData                        ||                          //The table does not contain data of our own
                    (
                        $this->ownData                      &&                         //or it does contain our own data but
                        !in_array('own', $columnSetting['view_right_roles'] ?? [])     //we are not allowed to see it
                    )
                ) &&
                !$this->tableEditPermissions                 &&                        // no permission to edit the table and
                !array_intersect($this->userRoles, $columnSetting['view_right_roles'] ?? []) // and we do not have the view right role and
            ) {
                continue;
            }

            /**
             * Build the class string
             */
            if (
                in_array($columnSetting['slug'], $this->sortElementIds) ||
                array_intersect($columnSetting['elementIds'] ?? [], $this->sortElementIds)
            ) {
                $class    = strtolower($this->sortDirection) . ' defaultsort';
            } elseif ($this->tableSettings->default_sort == $elementId) {
                $class    = "defaultsort";
            } else {
                $class    = "";
            }

            if (in_array($columnSetting['element_id'] ?? '', $this->sortElementIds)) {
                $class    = strtolower($this->sortDirection) . ' defaultsort';
            }

            if (!empty($this->hiddenColumns) && !empty($this->hiddenColumns[$columnSetting['slug']])) {
                $class    .= ' hidden';
            }

            $attributes = [
                'class'          => $class,
                'id'             => $columnSetting['slug'],
                'data-nice-name' => $columnSetting['name'],
            ];

            //Add a heading for each column
            if (!empty($columnSetting['width'])) {
                $attributes['style']    = "max-width:{$columnSetting['width']}px;width:{$columnSetting['width']}px;min-width:{$columnSetting['width']}px;text-wrap: balance;";
            }

            // add element using attribute array
            $th = addElement(
                'th',
                $tr,
                $attributes,
                $columnSetting['name']
            );

            addElement(
                'img',
                $th,
                [
                    'class'   => 'visibility-icon visible',
                    'src'     => TSJIPPY\PICTURESURL . "/visible.png",
                    'width'   => 20,
                    'height'  => 20,
                    'loading' => 'lazy'
                ]
            );

            $excelRow[]    = $columnSetting['name'];
        }

        //write header to excel
        $this->excelContent[] = $excelRow;

        //add a Actions heading if needed
        $actions = [];
        foreach ($this->formData->actions ?? [] as $action) {
            $actions[]    = $action;
        }
        $actions = apply_filters('tsjippy_form_actions', $actions);

        //we have full permissions on this table
        $addHeading    = false;
        if ($this->tableEditPermissions && !empty($actions)) {
            $addHeading    = true;
        } else {
            foreach ($actions as $action) {
                //we have permission for this specific button
                if (array_intersect($this->userRoles, (array)$this->columnSettings[$action]['edit_right_roles'] ?? [])) {
                    $addHeading    = true;
                } elseif ($type != 'others') {
                    //Loop over all submissions to see if the current user has permission for them
                    foreach ($this->submissions as $submission) {
                        //we have permission on this row for this button
                        if (
                            ($submission->user_id ?? '') == $this->user->ID    ||    // user_id is the current user
                            $submission->user_id == $this->user->ID                        // current user submitted the form

                        ) {
                            $addHeading    = true;
                        }
                    }
                }
            }
        }

        if ($addHeading) {
            addElement(
                'th',
                $tr,
                [
                    'id'             => 'actions',
                    'data-nice-name' => 'Actions'
                ],
                'Actions'
            );
        }
    }

    /**
     * New form results table
     *
     * @param    int        $formId        the id of the form
     *
     * @return    int                    The id of the new formtable
     */
    public function insertInDb($formId)
    {
        global $wpdb;

        //add new row in db
        $wpdb->insert(
            $this->shortcodeTable,
            array(
                'form_id'            => $formId,
            )
        );

        return $wpdb->insert_id;
    }

    /**
     * check for any formresults shortcode and add an id if needed
     *
     * @param    array    $data    The post data
     *
     * @return    array            The filtered post data
     */
    public function checkForFormShortcode($data)
    {
        //find any formresults shortcode
        $pattern = "/\[formresults([^\]]*{formname,slug}=(.*)[^\]]*)\]/s";

        //if there are matches
        if (preg_match_all($pattern, $data['post_content'], $matches)) {
            //loop over all the matches
            foreach ($matches[1] as $key => $shortcodeAtts) {
                //this shortcode has no id attribute
                if (!str_contains($shortcodeAtts, ' id=')) {
                    $shortcode                = $matches[0][$key];

                    $this->formData->slug     = $matches[2][$key];

                    $this->getForm();

                    $shortcodeId    = $this->insertInDb($this->formData->id);

                    $newShortcode    = str_replace('formresults', "formresults id=$shortcodeId", $shortcode);

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
