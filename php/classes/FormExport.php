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

        $backupName = str_replace(' ', '-', $this->formData->name) . ".sform";
        TSJIPPY\clearOutput();

        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=$backupName");

        $data    = [
            'post_content' => $this->formData->post->post_content,
            'post_title'   => $this->formData->post->post_title,
            'post_status'  => $this->formData->post->post_status,
            'post_type'    => $this->formData->post->post_type
        ];
        $content    = "form: " . json_encode(serialize($data)) . "\n";

        /**
         * Form Block Conditions
         */ 
        $blockConditions    = TSJIPPY\getFromDb(
            "get_block_conditions_$postId",
            'forms',
            "select * from %i where post_id=%d", 
            $this->blockConditionsTableName, 
            $this->formData->postId
        );

        if (!empty($blockConditions)) {
            $content    .= "block_conditions: " . json_encode(serialize($blockConditions)) . "\n";
        }

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
            "SELECT * FROM %i WHERE post_id=%d AND block_id=%d", 
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

        if (!str_contains($contents, 'form: ')) {
            return new \WP_Error("forms", "Invalid sform file!");
        }

        $lines              = explode("\n", $contents);
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
                // add a new page
                $postId = wp_insert_post($object, true, false);
            } elseif ($type    == 'emails') {
                // Form e-mails
                foreach ($object as $email) {
                    if (empty($email->subject) || empty($email->message)) {
                        continue;
                    }

                    $email->post_id    = $postId;

                    unset($email->id);

                    TSJIPPY\insertInDb($this->formEmailTable, $email, $this->tableFormats[$this->formEmailTable], 'forms');
                }
            } elseif ($type == 'block_conditions') {
                // Form block conditions
                foreach ($object as $condition) {
                    if (empty($condition->rules) || empty($condition->actions)) {
                        continue;
                    }

                    $condition->post_id    = $postId;

                    unset($condition->id);

                    TSJIPPY\insertInDb($this->blockConditionsTableName, $condition, $this->tableFormats[$this->blockConditionsTableName], 'forms');
                }
            } elseif ($type    == 'reminders') {

                // Form reminders
                foreach ($object as $reminder) {
                    if (empty($reminder->frequency) || empty($reminder->period)) {
                        continue;
                    }

                    $reminder->post_id    = $postId;

                    unset($reminder->id);

                    TSJIPPY\insertInDb($this->formReminderTable, $object, $this->tableFormats[$this->formReminderTable], 'forms');
                }
            } else {
                TSJIPPY\printArray("Unknown import type: $type");
                continue;
            }
        }

        $url    = get_permalink($postId);

        return "<div class='success'>Form imported successfully.<br>View it: <a href='$url' target='_blank'>here</a></div>";
    }
}
