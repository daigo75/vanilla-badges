<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Base class to be used by classes related to integration with external sources
 * (e.g. import and export of data).
 */
class BaseIntegration extends BaseClass {
	private $_Messages = array();

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
	 * @return stdClass The cleaned up object.
	 */
	// TODO Move method to its own class
	protected function CleanupData(stdClass $Data) {
		unset($Data->DateInserted);
		unset($Data->InsertUser);
		unset($Data->DateUpdated);
		unset($Data->UpdateUser);
		return $Data;
	}
}
