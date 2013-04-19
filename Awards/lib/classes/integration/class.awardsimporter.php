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
	 * Verifies that an archive's checksum matches the one calculated from its
	 * content.
	 *
	 * @param string OriginalChecksum The checksum stored within the archive.
	 * @return bool True, if the checksums match, False otherwise.
	 */
	private function VerifyArchiveChecksum($OriginalChecksum) {
		$this->Log()->info($this->StoreMessage(sprintf(T('Verifying archive checksum...'))));
		$FileHashes = array();

		$ImageFiles = $this->GetFiles($this->_TempFolder);
		//var_dump($ImageFiles); die();
		foreach($ImageFiles as $ImageFile) {
			$FileHashes[] = md5_file($ImageFile);
		}
		//var_dump($ImageFiles, $FileHashes);
		sort($FileHashes);
		$CalculatedChecksum = md5(implode(',', $FileHashes));
		//var_dump($CalculatedChecksum, $OriginalChecksum);
		$this->Log()->info($this->StoreMessage(sprintf(T('Original Checksum: %s. Calculated checksum: %s.'),
																									 $OriginalChecksum,
																									 $CalculatedChecksum)));

		if($CalculatedChecksum != $OriginalChecksum) {
			$this->Log()->info($this->StoreMessage(T('Checksum verification failed.')));
			return false;
		}
		else {
			$this->Log()->info($this->StoreMessage(T('Checksum verification passed.')));
			return true;
		}
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
			$this->Log()->error($this->StoreMessage(T('File does not exist')));
			return AWARDS_ERR_FILE_NOT_FOUND;
		}

		// Create a temporary folder for the data to import
		$this->_TempFolder = '/tmp/' . (string)uniqid('awards_import_', true);
		$this->Log()->debug($this->StoreMessage(sprintf(T('Creating temporary folder "%s"...'),
																										$this->_TempFolder)));
		//var_dump($this->_TempFolder);

		if(!mkdir($this->_TempFolder)) {
			$LogMsg = T('Could not create temporary folder.');
			$this->Log()->error($this->StoreMessage($LogMsg));
			return AWARDS_ERR_COULD_NOT_CREATE_FOLDER;
		}

		// Extract the data and the images
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
			$LogMsg = sprintf(T('Could not extract data to temporary folder "%s".'),
												$this->_TempFolder);
			return AWARDS_ERR_COULD_NOT_EXTRACT_EXPORTDATA;
		}

		$ArchiveChecksum = $Zip->getArchiveComment();
		$Zip->close();

		$this->Log()->info($this->StoreMessage(T('Data extraction completed.')));

		// Verify archive integrity
		if(!$this->VerifyArchiveChecksum($ArchiveChecksum)) {
			return AWARDS_ERR_CHECKSUM_ERROR;
		};

		return AWARDS_OK;
	}

	/**
	 * Loads a file containing exported Awards data in JSON format.
	 *
	 * @return string|bool A JSON representation of the data, or False on failure.
	 */
	private function LoadImportData() {
		$DataFileName = $this->_TempFolder . '/' . self::AWARD_DATA_FILE_NAME;
		$ImportData = file_get_contents($DataFileName);

		if($ImportData === false) {
			$this->Log()->error($this->StoreMessage(sprintf(T('Could not load data from file "%s"...'),
																											$DataFileName)));
		}
		return $ImportData;
	}

	/**
	 * Deletes temporary files and folders created during the import.
	 */
	private function Cleanup() {
		$this->DelTree($this->_TempFolder);
	}

	private function ImportImages($SubFolder) {
		$ImagesFolder = $this->_TempFolder . '/images/' . $SubFolder;
		// TODO Implement images import

		return AWARDS_OK;
	}

	private function ImportAwardClasses(stdClass $ImportData, array $ImportSettings) {
		$this->Log()->info($this->StoreMessage(T('Importing Award Classes...')));

		// Retrieve the action to take when an item is Duplicated
		$DuplicateItemAction = GetValue('DuplicateItemAction', $ImportSettings);

		// Transform Award Classes object into an associative array. This is needed
		// because each Class' data must be passed as an associative array to the model
		$AwardClasses = json_decode(json_encode($ImportData->AwardClasses), true);

		$Result = AWARDS_OK;
		foreach($AwardClasses as $AwardClass) {
			$AwardClassName = GetValue('AwardClassName', $AwardClass);
			$ExistingAwardClass = $this->AwardClassesModel()->GetAwardClassDataByName($AwardClassName);

			if($ExistingAwardClass !== false) {
				switch($DuplicateItemAction) {
					case self::DUPLICATE_ACTION_OVERWRITE:
						$AwardClass['AwardClassID'] = GetValue('AwardClassID', $ExistingAwardClass);
						break;
					case self::DUPLICATE_ACTION_RENAME:
						$AwardClass['AwardClassName'] .= uniqid('-', true);
						break;
					case self::DUPLICATE_ACTION_SKIP:
					default:
						continue 2;
				}

				unset($AwardClass['TotalAwardsUsingClass']);
				var_dump($AwardClass);

				// TODO Check why Award Classes are not saved correctly
				if($this->AwardClassesModel()->Save($AwardClass) === false) {
					$this->Log()->info($this->StoreMessage(sprintf(T('Could not import Award Class "%s". ' .
																													 'Class details (JSON): %s.'),
																												 $AwardClass['AwardClassName'],
																												 json_encode($AwardClass))));
					return AWARDS_ERR_COULD_NOT_IMPORT_AWARD_CLASS;
				}
			}
		}

		if($Result === AWARDS_OK) {
			// TODO Extract the images folder from the Award Classes data
			$Result = $this->ImportImages('awardclasses');
		}

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

		// Load the data to import from the exported JSON
		$ImportData = $this->LoadImportData();
		if($ImportData === false) {
			$Result = AWARDS_ERR_COULD_NOT_LOAD_DATA_FILE;
		}

		if($Result === AWARDS_OK) {
			// Decode the JSON string into an object
			$ImportData = json_decode($ImportData);
			//var_dump($ImportData); die();

			Gdn::Database()->BeginTransaction();
			try {
				// TODO Import Award Classes
				$Result = $this->ImportAwardClasses($ImportData, $ImportSettings);

				// TODO Import Awards
				if($Result === AWARDS_OK) {
					//$Result = $this->ImportAwards($ImportSettings);
				}
				// TODO Import Award Images

				//$Result = AWARDS_ERR_DUMMY_ERROR;

				// Use a transaction to either save ALL data (Award and Rules)
				// successfully, or none of it. This will prevent partial saves and
				// reduce inconsistencies
				if($Result === AWARDS_OK) {
					Gdn::Database()->CommitTransaction();
				}
				else {
					Gdn::Database()->RollbackTransaction();
				}
			}
			catch(Exception $e) {
				Gdn::Database()->RollbackTransaction();
				$ErrorMsg = sprintf(T('Exception occurred while importing Awards data. ' .
																								'Error: %s. Trace: %s'),
																							$e->getMessage(),
																							$e->getTraceAsString());
				$this->Log()->error($this->StoreMessage($ErrorMsg));

				$Result = AWARDS_ERR_EXCEPTION_OCCURRED;
			}
		}

		$this->Log()->info($this->StoreMessage(T('Cleaning up...')));
		$this->Cleanup();

		if($Result !== AWARDS_OK) {
			$this->Log()->info($this->StoreMessage(T('Operation aborted.')));
		}

		return $Result;
	}

}
