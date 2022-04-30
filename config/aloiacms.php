<?php

return [

    /*
    * This value represents the folder locations where the collections are saved
    * */
    'collections_path' => storage_path('app/collections'),

    ],

    'permissions' => [

        /*
         * This value represents the user the webserver uses to serve the static files
         * */
        'user' => env('FILE_OWNER', 'www-data'),

        /*
         * This value represents the user group the webserver user belongs to
         * */
        'group' => env('FILE_GROUP', 'www-data'),

        /*
         * This value represents any other folder/file path you want to update when setting the file permissions
         * */
        'additional_paths' => []

    ],

];
