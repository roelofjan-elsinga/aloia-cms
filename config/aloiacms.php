<?php

return [

    /*
    * This value represents the folder locations where the collections are saved
    * */
    'collections_path' => storage_path('app/collections'),

    'authentication' => [

        /*
         * The User model used for authentication
         * */
        'model' => \AloiaCms\Auth\User::class

    ]

];
