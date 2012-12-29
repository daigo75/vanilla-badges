<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Constants used by Logger Plugin.
 *
 * @package LoggerPlugin
 */

// Default Configuration Settings

// Paths
define('AWARDS_PLUGIN_PATH', PATH_PLUGINS . '/Awards');
define('AWARDS_PLUGIN_LIB_PATH', AWARDS_PLUGIN_PATH . '/lib');
define('AWARDS_PLUGIN_CLASS_PATH', AWARDS_PLUGIN_LIB_PATH . '/classes');
define('AWARDS_PLUGIN_MODEL_PATH', AWARDS_PLUGIN_CLASS_PATH . '/models');
define('AWARDS_PLUGIN_EXTERNAL_PATH', AWARDS_PLUGIN_LIB_PATH . '/external');
define('AWARDS_PLUGIN_VIEW_PATH', AWARDS_PLUGIN_PATH . '/views');

// URLs
define('AWARDS_PLUGIN_BASE_URL', '/plugin/awards');

// URLs for Awards Management
define('AWARDS_PLUGIN_AWARDS_LIST_URL', AWARDS_PLUGIN_BASE_URL . '/awardslist');
define('AWARDS_PLUGIN_AWARD_ADD_URL', AWARDS_PLUGIN_BASE_URL . '/awardadd');
define('AWARDS_PLUGIN_AWARD_EDIT_URL', AWARDS_PLUGIN_BASE_URL . '/awardedit');
define('AWARDS_PLUGIN_AWARD_DELETE_URL', AWARDS_PLUGIN_BASE_URL . '/awarddelete');

// URLs for Rules Management
define('AWARDS_PLUGIN_RULES_LIST_URL', AWARDS_PLUGIN_BASE_URL . '/ruleslist');
define('AWARDS_PLUGIN_RULE_ADD_URL', AWARDS_PLUGIN_BASE_URL . '/ruleadd');
define('AWARDS_PLUGIN_RULE_EDIT_URL', AWARDS_PLUGIN_BASE_URL . '/ruleedit');
define('AWARDS_PLUGIN_RULE_DELETE_URL', AWARDS_PLUGIN_BASE_URL . '/ruledelete');
define('AWARDS_PLUGIN_RULE_ENABLE_URL', AWARDS_PLUGIN_BASE_URL . '/ruleenable');

// URLs for Criteria Management
define('AWARDS_PLUGIN_CRITERIA_LIST_URL', AWARDS_PLUGIN_BASE_URL . '/criterialist');
define('AWARDS_PLUGIN_CRITERION_ADD_URL', AWARDS_PLUGIN_BASE_URL . '/criterionadd');
define('AWARDS_PLUGIN_CRITERION_EDIT_URL', AWARDS_PLUGIN_BASE_URL . '/criterionedit');
define('AWARDS_PLUGIN_CRITERION_DELETE_URL', AWARDS_PLUGIN_BASE_URL . '/criteriondelete');
define('AWARDS_PLUGIN_CRITERION_ENABLE_URL', AWARDS_PLUGIN_BASE_URL . '/criterionenable');

// URLs for User's Awards Management
define('AWARDS_PLUGIN_USERAWARDS_LIST_URL', AWARDS_PLUGIN_BASE_URL . '/userawardslist');
define('AWARDS_PLUGIN_USERAWARD_ADD_URL', AWARDS_PLUGIN_BASE_URL . '/userawardadd');
//define('AWARDS_PLUGIN_USERAWARD_EDIT_URL', AWARDS_PLUGIN_BASE_URL . '/userawardedit');
define('AWARDS_PLUGIN_USERAWARD_DELETE_URL', AWARDS_PLUGIN_BASE_URL . '/userawarddelete');

define('AWARDS_PLUGIN_GENERALSETTINGS_URL', AWARDS_PLUGIN_BASE_URL . '/settings');

// Return Codes
define('AWARDS_PLUGIN_OK', 0);
define('AWARDS_PLUGIN_ERR_INVALID_AWARD_ID', 1001);
//define('AWARDS_ERR_INVALID_TIMESTAMP', 1002);
//define('AWARDS_ERR_INVALID_SIGNATURE', 1003);
//define('AWARDS_ERR_INVALID_USER', 1004);

// Http Arguments
define('AWARDS_PLUGIN_ARG_AWARDID', 'award_id');
define('AWARDS_PLUGIN_ARG_RULEID', 'rule_id');
define('AWARDS_PLUGIN_ARG_CRITERIONID', 'rule_id');

define('AWARDS_PLUGIN_ARG_ENABLEFLAG', 'enable');
