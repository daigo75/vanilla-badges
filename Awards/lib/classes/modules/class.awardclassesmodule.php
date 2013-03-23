<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Renders the widget that displays the Award Classes.
 */
class AwardClassesModule extends ModuleEx {
	// @var Gdn_Dataset Stores the list of the User Awards to display
	private $_AwardClassesDataSet;

	// @var int The maximum amount of entries to display
	const MAX_ENTRIES = 15;

	/**
	 * Returns an instance of AwardClassesModel.
	 *
	 * @return AwardsModel An instance of AwardClassesModel.
	 * @see BaseManager::GetInstance()
	 */
	private function AwardClassesModel() {
		return $this->GetInstance('AwardClassesModel');
	}

	/**
	 * Loads the list of the configured Award Classes.
	 *
	 * @param int MaxEntries The maximum amount of entries to load.
	 */
	protected function LoadData($MaxEntries = self::MAX_ENTRIES) {
		$this->_AwardClassesDataSet =	$this->AwardClassesModel()->Get(array('RankPoints desc', 'AwardClassName asc'),
																																	$MaxEntries);
	}

	/**
	 * Renders a list of <li> items displaying the Award Classes.
	 *
	 * This method can be used in two ways:
	 * - By AwardClassesModule::ToString(), to render the list inside the User
	 *   Awards widget.
	 * - Externally, to just render the list items, with the purpose of updating
	 *   the widget content via AJAX.
	 *
	 * @return string HTML containing a list of <li> items, displaying User's
	 * Awards, or a single <li> with a message if there aren't any to be displayed.
	 */
	public function RenderAwardClassesList() {
		// If there are no Awards to display, just show a message
		if($this->_AwardClassesDataSet->NumRows() <= 0) {
			echo Wrap(T('None yet.'), 'li');
		}

		// Show a list of Award Classes
		foreach($this->_AwardClassesDataSet->Result() as $AwardClass) {
			//var_dump($AwardClass);

			// Retrieve Image for Award Class, using a dummy one if none is found
			if(empty($AwardClass->AwardClassImageFile)) {
				$AwardClass->AwardClassImageFile = AWARDS_PLUGIN_AWARDCLASSES_PICS_PATH . '/dummy-class-bg.png';
			}

			$AwardClassImage = Img($AwardClass->AwardClassImageFile,
														 array('class' => 'AwardImage Medium'));
			$AwardClassName = Wrap($AwardClass->AwardClassName,
														 'span',
														 array('class' => 'Name',));
			$AwardClassDescription = Wrap($AwardClass->AwardClassDescription,
																		'p',
																		array('class' => 'Description',));

			$AwardClassCell = Wrap($AwardClassImage . $AwardClassName . $AwardClassDescription,
													 'li',
													 array('class' => 'AwardClassInfo clearfix',));

			echo $AwardClassCell;
		}
	}

	/**
	 * Renders the output for the module.
	 *
	 * @param int MaxEntries The maximum amount of entries to load.
	 * @return string The HTML generated by the module.
	 */
	public function ToString($MaxEntries = self::MAX_ENTRIES) {
		$this->LoadData($MaxEntries);
		ob_start();
		?>
		<div id="AwardClassesModule">
			<?php
				echo Wrap(T('Award Classes'), 'h2');
			?>
			<div>
				<ul id="AwardClassesList" class="PanelInfo clearfix">
				<?php
					$this->RenderAwardClassesList();
				?>
				</ul>
			</div>
			<div class="Footer">
				<?php
					// Dummy
				?>
			</div>
		</div>
		<?php
		$Output = ob_get_contents();
		@ob_end_clean();
		return $Output;
	}
}
