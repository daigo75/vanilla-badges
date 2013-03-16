<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

require(AWARDS_PLUGIN_EXTERNAL_PATH . '/simple_html_dom/simple_html_dom.php');

interface IAwardRule {
	// @see BaseAwardRule::Process();
	public function Process($UserID, $RuleConfig, array $EventInfo = null);
	public function GetConfigUI();
	public function ValidateSettings(Gdn_Form $Form, array $Settings);
	public function SaveSettings(array $Settings);

	public static function RenderRuleField($InputHTML);
}

/**
 * Base Award Assignment Rule Class.
 */
class BaseAwardRule extends Gdn_Controller {
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

	// @var Gdn_Validation Internal validator, used to validate Rule settings.
	protected $Validation;
	// @var AwardRuleModel Internal model, used to save Rule's settings.
	protected $AwardRulesModel;

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

	/**
	 * Retrieves and returns the name of the View used to render the configuration
	 * interface for the Rule.
	 *
	 * @return string The name of the View used to render the configuration
	 * interface for the Rule.
	 */
	public function GetConfigUI() {
		$Reflector = new ReflectionClass(get_class($this));
		return dirname($Reflector->getFileName()) . '/views/settings_view.php';
	}

	/**
	 * Replaces aninput name with a hierarchical name, to allow grouping inputs
	 * once they are submitted.
	 * The result will be a name attribute such as Rules[RuleClass][GroupName][FieldName].
	 *
	 * @param string FieldName The name of the field to rename. It can be a simple
	 * field name (e.g. "MyField") or a hierarchical name. In latter case, the
	 * hierarchy must be indicated by underscores (e.g. Group_Field, Group_Subgroup_Field).
	 * @return string The new field name, in format "Rules[RuleClass][GroupName][FieldName]".
	 */
	protected static function RenameRuleField($FieldName) {
		/* Split the field into its sub-parts. Rule field names should be declared
		 * as follows:
		 * - Simple fields - MyField, SomeField, etc.
		 * - Grouped fields - Group_Field1, Group_Field2, etc. Separator must be an
		 *   underscore.
		 */
		$FieldNameParts = explode('_', $FieldName);

		/* Reformat Field Name by adding two prefixes:
		 * - "Rules". This prefix will group all the Rules fields under a single
		 *   array.
		 * - Class Name. This prefix will group all the fields belonging to this
		 *   Rule under their own array.
		 *
		 * The result will be a field named as follows:
		 * Rules[RuleName][GroupName][FieldName]
		 *
		 * Using this convention, PHP will automatically create nested array of
		 * fields and pass them into the $_POST variable. This will allow to group
		 * all the fields belonging to a specific Rule without having to search for
		 * them by using substring. Also, appending the Rule class to which the fields
		 * belong will automatically resolve any ambiguity, allowing all Rules to
		 * name their fields as they like.
		 */
		return 'Rules[' . get_called_class() . '][' . implode('][', $FieldNameParts) . ']';
	}

	/**
	 * Processes the HTML generated by one of the Gdn_Form::Input() methods and
	 * replaces the names of the inputs with a hierarchical name, to allow grouping
	 * them once they are submitted.
	 * The result will be a name attribute such as Rules[RuleClass][GroupName][FieldName].
	 *
	 * @param string InputHTML The HTML generated by a Gdn_Form::Input() function.
	 * @return string The original HTML, with the replaced names.
	 */
	public static function RenderRuleField($InputHTML) {
		$HTMLObj = str_get_html($InputHTML);
		foreach($HTMLObj->find('input[name],select[name]') as $Input) {
			// For each Checkbox, Garden framework adds a hidden field named
			// "Checkboxes[]", to keep track of all checkboxes, whether they have been
			// ticked or not. For such field, the name that we have to replace is stored
			// in its "value" attribute.
			if(strcasecmp($Input->name, 'Checkboxes[]') == 0){
				$InputAttribute = 'value';

			}
			else {
				$InputAttribute = 'name';
			}

			// The last element of InputNameParts contains the Field Name to process
			$InputNameParts	= explode('/', $Input->{$InputAttribute});
			$FieldName = array_pop($InputNameParts);

			// Process field name by transforming it into a hierarchical name
			$InputNameParts[] = self::RenameRuleField($FieldName);

			// Replace the name in the processed HTML Input element
			$Input->{$InputAttribute} = implode('/', $InputNameParts);
		}

		echo $HTMLObj;
	}

	/**
	 * Applies a validation rule to a field posted through a Form. This function
	 * calls Gdn_Validation::ApplyRule(), but first it renames the field to match
	 * the name generated by BaseAwardRule::RenderRuleField() function.
	 *
	 * @see Gdn_Validation::ApplyRule().
	 * @see BaseAwardRule::RenderRuleField().
	 */
	protected function ApplyRule($FieldName, $RuleName, $CustomError = '') {
		$FieldName = $this->RenameRuleField($FieldName);

		$this->Validation->ApplyRule($FieldName, $RuleName, $CustomError);
	}

	/**
	 * Validates Rule Settings.
	 * @throws A "not implemented" Exception. This method must be implemented by
	 * descendat classes.
	 */
	protected function _ValidateSettings() {
		throw new Exception(T('Not implemented. Descendant classes must implement this method.'));
	}

	/**
	 * Validates Rule settings. This method is a wrapper around
	 * BaseAwardRule::_ValidateSettings(), which is the method that actually
	 * performs the validation.
	 *
	 * @param Gdn_Form Form The Form which will contain the validation results.
	 * @param array Settings The Rule Settings to validate.
	 * @return bool True, if Validation was successful, False otherwise.
	 */
	public function ValidateSettings(Gdn_Form $Form, array $Settings) {
		$this->_ValidateSettings($Settings);

		$Form->SetValidationResults($this->Validation->Results());

		return (count($this->Validation->Results()) == 0);
	}

	/**
	 * Checks if the Rule should be enabled or not, based on its Settings.
	 * Descendant classes must implement this method.
	 *
	 * @param array Settings The Rule Settings.
	 * @return bool True, if the Rule should be enabled, False otherwise.
	 */
	protected function IsRuleEnabled(array $Settings) {
		throw new Exception(T('Not implemented. Descendant classes must implement this method.'));
	}

	// TODO Document method
	public function SaveSettings($AwardID, array $Settings) {
		$Values = array(
			'AwardID' => $AwardID,
			'RuleIsEnabled' => $this->IsRuleEnabled($Settings),
			'RuleClass' => get_called_class(),
			'RuleConfiguration' => json_encode($Settings),
		);


		$this->AwardRulesModel = new AwardRulesModel();
		return $this->AwardRulesModel->Save($Values);
	}

	/**
	 * Class constructor.
	 *
	 * @return BaseController An Instance of Base Controller.
	 */
	public function __construct() {
		parent::__construct();
		$this->Validation = new Gdn_Validation();
	}
}
