<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

class AwardsManager extends BaseManager {
	private $_AwardsModel;

	/**
	 * Class constructor.
	 *
	 * @return AwardsManager
	 */
	public function __construct() {
		parent::__construct();

		$this->AwardsModel = new AwardsModel();
	}
	/**
	 * Renders the Awards List page.
	 *
	 * @param AwardsPlugin Caller The Plugin who called the method.
	 * @param object Sender Sending controller instance.
	 */
	public function AwardsList(AwardsPlugin $Caller, Gdn_Controller $Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARDS_LIST_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// TODO Handle Limit and Offset
		$AwardsDataSet = $this->AwardsModel->Get();
		// TODO Add Pager

		$Sender->SetData('AwardsDataSet', $AwardsDataSet);

		$Sender->Render($Caller->GetView('awards_awardslist_view.php'));
	}

	/**
	 * Decodes the JSON containing the configuration for each Rule and adds its
	 * data to an array, in form of objects. Each object will contain the
	 * configuration for a Rule.
	 *
	 * @param Gdn_DataSet AwardDataSet The DataSet containing the configuration
	 * for an Award. Each row should contain a "RuleClass" entry, associated to
	 * a JSON string with the Rule Configuration.
	 * @return array An associative array of RuleClass => Object, where each
	 * object contains the configuration for the Rule.
	 */
	private function GetRulesSettings(stdClass $AwardData) {
		// Decode the JSON containing Rules Settings for processing
		$RulesSettings = json_decode($AwardData->RulesSettings);

		// If no settings are found, reflect it by returning an empty array
		if(empty($RulesSettings)) {
			return array();
		}

		$Result = array();
		foreach($RulesSettings as $RuleClass => $Settings) {
			$Result[$RuleClass] = $Settings;
		}

		//var_dump($AwardData->RulesSettings, $Result); die();

		return $Result;
	}

	// TODO Document method
	private function PrepareAwardRulesSections() {
		$Result = array();
		foreach(AwardRulesManager::$RuleGroups as $GroupID => $GroupLabel) {
			$GroupSection = new stdClass();
			$GroupSection->Label = $GroupLabel;
			$GroupSection->TypeSections = array();
			$GroupSection->CountRules = 0;

			foreach(AwardRulesManager::$RuleTypes as $TypeID =>$TypeLabel) {
				$TypeSection = new stdClass();
				$TypeSection->Label = $TypeLabel;
				$TypeSection->Rules = array();

				$GroupSection->TypeSections[$TypeID] = $TypeSection;
			}

			$Result[$GroupID] = $GroupSection;
		}

		return $Result;
	}

	/**
	 * Renders the page to Add/Edit an Award.
	 *
	 * @param AwardsPlugin Caller The Plugin which called the method.
	 * @param object Sender Sending controller instance.
	 */
	public function AwardAddEdit(AwardsPlugin $Caller, $Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARD_ADDEDIT_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// Load jQuery UI from Google CDN, for faster delivery
		$Sender->AddJsFile('http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/jquery-ui.min.js', '');
		// Load auxiliary files
		$Sender->AddJsFile('award_edit.js', 'plugins/Awards/js');

		// Retrieve the Award ID passed as an argument (if any)
		$AwardID = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_AWARDID, null);

		$Sender->Form->SetModel($this->AwardsModel);

		if(isset($AwardID)) {
			$AwardData = $this->AwardsModel->GetAwardData($AwardID)->FirstRow();
			//var_dump($AwardDataSet);
			$Sender->Form->SetData($AwardData);

			//$Sender->SetData('AwardDataSet', $AwardDataSet);
			$Sender->SetData('RulesSettings', $this->GetRulesSettings($AwardData));
		}

