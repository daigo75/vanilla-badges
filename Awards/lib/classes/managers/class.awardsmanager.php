<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

class AwardsManager extends BaseManager {
	/**
	 * Returns an instance of AwardsModel.
	 *
	 * @return AwardsModel An instance of AwardsModel.
	 * @see BaseManager::GetInstance()
	 */
	private function AwardsModel() {
		return $this->GetInstance('AwardsModel');
	}

	/**
	 * Returns an instance of UserAwardsModel.
	 *
	 * @return AwardsModel An instance of UserAwardsModel.
	 * @see BaseManager::GetInstance()
	 */
	private function UserAwardsModel() {
		return $this->GetInstance('UserAwardsModel');
	}

	/**
	 * Class constructor.
	 *
	 * @return AwardsManager
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Renders the Awards List page.
	 *
	 * @param AwardsPlugin Caller The Plugin who called the method.
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function AwardsList(AwardsPlugin $Caller, Gdn_Controller $Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARDS_LIST_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// TODO Handle Limit and Offset
		$AwardsDataSet = $this->AwardsModel()->GetWithTimesAwarded(array(), array('AwardName asc'));
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

	/**
	 * Prepares a hierarchy of Rule Groups and Sections, which will be used to
	 * render each Rule's settings section in the appropriate part of the page.
	 * Each Group will contain Sections, and each Section will contain the
	 * configuration UI of one or more Rules.
	 *
	 * @return array An associative array containing the hierarchy of Rules Groups
	 * and Sections.
	 */
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
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function AwardAddEdit(AwardsPlugin $Caller, $Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARD_ADDEDIT_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// Load jQuery UI
		$this->LoadJQueryUI($Sender);

		// Load auxiliary files
		$Sender->AddJsFile('award_edit.js', 'plugins/Awards/js');

		// Retrieve the Award ID passed as an argument (if any)
		$AwardID = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_AWARDID, null);

		// Set Award Data in the form
		$Sender->Form->SetModel($this->AwardsModel());

		// Load Award Classes
		$AwardClassesModel = new AwardClassesModel();
		$AwardClasses = $AwardClassesModel->Get();
		$Sender->SetData('AwardClasses', $AwardClasses);

		if(isset($AwardID)) {
			// Load Award Data
			$AwardData = $this->AwardsModel()->GetAwardData($AwardID)->FirstRow();
			$Sender->Form->SetData($AwardData);

			$Sender->SetData('RulesSettings', $this->GetRulesSettings($AwardData));
		}

