<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

class AwardsManager extends BaseManager {
	/**
	 * Renders the Awards List page.
	 *
	 * @param Gdn_Plugin Caller The Plugin which called the method.
	 * @param object Sender Sending controller instance.
	 */
	public function AwardsList(Gdn_Plugin $Caller, $Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARDS_LIST_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		$AwardsModel = new AwardsModel();
		// TODO Handle Limit and Offset
		$AwardsDataSet = $AwardsModel->Get();
		// TODO Add Pager

		$Sender->SetData('AwardsDataSet', $AwardsDataSet);

		$Sender->Render($Caller->GetView('awards_awardslist_view.php'));
	}

	/**
	 * Renders the page to Add/Edit an Award.
	 *
	 * @param Gdn_Plugin Caller The Plugin which called the method.
	 * @param object Sender Sending controller instance.
	 */
	public function AwardAddEdit(Gdn_Plugin $Caller, $Sender) {
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
				try {
					// Retrieve the URL of the Picture associated with the Award
					$ImageFile = PictureManager::GetPictureURL($Sender->Form,
																										 AWARDS_PLUGIN_AWARD_PICS_PATH,
																										 'Picture',
																										 $Sender->Form->GetFormValue('AwardImageFile'));
					// Add the Picture URL to the Form
					$Sender->Form->SetFormValue('AwardImageFile', $ImageFile);
				}
				catch(Exception $e) {
					$Form->AddError($e->getMessage());
					// If no image was uploaded, or if uploaded image could not be processed,
					// return the Default Picture URL
				}

				// Save Awards settings
				$Saved = $Sender->Form->Save();

				if ($Saved) {
					$Sender->InformMessage(T('Your changes have been saved.'));
					$Caller->FireEvent('ConfigChanged');

					// Once changes have been saved, redurect to the main page
					//Redirect(AWARDS_PLUGIN_AWARDS_LIST_URL);
					$this->AwardsList($Caller, $Sender);
				}
			}
		}

		// Retrieve the View that will be used to configure the Award
		$Sender->Render($Caller->GetView('awards_award_addedit_view.php'));
	}
}
