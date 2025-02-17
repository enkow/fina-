<?php

return [
	/*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

	'default' => env('FILESYSTEM_DISK', 'local'),

	/*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

	'disks' => [
		'local' => [
			'driver' => 'local',
			'root' => storage_path('app'),
			'throw' => false,
		],

		'backup' => [
			'driver' => 'local',
			'root' => storage_path('backup'),
			'url' => env('APP_URL') . '/../storage',
			'visibility' => 'public',
			'throw' => false,
		],

		'public' => [
			'driver' => 'local',
			'root' => storage_path('app/public'),
			'url' => env('APP_URL') . '/storage',
			'visibility' => 'public',
			'throw' => false,
		],

		'helpItemImages' => [
			'driver' => 'local',
			'root' => storage_path('app/helpItemImages'),
			'url' => env('APP_URL') . '/helpItemImages',
			'visibility' => 'public',
			'throw' => false,
		],

		'helpItemThumbnails' => [
			'driver' => 'local',
			'root' => storage_path('app/helpItemThumbnails'),
			'url' => env('APP_URL') . '/helpItemThumbnails',
			'visibility' => 'public',
			'throw' => false,
		],

		'setImages' => [
			'driver' => 'local',
			'root' => storage_path('app/setImages'),
			'url' => env('APP_URL') . '/setImages',
			'visibility' => 'public',
			'throw' => false,
		],

		'gameImages' => [
			'driver' => 'local',
			'root' => storage_path('app/gameImages'),
			'url' => env('APP_URL') . '/gameImages',
			'visibility' => 'public',
			'throw' => false,
		],

		'gameIcons' => [
			'driver' => 'local',
			'root' => storage_path('app/gameicons'),
			'url' => env('APP_URL') . '/gameIcons',
			'visibility' => 'public',
			'throw' => false,
		],

		'clubMaps' => [
			'driver' => 'local',
			'root' => storage_path('app/clubMaps'),
			'url' => env('APP_URL') . '/clubMaps',
			'visibility' => 'public',
			'throw' => false,
		],

		'gamePhotos' => [
			'driver' => 'local',
			'root' => storage_path('app/gamePhotos'),
			'url' => env('APP_URL') . '/gamePhotos',
			'visibility' => 'public',
			'throw' => false,
		],

		'featureAssets' => [
			'driver' => 'local',
			'root' => storage_path('app/featureAssets'),
			'url' => env('APP_URL') . '/featureAssets',
			'visibility' => 'public',
			'throw' => false,
		],

		'clubAgreements' => [
			'driver' => 'local',
			'root' => storage_path('app/clubAgreements'),
			'url' => env('APP_URL') . '/clubAgreements',
			'visibility' => 'public',
			'throw' => false,
		],

		'clubTerms' => [
			'driver' => 'local',
			'root' => storage_path('app/clubTerms'),
			'url' => env('APP_URL') . '/clubTerms',
			'visibility' => 'public',
			'throw' => false,
		],

		's3' => [
			'driver' => 's3',
			'key' => env('AWS_ACCESS_KEY_ID'),
			'secret' => env('AWS_SECRET_ACCESS_KEY'),
			'region' => env('AWS_DEFAULT_REGION'),
			'bucket' => env('AWS_BUCKET'),
			'url' => env('AWS_URL'),
			'endpoint' => env('AWS_ENDPOINT'),
			'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
			'throw' => false,
		],
	],

	/*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

	'links' => [
		public_path('storage') => storage_path('app/public'),
		public_path('images/help-item-thumbnails') => storage_path('app/helpItemThumbnails'),
		public_path('images/help-item-images') => storage_path('app/helpItemImages'),
		public_path('images/set-images') => storage_path('app/setImages'),
		public_path('images/game-images') => storage_path('app/gameImages'),
		public_path('images/game-icons') => storage_path('app/gameIcons'),
		public_path('club-assets/maps') => storage_path('app/clubMaps'),
		public_path('club-assets/game-photos') => storage_path('app/gamePhotos'),
		public_path('club-assets/terms') => storage_path('app/clubTerms'),
		public_path('club-assets/agreements') => storage_path('app/clubAgreements'),
		public_path('feature-assets') => storage_path('app/featureAssets'),
	],
];
