<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Handles the import of Awards, Award Classes and related images.
 */
class AwardsImporter extends BaseIntegration {
	// @var int Indicates that duplicate items should be skipped
	const DUPLICATE_ACTION_SKIP = 0;
	// @var int Indicates that duplicate items should be overwritten
	const DUPLICATE_ACTION_OVERWRITE = 1;
	// @var int Indicates that duplicate items should be renamed
	const DUPLICATE_ACTION_RENAME = 2;

	// @var string Temporary folder where compressed data is extracted before the import
	private $_TempFolder;

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
	 * Returns an instance of AwardClassesModel.
	 *
	 * @return AwardsModel An instance of AwardClassesModel.
	 * @see BaseManager::GetInstance()
	 */
	private function AwardClassesModel() {
		return $this->GetInstance('AwardClassesModel');
	}

	// TODO Document method
	public function DuplicateItemActions() {
		return array(
			self::DUPLICATE_ACTION_SKIP => T('Skip'),
			self::DUPLICATE_ACTION_OVERWRITE => T('Overwrite'),
			self::DUPLICATE_ACTION_RENAME => T('Rename'),
		);
	}

	/**
	 * Extracts the content of a zip file to a temporary folder.
	 *
	 * @param string FileName The name of the file to uncompress.
	 * @return int An integer value indicating the result of the operation.
	 */
	private function ExtractData($FileName) {
		$this->Log()->info($this->StoreMessage(sprintf(T('Extracting data from file "%s"...'),
																										 $FileName)));
		if(!file_exists($FileName)) {
			$this->Log()->error($this->StoreMessage(T('File does not exist') . ' ' . T('Operation aborted.')));
			return AWARDS_ERR_FILE_NOT_FOUND;
		}

		// Create a temporary folder for the data to import
		$this->_TempFolder = '/tmp/' . (string)uniqid('awards_import_', true);
		$this->Log()->debug($this->StoreMessage(sprintf(T('Creating temporary folder "%s"...'),
																										$this->_TempFolder)));
		var_dump($this->_TempFolder);

		if(!mkdir($this->_TempFolder)) {
			$LogMsg = T('Could not create temporary folder. Import aborted.');
			$this->Log()->error($this->StoreMessage($LogMsg));
			return AWARDS_ERR_COULD_NOT_CREATE_FOLDER;
		}

		// Import and compress the data and the images
		$Zip = new ZipArchive();
		// Open source Zip File
		$ZipResult = $Zip->open($FileName);
		if($ZipResult !== true) {
			$this->Log()->error($this->StoreMessage(sprintf(T('Error opening zip file "%s"...'),
																											$FileName)));
			return $ZipResult;
		}
		$this->Log()->info($this->StoreMessage(T('Extracting data...')));
		if($Zip->extractTo($this->_TempFolder) === false) {
			$LogMsg = sprintf(T('Could not extract data to temporary folder "%s". Import aborted.'),
												$this->_TempFolder);
			return AWARDS_ERR_COULD_NOT_EXTRACT_EXPORTDATA;
		}

		$this->Log()->info($this->StoreMessage(T('Data extraction completed.')));
		return AWARDS_OK;
	}

	private function Cleanup() {
		$this->DelTree($this->_TempFolder);
	}

	private function GenerateImportMetaData(Gdn_Form $Form) {
		$this->Log()->info($this->StoreMessage(T('Preparing Import MetaData...')));

		// Store Import metadata
		$ImportMetaData = new stdClass();
		$ImportMetaData->Version = self::EXPORT_V1;
		$ImportMetaData->Label = $Form->GetValue('ImportLabel');
		$ImportMetaData->Description = $Form->GetValue('ImportDescription');
		$ImportMetaData->RawTimeStamp = now();
		$ImportMetaData->TimeStamp = date('Y-m-d H:i:s', $ImportMetaData->RawTimeStamp);

		return $ImportMetaData;
	}

	private function GetAwardClassesData() {
		$this->Log()->info($this->StoreMessage(T('Importing Award Classes...')));

		$ImagesToImport = array();
		$AwardClasses = $this->AwardClassesModel()->Get()->Result();

		foreach($AwardClasses as $AwardClass) {
			$this->Log()->info($this->StoreMessage(sprintf(T('Processing Award Class "%s"...'),
																											 $AwardClass->AwardClassName)));
			$AwardClass = $this->CleanupData($AwardClass);

			// Skip Classes without an image
			if(empty($AwardClass->AwardClassImageFile)) {
				continue;
			}
			$ImagesToImport[] = PATH_ROOT . '/' . $AwardClass->AwardClassImageFile;
			// Remove path info from the image
			$AwardClass->AwardClassImageFile = basename($AwardClass->AwardClassImageFile);
		}
		$this->Log()->info($this->StoreMessage(T('OK')));

		$Result = new stdClass();
		$Result->ImagesToImport = &$ImagesToImport;
		$Result->Data = &$AwardClasses;
		return $Result;
	}

	private function GetAwardsData() {
		$this->Log()->info($this->StoreMessage(T('Importing Awards...')));
		// Import the Awards
		$ImagesToImport = array();
		$Awards = $this->AwardsModel()->Get()->Result();

		foreach($Awards as $Award) {
			$this->Log()->info($this->StoreMessage(sprintf(T('Processing Award "%s"...'),
																											 $Award->AwardName)));
			$Award = $this->CleanupData($Award);

			$ImagesToImport[] = PATH_ROOT . '/' . $Award->AwardImageFile;
			// Remove path info from the image
			$Award->AwardImageFile = basename($Award->AwardImageFile);
		}
		$ImportData->Awards = &$Awards;

		$Result = new stdClass();
		$Result->ImagesToImport = &$ImagesToImport;
		$Result->Data = &$Awards;
		return $Result;
	}

	// TODO Move method to its own class
	public function ImportData($ImportSettings) {
		$this->_Messages = array();
		$this->Log()->info($this->StoreMessage(T('Importing Awards...')));

		$Result = $this->ExtractData(GetValue('FileName', $ImportSettings));

		var_dump($Result); die();

		Gdn::Database()->BeginTransaction();
		try {
			// TODO Import Award Classes
			// TODO Import Awards
			// TODO Import Award Images

			$ImportResult = AWARDS_ERR_DUMMY_ERROR;

			// Use a transaction to either save ALL data (Award and Rules)
			// successfully, or none of it. This will prevent partial saves and
			// reduce inconsistencies
			if($ImportResult === AWARDS_OK) {
				Gdn::Database()->CommitTransaction();
			}
			else {
				Gdn::Database()->RollbackTransaction();
			}
		}
		catch(Exception $e) {
			Gdn::Database()->RollbackTransaction();
			$ErrorMsg = sprintf(T('Exception occurred while importing Awards data. ' .
																							'Error: %s.'),
																						$e->getMessage());
			$this->Log()->error($this->StoreMessage($ErrorMsg));
			$this->Log()->error($this->StoreMessage(T('Operation aborted')));

			return AWARDS_ERR_EXCEPTION_OCCURRED;
		}

		$this->Log()->info($this->StoreMessage(T('Cleaning up...')));
		$this->Cleanup();

		return AWARDS_OK;
	}

}
