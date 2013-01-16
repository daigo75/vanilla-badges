<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

// Register Rule with the Rule Manager
AwardRulesManager::RegisterRule('PostCountRule',
																array('Label' => T('Post Count'),
																			'Description' => T('Checks User\'s Post count'),
																			)
																);


/**
 * Post Count Award Rule.
 */
class PostCountRule extends BaseAwardRule {
	/* @var array Contains the types of calculation available for the Rule
	 * - At: Rule returns true when the specified threshold is reached.
	 * - Every: Rule returns true whenever the specified amount of content type is reached.
	 *
	 * Note: variable cannot be initialized on declaration because it makes use of
	 * T() for translations.
	 */
	public static $CountTypes;

	/**
	 * Runs the processing of the Rule, which will return how many times the Award
	 * should be assigned to the User, based on the specified configuration.
	 *
	 * @see AwardBaseRule::Process().
	 */
	public function Process($UserID, $RuleConfig, array $EventInfo = null) {
		return self::NO_ASSIGMENTS;
	}

	public function __construct() {
		parent::__construct();
		self::$CountTypes = array(1 => T('At'),
															2 => T('Every'),);
	}

	// TODO Add View to configure the Rule
	// TODO Add Model (if needed) to perform Rule checks
}
