<?php

if (!defined('PHORUM')) return;

//
// Install once custom profiles field.
//
function mod_spritmonitor_common() {
    global $PHORUM;

    // Install once custom profiles field
    if (    !isset($PHORUM['mod_spritmonitor_installed'])
         || !$PHORUM['mod_spritmonitor_installed'] ) {
        include('./mods/spritmonitor/install.php');
    }
}

//
// Add a control center option in case the user settings are shown.
//
function mod_spritmonitor_tpl_cc_usersettings($profile) {
    if (isset($profile['USERPROFILE'])) {
        global $PHORUM;
        include(phorum_get_template('spritmonitor::cc_usersettings'));
    }
    return $profile;
}

//
// Add sanity checks
//
function mod_spritmonitor_sanity_checks($sanity_checks) {
    if (    isset($sanity_checks)
         && is_array($sanity_checks) ) {
        $sanity_checks[] = array(
            'function'    => 'mod_spritmonitor_do_sanity_checks',
            'description' => 'Spritmonitor Module'
        );
    }
    return $sanity_checks;
}

//
// Do sanity checks
//
function mod_spritmonitor_do_sanity_checks() {
    global $PHORUM;

    // Check custom profile field
    // Get the current custom profile fields.
    $fields = $PHORUM['PROFILE_FIELDS'];
    // If this is not an array, we don't trust it.
    if (!is_array($fields)) {
        return array(
                   PHORUM_SANITY_CRIT,
                   "\$PHORUM['PROFILE_FIELDS'] is not an array."
               );
    } else {
        // Check if the field is available.
        $field_exists = false;
        foreach ($fields as $id => $fieldinfo) {
            if ($fieldinfo['name'] == 'mod_spritmonitor') {
                $field_exists = true;
                break;
            }
        }
        // The field does not exist.
        if (!$field_exists) {
            return array(
                       PHORUM_SANITY_CRIT,
                       'The custom profile field for the Spritmonitor '
                       ."Module doesn't exist."
                   );
        }
    }

    // Check if custom language file exists
    $checked = array();
    // Check for the default language file.
    if ( !file_exists
             ("./mods/spritmonitor/lang/{$PHORUM['language']}.php")
       ) {
        return array(
            PHORUM_SANITY_WARN,
            'Your default language is set to "'
                .htmlspecialchars($PHORUM['language'])
                .'", but the language file "mods/spritmonitor/lang/'
                .htmlspecialchars($PHORUM['language'])
                .'.php" is not available on your system.',
            'Install the specified language file to make this default '
                .'language work or change the Default Language setting under '
                .'General Settings.'
        );
    }
    $checked[$PHORUM['language']] = true;

    // Check for the forum specific language file(s).
    $forums = phorum_db_get_forums();
    foreach ($forums as $id => $forum) {
        if (    !empty($forum['language'])
             && !$checked[$forum['language']]
             && !file_exists("./mods/spritmonitor/lang/{$forum['language']}.php")
           ) {
            return array(
                PHORUM_SANITY_WARN,
                'The language for forum "'
                    .htmlspecialchars($forum['name'])
                    .'" is set to "'
                    .htmlspecialchars($forum['language'])
                    .'", but the language file "mods/spritmonitor/lang/'
                    .htmlspecialchars($forum['language'])
                    .'.php" is not available on your system.',
                'Install the specified language file to make this default '
                    .'language work or change the language setting for the '
                    .'forum.'
            );
        }
        $checked[$forum['language']] = true;
    }

    // Check if custom language file contains same array key as the english file
    $PHORUM['DATA']['LANG'] = array();
    include('./mods/spritmonitor/lang/english.php');
    $orig_data = $PHORUM['DATA']['LANG'];
    $orig_keys = array_keys($PHORUM['DATA']['LANG']['mod_spritmonitor']);
    // Check all files in the module language directory
    $tmphandle = opendir('./mods/spritmonitor/lang/');
    if ($tmphandle) {
        while ($file = readdir($tmphandle)) {
            if ($file == '.' || $file == '..' || $file == 'english.php')
                continue;
            else
                $PHORUM['DATA']['LANG'] = array();
                include("./mods/spritmonitor/lang/{$file}");
                $new_keys = array_keys($PHORUM['DATA']['LANG']['mod_spritmonitor']);

                $missing_keys = array();

                foreach ($orig_keys as $id => $key) {
                    if (!in_array($key,$new_keys)) {
                        $missing_keys[$key] = $orig_data[$key];
                    }
                }

                if (count($missing_keys)) {
                    $tmpmessage
                        = 'The following keys are missing in your custom language file '.$file.':';
                    foreach ($missing_keys as $key => $val) {
                        $tmpmessage .= '<br />'.$key;
                    }
                    return array(
                               PHORUM_SANITY_CRIT,
                               $tmpmessage,
                               'Please add these keys to this language file!'
                           );
                }
        }
        closedir($tmphandle);
    } else {
        return array(
                   PHORUM_SANITY_CRIT,
                   'Error getting file list of module language files.',
                   'Check if the mods/spritmonitor/lang/ directory exists.'
               );
    }

    // Check if read.tpl includes carcost::read
    $tmpfile = file_get_contents("./templates/{$PHORUM['default_forum_options']['template']}/read.tpl");
    if (!preg_match('/carcost::read/', $tmpfile)) {
          return array(
                     PHORUM_SANITY_CRIT,
                     'Your template file "read.tpl" misses include of '
                         .'additional CarCost template file.',
                     'You have to change the template file. See README in the module directory.'
                 );
    }

    // Check if read_hybrid.tpl includes carcost::read_hybrid
    $tmpfile = file_get_contents("./templates/{$PHORUM['default_forum_options']['template']}/read_hybrid.tpl");
    if (!preg_match('/carcost::read_hybrid/', $tmpfile)) {
          return array(
                     PHORUM_SANITY_CRIT,
                     'Your template file "read_hybrid.tpl" misses include of '
                         .'additional CarCost template file.',
                     'You have to change the template file. See README in the module directory.'
                 );
    }

    // Check if read_threads.tpl includes carcost::read_threads
    $tmpfile = file_get_contents("./templates/{$PHORUM['default_forum_options']['template']}/read_threads.tpl");
    if (!preg_match('/carcost::read_threads/', $tmpfile)) {
          return array(
                     PHORUM_SANITY_CRIT,
                     'Your template file "read_threads.tpl" misses include of '
                         .'additional CarCost template file.',
                     'You have to change the template file. See README in the module directory.'
                 );
    }

    // Check if profile.tpl includes carcost::profile
    $tmpfile = file_get_contents("./templates/{$PHORUM['default_forum_options']['template']}/profile.tpl");
    if (!preg_match('/carcost::profile/', $tmpfile)) {
          return array(
                     PHORUM_SANITY_CRIT,
                     'Your template file "profile.tpl" misses include of '
                         .'additional CarCost template file.',
                     'You have to change the template file. See README in the module directory.'
                 );
    }

    return array(PHORUM_SANITY_OK, NULL, NULL);
}

?>
