<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use stdClass;
use WP_Error;
use function TSJIPPY\addElement as addElement;

if (! defined('ABSPATH')) {
    exit;
}

class DisplayFormResults extends SubmitForm
{
    use ExportFormResults;

    public array    $columnSettings;
    public int      $currentPage;
    public bool     $enriched;
    public array    $excelContent;
    public array    $extraBlocks;
    public bool     $formEditPermissions;
    public array    $hiddenColumns;
    public bool     $ownData;
    public string   $sortColumn;
    public string   $sortDirection;
    public array    $sortBlockIds;
    public bool     $spliced;
    public array    $subBlocks;
    public bool     $tableEditPermissions;
    public object   $tableSettings;
    public bool     $tableViewPermissions;
    public int|null $total;
    public int      $shortcodeId;

    /**
     * Constructor for the DisplayFormResults class
     * @param string $shortcodeId
     */
    public function __construct($shortcodeId='', $all = false, $pageSize = 50, $formUrl = '', $userId = 0, $postId='')
    {
        global $wpdb;

        $blockId    = '';
        $postId     = '';

        if(!empty($shortcodeId)){
            $this->shortcodeId      = $shortcodeId;
            $this->shortcodeTable   = $wpdb->prefix . 'tsjippy_form_shortcodes';
            $this->shortcodeColumnSettingsTable = $wpdb->prefix . 'tsjippy_form_shortcode_column_settings';

            $this->loadShortcodeData();

            $blockId    = $this->tableSettings->block_id;
            $postId     = $this->tableSettings->post_id;
        }

        // call parent constructor
        parent::__construct($blockId, postId: $postId, all: $all, pageSize: $pageSize, formUrl: $formUrl, userId: $userId);

        $this->columnSettings       = [];
        $this->currentPage          = 0;
        $this->enriched             = false;
        $this->excelContent         = [];
        $this->extraBlocks        = [];
        $this->formEditPermissions  = false;
        $this->ownData              = false;
        $this->sortColumn           = '';
        $this->sortDirection        = 'ASC';
        $this->sortBlockIds       = [];
        $this->spliced              = false;
        $this->subBlocks          = [];
        $this->tableEditPermissions = false;
        $this->tableSettings        = new stdClass();
        $this->tableViewPermissions = false;
        $this->total                = 0;

        //Get personal visibility
        if (empty($this->formData)) {
            $this->hiddenColumns        = [];
        } elseif (!empty($this->formData->blockId)) {
            $value  = get_user_meta($this->user->ID, 'tsjippy_hidden_columns_' . $this->formData->blockId, true);
            if (!is_array($value)) {
                $value  = [];
            }
            $this->hiddenColumns        = $value;
        } else {
            return new WP_Error('forms', 'No form data found for the given form results shortcode');
        }

        // add the blocks filter before the parent construct, as that will apply the filter
        add_filter('tsjippy-forms-blocks', [$this, 'addExtraBlocks'], 10, 3);

        wp_enqueue_style('tsjippy_formtable_style');

        $family                     = new TSJIPPY\FAMILY\Family();
        $this->user->partnerId      = $family->getPartner($this->user->ID);

        if (function_exists('is_user_logged_in') && is_user_logged_in()) {
            $this->userRoles['everyone'] = 1; //used to indicate view rights on permissions
        }

        $result    = $this->enrichColumnSettings();
        if (is_wp_error($result)) {
            return $result;
        }

        $this->loadTableSettings();
    }

