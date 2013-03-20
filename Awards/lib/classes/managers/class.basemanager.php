<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Base Manager. Implements a set of common properties and methods.
 *
 * Note: the class is called "manager", rather than "controller", despite it
 * being a Controller. This is because Vanilla's Autoloader looks for Controllers
 * only in a specific place, which doesn't include Plugins directories. Thus, if
 * class name contains "controller", it won't be loaded automatically.
 */
class BaseManager extends Gdn_Plugin {
	// @var Logger The Logger used by the class.
	private $_Log;

	/**
	 * Returns the instance of the Logger used by the class.
	 *
	 * @param Logger An instance of the Logger.
	 */
	protected function Log() {
		if(empty($this->_Log)) {
			$this->_Log = LoggerPlugin::GetLogger();
		}

		return $this->_Log;
	}

	/**
	 * Loads full jQuery UI library, with its standard theme.
	 *
	 * @param Gdn_Controller Sender Sending controller instance.
	 */
	protected function LoadJQueryUI(Gdn_Controller $Sender) {
		// Load jQuery UI from Google CDN, for faster delivery
		$Sender->Head->AddString('<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" media="all" />');
		$Sender->AddJsFile('http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/jquery-ui.min.js', '');
	}

	/**
	 * Writes some content to a text file. This method tries to acquire an
	 * exclusive lock on the destination file before writing to it, and it returns
	 * an error if such lock cannot be acquired.
	 *
	 * @param string FileName The name of the destination file.
	 * @param string Content The content to write to the file.
	 * @return bool True, if the operation was successful, False otherwise.
	 */
	// TODO Move this method somewhere else. It's too generic to stay in the BaseManager class
	protected function WriteToFile($FileName, $Content) {
		//$FileName = realpath($FileName);
		if(!is_dir(dirname($FileName))) {
			$this->Log()->error(sprintf(T('Requested writing of content to file "%s", but path ' .
																		'is not valid. Content to write: "%s".'),
																	$FileName,
																	$Content));
			var_dump(is_dir(dirname($FileName)));die();
			return false;
		}

		$fp = fopen($FileName, 'w+');

		// Lock file exclusively
		if(flock($fp, LOCK_EX)) {
			fwrite($fp, $Content);
			// Release the lock
			flock($fp, LOCK_UN);
			$Result = true;
		}
		else {
	    $this->Log()->error(sprintf(T('Could not lock file "%s", writing aborted. ' .
																		'Content to write: "%s".'),
																	$FileName,
																	$Content));
			$Result = false;
		}

		fclose($fp);
		return $Result;
	}
}