		// If seeing the form for the first time...
		if($Sender->Form->AuthenticatedPostBack() === FALSE) {
			// Just show the form with the default values
		}
		else {
			$Data = $Sender->Form->FormValues();

			// If User Canceled, go back to the List
			if(GetValue('Cancel', $Data, false)) {
				Redirect(AWARDS_PLUGIN_AWARDS_LIST_URL);
			}

			// Validate PostBack
			// The field named "Save" is actually the Save button. If it exists, it means
			// that the User chose to save the changes.
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

	/**
	 * Renders the page to Delete an Award.
	 *
	 * @param AwardsPlugin Caller The Plugin which called the method.
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function AwardDelete(AwardsPlugin $Caller, $Sender) {
		// Prevent Users without proper permissions from accessing this page.
		$Sender->Permission('Plugins.Awards.Manage');

		$Sender->Form->SetModel($this->AwardsModel());

		// If seeing the form for the first time...
		if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
			// Retrieve the Award ID passed as an argument (if any)
			$AwardID = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_AWARDID, null);

			// Load the data of the Award to be deleted, if an Award ID is passed
			$AwardData = $this->AwardsModel()->GetAwardData($AwardID)->FirstRow(DATASET_TYPE_ARRAY);
			//var_dump($AwardID, $AwardData);
			$Sender->Form->SetData($AwardData);

			// Apply the config settings to the form.
			$Sender->Render($Caller->GetView('awards_award_delete_confirm_view.php'));
		}
		else {
			//var_dump($Sender->Form->FormValues());
			$Data = $Sender->Form->FormValues();

			// The field named "OK" is actually the OK button. If it exists, it means
			// that the User confirmed the deletion.
			if(Gdn::Session()->ValidateTransientKey($Data['TransientKey']) && $Sender->Form->ButtonExists('OK')) {
				// Delete Award
				$this->AwardsModel()->Delete($Sender->Form->GetValue('AwardID'));
				$this->Log()->info(sprintf(T('User %s (ID: %d) deleted Award "%s" (ID: %d).'),
																		Gdn::Session()->User->Name,
																		Gdn::Session()->User->UserID,
																		GetValue('AwardName', $Data),
																		GetValue('AwardID', $Data)
																		));

				$Sender->InformMessage(T('Award deleted.'));
			}
			// Render Awards List page
			Redirect(AWARDS_PLUGIN_AWARDS_LIST_URL);
		}
	}

	/**
	 * Loads and configures the Recent Award Recipients module, which will display
	 * a list of the last Users who earned an Award.
	 *
 	 * @param Controller Sender Sending controller instance.
 	 * @return RecentAwardRecipientsModule An instance of the module.
 	 */
	private function LoadRecentAwardRecipientsModule($Sender, $AwardID) {
		$RecentAwardRecipientsModule = new RecentAwardRecipientsModule($Sender);
		$RecentAwardRecipientsModule->LoadData($AwardID);
		return $RecentAwardRecipientsModule;
	}

	/**
	 * Renders the page displaying the details of an Award and the list of the
	 * Users who already earned it.
	 *
	 * @param AwardsPlugin Caller The Plugin which called the method.
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function AwardInfo(AwardsPlugin $Caller, $Sender) {
		$this->RemoveDashboardElements($Sender);
		// Add a class to help uniquely identifying this page
		$Sender->CssClass = 'AwardInfo';

		// Load Award Data
		$AwardID = GetValue(1, $Sender->RequestArgs);
		if(!empty($AwardID)) {
			//$AwardData = $this->AwardsModel()->GetAwardData($AwardID)->FirstRow();
			$AwardData = $this->AwardsModel()
												->GetWithTimesAwarded(array('VAAL.AwardID' => $AwardID),
																							array('VAAL.AwardName asc'))
												->FirstRow();
			$Sender->SetData('AwardData', $AwardData);
			$Sender->SetData('RecentAwardRecipientsModule', $this->LoadRecentAwardRecipientsModule($Sender, $AwardID));

			//$Sender->AddModule();
		}

		// Load details of Award as earned by the User
		if(!empty($AwardData) && Gdn::Session()->IsValid()) {
			$UserAwardData = $this->UserAwardsModel()->GetUserAwardData(Gdn::Session()->UserID, $AwardID);
			$Sender->SetData('UserAwardData', $UserAwardData);
		}

		// Retrieve the View that will be used to configure the Award
		$Sender->Render($Caller->GetView('awards_award_info_view.php'));
	}

	/**
	 * Enables or disables an Award.
	 *
	 * @param AwardsPlugin Caller The Plugin which called the method.
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function AwardEnable(AwardsPlugin $Caller, $Sender) {
		// Prevent Users without proper permissions from accessing this page.
		$Sender->Permission('Plugins.Awards.Manage');

		$AwardID = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_AWARDID, null);
		$EnableFlag = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_ENABLEFLAG, null);

		if(is_numeric($AwardID) && is_numeric($EnableFlag)) {
			if($this->AwardsModel()->EnableAward((int)$AwardID, (int)$EnableFlag)) {
				$Sender->InformMessage(T('Your changes have been saved.'));
			};
		}

		// Render Awards List page
		Redirect(AWARDS_PLUGIN_AWARDS_LIST_URL);
	}

	/**
	 * Process the Award Rules for the specified User ID.
	 *
	 * @param AwardsPlugin Caller The Plugin who called the method.
	 * @param Gdn_Controller Sender Sending controller instance.
	 * @param int UserID The ID of the User for which to process the Award Rules.
	 */
	public function ProcessAwards(AwardsPlugin $Caller, Gdn_Controller $Sender, $UserID) {
		if(!Gdn::Session()->IsValid()) {
			return;
		}

		// Retrieve the list of Awards still available to the User
		$AvailableAwardsDataSet = $this->AwardsModel()->GetAvailableAwards(Gdn::Session()->UserID);

		// Debug - Rules to process
		//var_dump($AvailableAwardsDataSet->Result());

		foreach($AvailableAwardsDataSet->Result() as $AwardData) {
			$this->Log()->debug(sprintf(T('Processing Award "%s"...'), $AwardData->AwardName));
			//var_dump($AwardData->AwardName);

			/* Retrieve the settings to be passed to the Rules to determine if the
			 * Award should be assigned
			 */
			$RulesSettings = $this->GetRulesSettings($AwardData);

			$AwardAssignmentCount = $Caller->RulesManager()->ProcessRules($UserID, $RulesSettings);
			$this->Log()->debug(sprintf(T('Assigning Award %d time(s).'), $AwardAssignmentCount));

			//var_dump($AwardData, $AwardAssignmentCount);

			// TODO Assign Award to User, if needed
			if($AwardAssignmentCount > 0) {
				$this->AssignAward($UserID, $AwardData, $AwardAssignmentCount);
			}
		}
	}

	protected function AssignAward($UserID, stdClass $AwardData, $AwardAssignmentCount) {
		$UserAwardFields = array(
			'UserID' => $UserID,
			'AwardID' => $AwardData->AwardID,
			'AwardedRankPoints' => $AwardData->RankPoints + $AwardData->AwardClassRankPoints,
			'TimesAwarded' => $AwardAssignmentCount,
			'Status' => AwardsModel::STATUS_ASSIGNED,
		);

		//var_dump("Assigning Award", $AwardAssignmentCount, $AwardData);

		$this->UserAwardsModel()->Save($UserAwardFields);
	}
}
