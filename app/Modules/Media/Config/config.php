<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Choose which filesystem disk to use to store the media
    |--------------------------------------------------------------------------
    | Choose one or more of the filesystems configured in config/filesystems.php
    */
    'disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Specify all the allowed file extensions a user can upload on the server
    |--------------------------------------------------------------------------
    | List of lowercase file extensions. Example:
    | [
    |    'bmp','csv','doc','docx','epg','eps','gif','ico','jpg','jpeg','jpe',
    |    'key','keynote','mp4','mp3','m4a','m4v','odg','odp','ods','odt','pdf',
    |    'png','ppt','pptx','svg','txt','xcf','xls','xlsx','webp','avif','zip'
    | ]
    | Leave empty to allow any file type.
    */
    'allowed-extensions' => [],

    /*
    |--------------------------------------------------------------------------
    | Determine the max file size upload
    | Defined in MB
    |--------------------------------------------------------------------------
    */
    'max-file-size' => 100,

    /*
    |--------------------------------------------------------------------------
    | List limit
    |--------------------------------------------------------------------------
    | Maximum number of items to show at once. Showing too many can cause
    | slow-downs in the page.
    */
    'list_limit' => 100,
];
