<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Extends base Gdn_Model by adding a Logger to it. This way, plugins that want
 * to use logging capabilities won't have to instantiate the Logger every time.
 */
class ModelEx extends Gdn_Model {
	// Logger that will be used by derived Models
	protected $Log;

	public function __construct($TableName) {
		parent::__construct($TableName);

		$this->Log = LoggerPlugin::GetLogger();
	}
}
