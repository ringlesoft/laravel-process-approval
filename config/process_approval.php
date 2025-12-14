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
     * 'web' is already applied by default
     */
    'approval_controller_middlewares' => [],

    /**
     * The name of the css library to use
     */
    'css_library' => 'tailwind', // tailwind | bootstrap |bootstrap3 | bootstrap4 | null

    /**
     * The name of the multi tenancy field in the users table
     */
    'multi_tenancy_field' => 'tenant_id',

    /**
     * If true, the package expects UUID primary keys for its tables and uses UUID-based
     * relations (e.g. uuid morphs / UUID foreign keys).
     *
     * Use this for fresh installs. Existing bigint installs should keep this false.
     */
    'use_uuids' => false,

    /**
     * If true, the package will automatically load migrations from the vendor package.
     *
     * When you publish migrations into your application (e.g. via `process-approval:install`),
     * make sure you set this to `false` to avoid running migrations twice.
     */
    'load_migrations' => true,
];
