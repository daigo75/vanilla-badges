<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

// File awards.defines.php must be included by manually specifying the whole
// path. It will then define some shortcuts for commonly used paths, such as
// AWARDS_PLUGIN_LIB_PATH, used just below.
require(PATH_PLUGINS . '/Awards/lib/awards.defines.php');
// AWARDS_PLUGIN_LIB_PATH is defined in awards.defines.php.
require(AWARDS_PLUGIN_LIB_PATH . '/awards.validation.php');

// Define the plugin:
$PluginInfo['Awards'] = array(
	'Description' => 'Awards plugin for Vanilla Forums',
	'Version' => '13.02.07 alpha',
	'RequiredApplications' => array('Vanilla' => '2.0'),
	'RequiredTheme' => FALSE,
	'RequiredPlugins' => array('Logger' => '12.10.28',
														 'AeliaFoundationClasses' => '13.02.27',
														 ),
	'HasLocale' => FALSE,
	'MobileFriendly' => TRUE,
	'SettingsUrl' => '/plugin/awards',
	'SettingsPermission' => 'Garden.AdminUser.Only',
	'Author' => 'D.Zanella',
	'AuthorEmail' => 'diego@pathtoenlightenment.net',
	'AuthorUrl' => 'http://dev.pathtoenlightenment.net',
	'RegisterPermissions' => array('Plugins.Awards.Manage',),
);

class AwardsPlugin extends Gdn_Plugin {
	private $_RulesManager;

	/**
	 * Returns an instance of a Class and stores it as a property of this class.
	 * The function follows the principle of lazy initialization, instantiating
	 * the class the first time it's requested.
	 *
	 * @param string ClassName The Class to instantiate.
	 * @param array Args An array of Arguments to pass to the Class' constructor.
	 * @return object An instance of the specified class.
	 * @throws An Exception if the specified class does not exist.
	 */
	private function GetInstance($ClassName) {
		$FieldName = '_' . $ClassName;
		$Args = func_get_args();
		// Discard the first argument, as it is the Class Name, which doesn't have
		// to be passed to the instance of the Class
		array_shift($Args);

		if(empty($this->$FieldName)) {
			$Reflect  = new ReflectionClass($ClassName);

			$this->$FieldName = $Reflect->newInstanceArgs($Args);
		}

		return $this->$FieldName;
	}

	/**
	 * Returns an instance of RulesManager.
	 *
	 * @see AwardsPlugin::GetInstance()
	 */
	public function RulesManager() {
		return $this->GetInstance('AwardRulesManager');
	}

	/**
	 * Returns an instance of AwardsController.
	 *
	 * @see AwardsPlugin::GetInstance()
	 */
	public function AwardsManager() {
		return $this->GetInstance('AwardsManager');
	}

	/**
	 * Plugin constructor
	 *
	 * This fires once per page load, during execution of bootstrap.php. It is a decent place to perform
	 * one-time-per-page setup of the plugin object. Be careful not to put anything too strenuous in here
	 * as it runs every page load and could slow down your forum.
	 */
	public function __construct() {
		parent::__construct();

		// Instantiate specialised Controllers
		//$this->RulesManager();
		//$this->AwardsManager();
	}

	/**
	 * Base_Render_Before Event Hook
	 *
	 * This is a common hook that fires for all controllers (Base), on the Render method (Render), just
	 * before execution of that method (Before). It is a good place to put UI stuff like CSS and Javascript
	 * inclusions. Note that all the Controller logic has already been run at this point.
	 *
	 * @param $Sender Sending controller instance
	 */
	public function Base_Render_Before($Sender) {
		// Files for Admin section
		if(strcasecmp($Sender->Application, 'dashboard') == 0) {
			$Sender->AddCssFile('awards_admin.css', 'plugins/Awards/design/css');
		}

		// Files for frontend
		if(strcasecmp($Sender->Application, 'vanilla') == 0) {
			$Sender->AddJsFile('awards.js', 'plugins/Awards/js');
		}

		// Common files
		$Sender->AddCssFile('awardclasses.css', 'plugins/Awards/design/css');

		// TODO Fire rule processing
	}

	/**
	 * Create a method called "Awards" on the PluginController
	 *
	 * @param $Sender Sending controller instance
	 */
	public function PluginController_Awards_Create($Sender) {
		/*
		 * If you build your views properly, this will be used as the <title> for your page, and for the header
		 * in the dashboard. Something like this works well: <h1><?php echo T($this->Data['Title']); ?></h1>
		 */
		$Sender->Title('Awards Plugin');
		$Sender->AddSideMenu('plugin/awards');

		// If your sub-pages use forms, this is a good place to get it ready
		$Sender->Form = new Gdn_Form();

		/*
		 * Note: When the URL is accessed without parameters, Controller_Index() is called. This is a good place
		 * for a dashboard settings screen.
		 */
		$this->Dispatch($Sender, $Sender->RequestArgs);
	}

