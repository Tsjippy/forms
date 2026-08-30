<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;
use stdClass;

if (! defined('ABSPATH')) {
    exit;
}

class FormExport extends SaveFormSettings
{
    /**
     * FormExport constructor.
     *
     * @param string   $blockId    
     * @param string   $postId     Post ID to export forms for
     * @param bool     $all        Whether to export all forms or just one
     * @param int      $pageSize   Number of forms to export per page
     * @param string   $formUrl    URL of the form to export
     * @param int      $userId     User ID to export forms for
     */
    public function __construct($blockId='', $postId='', $all=false, $pageSize=50, $formUrl='', $userId=0)
    {
        parent::__construct(blockId: $blockId, postId:$postId, all: $all, pageSize:$pageSize, userId:$userId);
    }

    /**
     * Writes the form export to the output buffer for download
     * 
     * @param int    $postId    ID of the formpost to export
     * @param int    $blockId   ID of the formblock to export
     * 
     * @return void
     */
    public function exportForm($postId, $blockId='')
    {
        $this->getForm($postId, $blockId);

        /**
         * Form Settings
         */
        unset($this->formData->url);

        // Remove the id
        unset($this->formData->blockId);

        // Set form version to 1
        $this->formData->version     = 1;

        $backupName = $this->formData->slug . ".sform";
        TSJIPPY\clearOutput();

        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=$backupName");

        $content    = "form: " . json_encode(serialize($this->formData)) . "\n";

        /**
         * Form Blocks
         */
        foreach ($this->formBlocks as &$block) {
            unset($block->block_id);
        }

        $content    .= "blocks: " . json_encode(serialize($this->formBlocks)) . "\n";

        /**
         * Form E-mails
         */
        $emailSettings    = TSJIPPY\getFromDb(
            "get_email_settings_$postId",
            'forms',
            "select * from %i where post_id=%d and block_id=%d", 
            $this->formEmailTable, 
            $this->formData->postId, 
            $this->formData->blockId
        );

        foreach ($emailSettings as &$emailSetting) {
            unset($emailSetting->post_id);
            unset($emailSetting->block_id);
        }

        if (!empty($emailSettings)) {
            $content    .= "emails: " . json_encode(serialize($emailSettings)) . "\n";
        }

        /**
         * Form Reminders
         */
        $reminders            = TSJIPPY\getFromDb(
            "form_reminders_$blockId",
            'forms',
            "SELECT * FROM %i WHERE", 
            $this->formData->postId, 
            $this->formData->blockId
        );

        if (!empty($reminders)) {
            unset($reminders->id);
            $content    .= "reminders: " . json_encode(serialize($reminders)) . "\n";
        }

        // phpcs:ignore
        echo $content;
        
        exit;
    }

