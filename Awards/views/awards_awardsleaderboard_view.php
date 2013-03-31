<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Renders some links that will allow to filter the view by Award Class.
 *
 * @param Gdn_DataSet AwardClassesData A DataSet containing all the available
 * Award Classes.
 * @param int CurrentAwardClassID The ID of the currently selected Award Class.
 * If empty, the view is considered unfiltered.
 */
// TODO Move Filters to separate sub-view
function RenderAwardClassFilters($AwardClassesData, $CurrentAwardClassID) {
	if(empty($AwardClassesData)) {
		return '';
	}

	echo '<div class="Filters Tabs">';
	echo '<ol id="ClassFilters">';
	$CssClass = empty($CurrentAwardClassID) ? 'Active' : '';
	echo Wrap(Anchor(T('All'),
									 AWARDS_PLUGIN_LEADERBOARD_PAGE_URL),
						'li',
						array('class' => 'FilterItem ' . $CssClass));

	// Render a filter for each Award Class
	foreach($AwardClassesData as $UserAwardClass) {
		$CssClass = ($CurrentAwardClassID === $UserAwardClass->AwardClassID) ? 'Active' : '';
		$FilterAnchor = Anchor($UserAwardClass->AwardClassName,
													 AWARDS_PLUGIN_LEADERBOARD_PAGE_URL . '?' . AWARDS_PLUGIN_ARG_AWARDCLASSID . '=' . $UserAwardClass->AwardClassID);
		echo Wrap($FilterAnchor,
							'li',
							array('class' => 'FilterItem ' . $CssClass));
	}
	echo '</ol>';
	echo '</div>';
}

function RenderUserInfo($AwardsData) {
	$UserObj = UserBuilder($AwardsData, '');
	$UserPhoto = UserPhoto($UserObj);
	$UserLink = UserAnchor($UserObj);

	echo Wrap($UserPhoto,
						'div',
						array('class' => 'UserPhoto'));
	echo Wrap($UserLink,
						'div',
						array('class' => 'UserLink'));
}


// Indicates how many columns there are in the table that shows the list of
// Awards. It's mainly used to set the "colspan" attributes of
// single-valued table rows, such as Title, or the "No Results Found" message.
$UserAwardsTableColumns = 3;

// The following HTML will be displayed when the DataSet is empty.
$OutputForEmptyDataSet = Wrap(T('No data found.'),
															'td',
															array('colspan' => $UserAwardsTableColumns,
																		'class' => 'NoResultsFound',)
															);

$UserAwardsData = GetValue('UserAwardsData', $this->Data);
$AwardClassesData = GetValue('AwardClassesData', $this->Data);
//var_dump($AwardClassesData);
?>
<div id="AwardsLeaderboard" class="AwardsPlugin">
	<div class="Header">
		<?php
			echo Wrap(T('Awards Leaderboard'), 'h1');
			RenderAwardClassFilters($AwardClassesData, GetValue('AwardClassID', $this->Data));
		?>
	</div>
	<div class="Content">
		<table id="TopUsers">
			<tbody>
				<?php
					if(empty($UserAwardsData) || ($UserAwardsData->NumRows() <= 0)) {
						echo Wrap($OutputForEmptyDataSet, 'tr');
					}
					else {
						$CurrentUserID = '';
						foreach($UserAwardsData as $UserAward) {
							//var_dump($UserAward);die();
							if($UserAward->UserID != $CurrentUserID) {
								if(!empty($CurrentUserID)) {
									// Write Total Awards Score below the Awards list
									echo Wrap(sprintf(T('%d Points'), $UserAward->TotalAwardsScore),
														'div',
														array('class' => 'UserAwardsScore'));

									// Close previous User's row and open a new one
									echo '</td></tr>';
								}

								// Save Current User
								$CurrentUserID = $UserAward->UserID;
								echo '<tr>';
								// Display User information
								echo '<td class="UserInfo">';
								RenderUserInfo($UserAward);
								echo '</td>';
								// Open table cell for Awards List
								echo '<td class="Awards">';
							}

							//var_dump($UserAward);die();
							$UserAwardImage = Img($UserAward->AwardImageFile,
																		array('alt' => $UserAward->AwardName,
																					'class' => 'AwardImage Medium ' . $UserAward->AwardClassName,
																					'title' => $UserAward->AwardName));

							// Build link to Award page
							$UserAwardImgLink = Anchor($UserAwardImage,
																				 AWARDS_PLUGIN_AWARD_INFO_URL . '/' . $UserAward->AwardID,
																				 '');

							// Display Award
							echo Wrap($UserAwardImgLink,
												'span',
												array('class' => 'AwardImageWrapper'));
						} while($UserAward = $UserAwardsData->NextRow());
						echo '</td>';
						echo '</tr>';
					}
				?>
			</tbody>
		</table>
	</div>
</div>