	/**
	 * Renders the Plugin's default (index) page.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_Index($Sender) {
		$this->Controller_Settings($Sender);
	}

	/**
	 * Renders the Settings page.
	 *
	 * @param object Sender Sending controller instance
	 */
	public function Controller_Settings($Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_GENERALSETTINGS_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		$Sender->SetData('PluginDescription',$this->GetPluginKey('Description'));

		$Validation = new Gdn_Validation();
		$ConfigurationModel = new Gdn_ConfigurationModel($Validation);
		$ConfigurationModel->SetField(array(
			// TODO Set default configuration values

		));

		// Set the model on the form.
		$Sender->Form->SetModel($ConfigurationModel);

		// If seeing the form for the first time...
		if($Sender->Form->AuthenticatedPostBack() === FALSE) {
			// Apply the config settings to the form.
			$Sender->Form->SetData($ConfigurationModel->Data);
		}
		else {
			// TODO Validate Configuration settings

			$Saved = $Sender->Form->Save();
			if ($Saved) {
				$Sender->StatusMessage = T('Your changes have been saved.');
			}
		}

		$Sender->Render($this->GetView('awards_generalsettings_view.php'));
	}

	/**
	 * Add a link to the dashboard menu
	 *
	 * By grabbing a reference to the current SideMenu object we gain access to its methods, allowing us
	 * to add a menu link to the newly created /plugin/Awards method.
	 *
	 * @param $Sender Sending controller instance
	 */
	public function Base_GetAppSettingsMenuItems_Handler($Sender) {
		$Menu = $Sender->EventArguments['SideMenu'];
		$Menu->AddLink('Add-ons', 'Awards', 'plugin/awards', 'Garden.AdminUser.Only');
	}

	/**
	 * Renders the Award Classes List page.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardClassesList($Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARDCLASSES_LIST_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// TODO Implement Awards Classes List page

		// TODO Implement automatic generation of CSS file containing the styles for each Award Class

		$Sender->Render($this->GetView('awards_awardclasseslist_view.php'));
	}

	/**
	 * Renders the Awards List page.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardsList($Sender) {
		$this->AwardsManager()->AwardsList($this, $Sender);
	}

	/**
	 * Renders the page to Add/Edit an Award.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardAddEdit($Sender) {
		$this->AwardsManager()->AwardAddEdit($this, $Sender);
	}

	/**
	 * Renders the Awards Rules List page.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_RulesList($Sender) {
		$this->RulesManager()->RulesList($this, $Sender);
	}

	/**
	 * Renders the User Awards List page.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_UserAwardsList($Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_USERAWARDS_LIST_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// TODO Implement User Awards List page

		$Sender->Render($this->GetView('awards_userawardslist_view.php'));
	}

	/**
	 * Plugin setup
	 *
	 * This method is fired only once, immediately after the plugin has been enabled in the /plugins/ screen,
	 * and is a great place to perform one-time setup tasks, such as database structure changes,
	 * addition/modification ofconfig file settings, filesystem changes, etc.
	 */
	public function Setup() {
		// TODO Set up the plugin's default values

		// Create Database Objects needed by the Plugin
		require('install/awards.schema.php');
		AwardsSchema::Install();
	}

	/**
	 * Plugin cleanup
	 *
	 * This method is fired only once, immediately before the plugin is disabled, and is a great place to
	 * perform cleanup tasks such as deletion of unsued files and folders.
	 */
	public function OnDisable() {
		RemoveFromConfig('Plugin.Awards.TrimSize');
		RemoveFromConfig('Plugin.Awards.RenderCondition');
	}

	/**
	 * Plugin cleanup
	 */
	public function CleanUp() {
		// TODO Remove Plugin's configuration parameters

		require('install/awards.schema.php');
		AwardsSchema::Uninstall();
	}
}

/*
		if ($Sender->Form->AuthenticatedPostBack() === FALSE) {

			$Model->Save($Values);
		}
		else {
			var_dump($Fields = $Sender->Form->FormValues());

			// TODO Move this code to its own place

			// Create a list of configuration arrays for each of the enabled rules
			$RulesParams = array();
			foreach($Fields['Rule'] as $RuleClass) {
				$RulesParams[$RuleClass] = array();
			}

			// Parse each of the fields returned by the form, and assign each one to
			// the Rule it belongs to. Since fields cannot be grouped, the Rule Name
			// must be added to the field name, as a prefix, and separated from the
			// field name by an underscore
			foreach($Fields as $Name => $Value) {
				if(strpos($Name, '_') > 0) {
					$FieldParts = explode('_', $Name);
					$RuleClass = array_shift($FieldParts);
					$ParamName = array_shift($FieldParts);

					// A Field value will be considered only if the Rule it belongs to has
					// been enabled
					if(isset($RulesParams[$RuleClass])) {
						$RulesParams[$RuleClass][$ParamName] = $Value;
					}
				}
			}
			var_dump($RulesParams);
 die();
		}*/
