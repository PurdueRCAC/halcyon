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
    */
    'allowed-extensions' => 'bmp,csv,doc,docx,epg,eps,gif,ico,jpg,jpeg,key,keynote,mp4,mp3,m4a,m4v,odg,odp,ods,odt,pdf,png,ppt,pptx,swf,txt,xcf,xls,xlsx,svg',
    /*
    |--------------------------------------------------------------------------
    | Determine the max file size upload
    | Defined in MB
    |--------------------------------------------------------------------------
    */
    'max-file-size' => '100',
];