    /**
     * Function to add the blocks for submission meta
     *
     * @param array $blocks The current array of blocks
     * @param object $object The form or shortcode object for which the blocks are being retrieved
     * @param bool $force Whether to force adding the extra blocks even if they already exist
     * @return array The updated array of blocks with the extra blocks added
     */
    public function addExtraBlocks($blocks, $object, $force)
    {
        // Build the array of block details
        $this->extraBlocks    = [
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

        if (!empty($this->formData->split_blocks)) {
            $this->extraBlocks[-5] = [
                'slug' => 'sub_id',
                'name' => 'Sub-Id',
                'type' => 'number'
            ];
        }

        foreach ($this->extraBlocks as $id => $newBlock) {
            if (isset($this->blockMapping['id'][$id])) {
                continue;
            }
            $block                 = new \stdClass();

            $block->blockId             = $id;
            if (isset($newBlock['type'])) {
                $block->type = $newBlock['type'];
            } else {
                $block->type        = 'text';
            }
            $block->slug            = $newBlock['slug'];
            $block->name            = $newBlock['name'];

            // Add to the front of the array
            array_unshift($blocks, $block);
        }

        return $blocks;
    }

    /**
     * Finds all blocks that should be splitted
     * Two options:
     *    1 - case of a BASENAME[index]SUBNAME name
     *    2 - case of a BASENAME[index] name
     */
    public function findSplitBlockIds()
    {
        $baseNames    = $blockIds = [];

        // Check if this is an splitted block
        if (empty($this->formData->split_blocks)) {
            return apply_filters('tsjippy-forms-split-block-ids', $blockIds, $this);
        }

        /**
         * loop over all block ids that data should be splitted on
         */
        foreach ($this->formData->split_blocks as $splitBlockId) {
            // Get the block slug
            $slug    = $this->getBlockById($splitBlockId, 'slug');

            // Find the base slug keyword followed by one or more numbers between [] followed by a keyword between []
            $pattern    = "/(.*?)\[[0-9]+\]\[([^\]]+)\]/i";

            // This slug matches the pattern
            if (preg_match($pattern, $slug, $matches)) {
                $baseNames[$matches[1]]      = $matches[1];
            } else {
                // Splitted block with just normal multiple values slug[index]
                $blockIds[$splitBlockId] = $splitBlockId;
            }
        }

        if (empty($baseNames)) {
            return apply_filters('tsjippy-forms-split-block-ids', $blockIds, $this);
        }

        /**
         * Loop over all blocks to find splitted ones
         */
        foreach ($this->formBlocks as $block) {
            // Check if this is an indexed splitted block basename[index][keyname]
            if (str_contains($block->slug, '[')) {
                // loop over all base names that data should be splitted on
                foreach ($baseNames as $baseName) {
                    // Check if this name belongs to this splitted block
                    $pattern        = "/$baseName\[[0-9]+\]\[([^\]]+)\]/i";

                    if (preg_match($pattern, $block->slug, $matches)) {
                        $name            = $matches[1];

                        // store found block ids by basename
                        if (empty($blockIds[$baseName])) {
                            $blockIds[$baseName]    = [];
                        }

                        if (empty($blockIds[$baseName][$name])) {
                            $blockIds[$baseName][$name]    = [];
                        }

                        // Add the current block id
                        $blockIds[$baseName][$name][$block->slug]    = $block->blockId;
                        break;
                    }
                }
            }
        }

        return apply_filters('tsjippy-forms-split-block-ids', $blockIds, $this);
    }

    /**
     * Retrieves all user metas and user data's and use them as submission data
     */
    public function getMetaKeyFormSubmissions($userId = null, $all = false)
    {
        $submissions = [];

        /**
         * Build the base submission
         */
        $counter    = 0;
        if (is_numeric($userId)) {
            $users  = [get_userdata($userId)];
        } else {
            $users  = get_users();
        }

        $needed = str_replace('[]', '', $this->blockMapping['slug']);

        foreach ($users as $user) {
            $submission     = new \stdClass();

            $submission->id               = $counter;
            $submission->block_id          = $this->formData->blockId;

            // Base submission data
            $submission->time_created     = $user->user_registered;
            $submission->time_last_edited = $user->user_registered;

            $submission->user_id          = $user->ID;
            $submission->submitter_id     = $user->ID;

            // Add the remaining user data if any
            foreach ($user->data as $key => $value) {
                if (!isset($needed[$key])) {
                    continue;
                }
                $submission->$key    = $value;
            }

            // Meta values
            // parse results to merge based on userId
            foreach (get_user_meta($user->ID) as $key => $meta) {
                $key    = str_replace('tsjippy_', '', $key);
                if (!isset($needed[$key])) {
                    continue;
                }

                $meta   = array_map('maybe_unserialize', $meta);
                if (count($meta) < 2) {
                    $meta   = $meta[0];
                }
                $submission->$key   = $meta;
            }

            $submissions[$user->ID] = $submission;

            $counter++;
        }

        // Get the total
        $this->total            = count($submissions);

        // Limit the amount to 100
        // phpcs:ignore
        if (!$all && is_numeric($_REQUEST['page-number'] ?? '') && $this->total > $this->pageSize) {
            // phpcs:ignore
            $this->currentPage    = TSJIPPY\sanitize($_REQUEST['page-number']);

            // phpcs:ignore
            if (isset($_POST['prev'])) {
                $this->currentPage--;
            }

            // phpcs:ignore
            if (isset($_POST['next'])) {
                $this->currentPage++;
            }
            $start             = $this->currentPage * $this->pageSize;

            $submissions       = array_slice($submissions, $start, $this->pageSize);

            $this->spliced     = true;
        } else {
            $this->currentPage = 0;
        }

        // sort colomn
        if (!empty($this->sortBlockIds)) {
            if ($this->sortDirection != 'ASC') {
                $this->sortDirection    = 'DESC';
            }
        }

        return apply_filters('tsjippy-forms-retrieved-formdata', $submissions, $userId, $this);
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
        if (empty($this->tableSettings->filter)) {
            return;
        }

        foreach ($this->tableSettings->filter as $filter) {

            $filterKey        = strtolower($filter['name']);

            // nothing to filter, continue
            // phpcs:ignore
            if (empty($_POST[$filterKey])) {
                continue;
            }

            // Get the data for the current filter
            // phpcs:ignore
            $filterValue    = TSJIPPY\sanitize($_POST[$filterKey]);

            $filterBlock  = $this->getBlockById($filter['block']);

            // Invalid filter block id
            if (!$filterBlock) {
                continue;
            }

            /**
             * Check if we are filtering on a indexed block
             */
            $exploded            = explode('[', $filterBlock->slug);
            if (count($exploded) > 1) {
                $filterIndex        = str_replace(']', '', end($exploded));

                $name               = "{$exploded[0]}[%][$filterIndex]";
                $filterBlockIds   = '';
            } else {
                $filterBlockIds    = [$filter['block']];
            }

            // Add the filter query
            if ($filter['type'] == '==') {
                $filter['type']    = '=';
            }

            if ($filter['type'] == 'like') {
                $filterValue    = "%$filterValue%";
            }

            $placeholders   = implode(', ', array_fill(0, count($filterBlockIds), '%d'));

            $where[]    = "(V.block_id NOT IN ($placeholders) or LOWER(V.value) {$filter['type']} %s)";
            $values[]    = array_merge($values, $filterBlockIds);
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
        $splitBlocks        = $this->formData->split_blocks;
        if (empty($splitBlocks)) {
            return;
        }

        $innerJoinString = "\n\tLEFT JOIN SubIdValues as V ON E.id = V.Sid";

        $ect             = ",\nSubIdValues AS (\n\tSELECT \n\t\tid AS Sid, \n\t\tsub_id,\n\t\t";
        $splitColumns    = [];

        /**
         * Process split blocks with the form base[index][key]
         */
        foreach ($this->findSplitBlockIds() as $base) {
            if(!is_array($base)){
                TSJIPPY\printArray([
                    $this->findSplitBlockIds(),
                    $base
                ]);
            }
            foreach ($base as $columnName => $ids) {
                // Make the array of blocks that share the same name a comma separated string for the query
                $implodedIds    = implode(", ", array_values($ids));

                // Store the other ids as well
                $splitBlocks    = array_merge($splitBlocks, array_values($ids));

                // Add the column to the query
                $splitColumns[] = "MAX(CASE WHEN block_id IN ($implodedIds) THEN value END) AS '$columnName'";

                // Make sure we sort on the $columnName if needed
                foreach ($this->sortBlockIds as $blockId => $value) {
                    if (isset($ids[$blockId])) {
                        unset($this->sortBlockIds[$blockId]);
                        $this->sortBlockIds[$columnName] = $columnName;
                    }
                }
                unset($blockId);
            }
        }

        /**
         * Process simple base[index] splits
         */
        if (empty($splitColumns)) {
            foreach ($splitBlocks as $splitBlock) {
                // Add the column to the query
                $splitColumns[]         = "MAX(CASE WHEN block_id = $splitBlock THEN value END) AS '$splitBlock'";
            }
        }

        $ect .= implode(",\n\t\t", $splitColumns);
        $ect .= ",\n\t\tMAX(CASE WHEN block_id = '-6' THEN value END) AS 'sub_archived'";
        $ect .= "\n\tFROM Raw";
        $ect .= "\n\tWHERE sub_id IS NOT NULL";
        $ect .= "\n\tGROUP BY id, sub_id";
        $ect .= "\n)";

        if (!$this->showArchived) {
            $finalWhere[]            = "(sub_archived <> 1 or sub_archived is null)";
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
        $splitBlocks      = $this->formData->split_blocks;
        $existingColumns    = ['id', 'time_created', 'time_last_edited', 'user_id', 'archived', 'submitter_id'];

        $columns            = $existingColumns;

        $columnsString      = "S.block_id as form_block_id, S.".implode(', S.', $columns);

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
        $ect                 = "-- Table with raw data on several rows, where only the block_id and value are unique\n"
            . "WITH Raw AS (\n\t"
            . "SELECT $columnsString, V.block_id, V.sub_id, V.value\n\tFROM %i as S\n\t"
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

        foreach ($this->formBlocks as $block) {
            // Negative block ids are from the submission table
            if ($block->blockId < 0 || in_array($block->blockId, $splitBlocks) || isset($this->nonInputs[$block->type])) {
                continue;
            }

            $toColumn[]         = "MAX(CASE WHEN block_id = '$block->blockId' THEN value END) AS '$block->blockId'";
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
     * @param    int|array $userId       Optional the user id to get the results of or an array of user ids. Default null
     * @param    int       $submissionId Optional a specific submission id. Default null
     * @param    bool      $all          Whether to retrieve all submissions or paged. Default false
     * @param    array     $where        Optional array of where conditions. Default empty array
     * @param    array     $values       Optional array of values for the where conditions. Default empty array
     *
     * @return    array                  Array of results
     */
    public function getSubmissions($userId = null, $submissionId = null, $all = false, $where = [], $values = [])
    {
        global $wpdb;

        $userId    = apply_filters('tsjippy-forms-user-ids-to-retrieve', $userId, $this);

        // phpcs:ignore
        if ($this->all) {
            $all    = true;
        }

        // Submission id
        // phpcs:ignore
        if (empty($submissionId) && !empty($_REQUEST['id'])) {
            // phpcs:ignore
            $submissionId    = TSJIPPY\sanitize($_REQUEST['id']);
        }

        if (!empty($this->submissions) && is_numeric($submissionId)) {
            foreach ($this->submissions as $submission) {
                if ($submission->id == $submissionId) {
                    return [$submission];
                }
            }
        }

        // We want to see archived entries if a specific submission id is queried
        $showArchived = $this->showArchived || is_numeric($submissionId);

        // Check if a form is loaded
        if (empty($this->formData->blockId) && !empty($submissionId)) {
            // Load the form before loading the submission, because we need the form blocks to load the submission data
            $this->getFormBySubmissionId($submissionId);
        }

        if (!empty($this->formData->save_in_meta)) {
            return $this->getMetaKeyFormSubmissions($userId, $all);
        }

        /**
         * Get the where statements
         */
        // Block Id
        if (isset($this->formData->blockId)) {
            $where[]    = "S.block_id=%d";
            $values[]   = $this->formData->blockId;
        }

        // Archived
        if (!$showArchived && $submissionId == null) {
            $where[]    =  "S.archived=0";
        }

        // Specific Submission
        if (is_numeric($submissionId)) {
            $where[]    = "S.id=%d";
            $values[]   = $submissionId;
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
         * @param array params
         *    string $base        The base query
         *    array    $where       Array of where statements
         *    array    $values      Array of values for the where statements
         * @param   int     $userId   The user Id
         * @param   object  $object The current instance
         */
        $filtered    = apply_filters(
            'tsjippy-forms-formdata-retrieval-query',
            [
                'query'  => '',
                'where'  => $where,
                'values' => $values,
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
        // phpcs:ignore
        $this->total    = $wpdb->get_var($wpdb->prepare($countQuery, ...$values));

        if (empty($this->total)) {
            return apply_filters('tsjippy-forms-retrieved-formdata', [], $userId, $this);
        }

        /**
         * Pagination
         */
        // Limit the amount to 100
        // phpcs:ignore
        if (is_numeric($_REQUEST['page-number'] ?? '')) {
            // phpcs:ignore
            $this->currentPage    = TSJIPPY\sanitize($_REQUEST['page-number']);

            // phpcs:ignore
            if (isset($_POST['prev'])) {
                $this->currentPage--;
            }

            // phpcs:ignore
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
        if (!empty($this->sortBlockIds)) {
            if ($this->sortDirection != 'ASC') {
                $this->sortDirection    = 'DESC';
            }

            $query        .= " \nORDER BY ";
            $sortables    = [];
            foreach ($this->sortBlockIds as $blockId => $value) {
                if ($blockId < 0) {
                    $blockId     = $this->extraBlocks[$blockId]['slug'];
                }

                $sortables[] = "`$blockId` $this->sortDirection";
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
        // phpcs:disable
        $submissions    = $wpdb->get_results(
            $wpdb->prepare("$ecd\n\n$query", ...$values)
        );
        // phpcs:enable

        if ($wpdb->last_error !== '') {
            TSJIPPY\printArray($wpdb->print_error());
        }

        /**
         * Unserialize values
         */
        foreach ($submissions as &$submission) {
            foreach ($submission as $blockId => &$value) {
                if (!empty($nonSplittedValues[$submission->id]) && is_numeric($blockId)) {
                    $value    = $nonSplittedValues[$submission->id][$blockId];
                } else {
                    $value    = maybe_unserialize($value);
                }
            }
        }

        $submissions    = apply_filters('tsjippy-forms-retrieved-formdata', $submissions, $userId, $this);

        return $submissions;
    }

    /**
     * Set formresults of the current form
     *
     * @param    int     $userId       Optional the user id to get the results of. Default null
     * @param    int     $submissionId Optional a specific id. Default null
     * @param    bool    $all          Whether to retrieve all submissions or paged
     * @param    bool    $force        Whether to retrieve submissions even if already done
     * @param    array   $where        Optional array of where conditions. Default empty array
     * @param    array   $values       Optional array of values for the where conditions. Default empty array
     */
    public function parseSubmissions($userId = null, $submissionId = null, $all = false, $force = false, $where = [], $values = [])
    {
        // no need to this again
        if (!empty($this->submissions) && !$force && empty($submissionId)) {
            return;
        }

        $this->submissions        = $this->getSubmissions($userId, $submissionId, $all, $where, $values);

        if (count($this->submissions) == 1) {
            $this->submission    = array_values($this->submissions)[0];
        } elseif (!empty($submissionId)) {
            $this->submission    = $this->submissions[0];
        }
    }

    /**
     * Adds a new column setting for a new block
     *
     * @param object    $block    the block to check if column settings exists for
     * @param array        $blockIds    optional array of block ids that belong to this column
     *
     * @return false|array            false if no column setting was added, array of column settings if added
     */
    public function addColumnSetting($block, $blockIds = [])
    {
        //do not show non-input blocks
        if (isset($this->nonInputs[$block->type])) {
            return false;
        }

        $this->columnSettings[$block->blockId] = [
            'slug'                => $block->split_slug ?? $block->slug,
            'name'                => $block->split_name ?? $block->name,
            'show'                => 1,
            'edit_right_roles'    => [],
            'view_right_roles'    => []
        ];

        // Only add block ids if available
        if (!empty($blockIds)) {
            $this->columnSettings[$block->blockId]['blockIds']    = $blockIds;
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

            $this->getForm($this->tableSettings->post_id);

            if (empty($this->tableSettings->view_right_roles)) {
                $this->tableSettings->view_right_roles    = [];
            }

            if (empty($this->tableSettings->edit_right_roles)) {
                $this->tableSettings->edit_right_roles    = $this->formData->full_right_roles;
            }
        }

        $this->enriched    = true;

        /**
         * Get all splitted blocks that share the same name and add the ids to the column settings
         */
        $blockIds    = $this->findSplitBlockIds();
        $relatedIds    = [];
        if (!empty($blockIds)) {
            // loop over all base names that data should be splitted on
            foreach ($blockIds as $baseName => $names) {
                if (is_numeric($baseName)) {
                    if (isset($this->columnSettings[$names])) {
                        $this->columnSettings[$names]['blockIds']    = [$names];
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
                        $block    = $this->getBlockById($id);
                        $block->split_slug = strtolower(str_replace(' ', '-', $name));
                        $block->split_name = ucfirst($name);

                        $this->addColumnSetting($block, $relatedIds[$id]);
                    }

                    $this->columnSettings[$id]['blockIds']    = $elIds;
                }
            }
        }

        //loop over all blocks to build a new array
        foreach ($this->formBlocks as $block) {

            $id = $block->blockId;
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

            //check if the block is in the array, if not add it
            if (!isset($this->columnSettings[$id])) {
                $this->addColumnSetting($block, $relatedIds[$id] ?? []);
            }
        }

        //Add a row for each table action as well
        foreach ($this->formData->actions as $action) {
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

            if (isset($names[$setting['name']])) {
                //remove the duplicate block: same name but different id
                unset($this->columnSettings[$key]);
            }

            $names[$setting['name']] = 1;

            if (!$setting['show']) {

                //remove the block
                unset($this->columnSettings[$key]);

                //add it again, at the end of the array
                $this->columnSettings[$key] = $setting;
            }
        }
    }

    /**
     * Get the contents of a row for the table
     *
     * @param object $tr The submission object for the current row
     *
     * @return array The contents of the row as an array
     */
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

        foreach ($this->columnSettings as $blockId => $columnSetting) {

            if (
                !is_array($columnSetting) ||
                !$columnSetting['show'] ||
                !is_numeric($blockId)
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
                        !isset($columnSetting['view_right_roles']['own'])       //but we are not allowed to see it
                    )
                )    &&
                !$this->tableEditPermissions &&                                                 //no permission to edit the table and
                !array_intersect_key($this->userRoles, $columnSetting['view_right_roles'] ?? [])          // and we do not have the view right role
            ) {
                //later on there will be a row with data in this column
                if (
                    $this->ownData &&                                                           // we are only showing own data
                    isset($columnSetting['view_right_roles']['own'])                         // and this column can be viewed by owner
                ) {
                    $value = 'X'; // we cannot see this value, but we can see other values in this column
                } else {
                    continue;
                }
            }

            //if this row has no value in this column remove the row
            if (
                $columnSetting['name'] == ($this->tableSettings->hide_row ?? '') &&                      // We are currently checking a cell in that column
                (
                    (
                        empty($values[$this->tableSettings->hide_row]) &&                        // The cell has no value
                        empty($values[trim($this->tableSettings->hide_row, '[]')])               // also check the name without []
                    )
                ) &&
                !array_intersect_key($this->userRoles, $columnSetting['edit_right_roles'])    &&        // And we have no right to edit this specific column
                !$this->tableEditPermissions                                                            // and we have no right to edit all table data
            ) {
                return;
            }

            if (
                isset($columnSetting['edit_right_roles']['own']) &&
                $ownEntry ||
                array_intersect_key($this->userRoles, $columnSetting['edit_right_roles']) ||
                $this->tableEditPermissions
            ) {
                $blockEditRights = true;
            } else {
                $blockEditRights = false;
            }

            $attributes = [];

            /*
                Write the content to the cell, convert to something if needed
            */
            $class          = str_replace('[]', '', $columnSetting['slug']);

            $blockName    = $columnSetting['name'];

            //add field value if we are allowed to see it
            if ($value != 'X') {
                $rowHasContents    = true;

                /**
                 * Find splitted block values
                 */
                $columnBlocks = $columnSetting['blockIds'] ?? [];
                if (in_array($blockId, $columnBlocks)) {
                    if (!empty($this->submission->sub_id)) {
                        $attributes["data-subid"] = $this->submission->sub_id;
                    }

                    // Find the splitted value
                    foreach ($columnBlocks as $id) {
                        if (!empty($this->submission->{$id})) {
                            $value    = $this->submission->{$id};
                            break;
                        }
                    }
                }

                /**
                 * Find regular values
                 */
                if (isset($this->submission->{$blockId})) {
                    $value    = $this->submission->{$blockId};
                } elseif (isset($this->submission->{$blockName})) {
                    $value    = $this->submission->{$blockName};
                } elseif (isset($this->submission->{$class})) {
                    $value    = $this->submission->{$class};
                } elseif (empty($value)) {
                    $value    = 'X';
                }

                if ($value === null) {
                    $value = '';
                }

                //transform if needed
                $orgFieldValue    = $value;

                $value            = apply_filters('tsjippy-forms-result-table-value', $value, $columnSetting, $this->submission, $this);

                $block          = $this->getBlockBySlug($class);
                if ($block) {
                    $value        = $this->transformInputData($value, $block, $this->submission);
                }

                //show original email in excel
                if (gettype($value) == 'string' && (str_contains($value, '@') || str_contains($value, '<'))) {
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
            if ($blockName == "displayname") {
                $class .= ' sticky';
            }

            if (!empty($this->hiddenColumns[$columnSetting['slug']])) {
                $class    .= ' hidden';
            }

            if (isset($columnSetting['copy'])) {
                $class    .= ' copy-wrapper';
            }

            //if the user has one of the roles defined for this block
            if ($blockEditRights && $blockName != 'id') {
                $class    .= ' edit forms-table';
            }

            $attributes['class'] = trim($class);

            //Convert underscores to spaces, but not in urls
            if (is_string($value) && !str_contains($value, 'href=') && !str_contains($value, 'src=')) {
                $value    = str_replace('_', ' ', $value);
            }

            if (!empty($columnSetting['width'])) {
                $attributes['style']    = "max-width:{$columnSetting['width']}px;width:{$columnSetting['width']}px;min-width:{$columnSetting['width']}px;text-wrap: balance;";
            }

            // for action buttons there is no block id
            if ($blockId) {
                $attributes['data-block-id'] = $blockId;
            }

            /**
             * Filters the cell attributes
             * 
             * @param   array   $attributes             The td attributes
             * @param   object  $displayFormResults     The current instance
             * @param   array   $columnSetting          The current column settings array
             * @param   array   $submission             The current submission
             */
            $attributes    = apply_filters('tsjippy-forms-result-cell-attributes', $attributes, $this, $columnSetting, $this->submission);

            $td            = addElement('td', $tr, $attributes);

            if (!empty($value)) {
                if (is_array($value)) {
                    $value  = implode(', ', $value);
                }
                TSJIPPY\addRawHtml($value, $td);
            }

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
     * 
     * @param object $row The submission object for the current row
     * 
     * 
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
                !array_intersect_key($this->userRoles, (array)$this->columnSettings[$action]['edit_right_roles'])
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
                "text"  => ucfirst($action),
                'type'  => 'button'
            ];
        }

        /**
         * Filters the avaiable buttons and their attributes
         * 
         * @param   array   $attributes Array of arrays with attributes
         * @param   object  $submission The current submission
         * @param   object  $object     The current DisplayFormResults object
         */
        $attributes = apply_filters('tsjippy-forms-results-row-actions', $attributes, $this->submission, $this);

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
     * @param string $body The contents of the row
     * 
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
        if (
            !isset($this->shortcodeId) ||
            !is_numeric($this->shortcodeId) ||
            $this->shortcodeId == -1
        ) {
            return new WP_Error('forms', 'no shortcoode id');
        }

        $this->tableSettings = TSJIPPY\getFromDb(
            "get_shortcode_$this->shortcodeId",
            "forms",
            "SELECT * FROM %i WHERE id = %d",
            $this->shortcodeTable,
            $this->shortcodeId
        )[0];

        if(empty($this->tableSettings->edit_right_roles)){
            $this->tableSettings->edit_right_roles = [
                'administrator' => 1,
                'editor' => 1
            ];
        }

        if(empty($this->tableSettings->view_right_roles)){
            $this->tableSettings->view_right_roles  = $this->tableSettings->edit_right_roles;
        }

        $this->columnSettings        = [];
        $results                     = TSJIPPY\getFromDb(
            "get_shortcode_settings_$this->shortcodeId",
            "forms",
            "SELECT * FROM %i WHERE shortcode_id = %d ORDER BY `priority` ASC",
            $this->shortcodeColumnSettingsTable,
            $this->shortcodeId
        );

        foreach ($results as $setting) {
            // do not add if the block does not exist anymore
            if (
                is_numeric($setting->block_id) &&
                $setting->block_id    > -1 &&
                !isset($this->blockMapping['id'][$setting->block_id])
            ) {
                continue;
            }

            //unserialize the values
            foreach ($setting as &$value) {
                $value    = maybe_unserialize($value);
            }

            if (empty($setting->view_right_roles)) {
                $setting->view_right_roles    = [];
            }

            if (empty($setting->edit_right_roles)) {
                $setting->edit_right_roles    = [];
            }

            $this->columnSettings[$setting->block_id] = (array)$setting;
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
                <input type='hidden' class='no-reset' name='form-id' value='<?php echo esc_attr($this->formData->blockId); ?>'>

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
                        foreach ($this->columnSettings as $blockIndex => $columnSetting) {
                            if (!isset($columnSetting['slug'])) {
                                continue;
                            }

                            $name    = $columnSetting['name'];
                            if (empty($name)) {
                                $name = ucfirst(str_replace('-', ' ', $columnSetting['slug']));
                            }

                            $width        = empty($columnSetting['width']) ? 200 : $columnSetting['width'];

                            if (!$columnSetting['show']) {
                                $visibility    = 'invisible';
                            } else {
                                $visibility    = 'visible';
                            }

                        ?>
                            <tr class="column-setting-wrapper" data-block-id="<?php echo esc_attr($blockIndex); ?>">
                                <input type="hidden" class="no-reset" name="column-settings[<?php echo esc_attr($blockIndex); ?>][column-id]" value="<?php echo esc_attr($columnSetting['id'] ?? -1); ?>">
                                <input type="hidden" class="no-reset" name="column-settings[<?php echo esc_attr($blockIndex); ?>][slug]" value="<?php echo esc_attr($columnSetting['slug'] ?? ''); ?>">
                                <td>
                                    <span class="movecontrol formfield-button" aria-hidden="true">:::</span>
                                </td>
                                <td>
                                    <span class="column-settings" style="margin-right:0px;">
                                        <?php echo esc_html($columnSetting['slug']); ?>
                                    </span>
                                </td>
                                <td>
                                    <input type="text" class="column-settings" name="column-settings[<?php echo esc_attr($blockIndex); ?>][nice-name]" value="<?php echo esc_attr($name); ?>" style="margin-right:0px;">
                                </td>
                                <td>
                                    <input type="hidden" class="no-reset" name="column-settings[<?php echo esc_attr($blockIndex); ?>][show]" value="<?php echo esc_attr($columnSetting['show']); ?>">
                                    <span class="visibility-icon">
                                        <img class='visibility-icon $visibility' src=' <?php echo esc_url(TSJIPPY\PICTURESURL .  "/$visibility.png"); ?>' width='20px' loading='lazy' style='min-width:20px;'>
                                    </span>
                                </td>
                                <?php
                                //only add view permission for numeric blocks others are buttons
                                if (is_numeric($blockIndex)) {
                                ?>
                                    <td style='max-width:200px;text-wrap: auto; text-align: left;'>
                                        <select class='column-settings inline' name='column-settings[<?php echo esc_attr($blockIndex); ?>][view-right-roles][]' multiple='multiple' style="margin-right:0px;">
                                            <?php
                                            foreach ($viewRoles as $key => $roleName) {
                                            ?>
                                                <option value='<?php echo esc_attr($key); ?>' <?php if (isset($columnSetting['view_right_roles'][$key])) echo "selected=selected";  ?>>
                                                    <?php echo esc_html($roleName); ?>
                                                </option>
                                            <?php
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
                                    <select class='column-settings inline' name='column-settings[<?php echo esc_attr($blockIndex); ?>][edit-right-roles][]' multiple='multiple' style="margin-right:0px;">
                                        <?php
                                        foreach ($editRoles as $key => $roleName) {
                                        ?>
                                            <option value='<?php echo esc_attr($key); ?>' <?php if (isset($columnSetting['edit_right_roles'][$key])) echo "selected=selected"; ?>>
                                                <?php echo esc_html($roleName); ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="column-settings" name="column-settings[<?php echo esc_attr($blockIndex); ?>][width]" value="<?php echo esc_attr($width); ?>" placeholder="200" min="100" style="max-width: 80px; margin-right:0px;">px
                                </td>
                                <td>
                                    <input type="checkbox" class="column-settings" name="column-settings[<?php echo esc_attr($blockIndex); ?>][copy]" value="1" <?php if (isset($columnSetting['copy'])) echo 'checked'; ?> style="max-width: 40px; margin-right:0px;">
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
        $users  = TSJIPPY\getUserAccounts(returnFamily: false, adults: true, uniqueDisplayName: true);
        foreach($users as $key => $user){
            unset($users[$key]);

            $users[$user->ID] = $user->display_name;
        }

        //Sort the users
        asort($users);

        ?>
        <div class="tabcontent <?php echo esc_attr($class); ?>" id="table-rights-<?php echo esc_attr($this->shortcodeId); ?>">
            <form>
                <input type='hidden' class='no-reset' class='shortcode-settings' name='shortcode-id' value='<?php echo esc_attr($this->shortcodeId); ?>'>
                <input type='hidden' class='no-reset' class='shortcode-settings' name='form-id' value='<?php echo esc_attr($this->formData->blockId); ?>'>

                <h4>
                    Set the title for the results table
                </h4>
                <input type='text' name="table-settings[title]" value='<?php echo esc_attr($this->tableSettings->title); ?>' style='width:500px;'>

                <div class="table-rights-wrapper">
                    <h4>
                        Select the default column the table is sorted on
                    </h4>
                    <select name="table-settings[default-sort]">
                        <option
                            value='' <?php if ($this->tableSettings->default_sort == '') echo 'selected=selected'; ?>>
                            ---
                        </option>

                        <?php

                        foreach ($this->columnSettings as $key => $columnSetting) {
                            if (!is_array($columnSetting)) {
                                continue;
                            }

                            $name = $columnSetting['name'];

                        ?>
                            <option
                                value='<?php echo esc_attr($key); ?>'
                                <?php if (($this->tableSettings->default_sort ?? '') == $key) echo "selected=selected"; ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>

                    <h4>
                        Select the sort direction
                    </h4>
                    <label>
                        <input
                            type='radio'
                            name='table-settings[sort-direction]'
                            id='sort-direction'
                            value='asc'
                            <?php if (($this->tableSettings->sort_direction ?? '') == 'asc') echo 'checked'; ?>>
                        Ascending
                    </label>
                    <label>
                        <input
                            type='radio'
                            name='table-settings[sort-direction]'
                            id='sort-direction'
                            value='dsc'
                            <?php if (($this->tableSettings->sort_direction ?? '') == 'dsc') echo 'checked'; ?>>
                        Decending
                    </label>
                </div>
                <br>
                <div class="table-filters-wrapper" style='margin-top:10px;'>
                    <h4>
                        Select the fields the table can be filtered on
                    </h4>
                    <table class='no-border clone-divs-wrapper'>
                        <?php
                        $filters    = $this->tableSettings->filter;

                        if (!is_array($this->tableSettings->filter ?? '')) {
                            $this->tableSettings->filter    = [];
                            $filters    = [''];
                        }

                        foreach ($filters as $index => $filter) {
                        ?>
                            <tr class='clone-div' data-div-id='<?php echo esc_attr($index); ?>'>
                                <td>
                                    <select name='table-settings[filter][<?php echo esc_attr($index); ?>][block]' class='inline'>
                                        <?php
                                        foreach ($this->columnSettings as $key => $columnSetting) {

                                            if (!is_array($columnSetting)) {
                                                continue;
                                            }

                                            $name = $columnSetting['name'];

                                            //Check which option is the selected one
                                        ?>
                                            <option
                                                value='<?php echo esc_attr($key); ?>'
                                                <?php if ($this->tableSettings->filter[$index]['block'] == $key) echo 'selected="selected"'; ?>>
                                                <?php echo esc_html($name); ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td>
                                    filter type
                                    <select name='table-settings[filter][<?php echo esc_attr($index); ?>][type]' class='inline'>
                                        <?php
                                        foreach (['>=', '<', '==', 'like'] as $type) {
                                        ?>
                                            <option
                                                value='<?php echo esc_attr($type); ?>'
                                                <?php if ($this->tableSettings->filter[$index]['type'] == $type) echo 'selected="selected"'; ?>>
                                                <?php echo esc_html($type); ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td>
                                    filter type
                                    <select name='table-settings[filter][<?php echo esc_attr($index); ?>][type]' class='inline'>
                                        <?php
                                        foreach (['>=', '<', '==', 'like'] as $type) {
                                        ?>
                                            <option
                                                value='<?php echo esc_attr($type); ?>'
                                                <?php if ($this->tableSettings->filter[$index]['type'] == $type) echo 'selected="selected"'; ?>>
                                                <?php echo esc_html($type); ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td>
                                    Filter name
                                    <input name='table-settings[filter][<?php echo esc_attr($index); ?>][name]' value='<?php echo esc_attr($this->tableSettings->filter[$index]['name'] ?? ''); ?>'>
                                </td>
                                <td>
                                    <button type='button' class='add button'>+</button>
                                </td>
                                <td>
                                    <button type='button' class='remove button'>-</button>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                </div>

                <div class="table-rights-wrapper">
                    <h4>
                        Select a column which determines if a row should be shown.
                    </h4>
                    <label>
                        The row will be hidden if a cell in this column has no value and the viewer has no right to edit.
                    </label>
                    <select name="table-settings[hide-row]">
                        <option value='' <?php if (empty($this->tableSettings->hide_row ?? '')) echo 'selected'; ?>>---</option>
                        <?php

                        foreach ($this->columnSettings as $key => $columnSetting) {
                            if (!is_array($columnSetting)) {
                                continue;
                            }

                            $name = $columnSetting['name'];
                        ?>
                            <option
                                value='<?php echo esc_attr($columnSetting['name']); ?>'
                                <?php
                                if (($this->tableSettings->hide_row ?? '') == $columnSetting['name']) echo 'selected="selected"'; ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>
                </div>

                <div class="table-rights-wrapper">
                    <h4>
                        Select which results to display
                    </h4>
                    <select name="table-settings[result-type]">
                        <option
                            value="personal"
                            <?php if (($this->tableSettings->result_type ?? '') == 'personal') echo 'selected'; ?>>
                            Only personal
                        </option>

                        <option
                            value="all"
                            <?php if (($this->tableSettings->result_type ?? '') == 'all') echo 'selected'; ?>>
                            All the viewer has permission for
                        </option>
                    </select>
                    <br>
                    <label>
                        <input
                            type='checkbox'
                            name='table-settings[split-table]'
                            value='1'
                            <?php if ($this->tableSettings->split_table ?? false) echo 'checked'; ?>>
                        Split the results in own entries and others entries
                    </label>

                </div>

                <?php
                do_action('tsjippy-forms-after-table-settings', $this);
                ?>

                <div style='margin-top:10px;'>
                    <button class='button table-permissions-rights-form' type='button'>
                        Advanced
                    </button>

                    <div class='permission-wrapper hidden'>
                        <?php
                        // Splitted fields
                        $foundBlocks = [];
                        foreach ($this->formBlocks as $key => $block) {
                            $pattern = "/([^\[]+)\[[0-9]*\]/i";

                            if (
                                preg_match($pattern, $block->slug, $matches)    &&   // preg match was succesfull
                                !isset($foundBlocks[$matches[1]])         // the match is not yet in the found blocks
                            ) {
                                $foundBlocks[$matches[1]] = $block->blockId;
                            }
                        }

                        if (!empty($foundBlocks)) {
                            ?>
                            <div class="table-rights-wrapper">
                                <h4>
                                    Select fields where you want to create seperate rows for
                                </h4>
                                <?php

                                foreach ($foundBlocks as $block => $id) {
                                    $name    = ucfirst(strtolower(str_replace('_', ' ', $block)));

                                    //Check which option is the selected one
                                    ?>
                                    <label>
                                        <input type='checkbox' name='form-settings[split][<?php echo esc_attr($id); ?>]' value='1' <?php if (in_array($id, $this->formData->split_blocks)) echo 'checked'; ?>>
                                        <?php echo esc_html($name); ?>
                                    </label>
                                    <br>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="table-rights-wrapper">
                            <h4>
                                Roles or users with permission to VIEW the table
                            </h4>
                            Finetune it per column on the 'column settings' tab
                            <select name='table-settings[view-right-roles][]' multiple>
                                <option value=''>
                                    ---
                                </option>

                                <optgroup label="Roles">
                                    <?php
                                    foreach ($viewRoles as $key => $roleName) {
                                        ?>
                                        <option value='<?php echo esc_attr($key); ?>' <?php if (isset($this->tableSettings->view_right_roles[$key])) echo 'selected'; ?>>
                                            <?php echo esc_html($roleName); ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </optgroup>
                                <optgroup label="Users">
                                    <?php
                                    foreach ($users as $key => $name) {
                                        ?>
                                        <option
                                            value='<?php echo esc_attr($key); ?>'
                                            <?php if (isset($this->formData->view_right_roles[$key])) echo 'selected'; ?>>
                                            <?php echo esc_html($name); ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </optgroup> 
                            </select>
                            <br>

                            <h4>
                                Roles or users with permission to EDIT the table
                            </h4>
                            Finetune it per column on the 'column settings' tab
                            <select name='table-settings[edit-right-roles][]' multiple>
                                <option value=''>
                                    ---
                                </option>

                                <optgroup label="Roles">
                                    <?php
                                    foreach ($editRoles as $key => $roleName) {
                                        ?>
                                        <option value='<?php echo esc_attr($key); ?>' <?php if (isset($this->tableSettings->edit_right_roles[$key])) echo 'selected'; ?>>
                                            <?php echo esc_html($roleName); ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </optgroup>
                                <optgroup label="Users">
                                    <?php
                                    foreach ($users as $key => $name) {
                                    ?>
                                        <option
                                            value='<?php echo esc_attr($key); ?>'
                                            <?php if (isset($this->formData->edit_right_roles[$key])) echo 'selected'; ?>>
                                            <?php echo esc_html($name); ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </optgroup> 
                            </select>
                        </div>

                        <div class="table-rights-wrapper">
                            <h4 class="label">
                                View archived results by default
                            </h4>
                            <label>
                                <input
                                    type="radio"
                                    name="table-settings[archived]"
                                    value="1"
                                    <?php if ($this->tableSettings->archived ?? false) echo 'checked'; ?>>
                                Yes
                            </label>
                            <label>
                                <input
                                    type="radio"
                                    name="table-settings[archived]"
                                    value="0"
                                    <?php if (!($this->tableSettings->archived ?? false)) echo 'checked'; ?>>
                                No
                            </label>
                        </div>

                        <!-- We can define auto archive field both on table and on form settings-->
                        <div class="table-rights-wrapper">
                            <h4 class="label">
                                Auto archive results
                            </h4>
                            <label>
                                <input
                                    type="radio"
                                    name="form-settings[autoarchive]"
                                    value="1"
                                    <?php if ($this->tableSettings->autoarchive ?? false) echo 'checked'; ?>>
                                Yes
                            </label>
                            <label>
                                <input
                                    type="radio"
                                    name="form-settings[autoarchive]"
                                    value="0"
                                    <?php if (!($this->tableSettings->autoarchive ?? false)) echo 'checked'; ?>>
                                No
                            </label>
                        </div>

                        <div
                            class='auto-archive-logic
                            <?php if ($this->tableSettings->autoarchive ?? false) echo 'hidden'; ?>'>
                            Auto archive a (sub) entry when field<br>
                            <select name="form-settings[autoarchive-el]" class='inline' style="margin-right:10px;">
                                <option value='' <?php if (empty($this->formData->autoarchive_el))  echo 'selected'; ?>>
                                    ---
                                </option>
                                <?php

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
                                ?>
                                    <option
                                        value='<?php echo esc_attr($key); ?>'
                                        <?php if (($this->formData->autoarchive_el ?? '') == $key) {
                                            echo 'selected';
                                        } ?>>
                                        <?php echo esc_html($name); ?>
                                    </option>
                                <?php
                                }
                                ?>
                            </select>
                            <label style="margin:0 10px;">equals</label>
                            <input type='text' class='wide' name="form-settings[autoarchive-value]" value="<?php echo esc_attr($this->formData->autoarchive_value ?? ''); ?>" style='max-width:200px;'>
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
            $class1        = "hidden";
            $class2        = '';
            //Column settings active
        } else {
            $class1        = "";
            $class2        = "hidden";
        }

        ob_start();
        ?>
        <div class="modal form-shortcode-settings hidden">
            <!-- Modal content -->
            <div class="modal-content" style='max-width:100vw;min-width:90vw;'>
                <?php TSJIPPY\addCloseButtton(); ?>

                <button id="column-settings" class="button tablink <?php if (!empty($this->tableSettings)) echo 'active'; ?>" data-target="column-settings-<?php echo esc_attr($this->shortcodeId); ?>">
                    Column settings
                </button>
                <button id="table-settings" class="button tablink <?php if (empty($this->tableSettings)) echo 'active'; ?>" data-target="table-rights-<?php echo esc_attr($this->shortcodeId); ?>">
                    Table settings
                </button>

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
        if (!$this->formEditPermissions ?? false) {
            if (
                array_intersect_key($this->userRoles,  $this->formData->full_right_roles ?? [])    ||
                (
                    isset($this->tableSettings->full_right_roles) &&                    // we have full rights to the table
                    array_intersect_key($this->userRoles, $this->tableSettings->full_right_roles)
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
                array_intersect_key($this->userRoles, $this->tableSettings->edit_right_roles ?? []) ||
                isset($this->tableSettings->edit_right_roles[$this->userId])
            ) {
                $this->tableEditPermissions = true;
            } else {
                $this->tableEditPermissions = false;
            }

            $this->tableEditPermissions    = apply_filters('tsjippy-forms-table-edit-permissions', $this->tableEditPermissions, $this);
        }

        $this->tableViewPermissions    = true;
        if (
            $this->onlyOwn                                            ||
            (
                ($this->tableSettings->result_type ?? '') == 'personal'    &&
                !$this->all
            )    ||
            !$this->tableEditPermissions                            &&
            !array_intersect_key($this->userRoles, $this->tableSettings->view_right_roles) &&
            !isset($this->tableSettings->view_right_roles[$this->userId]) &&
            !wp_doing_cron()
        ) {
            $this->tableViewPermissions     = false;
        }

        $this->tableViewPermissions    = apply_filters('tsjippy-forms-table-view-permissions', $this->tableViewPermissions, $this);
    }

    /**
     * Renders the table filter html
     * 
     * @param    string        $parent        The parent block to add the filter form to
     *
     * @return string    The html
     */
    protected function renderFilterForm($parent)
    {
        // Filtering not enabled
        if (empty($this->tableSettings->filter)) {
            return;
        }

        $form           = addElement('form', $parent, ['method' => 'post', 'class' => 'filter-options']);
        $filterWrapper  = addElement('div', $form, ['class' => 'filter-wrapper', 'style' => "margin-top: 10px;"]);

        $hasFilters    = false;
        foreach ($this->tableSettings->filter as $filter) {
            $filterBlock = $this->getBlockById($filter['block']);
            $filterValue   = false;
            $filterKey     = strtolower($filter['name']);

            if (!$filterBlock || empty($filterKey)) {
                continue;
            }

            $hasFilters = true;

            // phpcs:ignore
            if (!empty($_POST[$filterKey])) {
                // phpcs:ignore
                $filterValue = TSJIPPY\sanitize($_POST[$filterKey]);
            }

            $wrapperSpan    = addElement('span', $filterWrapper, ['class' => 'filter-option']);
            addElement('h4', $wrapperSpan, [], ucfirst($filterKey));

            $blockNode    = '';

            // make sure the name is not the block name but the filtername
            //$blockNode->setAttribute('name', $filterKey);
        }

        if (!$hasFilters) {
            $form->remove();
        }

        addElement('button', $filterWrapper, ['class' => 'button filter-results', 'type' => 'button', 'style' => 'height: fit-content;'], 'Filter');
    }

    /**
     * Renders the table buttons html
     * 
     * @param \DOMElement        $parent        The parent block to add the buttons to
     *
     * @return string    The html
     */
    public function renderTableButtons($parent)
    {
        $tableButtonsWrapper    = addElement('div', $parent, ['class' => 'table-buttons-wrapper']);

        //Show form properties button if we have form edit permissions
        if ($this->tableEditPermissions) {
            addElement('button', $tableButtonsWrapper, ['class' => 'button small edit-formshortcode-settings'], 'Edit settings');
            TSJIPPY\addRawHtml($this->addShortcodeSettingsModal(), $tableButtonsWrapper);
        }

        // Archived button
        if ($this->showArchived) {
            addElement('button', $tableButtonsWrapper, ['class' => 'button tsjippy small archive-switch-hide'], 'Hide archived entries');
        } else {
            addElement('button', $tableButtonsWrapper, ['class' => 'button tsjippy small archive-switch-show'], 'Show archived entries');
        }

        // Only own button
        if (
            $this->tableViewPermissions &&
            $this->onlyOwn ||
            (($this->tableSettings->result_type ?? '') == 'personal' && !$this->all)
        ) {
            addElement('button', $tableButtonsWrapper, ['class' => 'button tsjippy small only-own-switch-all'], 'Show all entries');
        } elseif (
            $this->tableViewPermissions &&
            (
                !$this->onlyOwn    ||
                $this->all        ||
                ($this->tableSettings->result_type ?? '') != 'personal'
            )
        ) {
            addElement('button', $tableButtonsWrapper, ['class' => 'button tsjippy small only-own-switch-on'], 'Show only my own entries');
        }

        addElement('button', $tableButtonsWrapper, ['class' => 'button small show fullscreenbutton'], 'Show full screen');

        $hidden    = '';
        if (empty($this->hiddenColumns)) {
            $hidden    = 'hidden';
        }
        addElement('button', $tableButtonsWrapper, ['class' => "button small reset-col-vis $hidden", "data-form-id" => $this->formData->blockId], 'Reset visibility');

        $this->renderFilterForm($tableButtonsWrapper);
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
                'data-form-id'      => $this->formData->blockId,
                'data-shortcode-id' => $this->shortcodeId
            ]
        );

        addElement('td', $table, [], 'No records found');

        if (empty($parent)) {
            return $table;
        }
    }

    /**
     * creates the main table html
     *
     * @param    string       $type          Either 'own', 'others' or 'all'
     * @param    array        $submissions   Array of Submissions
     * @param    \DOMElement  $parent        The parent block to add the table to
     *
     * @return    bool                       If there are submissions or not
     */
    public function theTable($type, $submissions, $parent)
    {
        $table  = addElement(
            'table',
            $parent,
            [
                'class'             => 'tsjippy table form-data',
                'data-form-id'      => $this->formData->blockId,
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
     * 
     * @param    \DOMElement  $parent   The parent block to add the navigation menu to
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
        $pageNumberWrapper  = addElement('span', $navigator, ['class' => 'page-number-wrapper']);

        $step               =  max(1, round($pageCount / 10)) - 1;

        for ($x = 0; $x < $pageCount; $x += $step) {
            // First step is one smaller than the rest as we start on 1
            if ($x == $step) {
                $step++;
            }

            // Display 1 more as we start on zero
            $pageNr    = $x + 1;

            $class    = '';
            if ($this->currentPage == $x) {
                $class    = "current";
            }
            addElement('span', $pageNumberWrapper, ['class' => "page-number $class", 'data-nr' => $x], $pageNr);
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
     * @param    string      $type     Either 'own', 'others' or 'all'
     * @param    bool        $force    Whether to retrieve submissions even if already done
     * @param    bool        $all      Retrieve all bookings or paged, default false for paged
     * @param    \DOMElement  $parent   The DOM Block to apped to default empty for new dom document creation
     *
     * @return   \DOMElement|false     The created block
     */
    public function renderTable($type, $force = false, $all = false, $parent = '')
    {
        $userId    = null;

        // Check permissions
        if (
            $this->onlyOwn ||
            !$this->tableViewPermissions
        ) {
            // we do not have permission to view someone elses submissions
            if ($type == 'others') {
                return 'You do not have permissions to see this. ';
            }
            $type        = 'own';
        }

        /**
         * Filter whether or not to show the table, 
         * this can be used to for example show a message instead of the table when there are no submissions or when the user has no permissions
         * 
         * @param    bool           $shouldShow Whether or not to show the table, default true
         * @param    object         $object     The current instance of the form table class, can be used to get more information about the form and the user to decide whether or not to show the table
         * @param    string         $type       The type of results that would be shown, either 'own', 'others' or 'all'
         * @param    \DOMElement    $parent     The parent node to append to
         */
        $shouldShow    = apply_filters('tsjippy-forms-table-should-show', true, $this, $type, $parent);

        if ($shouldShow !== true) {
            return     $shouldShow;
        }

        // get submissions for the current user only
        if ($type == 'own') {
            $userId    = get_current_user_id();

            if (!$userId) {
                // phpcs:ignore
                if (($_REQUEST['hash'] ?? '') == wp_hash($_REQUEST['id'] ?? '')) {
                    // phpcs:ignore
                    $userId        = TSJIPPY\sanitize($_REQUEST['hash']);
                } else {
                    return $this->emptyTable();
                }
            }
        }

        // Check if we should sort the data
        // phpcs:ignore
        if (($this->tableSettings->default_sort ?? '') != '' || isset($_REQUEST['sortcol'])) {
            // Get the sort column from $_POST
            // phpcs:ignore
            if (isset($_REQUEST['sortcol'])) {
                // phpcs:ignore
                $sortCol    = TSJIPPY\sanitize($_REQUEST['sortcol']);
                $this->sortBlockIds    = [$sortCol => $sortCol];
            }

            // Default sort blocks
            else {
                $defaultSortBlock     = $this->tableSettings->default_sort;
                $sortBlock            = $this->getBlockById($defaultSortBlock);

                // check if this is an sub id, use all blocks in that case
                if ($sortBlock) {
                    $exploded            = explode('[', $sortBlock->slug);

                    if (count($exploded) > 1) {
                        $sort = str_replace(']', '', end($exploded));
                        $name = "{$exploded[0]}[%][$sort]";
                    } else {
                        $this->sortBlockIds    = [$defaultSortBlock =>  $defaultSortBlock];
                    }
                }
            }
        }

        if (isset($this->tableSettings->sort_direction)) {
            $this->sortDirection    = strtoupper($this->tableSettings->sort_direction);
        }

        // phpcs:ignore
        if (isset($_REQUEST['sortdir'])) {
            // phpcs:ignore
            $this->sortDirection    = TSJIPPY\sanitize($_REQUEST['sortdir']);
        }

        // phpcs:ignore
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

        $wrapper    = addElement('div', $parent, ['class' => 'form-results-wrapper', 'style' => 'margin-top: 10px;']);

        $this->renderTableButtons($wrapper);

        if ($type == 'own') {
            addElement('h4', $wrapper, [], "Your own submissions");
        } elseif ($type == 'others') {
            $type    = 'others';
            addElement('h4', $wrapper, [], "Submissions of others");
        }

        $this->navigationMenu($wrapper);

        $this->theTable($type, $this->submissions, $wrapper);

        $this->printTableFooter($wrapper);

        return $wrapper;
    }

    /**
     * Prints the table footer
     *
     * @param    \DOMElement    $parent    The parent node to append to
     */
    private function printTableFooter($parent)
    {
        $footer = addElement('div', $parent, ['class' => 'tsjippy-table-footer']);

        $p      = addElement('p', $footer, ['id' => 'table-remark'], 'Click on any cell with ');

        addElement('span', $p, ['class' => "edit forms-table"], "underlined text");

        $p->append(" to edit its contents.");

        addElement('br', $p);

        $p->append("Click on any header to sort the column.");

        $formWrapper    = addElement('div', $footer);

        $form           = addElement('form', $formWrapper, ['method' => "post", 'class' => "export-form", 'id' => "export-xls"]);

        addElement('button', $form, ['class' => "button button-primary", 'type' => "submit", 'name' => "export-xls"], 'Export data to excel');

        /**
         * Runs within the formwrapper div of the results table
         * 
         * @param   \DOMElement $parent The parent node
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
        //process any $_GET acions
        do_action('tsjippy-forms-table-GET-actions');
        do_action('tsjippy-forms-table-POST-actions');

        //Load js
        wp_enqueue_script('tsjippy_forms_table_script');

        $formTableWrapper   = addElement('div', '', ['class' => 'form table-wrapper']);
        $tableHead          = addElement('div', $formTableWrapper, ['class' => 'form table-head']);
        addElement('h2', $tableHead, ['class' => "table-title"], $this->tableSettings->title ?? '');
        addElement('br', $tableHead);

        if (
            (
                $split === null    &&                                    // we should use the table settings
                $this->tableSettings->split_table ?? false            // and we should split
            ) ||
            $split == true                                            // we should always split
        ) {
            $this->renderTable('own', true, $all, $formTableWrapper);

            $this->renderTable('others', true, $all, $formTableWrapper);
        } else {
            $this->renderTable('all', false, $all, $formTableWrapper);
        }

        /**
         * Allows to change the DOMElements
         * 
         * @param   \DOMElement  $formTableWrapper   The formtable node
         * @param   object      $object             The DisplayFormResults instance
         */
        do_action('tsjippy-forms-results-html', $formTableWrapper, $this);

        return $formTableWrapper->ownerDocument->saveHTML();
    }

    /**
     * Prints the results table head
     *
     * @param    string        $type        Either 'own', 'others' or 'all'
     * @param    \DOMElement   $table       The table node to add the head to
     */
    private function resultTableHead($type, $table)
    {
        $excelRow = [];
        $thead    = addElement('thead', $table);
        $tr       = addElement('tr', $thead);

        // Loop over the column settings
        foreach ($this->columnSettings as $blockId => $columnSetting) {

            if (
                !is_numeric($blockId)                    ||
                !$columnSetting['show']                    ||                          //hidden column
                (
                    !$this->ownData                        ||                          //The table does not contain data of our own
                    (
                        $this->ownData                      &&                  //or it does contain our own data but
                        !isset($columnSetting['view_right_roles']['own'])     //we are not allowed to see it
                    )
                ) &&
                !$this->tableEditPermissions                 &&                        // no permission to edit the table and
                !array_intersect_key($this->userRoles, $columnSetting['view_right_roles']) // and we do not have the view right role and
            ) {
                continue;
            }

            /**
             * Build the class string
             */
            if (
                isset($this->sortBlockIds[$columnSetting['slug']]) ||
                array_intersect($columnSetting['blockIds'] ?? [], $this->sortBlockIds)
            ) {
                $class    = strtolower($this->sortDirection) . ' defaultsort';
            } elseif ($this->tableSettings->default_sort == $blockId) {
                $class    = "defaultsort";
            } else {
                $class    = "";
            }

            if (isset($this->sortBlockIds[$columnSetting['block_id'] ?? ''])) {
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

            // add block using attribute array
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

        /**
         * Filters the forms actions
         * 
         * @param   array   $actions The form table actions
         */
        $actions = apply_filters('tsjippy-forms-actions', $actions);

        //we have full permissions on this table
        $addHeading    = false;
        if ($this->tableEditPermissions && !empty($actions)) {
            $addHeading    = true;
        } else {
            foreach ($actions as $action) {
                //we have permission for this specific button
                if (array_intersect_key($this->userRoles, $this->columnSettings[$action]['edit_right_roles'] ?? [])) {
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
        //add new row in db
        return TSJIPPY\insertInDb(
            $this->shortcodeTable,
            array(
                'post_id'   => $formId,
                'block_id'  => ''
            ),
            [
                '%d'
            ],
            'forms'
        );
    }
}
