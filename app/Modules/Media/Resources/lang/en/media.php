<?php
return [
	'module name' => 'Media Manager',
	'module desc' => 'Module for managing site media',

	// Images
	'align' => 'Align',
	'align desc' => 'If "Not Set", the alignment is defined by the class ".img_caption.none". Usually to get the image centred on the page.',
	'browse files' => 'Browse files',
	'caption' => 'Caption',
	'caption desc' => 'If set to "Yes", the Image Title will be used as caption.',
	'image title' => ':name - :size',

	// Folders
	'create complete' => 'Create Complete: %s',
	'create folder' => 'Create Folder',
	'folder' => 'Folder',
	'folder name' => 'Folder name',
	'folder path' => 'Folder path',
	'rename' => 'Rename',

	'clear list' => 'Clear List',
	'configuration' => 'Media Manager Options',
	'detail view' => 'List View',
	'new name' => 'New name',
	'use helper in content' => 'Or use <code>:helper</code> in content',

	// Error messages
	'error' => [
		'bad request' => 'Bad Request',
		'file exists' => 'File already exists',
		'unable to delete folder not empty' => 'Unable to delete:&#160;%s. Folder is not empty!',
		'unable to delete bad file name' => 'Unable to delete:&#160;%s. File name must only contain alphanumeric characters and no spaces.',
		'missing directory name' => 'No directory name provided',
		'invalid directory name' => 'Invalid directory name provided. Directory name must only contain alphanumeric characters and no spaces.',
		'missing source directory' => 'Source directory not found',
		'destination exists' => 'Destination directory already exists',
		'directory not empty' => 'Directory not empty',
		'directory delete failed' => 'Failed to delete the directory',
		'unable to upload file' => 'Unable to upload file.',
		'file not found' => 'Specified file ":file" does not exist',
		'directory not found' => 'Specified directory ":directory" does not exist',
		'cannot read image dimensions' => 'There was a problem reading the image dimensions.',
	],

	// Fields
	'field' => [
		'legal extensions' => 'Legal Extensions (File Types)',
		'legal extensions desc' => 'Extensions (file types) you are allowed to upload (comma separated).',
		'legal image extensions' => 'Legal Image Extensions (File Types)',
		'legal image extensions desc' => 'Image Extensions (file types) you are allowed to upload (comma separated). These are used to check for valid image headers.',
		'maximum size' => 'Maximum Size (in MB)',
		'maximum size desc' => 'The maximum size for an upload (in megabytes). Use zero for no limit. Note: your server has a maximum limit.',
	],

	'files' => 'Files',

	// File size
	'filesize' => 'File size',
	'filesize bytes' => ':size B',
	'filesize kilobytes' => ':size KB',
	'filesize megabytes' => ':size MB',

	// Misc
	'list' => [
		'name' => 'Name',
		'size' => 'Size',
		'type' => 'Type',
		'modified' => 'Last modified',
		'path' => 'Path',
		'width' => 'Width',
		'height' => 'Height',
	],

	'upload' => 'Upload',
	'download' => 'Download',
	'file info' => 'Info',
	'file link' => 'Get link',
	'file path' => 'File path',
	'no images found' => 'No Images Found',
	'not set' => 'Not Set',
	'overall progress' => 'Overall Progress',
	'pixel dimensions' => 'Pixel Dimensions (W x H)',
	'start upload' => 'Start Upload',
	'thumbnail view' => 'Thumbnail View',
	'title' => 'Image Title',

	// Upload
	'upload successful' => 'Upload Successful',
	'upload instructions' => 'Drop files or click here to upload',
];
