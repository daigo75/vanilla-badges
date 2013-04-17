<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Handles the export of Awards, Award Classes and relatedi images.
 */
class AwardsExporter extends BaseManager {
	private $_Messages = array();
	private $_ZipFileName = array();

	public function GetMessages() {
		return $this->_Messages;
	}

	public function GetZipFileName() {
		return $this->_ZipFileName;
	}


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

	/* @var string The version of the export data. It will be used in the future
	 * to distinguish exports created by different versions of the plugin.
	 */
	const EXPORT_V1 = '1';

	/**
	 * Stores a log message in a list. Messages will be printed out to screen
	 * later on.
	 *
	 * @param string Message The message to store.
	 * @return string The message to store, unaltered. This allows to pass the
	 * result of this function to the Logger.
	 */
	// TODO Move method to its own class
	// TODO Improve integration with Logger plugin and use it to display the messages.
	private function StoreMessage($Message) {
		$this->_Messages[] = sprintf('%s - %s', date('Y-m-d H:i:s'), $Message);
		return $Message;
	}

	/**
	 * Cleans up a Data object by removing all internal fields, such as DateInserted,
	 * DateUpdated, InsertUser and UpdateUser. Such fields are meaningful only in
	 * current system, and are not needed to import or export data.
	 *
	 * @param stdClass Data The data object to clean up.
	 * @return stdClass The cleaned up object.
	 */
	// TODO Move method to its own class
	private function CleanupData(stdClass $Data) {
		unset($Data->DateInserted);
		unset($Data->InsertUser);
		unset($Data->DateUpdated);
		unset($Data->UpdateUser);
		return $Data;
	}

	private function CompressData(stdClass $ExportData, array $ImagesToExport) {
		// TODO Allow to configure another Export path
		$this->_ZipFileName = AWARDS_PLUGIN_EXPORT_PATH . '/vanilla_awards_' . (string)date('YmdHis', $ExportData->ExportInfo->RawTimeStamp) . '.zip';

		// Export and compress the data and the images
		$Zip = new ZipArchive();
		$this->Log()->info($this->StoreMessage(sprintf(T('Compressing data into file "%s"...'),
																										 $this->_ZipFileName)));
		// Create destination Zip File
		$ZipResult = $Zip->open($this->_ZipFileName, ZIPARCHIVE::OVERWRITE);
		if($ZipResult !== true) {
			$this->Log()->error($this->StoreMessage(sprintf(T('Error creating zip file "%s"...'),
																											$this->_ZipFileName)));
			return $ZipResult;
		}
		$this->Log()->info($this->StoreMessage(T('Storing Awards data...')));

		// Store the Awards data in JSON format
		$ExportDataFileName = 'awards_data.json';
		// Store Awards data, in JSON format
		if($Zip->addFromString($ExportDataFileName, json_encode($ExportData)) === false) {
			$this->Log()->error($this->StoreMessage(T('Error storing export data.')));
			return AWARDS_ERR_COULD_NOT_COMPRESS_EXPORTDATA;
		}

		$this->Log()->info($this->StoreMessage(T('Storing images...')));

		// Store images in Zip file
		foreach($ImagesToExport as $DirName => $ImageFiles) {
			$LocaDirName = 'images/' . $DirName;
			$Zip->addEmptyDir($LocaDirName);
			$this->Log()->info($this->StoreMessage(sprintf(T('Storing files in folder "%s"...'),
																										 $LocaDirName)));

			foreach($ImageFiles as $ImageFile) {
				$this->Log()->info($this->StoreMessage(sprintf(T('Storing image file "%s"...'),
																											 $ImageFile)));
				if(!$Zip->addFile($ImageFile, $LocaDirName . '/' . basename($ImageFile))) {
					$this->Log()->error($this->StoreMessage(sprintf(T('Error storing file "%s"...'),
																													$ImageFile)));
					return AWARDS_ERR_COULD_NOT_COMPRESS_IMAGE;
				};
			}
		}

		$Zip->close();
		$this->Log()->info($this->StoreMessage(T('Export completed successfully.')));
		return AWARDS_OK;
	}

