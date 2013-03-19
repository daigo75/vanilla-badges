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
}