    /**
     * Inserts form blocks, while updating conditions with new block ids
     *
     * @param array    $formEmails        Array of form emails to insert
     * @param array    $blockIdMapping     Mapping of old block ids to new block ids
     *
     * @return array|\WP_Error            Array of old block ids to new block ids or WP_Error on failure
     */
    protected function insertFormEmails($formEmails, $blockIdMapping)
    {
        // Form blocks
        foreach ($formEmails as $email) {

            $email->block_id    = $this->formData->blockId;

            if (!empty($email->submitted_trigger)) {
                $triggers    = maybe_unserialize($email->submitted_trigger);

                if (isset($triggers['block'])) {
                    if (is_numeric($triggers['block'])) {
                        $trigger['blocks']    = $blockIdMapping[$trigger['block']];
                    }

                    if (is_numeric($triggers['valueblock'])) {
                        $triggers['valueblock']    = $blockIdMapping[$trigger['valueblock']];
                    }
                } else {
                    foreach ($triggers as &$trigger) {
                        if (is_numeric($trigger['block'])) {
                            $trigger['block']    = $blockIdMapping[$trigger['block']];
                        }

                        if (is_numeric($trigger['valueblock'])) {
                            $trigger['valueblock']    = $blockIdMapping[$trigger['valueblock']];
                        }
                    }
                }

                $email->submitted_trigger    = serialize($triggers);
            }

            if (!empty($email->conditional_field)) {
                $email->conditional_field    = $blockIdMapping[$email->conditional_field];
            }

            if (!empty($email->conditional_fields)) {
                $conditionalFields    = maybe_unserialize($email->conditional_fields);

                foreach ($conditionalFields as &$conditionalFieldId) {
                    $conditionalFieldId    = $blockIdMapping[$conditionalFieldId];
                }

                $email->conditional_fields    = serialize($conditionalFields);
            }

            if (!empty($email->conditional_from_email)) {
                $conditionalFromEmails    = maybe_unserialize($email->conditional_from_email);

                foreach ($conditionalFromEmails as &$conditionalFromEmail) {
                    $conditionalFromEmail['fieldid']    = $blockIdMapping[$conditionalFromEmail['fieldid']];
                }

                $email->conditional_from_email    = serialize($conditionalFromEmails);
            }

            if (!empty($email->conditional_email_to)) {
                $conditionalEmailTo    = maybe_unserialize($email->conditional_email_to);

                foreach ($conditionalEmailTo as &$conditionalEmailToField) {
                    $conditionalEmailToField['fieldid']    = $blockIdMapping[$conditionalEmailToField['fieldid']];
                }

                $email->conditional_email_to    = serialize($conditionalEmailTo);
            }

            //$emailId         = $this->insertOrUpdateData($this->elTableName, $email);

            /* if (is_wp_error($emailId)) {
                return $emailId;
            } */
        }

        return true;
    }

    /**
     * Imports a form from a file
     *
     * @param string    $path        Path to the form file
     *
     * @return int|\WP_Error         Post id WP_Error on failure
     */
    public function importForm($path)
    {
        if (!file_exists($path)) {
            return new \WP_Error('forms', "$path does not exist");
        }

        $wpFileSystem  = TSJIPPY\loadWpFileSystem();

        $contents      = $wpFileSystem->get_contents($path);

        if (!str_contains($contents, 'form: ') || !str_contains($contents, 'blocks: ')) {
            return new \WP_Error("forms", "Invalid sform file!");
        }

        $lines              = explode("\n", $contents);

        $autoArchiveEl      = null;
        $blockIdMapping   = [];
        $url                = '';   
        $postId             = 0;

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $exploded    = explode(': ', $line, 2);
            if (count($exploded) != 2) {
                continue;
            }

            $type        = $exploded[0];
            $data        = $exploded[1];

            $object        = unserialize(json_decode($data));

            if ($type    == 'form') {
                $autoArchiveEl    = $object->autoarchive_el;

                // add a new page
                $post = array(
                    'post_type'        => 'page',
                    'post_title'    => "$object->name form",
                    'post_content'  => '[tsjippy_formbuilder slug={$object->slug}]',
                    'post_status'   => "publish",
                    'post_author'   => '1'
                );

                $postId = wp_insert_post($post, true, false);
                $url    = get_permalink($postId);

                // Form data
                $object->url    = $url;

                if (empty($this->formData)) {
                    $this->formData    = new stdClass();
                }

                $this->formData->blockId     = $this->insertOrUpdateData($this->tableName, $object);

                if (is_wp_error($this->formData->blockId)) {
                    return $this->formData->blockId;
                }
            } elseif ($type    == 'blocks') {
                //$blockIdMapping    = $this->insertFormBlocks($object);

                if (is_wp_error($blockIdMapping)) {
                    return $blockIdMapping;
                }
            } elseif ($type    == 'emails') {
                // Form e-mails
                $this->insertFormEmails($object, $blockIdMapping);
            } elseif ($type    == 'reminders') {
                // Form reminders
                foreach ($object as $reminder) {
                    if (empty($reminder->frequency) || empty($reminder->period)) {
                        continue;
                    }

                    $reminder->post_id    = $this->formData->postId;
                    $reminder->block_id   = $this->formData->blockId;

                    $this->insertOrUpdateData($this->formReminderTable, $reminder);
                }
            } else {
                TSJIPPY\printArray("Unknown import type: $type");
                continue;
            }
        }

        return $postId;
    }
}
