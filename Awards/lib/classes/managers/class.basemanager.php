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
}
