<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Constants used by Awards Plugin.
 *
 * @package AwardsPlugin
 */

// Default Configuration Settings

// Paths
define('AWARDS_PLUGIN_PATH', PATH_PLUGINS . '/Awards');
define('AWARDS_PLUGIN_LIB_PATH', AWARDS_PLUGIN_PATH . '/lib');
define('AWARDS_PLUGIN_CLASS_PATH', AWARDS_PLUGIN_LIB_PATH . '/classes');
define('AWARDS_PLUGIN_MODEL_PATH', AWARDS_PLUGIN_CLASS_PATH . '/models');
define('AWARDS_PLUGIN_EXTERNAL_PATH', AWARDS_PLUGIN_LIB_PATH . '/external');
define('AWARDS_PLUGIN_VIEWS_PATH', AWARDS_PLUGIN_PATH . '/views');
define('AWARDS_PLUGIN_RULES_PATH', AWARDS_PLUGIN_CLASS_PATH . '/rules');
define('AWARDS_PLUGIN_AWARD_PICS_PATH', 'plugins/Awards/design/images/awards');
define('AWARDS_PLUGIN_AWARDCLASSES_PICS_PATH', 'plugins/Awards/design/images/awardclasses');
define('AWARDS_PLUGIN_AWARDCLASSES_CSS_FILE', AWARDS_PLUGIN_PATH . '/design/css/awardclasses.css');

// Subdirectories where Core and Custom Rules will be located
define('AWARDS_PLUGIN_CORE_RULES_DIR', 'core');
define('AWARDS_PLUGIN_CUSTOM_RULES_DIR', 'custom');

// URLs
define('AWARDS_PLUGIN_BASE_URL', 'plugin/awards');

// URLs for Award Classes Management
define('AWARDS_PLUGIN_AWARDCLASSES_LIST_URL', AWARDS_PLUGIN_BASE_URL . '/awardclasseslist');
define('AWARDS_PLUGIN_AWARDCLASS_ADDEDIT_URL', AWARDS_PLUGIN_BASE_URL . '/awardclassaddedit');
define('AWARDS_PLUGIN_AWARDCLASS_DELETE_URL', AWARDS_PLUGIN_BASE_URL . '/awardclassdelete');
define('AWARDS_PLUGIN_AWARDCLASS_CLONE_URL', AWARDS_PLUGIN_BASE_URL . '/awardclassclone');

// URLs for Awards Management
define('AWARDS_PLUGIN_AWARDS_LIST_URL', AWARDS_PLUGIN_BASE_URL . '/awardslist');
define('AWARDS_PLUGIN_AWARD_ADDEDIT_URL', AWARDS_PLUGIN_BASE_URL . '/awardaddedit');
define('AWARDS_PLUGIN_AWARD_DELETE_URL', AWARDS_PLUGIN_BASE_URL . '/awarddelete');
define('AWARDS_PLUGIN_AWARD_CLONE_URL', AWARDS_PLUGIN_BASE_URL . '/awardclone');
define('AWARDS_PLUGIN_AWARD_ENABLE_URL', AWARDS_PLUGIN_BASE_URL . '/awardenable');
define('AWARDS_PLUGIN_AWARD_INFO_URL', AWARDS_PLUGIN_BASE_URL . '/awardinfo');

// URLs for User's Awards Management
define('AWARDS_PLUGIN_USERAWARDS_LIST_URL', AWARDS_PLUGIN_BASE_URL . '/userawardslist');
define('AWARDS_PLUGIN_USERAWARD_ADD_URL', AWARDS_PLUGIN_BASE_URL . '/userawardadd');
//define('AWARDS_PLUGIN_USERAWARD_EDIT_URL', AWARDS_PLUGIN_BASE_URL . '/userawardedit');
define('AWARDS_PLUGIN_USERAWARD_DELETE_URL', AWARDS_PLUGIN_BASE_URL . '/userawarddelete');

define('AWARDS_PLUGIN_GENERALSETTINGS_URL', AWARDS_PLUGIN_BASE_URL . '/settings');

// Return Codes
define('AWARDS_PLUGIN_OK', 0);
define('AWARDS_PLUGIN_ERR_INVALID_AWARD_ID', 1001);
define('AWARDS_PLUGIN_ERR_AWARD_NO_RULES', 1002);
//define('AWARDS_ERR_INVALID_TIMESTAMP', 1002);
//define('AWARDS_ERR_INVALID_SIGNATURE', 1003);
//define('AWARDS_ERR_INVALID_USER', 1004);

// Http Arguments
define('AWARDS_PLUGIN_ARG_AWARDID', 'award_id');
define('AWARDS_PLUGIN_ARG_AWARDCLASSID', 'award_class_id');
define('AWARDS_PLUGIN_ARG_RULEID', 'rule_id');

define('AWARDS_PLUGIN_ARG_ENABLEFLAG', 'enable');
