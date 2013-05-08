<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Renders the widget that displays the Awards earned by the User.
 */
class UserAwardsModule extends ModuleEx {
	// @var Gdn_Dataset Stores the list of the User Awards to display
	private $_UserAwardsDataSet;
	// @var int The ID of the User for whom to display the Awards.
	private $_UserID;

	/**
	 * Returns an instance of UserAwardsModel.
	 *
	 * @return AwardsModel An instance of UserAwardsModel.
	 * @see BaseManager::GetInstance()
	 */
	private function UserAwardsModel() {
		return $this->GetInstance('UserAwardsModel');
	}

	/**
	 * Loads the list of the Awards obtained by the User.
	 */
	public function LoadData($UserID) {
		$this->_UserID = $UserID;
		if(empty($this->_UserID)) {
			return;
		}

		$this->_UserAwardsDataSet =	$this->UserAwardsModel()->GetForUser($UserID, array('DateAwarded    desc',
																																										'AwardName asc'));
	}

	/**
	 * Renders a list of <li> items displaying the Awards earned by a User.
	 *
	 * This method can be used in two ways:
	 * - By UserAwardsModule::ToString(), to render the list inside the User
	 *   Awards widget.
	 * - Externally, to just render the list items, with the purpose of updating
	 *   the widget content via AJAX.
	 *
	 * @return string HTML containing a list of <li> items, displaying User's
	 * Awards, or a single <li> with a message if there aren't any to be displayed.
	 */
	public function RenderAwardsList() {
		// Do not display anything if User is not logged in
		if(empty($this->_UserID)) {
			return '';
		}
		// If there are no Awards to display, just show a message
		if($this->_UserAwardsDataSet->NumRows() <= 0) {
			echo Wrap(T('None yet.'), 'li');
		}

		// Show a list of User's Awards
		foreach($this->_UserAwardsDataSet->Result() as $Award) {
			$AwardImage = Wrap(Img($Award->AwardImageFile,
														 array('class' => 'AwardImage Medium ' . $Award->AwardClassCSSClass,
																	 'alt' => $Award->AwardName,
																	 'title' => $Award->AwardName)),
												 'td',
												 array('class' => 'Image'));

			$AwardLink = Anchor($AwardImage,
													AWARDS_PLUGIN_AWARD_INFO_URL . '/' . $Award->AwardID,
													'');


			echo Wrap($AwardLink,
								'li',
								array('class' => 'Award',)
			);
		}
	}

	/**
	 * Indicates if the User is viewing his own profile.
	 *
	 * @return bool True if User is Viewing his own profile, False otherwise.
	 */
	private function ViewingOwnProfile() {
		return $this->_UserID === Gdn::Session()->UserID;
	}

	/**
	 * Renders the output for the module.
	 *
	 * @return string The HTML generated by the module.
	 */
	public function ToString() {
		// Do not display anything if User is not logged in
		if(empty($this->_UserID)) {
			return '';
		}
		ob_start();
		?>
		<div id="UserAwards" class="Box">
			<?php
				// Title uses the word "Discussions", rather than "Threads", because
				// that is the word that normally identifies threads in the User Interface
				$WidgetTitle = $this->ViewingOwnProfile() ? T('My Awards') : T('Awards');
				echo Wrap($WidgetTitle, 'h4');
			?>
			<div>
				<ul id="AwardsList" class="PanelInfo clearfix">
				<?php
					$this->RenderAwardsList();
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