		// If seeing the form for the first time...
		if($Sender->Form->AuthenticatedPostBack() === FALSE) {
			// Just show the form with the default values
		}
		else {
			// The field named "Save" is actually the Save button. If it exists, it means
			// that the User chose to save the changes.
			$Data = $Sender->Form->FormValues();

			// If User Canceled, go back to the List
			if(GetValue('Cancel', $Data, false)) {
				Redirect(AWARDS_PLUGIN_AWARDS_LIST_URL);
			}

			// Validate PostBack
			if(Gdn::Session()->ValidateTransientKey($Data['TransientKey']) && $Data['Save']) {
				try {
					// Retrieve the URL of the Picture associated with the Award.
					$ImageFile = PictureManager::GetPictureURL(AWARDS_PLUGIN_AWARD_PICS_PATH,
																										 'Picture',
																										 $Sender->Form->GetFormValue('AwardImageFile'));
					// Add the Picture URL to the Form
					$Sender->Form->SetFormValue('AwardImageFile', $ImageFile);
				}
				catch(Exception $e) {
					$Sender->Form->AddError($e->getMessage());
				}

				// Validate settings for Award Rules
				$RulesSettingsOK = $Caller->RulesManager()->ValidateRulesSettings($Sender->Form);
				if($RulesSettingsOK) {
					Gdn::Database()->BeginTransaction();

					try{
						// Convert the Rules settings to JSON and add it to the data to be saved
						$JSONRulesSettings = $Caller->RulesManager()->RulesSettingsToJSON($Sender->Form);
						$Sender->Form->SetFormValue('RulesSettings', $JSONRulesSettings);

						// If there are no Rule Settings, the Award is forcibly disabled.
						// Without any Rule configuration it would never be assigned, anyway
						//var_dump($JSONRulesSettings);
						if(empty($JSONRulesSettings)) {
							$Sender->Form->SetFormValue('AwardIsEnabled', 0);
						}

						// Save Awards settings
						$Saved = $Sender->Form->Save();

						// Use a transaction to either save ALL data (Award and Rules)
						// successfully, or none of it. This will prevent partial saves and
						// reduce inconsistencies
						if($Saved) {
							Gdn::Database()->CommitTransaction();
						}
						else {
							Gdn::Database()->RollbackTransaction();
						}
					}
					catch(Exception $e) {
						Gdn::Database()->RollbackTransaction();
						$this->Log()->error($ErrorMsg = sprintf(T('Exception occurred while saving Award configuration. ' .
																										'Award Name: %s. Error: %s.'),
																									$Sender->Form->GetFormValue('AwardName'),
																									$e->getMessage()));
						throw $e;
					}
				}

				if($Saved) {
					$Sender->InformMessage(T('Your changes have been saved.'));
					$Caller->FireEvent('ConfigChanged');

					// Once changes have been saved, redurect to the main page
					//Redirect(AWARDS_PLUGIN_AWARDS_LIST_URL);
					$this->AwardsList($Caller, $Sender);
				}
			}
		}

		// Pass the list of installed rules to the View, so that it can ask each
		// one to render its configuration section
		$Sender->SetData('AwardRules', $Caller->RulesManager()->GetRules());

		// Builds a structure that will be used to group the Rules in sections
		$Sender->SetData('AwardRulesSections', $this->PrepareAwardRulesSections());

		// Retrieve the View that will be used to configure the Award
		$Sender->Render($Caller->GetView('awards_award_addedit_view.php'));
	}


	public function AwardDelete(AwardsPlugin $Caller, $Sender) {
		// Prevent Users without proper permissions from accessing this page.
		$Sender->Permission('Plugins.UserStats.Manage');

		$Sender->Form->SetModel($this->AwardsModel);

		// If seeing the form for the first time...
		if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
			// Retrieve the Award ID passed as an argument (if any)
			$AwardID = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_AWARDID, null);

			// Load the data of the Client to be edited, if a Client ID is passed
			$AwardData = $this->AwardsModel->GetAwardData($AwardID);
			//var_dump($AwardID, $AwardData);
			$Sender->Form->SetData($AwardData->FirstRow(DATASET_TYPE_ARRAY));

			// Apply the config settings to the form.
			$Sender->Render($Caller->GetView('awards_delete_confirm_view.php'));
		}
		else {
			//var_dump($Sender->Form->FormValues());
			$Data = $Sender->Form->FormValues();

			// The field named "OK" is actually the OK button. If it exists, it means
			// that the User confirmed the deletion.
			if(Gdn::Session()->ValidateTransientKey($Data['TransientKey']) && $Sender->Form->ButtonExists('OK')) {
				// Delete Client Id
				$this->AwardsModel->Delete($Sender->Form->GetValue('AwardID'));

				$Sender->InformMessage(T('Award deleted.'));
			}
			// Render Awards List page
			$this->AwardsList($Caller, $Sender);
		}
	}

	/**
	 * Process the Award Rules for the specified User ID.
	 *
	 * @param AwardsPlugin Caller The Plugin who called the method.
	 * @param object Sender Sending controller instance.
	 * @param int UserID The ID of the User for which to process the Award Rules.
	 */
	public function ProcessAwards(AwardsPlugin $Caller, Gdn_Controller $Sender, $UserID) {
		// TODO Implement Rule Processing
		if(!Gdn::Session()->IsValid()) {
			return;
		}

		$AvailableAwardsDataSet = $this->AwardsModel->GetAvailableAwards(Gdn::Session()->UserID);

		// Debug - Rules to process
		//var_dump($AvailableAwardsDataSet->Result());

		foreach($AvailableAwardsDataSet->Result() as $AwardData) {
			$this->Log()->debug(sprintf(T('Processing Award "%s"...'), $AwardData->AwardName));
			//var_dump($AwardData->AwardName);

			$RulesSettings = $this->GetRulesSettings($AwardData);

			$AwardAssignments = $Caller->RulesManager()->ProcessRules($UserID, $RulesSettings);
			$this->Log()->debug(sprintf(T('Assigning Award %d time(s).'), $AwardAssignments));

			// TODO Assign Award to User, if needed
		}
	}
}
