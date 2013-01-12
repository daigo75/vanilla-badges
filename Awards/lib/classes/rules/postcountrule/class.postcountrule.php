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
	/**
	 * Runs the processing of the Rule, which will return how many times the Award
	 * should be assigned to the User, based on the specified configuration.
	 *
	 * @see AwardBaseRule::Process().
	 */
	public function Process($UserID, $RuleConfig, array $EventInfo = null) {
		return self::NO_ASSIGMENTS;
	}

	// TODO Add View to configure the Rule
	// TODO Add Model (if needed) to perform Rule checks
}
