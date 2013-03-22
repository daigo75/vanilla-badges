<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

class AwardClassesManager extends BaseManager {
	/**
	 * Returns an instance of AwardClassesModel.
	 *
	 * @return AwardClassesModel An instance of AwardClassesModel.
	 * @see BaseManager::GetInstance()
	 */
	private function AwardClassesModel() {
		return $this->GetInstance('AwardClassesModel');
	}
	/**
	 * Class constructor.
	 *
	 * @return AwardClassesManager
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Prepares some Award Class Data to be used for cloning an Award Class. This
	 * method removes or alters all data that identifies an Award, so that the
	 * User will be forced to enter different details for the clone.
	 *
	 * @param stdClass Award Class Data An object containing Award Class data.
	 * @return stdClass The processed Award Class Data object.
	 */
	private function PrepareAwardClassDataForCloning(stdClass $AwardClassData) {
		//var_dump($AwardClassData);die();
		// Save references to source Award
		$AwardClassData->SourceAwardClassID = $AwardClassData->AwardClassID;
		$AwardClassData->SourceAwardClassName = $AwardClassData->AwardClassName;
		$AwardClassData->SourceAwardClassDescription = $AwardClassData->AwardClassDescription;

		// Unset and alter AwardClass key data, as clone will have to use its own
		unset($AwardClassData->AwardClassID);
		unset($AwardClassData->DateInserted);
		unset($AwardClassData->DateUpdated);

		$AwardClassData->AwardClassName = T('CLONE-') . $AwardClassData->AwardClassName;
		$AwardClassData->AwardClassDescription = T('CLONE-') . $AwardClassData->AwardClassDescription ;
		return $AwardClassData;
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
		$AwardClassesDataSet = $this->AwardClassesModel()->Get();
		// TODO Add Pager

		$Sender->SetData('AwardClassesDataSet', $AwardClassesDataSet);

		$Sender->Render($Caller->GetView('awards_awardclasseslist_view.php'));
	}

	/**
	 * Loads the Syntax Highlighter that will help Users in creating the CSS for
	 * the Award Class.
	 *
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
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

		$Sender->Form->SetModel($this->AwardClassesModel());

		if(!empty($AwardClassID)) {
			$AwardClassData = $this->AwardClassesModel()->GetAwardClassData($AwardClassID)->FirstRow();
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
					// Trim Award Class name. Such name will become a CSS class, whose name
					// cannot contain non-printable characters
					$Sender->Form->SetFormValue('AwardClassName', trim($Sender->Form->GetValue('AwardClassName')));

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
	 * Renders the page to Clone an Award Class.
	 *
	 * @param AwardsPlugin Caller The Plugin which called the method.
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	public function AwardClassClone(AwardsPlugin $Caller, $Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_AWARDCLASS_CLONE_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// Retrieve the Award ID passed as an argument (if any)
		$AwardClassID = $Sender->Request->Get(AWARDS_PLUGIN_ARG_AWARDCLASSID, null);
		// Can't continue without an Award Class ID
		if(empty($AwardClassID)) {
			Redirect(AWARDS_PLUGIN_AWARDCLASSES_LIST_URL);
		}

		// Load Award Class Data
		$AwardClassData = $this->AwardClassesModel()->GetAwardClassData($AwardClassID)->FirstRow();
		if(empty($AwardClassData)) {
			$this->Log()->error(sprintf(T('Requested cloning of invalid Award Class ID: %d. Request by User %s (ID: %d).'),
																	$AwardClassID,
																	Gdn::Session()->User->Name,
																	Gdn::Session()->UserID));
			Redirect(AWARDS_PLUGIN_AWARDCLASSES_LIST_URL);
		}

		$AwardClassData = $this->PrepareAwardClassDataForCloning($AwardClassData);
		// Set a flag that will inform the User that he is cloning an Award
		$Sender->SetData('Cloning', 1);

		$Sender->Form->SetData($AwardClassData);
		$Sender->Request->SetValueOn(Gdn_Request::INPUT_GET, AWARDS_PLUGIN_ARG_AWARDCLASSID, null);
		$this->AwardClassAddEdit($Caller, $Sender);
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

		$Sender->Form->SetModel($this->AwardClassesModel());

		// If seeing the form for the first time...
		if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
			// Retrieve the Award Class ID passed as an argument (if any)
			$AwardClassID = $Sender->Request->GetValue(AWARDS_PLUGIN_ARG_AWARDCLASSID, null);

			// Load the data of the Award Class to be edited, if an Award Class ID
			$AwardClassData = $this->AwardClassesModel()->GetAwardClassData($AwardClassID)->FirstRow(DATASET_TYPE_ARRAY);

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
				// Delete Award Class
				$this->AwardClassesModel()->Delete($Sender->Form->GetValue('AwardClassID'));
				$this->Log()->info(sprintf(T('User %s (ID: %d) deleted Award "%s" (ID: %d).'),
																		Gdn::Session()->User->Name,
																		Gdn::Session()->User->UserID,
																		GetValue('AwardClassName', $Data),
																		GetValue('AwardClassID', $Data)
																		));

				$Sender->InformMessage(T('Award Class deleted.'));
			}
			// Render AwardClasses List page
			Redirect(AWARDS_PLUGIN_AWARDCLASSES_LIST_URL);
		}
	}

	// TODO Document method
	public function GenerateAwardClassesCSS(Gdn_Pluggable $Sender) {
		// TODO Implement automatic generation of CSS file containing the styles for each Award Class
		$AwardClassesDataSet = $this->AwardClassesModel()->Get();

		// Prepare the notice to put at the beginning of the generated CSS file
		$CSSEntries = array(T("/**\n" .
													"* This file contains all the CSS Classes related derived from Award Classes and\n" .
													"* it's generated automatically by Awards Plugin. Don't change it manually,\n" .
													"* all changes will be overwritten by the Plugin when it runs.\n" .
													"*/\n"));

		// Add CSS for each Class
		foreach($AwardClassesDataSet as $AwardClassData) {
			// Use the Award Class Name as a CSS Class and concatenate the CSS code
			$CSSDeclaration = '.' . $AwardClassData->AwardClassName . " {\n";

			// Add the background image using the uploaded image
			if(!empty($AwardClassData->AwardClassImageFile)) {
				$CSSDeclaration .= "background: url(\"" . Url($AwardClassData->AwardClassImageFile) . "\") top left no-repeat;\n";
			}
			// Add the rest of the CSS
			$CSSDeclaration .= $AwardClassData->AwardClassCSS . "\n}\n";

			$CSSEntries[] = $CSSDeclaration;
		}

		$this->WriteToFile(AWARDS_PLUGIN_AWARDCLASSES_CSS_FILE, implode("\n", $CSSEntries));
	}
}
