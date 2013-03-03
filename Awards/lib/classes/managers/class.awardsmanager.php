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
	public function AwardsList(AwardsPlugin $Caller, $Sender) {
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
	private function GetRulesSettings(Gdn_DataSet $AwardDataSet) {
		$RulesSettings = array();
		foreach($AwardDataSet->Result(DATASET_TYPE_ARRAY) as $Row) {
			$RulesSettings[$Row['RuleClass']] = json_decode($Row['RuleConfiguration']);
		}

		return $RulesSettings;
	}

	/**
	 * Renders the page to Add/Edit an Award.
	 *
	 * @param AwardsPlugin Caller The Plugin which called the method.
	 * @param object Sender Sending controller instance.
	 */
	public function AwardAddEdit(AwardsPlugin $Caller, $Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARDS_ADDEDIT_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// Retrieve the Award ID passed as an argument (if any)
		$AwardID = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_AWARDID, null);

		$Sender->Form->SetModel($this->AwardsModel);

		if(isset($AwardID)) {
			$AwardDataSet = $this->AwardsModel->GetAwardData($AwardID);
			//var_dump($AwardDataSet);
			$Sender->Form->SetData($AwardDataSet->FirstRow());

			$Sender->SetData('AwardDataSet', $AwardDataSet);
			$Sender->SetData('RulesSettings', $this->GetRulesSettings($AwardDataSet));
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
						// Save Awards settings
						$Saved = $Sender->Form->Save();

						if($Saved) {
							// TODO Save configuration for each enabled Award Rule
							$Saved = $Caller->RulesManager()->SaveRulesSettings($Sender->Form);
						}

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

		// Retrieve the View that will be used to configure the Award
		$Sender->Render($Caller->GetView('awards_award_addedit_view.php'));
	}
}