	private function GenerateExportMetaData(Gdn_Form $Form) {
		$this->Log()->info($this->StoreMessage(T('Preparing Export MetaData...')));

		// Store Export metadata
		$ExportMetaData = new stdClass();
		$ExportMetaData->Version = self::EXPORT_V1;
		$ExportMetaData->Label = $Form->GetValue('ExportLabel');
		$ExportMetaData->Description = $Form->GetValue('ExportDescription');
		$ExportMetaData->RawTimeStamp = now();
		$ExportMetaData->TimeStamp = date('Y-m-d H:i:s', $ExportMetaData->RawTimeStamp);

		return $ExportMetaData;
	}

	private function GetAwardClassesData() {
		$this->Log()->info($this->StoreMessage(T('Exporting Award Classes...')));

		$ImagesToExport = array();
		$AwardClasses = $this->AwardClassesModel()->Get()->Result();

		foreach($AwardClasses as $AwardClass) {
			$this->Log()->info($this->StoreMessage(sprintf(T('Processing Award Class "%s"...'),
																											 $AwardClass->AwardClassName)));
			$AwardClass = $this->CleanupData($AwardClass);

			// Skip Classes without an image
			if(empty($AwardClass->AwardClassImageFile)) {
				continue;
			}
			$ImagesToExport[] = PATH_ROOT . '/' . $AwardClass->AwardClassImageFile;
			// Remove path info from the image
			$AwardClass->AwardClassImageFile = basename($AwardClass->AwardClassImageFile);
		}
		$this->Log()->info($this->StoreMessage(T('OK')));

		$Result = new stdClass();
		$Result->ImagesToExport = &$ImagesToExport;
		$Result->Data = &$AwardClasses;
		return $Result;
	}

	private function GetAwardsData() {
		$this->Log()->info($this->StoreMessage(T('Exporting Awards...')));
		// Export the Awards
		$ImagesToExport = array();
		$Awards = $this->AwardsModel()->Get()->Result();

		foreach($Awards as $Award) {
			$this->Log()->info($this->StoreMessage(sprintf(T('Processing Award "%s"...'),
																											 $Award->AwardName)));
			$Award = $this->CleanupData($Award);

			$ImagesToExport[] = PATH_ROOT . '/' . $Award->AwardImageFile;
			// Remove path info from the image
			$Award->AwardImageFile = basename($Award->AwardImageFile);
		}
		$ExportData->Awards = &$Awards;

		$Result = new stdClass();
		$Result->ImagesToExport = &$ImagesToExport;
		$Result->Data = &$Awards;
		return $Result;
	}

	// TODO Move method to its own class
	public function ExportData($Sender) {
		$this->_Messages = array();
		$this->Log()->info($this->StoreMessage(T('Exporting Awards...')));

		// Create a temporary folder for the data to export
		$TempFolder = '/tmp/' . (string)uniqid('awards_export_', true);
		if(!mkdir($TempFolder)) {
			$LogMsg = sprintf(T('Could not create temporary folder "%s". Export aborted.'),
												$TempFolder);
			$this->Log()->error($this->StoreMessage($LogMsg));
			return AWARDS_ERR_COULD_NOT_CREATE_FOLDER;
		}

		// Create the result object
		$ExportData = new stdClass();

		// Generate some metadata about the export
		$ExportData->ExportInfo = $this->GenerateExportMetaData($Sender->Form);

		// Initialise the list of image files to be exported
		$ImagesToExport = array();

		// If requested, export Award Classes
		if($Sender->Form->GetValue('ExportClasses') == 1) {
			$AwardClassesData = $this->GetAwardClassesData();

			$ImagesToExport['awardclasses'] = &$AwardClassesData->ImagesToExport;
			$ExportData->AwardClasses = &$AwardClassesData->Data;
		}

		// Export the Awards
		$AwardsData = $this->GetAwardsData();
		$ImagesToExport['awards'] = &$AwardsData->ImagesToExport;
		$ExportData->Awards = &$AwardsData->Data;

		return $this->CompressData($ExportData, $ImagesToExport);
	}

}
