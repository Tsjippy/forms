<?php

namespace TSJIPPY\FORMS;

use TSJIPPY;

if (! defined('ABSPATH')) {
    exit;
}
// Attributes gets passed to this file

/** @disregard P1008 */
$uploader = new TSJIPPY\FILEUPLOAD\FileUploadHtml(get_current_user_id(), library: $attributes['library'] ?? false);

$uploader->getUploadHtml(
    inputName: $attributes['name'] ?? 'upload', 
    targetDir: $attributes['targetDir'] ?? '', 
    multiple: $attributes['multiple'] ?? false, 
    options: $attributes['options'] ?? [], 
    editBeforeUpload: $attributes['edit'] ?? false, 
    metaKey: $attributes['metaKey'] ?? '', 
    auto: false, 
    echo: true
);