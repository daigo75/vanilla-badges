<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

// Register Rule with the Rule Manager
AwardRulesManager::RegisterRule('PostCountRule',
																array('Label' => T('Post Count'),
																			'Description' => T('Checks User\'s Post count'),
																			'Type' => AwardRulesManager::TYPE_CONTENT,
																			'Group' => AwardRulesManager::GROUP_GENERAL,
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

	/**
	 * Validates Rule's settings.
	 *
	 * @param array Settings The array of settings to validate.
	 * @return bool True, if all settings are valid, False otherwise.
	 */
	protected function _ValidateSettings(array $Settings) {
		$Result = array();

		// Check settings for Discussions threshold
		$DiscussionsSettings = GetValue('Discussions', $Settings);
		$DiscussionsThreshold = GetValue('Amount', $DiscussionsSettings);
		if(GetValue('Enabled', $DiscussionsSettings) || !empty($DiscussionsThreshold)) {
			if(!is_numeric($DiscussionsThreshold) || ($DiscussionsThreshold <= 0)) {
				$this->Validation->AddValidationResult(self::RenameRuleField('Discussions_Amount'),
																							 T('Discussions threshold must be a positive integer.'));
			}
		}

		// Check settings for Comments  threshold
		$CommentsSettings = GetValue('Comments', $Settings);
		$CommentsThreshold = GetValue('Amount', $CommentsSettings);
		if(GetValue('Enabled', $CommentsSettings) || !empty($CommentsThreshold)) {
			if(!is_numeric($CommentsThreshold) || ($CommentsThreshold <= 0)) {
				$this->Validation->AddValidationResult(self::RenameRuleField('Comments_Amount'),
																							 T('Comments threshold must be a positive integer.'));
			}
		}
		return (count($this->Validation->Results()) == 0);
	}

	protected function IsRuleEnabled(array $Settings) {
		return GetValue('Discussions', $Settings) || GetValue('Comments', $Settings);
	}

	/**
	 * Class constructor.
	 *
	 * @return PostCountRule.
	 */
	public function __construct() {
		parent::__construct();
		self::$CountTypes = array(1 => T('At'),
															2 => T('Every'),);
	}

	// TODO Add Model (if needed) to perform Rule checks
}
