<?php if (!defined('APPLICATION')) exit();
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
class BaseManager{
	private $Log;

	/**
	 * Class constructor.
	 *
	 * @return BaseController An Instance of Base Controller.
	 */
	public function __construct() {
		$this->Log = $this->Log = LoggerPlugin::GetLogger('Awards');
	}
}
