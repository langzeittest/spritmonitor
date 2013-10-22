<?php

//
// This install file will be included by the module automatically at the first
// time that it is run. This file will take care of adding the custom user
// field "mod_spritmonitor" to Phorum. This way, the administrator won't have
// to create the custom field manually nor to call the settings page.
//

if (!defined('PHORUM')) return;

include_once('./include/api/custom_profile_fields.php');

// Store custom profile field

// Get the current custom profile field if exists.
$field = phorum_api_custom_profile_field_byname('mod_spritmonitor');

// If the field does not exist. Add it.
if ($field_exists===NULL) {
    phorum_api_custom_profile_field_configure
        ( array
              ( 'id'            => NULL,
                'name'          => 'mod_spritmonitor',
                'length'        => 8,
                'html_disabled' => 1,
                'show_in_admin' => 1 ) );
}

$PHORUM['mod_spritmonitor_installed'] = 1;

if ( !phorum_db_update_settings
         ( array
               ( 'mod_spritmonitor_installed'=>$PHORUM['mod_spritmonitor_installed'] ) ) ) {
    $error = 'Database error while updating settings.';
}

?>
