<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Manages the list of all available Rules Criteria and provides convenience
 * functions to retrieve the Model, Validation and View for each one.
 */
class RulesCriteriaManager {
	private $Log;
	// @var array Contains a list of all available Criteria.
	public static $Criteria = array();

	/**
	 * Install an Criterion Class's auxiliary classes into Vanilla Factories, for
	 * later use.
	 *
	 * @param Criterion The Class of the Criterion.
	 * @return void.
	 */
	protected function InstallCriterion($CriterionClass) {
		// Install Criterion's Model and Validation class names into Vanilla
		// built-in factory. This will allow to leverage Vanilla's mechanisms for
		// the management of Singletons
		$ConfigModelClass = $this->GetConfigModelClass($CriterionClass);
		$ValidationClass = $this->GetValidationClass($CriterionClass);

		Gdn::FactoryInstall($ConfigModelClass,
												$ConfigModelClass,
												AWARDS_PLUGIN_CRITERIA_PATH . '/' . $CriterionClass . '/models',
												Gdn::FactorySingleton);
		Gdn::FactoryInstall($ValidationClass,
												$ValidationClass,
												AWARDS_PLUGIN_CRITERIA_PATH . '/' . $CriterionClass . '/validators',
												Gdn::FactorySingleton);
	}

	/**
	 * Install in Vanilla's Factories all auxiliary classes for available Criterion
	 * Classes.
	 *
	 * @return void.
	 */
	protected function InstallCriteria() {
		foreach(self::$Criteria as $CriterionClass => $CriterionInfo) {
			$this->InstallCriterion($CriterionClass);
		}
	}

	/**
	 * Checks if a Criterion Class exists in the list of the configured ones.
	 *
	 * @param CriterionClass The Criterion class to be checked.
	 * @return True if the class exists in the list of configured Criteria, False otherwise.
	 */
	function CriterionExists($CriterionClass) {
		return array_key_exists($CriterionClass, self::$Criteria);
	}

	/**
	 * Builds and returns the name of the Model that handles the configuration
	 * of a Criterion class.
	 *
	 * @param CriterionClass The class of Criterion for which to retrieve the
	 * Model.
	 * @return The class name of the Model.
	 */
	protected function GetConfigModelClass($CriterionClass) {
		return $this->CriterionExists($CriterionClass) ? sprintf('%sConfigModel', $CriterionClass) : null;
	}

	/**
	 * Builds and returns the name of the Validation that will be used to validate
	 * the configuration for the a Criterion.
	 *
	 * @param CriterionClass The class of Criterion for which to retrieve the
	 * Model.
	 * @return The class name of the Validation.
	 */
	protected function GetValidationClass($CriterionClass) {
		return $this->CriterionExists($CriterionClass) ? sprintf('%sValidation', $CriterionClass) : null;
	}

	/**
	 * Builds and returns the full name of the View to be used as an interface to
	 * configure Criterion.
	 *
	 * @param CriterionClass The class of Criterion for which to retrieve the
	 * View.
	 * @return The full path and file name of the View.
	 */
	public function GetConfigView($CriterionClass) {
		if(!$this->CriterionExists($CriterionClass)) {
			return null;
		}

		return sprintf('%s/%s/views/config_view.php', AWARDS_PLUGIN_CRITERIA_PATH, strtolower($CriterionClass));
	}

	/**
	 * Factory method. It instantiates the appropriate Model and Validation for
	 * the specified Criterion, and returns the configured Model.
	 *
	 * @param CriterionClass The class of Criterion for which to instantiate the
	 * Model.
	 * @return An instance of the Model to handle the configuration of the
	 * specified Criterion Class.
	 * @throws An Exception if either the Model or its Validation could not be
	 * instantiated.
	 */
	public function GetModel($CriterionClass) {
		$ModelClass = $this->GetConfigModelClass($CriterionClass);

		// If ModelClass is valid, then it means that the Criterion is in the list.
		// Therefore, the Validation just needs to be retrieved.
		if(isset($ModelClass)) {
			$ValidationClass = $this->GetValidationClass($CriterionClass);

			try {
				// The Validation is passed to the Model to "assemble" a complete model,
				// which will automatically perform appropriate validation of the
				// configuration.
				$Model = Gdn::Factory($ModelClass, Gdn::Factory($ValidationClass));

				return $Model;
			}
			catch(Exception $e) {
				// Log the exception to keep track of it, but throw it again afterwards.
				// This is useful in case the person who sees the Exception can't fix it
				// and has to require assistance from a Developer, who might not be
				// readily available.
				$Message = sprintf(T('Exception occurred while instantiating Model for Criterion "%s": %s'),
																			$CriterionClass,
																			$e->getMessage());
				$this->Logger->Error($Message);
				throw new Exception($Message, null, $e);
			}
		}
	}

	/**
	 * Given an Attribute Name, it returns a list of all the Criterion Classes and
	 * the value of the specified Attribute for each class.
	 *
	 * @return An associative array having Criterion Classes as Keys and the
	 * specified Attribute as Values.
	 */
	protected function GetCriteriaListWithAttribute($AttributeName) {
		$result = array();
		foreach(self::$Criteria as $CriterionClass => $Attributes) {
			$result[$CriterionClass] = $Attributes[$AttributeName];
		}
		return $result;
	}

	/**
	 * Returns a list of all the Criterion Classes with their Labels.
	 *
	 * @return An associative array having Criterion Classes as Keys and their
	 * Labels as Values.
	 */
	public function GetCriteriaLabels() {
		return $this->GetCriteriaListWithAttribute('Label');
	}

	/**
	 * Returns a list of all the Criterion Classes with their descriptions.
	 *
	 * @return An associative array having Criterion Classes as Keys and their
	 * Descriptions as Values.
	 */
	public function GetCriteriaDescriptions() {
		return $this->GetCriteriaListWithAttribute('Description');
	}

	/**
	 * Returns the Information about a specified Criterion Class.
	 *
	 * @param CriterionClass The Criterion Class for which to retrieve the
	 * information.
	 * @return An associative array containing information about the Criterion
	 * Class.
	 */
	public function GetCriterionInfo($CriterionClass) {
		return self::$Criteria[$CriterionClass];
	}

	/**
	 * Scans the Criteria directory for all appender files and loads them, so
	 * that they can add themselves to the list of available appenders.
	 *
	 * @return void.
	 */
	private function LoadCriteriaDefinitions() {
		$CriteriaDir = sprintf('%s/criteria', AWARDS_PLUGIN_CLASS_PATH);
		$Handle = opendir($CriteriaDir);
		if(empty($Handle)) {
			return;
		}

		// Load all Criterion Files, so that they can add themselves to the list of
		// installed Criteria
    while($File = readdir($Handle)) {
      if(strpos($File, 'class.criterion') == 0) {
				include_once(sprintf('%s/%s', $CriteriaDir, $File));
			}
		}
		closedir($Handle);
	}

	/**
	 * Constructor. It initializes the class and populates the list of available
	 * Criteria.
	 */
	public function __construct() {
		// Get System (root) Logger
		$this->Logger = LoggerPlugin::GetLogger();

		$this->LoadCriteriaDefinitions();
		$this->InstallCriteria();
	}
}
