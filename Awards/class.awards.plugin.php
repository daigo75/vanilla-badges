<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

// File logger.defines.php must be included by manually specifying the whole
// path. It will then define some shortcuts for commonly used paths, such as
// AWARDS_PLUGIN_LIB_PATH, used just below.
require(PATH_PLUGINS . '/Awards/lib/awards.defines.php');
// AWARDS_PLUGIN_LIB_PATH is defined in logger.defines.php.
require(AWARDS_PLUGIN_LIB_PATH . '/awards.validation.php');

// Define the plugin:
$PluginInfo['Awards'] = array(
	'Description' => 'Awards plugin for Vanilla Forums',
	'Version' => '1.0',
	'RequiredApplications' => array('Vanilla' => '2.0'),
	'RequiredTheme' => FALSE,
	'RequiredPlugins' => array('Logger' => '12.10.28'),
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
	 * Returns an instance of RulesManager. The function follows the principle
	 * of lazy initialization, instantiating the class the first time it's
	 * requested. This method is static because the RulesManager is required
	 * by a global validation function.
	 *
	 * @return object An instance of RulesManager.
	 */
	public function RulesManager() {
		if(empty($this->_RulesManager)) {
			// Logger Rules Manager will be used to keep track of available
			// Rules
			$this->_RulesManager = new AwardRulesManager();
		}

		return $this->_RulesManager;
	}

	/**
	 * Retrieves the picture file uploaded with a form and returns the full URL
	 * to it. If a file has not been uploaded, the the method builds a URL uses
	 * a default picture file name to build the URL.
	 *
	 * @param Gdn_Form Form The Form through which the Picture was uploaded.
	 * @param string PictureField The name of the form field containing the
	 * picture.
	 * @param string DefaultPictureURLField The name of the form field containing
	 * the URL of the picture to be used as a default.
	 *
	 */
	private function GetPictureURL(Gdn_Form $Form, $PictureField = 'Picture', $DefaultPictureURLField = 'DefaultPictureURL') {
		// If no file was uploaded, return the value of the Default Picture field
		if(!array_key_exists($InputName, $_FILES)) {
			return $Form->GetFormValue($DefaultPictureURLField, null);
		}

		$UploadImage = new Gdn_UploadImage();
		try {
			// Validate the upload
			$TmpImage = $UploadImage->ValidateUpload('Picture');
			//$UploadImage->GenerateTargetName(PATH_LOCAL_UPLOADS, '', TRUE));

			$UploadedFileName = $UploadImage->GetUploadedFileName();

			// Save the uploaded image
			$ParsedValues = $UploadImage->SaveImageAs($TmpImage,
																							AWARDS_PLUGIN_AWARD_PICS_PATH . '/' . $UploadedFileName,
																							50,
																							50,
																							array('Crop' => true));

			// Build a picture URL from the uploaded file
			return Url(AWARDS_PLUGIN_AWARDS_PICS_URL . '/' . $UploadedFileName);
		}
		catch(Exception $e) {
			$Form->AddError($e->getMessage());
			// If no image was uploaded, or if uploaded image could not be processed,
			// return the URL contained in the DefaultPictureURL field, or null if
			// not even that is found
			return $Form->GetFormValue($DefaultPictureURLField, null);
		}
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
		$Sender->AddCssFile($this->GetResource('design/css/awards.css', FALSE, FALSE));
		$Sender->AddCssFile($this->GetResource('design/css/awardclasses.css', FALSE, FALSE));
		$Sender->AddJsFile($this->GetResource('js/awards.js', FALSE, FALSE));
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

		// TODO Implement Awards List page

		// TODO Implement automatic generation of CSS file containing the styles for each Award Class

		$Sender->Render($this->GetView('awards_awardclasseslist_view.php'));
	}

	/**
	 * Renders the Awards List page.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardsList($Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARDS_LIST_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		$AwardsModel = new AwardsModel();
		// TODO Handle Limit and Offset
		$AwardsDataSet = $AwardsModel->Get();
		// TODO Add Pager

		$Sender->SetData('AwardsDataSet', $AwardsDataSet);

		$Sender->Render($this->GetView('awards_awardslist_view.php'));
	}

	/**
	 * Renders the page to Add/Edit an Award.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_AwardAddEdit($Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARDS_ADDEDIT_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// Retrieve the Award ID passed as an argument (if any)
		$AwardID = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_AWARDID, null);

		$AwardsModel = new AwardsModel();
		$Sender->Form->SetModel($AwardsModel);

		if(isset($AwardID)) {
			$AwardDataSet = $AwardsModel->GetAwardData($AwardID);
			//var_dump($AwardDataSet);
			$Sender->Form->SetData($AwardDataSet->FirstRow());
			$Sender->SetData('AwardDataSet', $AwardDataSet);
		}

		// If seeing the form for the first time...
		if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
			// Just show the form with the default values

		}
		else {
			// The field named "Save" is actually the Save button. If it exists, it means
			// that the User chose to save the changes.
			$Data = $Sender->Form->FormValues();

			// If User Canceled, go back to the List
			if($Data['Cancel']) {
				Redirect(AWARDS_PLUGIN_AWARDS_LIST_URL);
			}

			// Validate PostBack
			if(Gdn::Session()->ValidateTransientKey($Data['TransientKey']) && $Data['Save']) {
				// Retrieve the URL of the Picture associated with the Award
				$ImageFile = $this->GetPictureURL($Sender->Form, 'Picture', 'ImageFile');
				//var_dump($ImageFile);

				// Add the Picture URL to the Form
				$Sender->Form->SetFormValue('ImageFile', $ImageFile);

				// Save Awards settings
				$Saved = $Sender->Form->Save();

				if ($Saved) {
					$Sender->InformMessage(T('Your changes have been saved.'));
					$this->FireEvent('ConfigChanged');

					// Once changes have been saved, redurect to the main page
					Redirect(AWARDS_PLUGIN_AWARDS_LIST_URL);
				}
			}
		}

		// Retrieve the View that will be used to configure the Award
		$Sender->Render($this->GetView('awards_award_addedit_view.php'));
	}

	/**
	 * Renders the Awards Rules List page.
	 *
	 * @param object Sender Sending controller instance.
	 */
	public function Controller_RulesList($Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_RULES_LIST_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// TODO Implement Awards Rules List page
		$RulesManager = $this->RulesManager();

		$Sender->Render($this->GetView('awards_ruleslist_view.php'));
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
