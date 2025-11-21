<?php

return [

    /*
     * If set to false, no activities will be saved to the database.
     */
    'enabled' => env('ATTRIBUTE_CHANGE_LOGGER_ENABLED', true),

    /*
     * This model will be used to log activity.
     * It should implement the DVSoft\AttributeChangeLog\Models\AttributeChangeLog interface
     * and extend Illuminate\Database\Eloquent\Model.
     */
    'attribute_change_log_model' => \DvSoft\AttributeChangeLog\Models\AttributeChangeLog::class,

    /*
     * This is the name of the table that will be created by the migration and
     * used by the Activity model shipped with this package.
     */
    'table_name' => env('ATTRIBUTE_CHANGE_TABLE_NAME', 'actribute_change_logs'),

    /*
     * This is the database connection that will be used by the migration and
     * the Attribute Change Log model shipped with this package. In case it's not set
     * Laravel's database.default will be used instead.
     */
    'database_connection' => env('ATTRIBUTE_CHANGE_DB_CONNECTION'),
];
