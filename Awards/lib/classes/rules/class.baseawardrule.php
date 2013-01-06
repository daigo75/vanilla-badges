<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Base Award Assignment Rule Class.
 */
class BaseAwardRule {
	const NO_ASSIGNMENTS = 0;

	/**
	 * Runs the processing of the Rule, which will return how many times the Award
	 * should be assigned to the User, based on the specified configuration.
	 *
	 * @param int UserID The ID of the User candidated to receive an Award.
	 * @param mixed RuleConfig The configuration to be applied to the Rule.
	 * @param array EventInfo Additional information passed with the event that
	 * triggered the processing of Awards.
	 */
	public function Process($UserID, $RuleConfig, array $EventInfo = null) {
		return self::NO_ASSIGMENTS;
	}
}
