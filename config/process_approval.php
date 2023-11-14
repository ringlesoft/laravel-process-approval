<?php

return [

    /**
     * This is the name of the table that contains the roles used to classify users
     * (for spatie-laravel-permissions it is the `roles` table
     */
    'roles_model' => "\\Spatie\\Permission\\Models\\Role",


    /**
     * The model associated with login and authentication
     */
    'users_model' => "\\App\\Models\\User",


    /**
     * The Namespace in which application models ar located
     */
    'models_path' => "\\App\Models",

    /**
     * The middlewares that will be applied to the routes pointing to the approval controller
     * Normally ['auth']
     */
    'approval_controller_middlewares' => [],

    /**
     * The name of the css library to use
     */
    'css_library' => 'tailwind', // tailwind | bootstrap
];
