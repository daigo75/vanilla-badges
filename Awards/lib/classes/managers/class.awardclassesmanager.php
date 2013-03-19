<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

class AwardClassesManager extends BaseManager {
	private $_AwardClassesModel;

	/**
	 * Class constructor.
	 *
	 * @return AwardClassesManager
	 */
	public function __construct() {
		parent::__construct();

		$this->AwardClassesModel = new AwardClassesModel();
	}
	/**
	 * Renders the AwardClasses List page.
	 *
	 * @param AwardsPlugin Caller The Plugin who called the method.
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function AwardClassesList(AwardsPlugin $Caller, Gdn_Controller $Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARDCLASSES_LIST_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// TODO Handle Limit and Offset
		$AwardClassesDataSet = $this->AwardClassesModel->Get();
		// TODO Add Pager

		$Sender->SetData('AwardClassesDataSet', $AwardClassesDataSet);

		$Sender->Render($Caller->GetView('awards_awardclasseslist_view.php'));
	}

	private function LoadSyntaxHighlighter(Gdn_Controller $Sender) {
		// Load the Syntax Highlighter for the Class CSS textarea
		$Sender->AddCssFile('codemirror.css', 'plugins/Awards/js/codemirror/lib');
		$Sender->AddJsFile('codemirror.js', 'plugins/Awards/js/codemirror/lib');
		$Sender->AddJsFile('css.js', 'plugins/Awards/js/codemirror/mode/css');
	}

	/**
	 * Renders the page to Add/Edit an Award Class.
	 *
	 * @param AwardsPlugin Caller The Plugin which called the method.
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function AwardClassAddEdit(AwardsPlugin $Caller, Gdn_Controller $Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARDCLASS_ADDEDIT_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// Load jQuery UI
		$this->LoadJQueryUI($Sender);

		// Load JavaScript Syntax Hihglighter. It will be used to help User editing Class' CSS
		$this->LoadSyntaxHighlighter($Sender);

		// Load auxiliary files
		$Sender->AddJsFile('awardclass_edit.js', 'plugins/Awards/js');

		// Retrieve the Award Class ID passed as an argument (if any)
		$AwardClassID = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_AWARDCLASSID, null);

		$Sender->Form->SetModel($this->AwardClassesModel);

		if(isset($AwardClassID)) {
			$AwardClassData = $this->AwardClassesModel->GetAwardClassData($AwardClassID)->FirstRow();
			//var_dump($AwardClassData);
			$Sender->Form->SetData($AwardClassData);
		}

		// If seeing the form for the first time...
		if($Sender->Form->AuthenticatedPostBack() === FALSE) {
			// Just show the form with the default values
		}
		else {
			$Data = $Sender->Form->FormValues();

			// If User Canceled, go back to the List
			if(GetValue('Cancel', $Data, false)) {
				Redirect(AWARDS_PLUGIN_AWARDCLASSES_LIST_URL);
			}

			// Validate PostBack
			// The field named "Save" is actually the Save button. If it exists, it means
			// that the User chose to save the changes.
			if(Gdn::Session()->ValidateTransientKey($Data['TransientKey']) && $Data['Save']) {
				try {
					// Retrieve the URL of the Picture associated with the Award.
					$ImageFile = PictureManager::GetPictureURL(AWARDS_PLUGIN_AWARDCLASSES_PICS_PATH,
																										 'Picture',
																										 $Sender->Form->GetFormValue('AwardClassImageFile'));
					// Add the Picture URL to the Form
					$Sender->Form->SetFormValue('AwardClassImageFile', $ImageFile);
				}
				catch(Exception $e) {
					$Sender->Form->AddError($e->getMessage());
				}

				Gdn::Database()->BeginTransaction();
				try{
					// TODO Implement automatic generation of CSS file containing the styles for each Award Class

					// Save AwardClasses settings
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
					$this->Log()->error($ErrorMsg = sprintf(T('Exception occurred while saving Award Class. ' .
																									'Award Class Name: %s. Error: %s.'),
																								$Sender->Form->GetFormValue('AwardClassName'),
																								$e->getMessage()));
					throw $e;
				}

				if($Saved) {
					$Sender->InformMessage(T('Your changes have been saved.'));
					$Caller->FireEvent('ConfigChanged');

					// Once changes have been saved, redurect to the main page
					//Redirect(AWARDS_PLUGIN_AWARDCLASSES_LIST_URL);
					$this->AwardClassesList($Caller, $Sender);
				}
			}
		}

		// Retrieve the View that will be used to configure the Award Class
		$Sender->Render($Caller->GetView('awards_awardclass_addedit_view.php'));
	}

	/**
	 * Renders the page to Delete an Award Class.
	 *
	 * @param AwardsPlugin Caller The Plugin which called the method.
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function AwardClassDelete(AwardsPlugin $Caller, Gdn_Controller $Sender) {
		// Prevent Users without proper permissions from accessing this page.
		$Sender->Permission('Plugins.Awards.Manage');

		$Sender->Form->SetModel($this->AwardClassesModel);

		// If seeing the form for the first time...
		if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
			// Retrieve the Award Class ID passed as an argument (if any)
			$AwardClassID = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_AWARDCLASSID, null);

			// Load the data of the Award Class to be edited, if an Award Class ID
			$AwardClassData = $this->AwardClassesModel->GetAwardClassData($AwardClassID)->FirstRow(DATASET_TYPE_ARRAY);

			// If Class is in use, prevent its deletion
			if(GetValue('TotalAwardsUsingClass', $AwardClassData) > 0) {
				$Sender->Form->AddError(sprintf(T('Award Class "%s" cannot be deleted because there are still Awards using it.'),
																				GetValue('AwardClassName', $AwardClassData)));
				$this->AwardClassesList($Caller, $Sender);
			}

			//var_dump($AwardClassID, $AwardClassData);
			$Sender->Form->SetData($AwardClassData);

			// Apply the config settings to the form.
			$Sender->Render($Caller->GetView('awards_awardclass_delete_confirm_view.php'));
		}
		else {
			//var_dump($Sender->Form->FormValues());
			$Data = $Sender->Form->FormValues();

			// The field named "OK" is actually the OK button. If it exists, it means
			// that the User confirmed the deletion.
			if(Gdn::Session()->ValidateTransientKey($Data['TransientKey']) && $Sender->Form->ButtonExists('OK')) {
				// Delete Client Id
				$this->AwardClassesModel->Delete($Sender->Form->GetValue('AwardClassID'));

				$Sender->InformMessage(T('Award Class deleted.'));
			}
			// Render AwardClasses List page
			Redirect(AWARDS_PLUGIN_AWARDCLASSES_LIST_URL);
		}
	}

	/**
	 * Enables or disables an Award.
	 *
	 * @param AwardsPlugin Caller The Plugin which called the method.
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function AwardEnable(AwardsPlugin $Caller, Gdn_Controller $Sender) {
		// Prevent Users without proper permissions from accessing this page.
		$Sender->Permission('Plugins.Awards.Manage');

		$AwardClassID = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_AWARDID, null);
		$EnableFlag = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_ENABLEFLAG, null);

		if(is_numeric($AwardClassID) && is_numeric($EnableFlag)) {
			if($this->AwardClassesModel->EnableAward((int)$AwardClassID, (int)$EnableFlag)) {
				$Sender->InformMessage(T('Your changes have been saved.'));
			};
		}

		// Render AwardClasses List page
		Redirect(AWARDS_PLUGIN_AWARDCLASSES_LIST_URL);
	}

	/**
	 * Process the Award Rules for the specified User ID.
	 *
	 * @param AwardsPlugin Caller The Plugin who called the method.
	 * @param Gdn_Controller Sender Sending controller instance.
	 * @param int UserID The ID of the User for which to process the Award Rules.
	 */
	public function ProcessAwardClasses(AwardsPlugin $Caller, Gdn_Controller $Sender, $UserID) {
		// TODO Implement Rule Processing
		if(!Gdn::Session()->IsValid()) {
			return;
		}

		$AvailableAwardClassesDataSet = $this->AwardClassesModel->GetAvailableAwardClasses(Gdn::Session()->UserID);

		// Debug - Rules to process
		//var_dump($AvailableAwardClassesDataSet->Result());

		foreach($AvailableAwardClassesDataSet->Result() as $AwardClassData) {
			$this->Log()->debug(sprintf(T('Processing Award "%s"...'), $AwardClassData->AwardName));
			//var_dump($AwardClassData->AwardName);

			$RulesSettings = $this->GetRulesSettings($AwardClassData);

			$AwardAssignments = $Caller->RulesManager()->ProcessRules($UserID, $RulesSettings);
			$this->Log()->debug(sprintf(T('Assigning Award %d time(s).'), $AwardAssignments));

			// TODO Assign Award to User, if needed
		}
	}
}