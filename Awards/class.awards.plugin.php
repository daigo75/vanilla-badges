<?php if(!defined('APPLICATION')) exit();
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
	'Name' => 'Awards Plugin',
	'Description' => 'Awards Plugin for Vanilla Forums',
	'Version' => '13.03.22 alpha',
	'RequiredApplications' => array('Vanilla' => '2.0'),
	'RequiredTheme' => FALSE,
	'RequiredPlugins' => array('Logger' => '12.10.28',
														 //'AeliaFoundationClasses' => '13.02.27',
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
	/* @var array Lists the applications in which the Award assignments will be
	 * processed. This will allow the processing to happen only in the frontend,
	 * without slowing down the Dashboard.
	 */
	private $_AllowedApplications = array(
		'dashboard',
		'vanilla',
		'conversations',
	);

	// @var string The Route Code to be used when registering the Activity Types for the plugin.
	const AWARD_ROUTECODE = 'award';
	// @var string Name of the Activity Type to use when a User earns an Award
	const ACTIVITY_AWARDEARNED = 'AwardEarned';
	// @var string Name of the Activity Type to use when an Award is revoked
	const ACTIVITY_AWARDREVOKED = 'AwardRevoked';

	/* @var array Keeps a list of the available Award Actvities. Used mainly to
	 * recognise, amongst the Activity entries, the ones related to the Awards.
	 */
	private $AwardActivities = array(
		self::ACTIVITY_AWARDEARNED,
		self::ACTIVITY_AWARDREVOKED,
	);

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
	 * @return RulesManager An instance of RulesManager.
	 * @see AwardsPlugin::GetInstance()
	 */
	public function RulesManager() {
		return $this->GetInstance('AwardRulesManager');
	}

	/**
	 * Returns an instance of AwardsManager.
	 *
	 * @return AwardsManager An instance of AwardsManager.
	 * @see AwardsPlugin::GetInstance()
	 */
	public function AwardsManager() {
		return $this->GetInstance('AwardsManager');

	}
	/**
	 * Returns an instance of AwardClassesManager.
	 *
	 * @return AwardClassesManager An instance of AwardClassesManager.
	 * @see AwardsPlugin::GetInstance()
	 */
	public function AwardClassesManager() {
		return $this->GetInstance('AwardClassesManager');
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
	 * Processes all the Awards available to current User, eventually assigning
	 * one or more to him.
	 *
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	 private function ProcessAwards(Gdn_Controller $Sender) {
		// Can't process the Awards if no User is logged in
		if(!Gdn::Session()->IsValid()) {
			return;
		}
		$this->AwardsManager()->ProcessAwards($this, $Sender, Gdn::Session()->UserID);
	}

	/**
	 * Base_Render_Before Event Handler.
	 *
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function Base_Render_Before(Gdn_Controller $Sender) {
		// Files for frontend
		if(InArrayI($Sender->Application, $this->_AllowedApplications)) {
			$Sender->AddCssFile('awards.css', 'plugins/Awards/design/css');
			$Sender->AddJsFile('awards.js', 'plugins/Awards/js');
		}
		// Common files
		$Sender->AddCssFile('awardclasses.css', 'plugins/Awards/design/css');
	}

	/**
	 * Base_AfterBody_Handler Event Handler.
	 *
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function Base_AfterBody_Handler(Gdn_Controller $Sender) {
		// Files for frontend
		if(InArrayI($Sender->Application, $this->_AllowedApplications)) {
			// Process (and assign) Awards
			$this->ProcessAwards($Sender);
		}
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
		$Sender->Title($this->GetPluginKey('Name'));
		$Sender->AddSideMenu('plugin/awards');

		// If your sub-pages use forms, this is a good place to get it ready
		$Sender->Form = new Gdn_Form();
		// Display inline errors
		$Sender->Form->ShowErrors();


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

		$Sender->SetData('PluginDescription', $this->GetPluginKey('Description'));

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
			if($Saved) {
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
		$Menu->AddLink('Add-ons', $this->GetPluginKey('Name'), 'plugin/awards', 'Garden.AdminUser.Only');
	}

	/**
	 * Renders the Award Classes List page.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardClassesList($Sender) {
		$this->AwardClassesManager()->AwardClassesList($this, $Sender);
	}

	/**
	 * Renders the page to Add/Edit an Award Class.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardClassAddEdit($Sender) {
		$this->AwardClassesManager()->AwardClassAddEdit($this, $Sender);
	}

	/**
	 * Renders the page to Clone an Award Class.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardClassClone($Sender) {
		$this->AwardClassesManager()->AwardClassClone($this, $Sender);
	}

	/**
	 * Renders the page that allows Users to delete an Award Class.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardClassDelete($Sender) {
		$this->AwardClassesManager()->AwardClassDelete($this, $Sender);
	}

	/**
	 * Handler of event AwardsPlugin::ConfigChanged().
	 *
	 * @param Gdn_Pluggable Sender The object which fired the event.
	 */
	public function AwardsPlugin_ConfigChanged_Handler(Gdn_Pluggable $Sender) {
		$this->AwardClassesManager()->GenerateAwardClassesCSS($Sender);
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
	 * Renders the page to Clone an Award.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardClone($Sender) {
		$this->AwardsManager()->AwardClone($this, $Sender);
	}

	/**
	 * Renders the page that allows Users to delete an Award.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardDelete($Sender) {
		$this->AwardsManager()->AwardDelete($this, $Sender);
	}

	/**
	 * Enables/disabled an Award.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardEnable($Sender) {
		$this->AwardsManager()->AwardEnable($this, $Sender);
	}

	/**
	 * Renders the Award Info page, containing the details of an Award.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardInfo($Sender) {
		// Add the module with the list of Awards earned by current User
		$Sender->AddModule($this->LoadUserAwardsModule($Sender));

		$this->AwardsManager()->AwardInfo($this, $Sender);
	}

	/**
	 * Renders the Awards Rules List page.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_RulesList($Sender) {
		$this->RulesManager()->RulesList($Sender);
	}

	/**
	 * Renders the User Awards List page (in the Dashboard).
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
	 * ProfileController_Render_Before event handler.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function ProfileController_Render_Before($Sender, $Args) {
		/* Load the module that will render the User Awards List widget and add it
		 * to the modules list
		 */
		$Sender->AddModule($this->LoadUserAwardsModule($Sender));
	}

	/**
	 * Loads and configures the Hot Threads module, which will generate the HTML
	 * for the Hot Threads widget in the Sidebar.
	 *
 	 * @param Controller Sender Sending controller instance.
 	 * @return HotThreadsListModule An instance of the module.
 	 */
	private function LoadUserAwardsModule($Sender) {
		// If a User ID is specified explicitly, take that one. If not, take currently logged in User
		$UserID = isset($Sender->User->UserID) ? $Sender->User->UserID : Gdn::Session()->UserID;

		$UserAwardsModule = new UserAwardsModule($Sender);
		$UserAwardsModule->LoadData($UserID);
		return $UserAwardsModule;
	}

	/**
	 * Adds the Activity Types used by the plugin. They ares used to notify Users
	 * of earned and revoked Awards.
	 */
	private function AddAwardsActivityTypes() {
		// "Award earned" Activity Type
		Gdn::SQL()->Replace('ActivityType',
												array('AllowComments' => '0',
															// RouteCode is just a keyword which will be transformed into a link
															// to the Award on the Activities page
															'RouteCode' => self::AWARD_ROUTECODE,
															// Send notifications when Awards are earned
															'Notify' => '1',
															// Make Awards public
															'Public' => '0',
															// Message showing "You earned the XYZ Award
															'ProfileHeadline' => '%3$s earned the %8$s Award.',
															// Message showing "User earned the XYZ Award
															'FullHeadline' => '%1$s earned the %8$s Award.'),
												array('Name' => self::ACTIVITY_AWARDEARNED), TRUE);
	}

	/**
	 * Deletes the Activity Types used by the plugin.
	 */
	private function RemoveAwardsActivityTypes() {
		Gdn::SQL()->Delete('ActivityType', array('Name' => 'AwardEarned'));
	}

	/**
	 * ActivityModel_AfterActivityQuery Event Handler.
	 *
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function ActivityModel_AfterActivityQuery_Handler($Sender) {
		$BaseURL = Url('/', true);

		// Add the data related to the Awards
		$Sender->SQL
			// For the Awards Notifications, field Route contains the ID of the Award
			->LeftJoin('Awards AWDS', '(t.RouteCode = \'' . self::AWARD_ROUTECODE . '\') AND (AWDS.AwardID = a.Route)')
			->LeftJoin('AwardClasses AWCS', '(AWCS.AwardClassID = AWDS.AwardClassID)')
			->Select('AWCS.AwardClassName')
			->Select('AWDS.AwardImageFile', 'COALESCE(CONCAT(\'' . $BaseURL . '\', %s), au.Photo)', 'ActivityPhoto')
			->Select('AWDS.AwardName', 'COALESCE(%s, t.RouteCode)', 'RouteCode')
			->Select('AWDS.AwardID', 'COALESCE(CONCAT(\'' . AWARDS_PLUGIN_AWARD_INFO_URL . '/\', %s), a.Route)', 'Route');
	}

	/**
	 * Intercept rendering of the Activity to alter the styles when it's time to
	 * display an Awards.
	 *
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function Base_BeforeActivity_Handler($Sender) {
		$Activity = &$Sender->EventArguments['Activity'];
		$CssClass = &$Sender->EventArguments['CssClass'];

		if(InArrayI($Activity->ActivityType, $this->AwardActivities)) {
			$CssClass .= ' AwardActivity ' . $Activity->AwardClassName;
		}
	}

	/**
	 * ProfileController_AfterPreferencesDefined Event Handler.
	 * Adds Awards notification options to User's Preferences screen.
	 *
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function ProfileController_AfterPreferencesDefined_Handler($Sender) {
		$Sender->Preferences['Notifications']['Email.' . self::ACTIVITY_AWARDEARNED] = T('Notify me of earned Awards.');
		$Sender->Preferences['Notifications']['Popup.' . self::ACTIVITY_AWARDEARNED] = T('Notify me of earned Awards.');
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
		SaveToConfig('Preferences.Email.' . self::ACTIVITY_AWARDEARNED, 1);
		SaveToConfig('Preferences.Popup.' . self::ACTIVITY_AWARDEARNED, 1);

		// Set up the Activity Types related to the Awards
		$this->AddAwardsActivityTypes();

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
	}

	/**
	 * Plugin cleanup
	 */
	public function CleanUp() {
		// TODO Remove Plugin's configuration parameters
		RemoveFromConfig('Preferences.Email.' . self::ACTIVITY_AWARDEARNED);
		RemoveFromConfig('Preferences.Popup.' . self::ACTIVITY_AWARDEARNED);

		$this->RemoveAwardsActivityTypes();

		require('install/awards.schema.php');
		AwardsSchema::Uninstall();
	}
}
