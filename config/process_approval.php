<?php

return [

    /**
     * The Role model class used to resolve approver roles.
     *
     * For spatie/laravel-permission, this is typically `Spatie\Permission\Models\Role`.
     */
    'roles_model' => "\\Spatie\\Permission\\Models\\Role",


    /**
     * The User model class used as the actor/approver (usually your authenticatable model).
     */
    'users_model' => "\\App\\Models\\User",


    /**
     * Default namespace where your application models live.
     *
     * Used to build fully-qualified class names when you refer to models by short name.
     */
    'models_path' => "\\App\Models",

    /**
     * Middlewares applied to the approval routes.
     *
     * The `web` middleware group is already applied by default.
     */
    'approval_controller_middlewares' => [],

    /**
     * CSS library to use for the shipped Blade components/views.
     *
     * Supported: tailwind, bootstrap, bootstrap3, bootstrap4, or null.
     */
    'css_library' => 'tailwind', // tailwind | bootstrap |bootstrap3 | bootstrap4 | null

    /**
     * For the vanilla CSS option, set to true to allow dark mode.
     * This will follow the global system/app dark mode setting
     */
    'allow_dark_mode' => false,

    /**
     * Multi-tenancy field name used to scope approvals (if you enable multi-tenancy).
     *
     * This field is expected to exist on your approvable/user models.
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
