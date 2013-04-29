<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Base class to be used by classes related to integration with external sources
 * (e.g. import and export of data).
 */
class BaseIntegration extends BaseClass {
	// @var int Indicates that duplicate items should be skipped
	const DUPLICATE_ACTION_SKIP = 0;
	// @var int Indicates that duplicate items should be overwritten
	const DUPLICATE_ACTION_OVERWRITE = 1;
	// @var int Indicates that duplicate items should be renamed
	const DUPLICATE_ACTION_RENAME = 2;

	// @var string The name of the file to store exported Awards data.
	const AWARD_DATA_FILE_NAME = 'awards_data.json';

	// @var array Stores an array of messages generated by the import/export methods.
	private $_Messages = array();

	/**
	 * Returns a list of the actions available to deal with duplicate items.
	 *
	 * @return array A list of the actions available to deal with duplicate items.
	 */
	public function DuplicateItemActions() {
		return array(
			self::DUPLICATE_ACTION_SKIP => T('Skip'),
			self::DUPLICATE_ACTION_OVERWRITE => T('Overwrite'),
			self::DUPLICATE_ACTION_RENAME => T('Rename'),
		);
	}

	/**
	 * Returns an array containing all the messages stored by the class.
	 *
	 * @return array An array of messages.
	 */
	public function GetMessages() {
		return $this->_Messages;
	}

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
	protected function StoreMessage($Message) {
		$this->_Messages[] = sprintf('%s - %s', date('Y-m-d H:i:s'), $Message);
		return $Message;
	}

	/**
	 * Cleans up a Data object by removing all internal fields, such as DateInserted,
	 * DateUpdated, InsertUser and UpdateUser. Such fields are meaningful only in
	 * current system, and are not needed to import or export data.
	 *
	 * @param stdClass Data The data object to clean up.
	 * @param array FieldsToRemove Additional Fields to remove from the object.
	 * @return stdClass The cleaned up object.
	 */
	protected function CleanupData(stdClass $Data, array $FieldsToRemove = array()) {
		// Remove standard System fields
		unset($Data->DateInserted);
		unset($Data->InsertUser);
		unset($Data->DateUpdated);
		unset($Data->UpdateUser);

		// Remove additional fields
		foreach($FieldsToRemove as $Field) {
			unset($Data->$Field);
		}

		return $Data;
	}

	/**
	 * Returns a list of all Files and, optionally, Directories found in a
	 * directory.
	 *
	 * @param string Directory The directory to scan for files.
	 * @param int Recursive Indicates if the directory should be scanned recursively.
	 * @param int FilesOnly Indicates if resulting list should only contain files.
	 * @result array A list of Files and, optionally, Directories.
	 */
	protected function GetFiles($Directory, $Recursive = true, $FilesOnly = true) {
		$Files = array_diff(scandir($Directory), array('.', '..'));
		$Result = array();

		foreach($Files as $File) {
			$FileName = $Directory . '/' . $File;
			if(is_dir($FileName)) {
				// If Recursive flag is set, find files in subdirectories
				if($Recursive) {
					$Result = array_merge($Result, $this->GetFiles($FileName));
				}

				// If only files are expected, move to next one (i.e. don't add
				// directories) to the Result list
				if($FilesOnly) {
					continue;
				}
			}
			$Result[] = $FileName;
    }

		return $Result;
	}

	/**
	 * Deletes a directory and all its content, recursively.
	 *
	 * @param string Directory The directory to be deleted.
	 * @return bool True, if the operation succeeded, False otherwise.
	 */
	protected function DelTree($Directory) {
		$this->Log()->info(sprintf(T('Deleting directory "%s" and all its content...'),
															 $Directory));
		$Files = array_diff(scandir($Directory), array('.', '..'));
    foreach($Files as $File) {
			$FileName = $Directory . '/' . $File;
			if(is_dir($FileName)) {
				$Result = $this->DelTree($FileName);
			}
			else {
				$this->Log()->trace(sprintf(T('Deleting file "%s"...'),
																		$FileName));
				$Result = unlink($FileName);
			}

			// Stop on first error
			if($Result === false) {
				break;
			}
    }

		if($Result === true)  {
	    $Result = rmdir($Directory);
		}

		// Log deletion failure
		if($Result === false) {
			$this->Log()->error(sprintf(T('Deletion of directory "%s" failed.'),
																	$Directory));
		}
		return $Result;
  }

	/**
	 * Modifies an input string by appending a suffix composed by the "renamed"
	 * keyword and a random string. It's useful to automatically rename items
	 * during import phase, when an item with the same name exists.
	 *
	 * @param string OriginalName The original string to be modified.
	 * @return string The modified string.
	 */
	protected function RandomRename($OriginalName) {
		return $OriginalName . '-' . T('renamed') . '-' . md5(uniqid('', true));
	}
}
