<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

function RenderAwardClassFilters($AwardClassesData, $CurrentAwardClassID) {
	if(empty($AwardClassesData)) {
		return '';
	}

	echo '<ol id="ClassFilters">';
	$CssClass = empty($CurrentAwardClassID) ? 'Active' : '';
	echo Wrap(Anchor(T('All'),
									 AWARDS_PLUGIN_AWARDS_PAGE_URL),
						'li',
						array('class' => 'FilterItem ' . $CssClass));

	// Render a filter for each Award Class
	foreach($AwardClassesData as $AwardClass) {
		$CssClass = ($CurrentAwardClassID === $AwardClass->AwardClassID) ? 'Active' : '';
		$FilterAnchor = Anchor($AwardClass->AwardClassName,
													 AWARDS_PLUGIN_AWARDS_PAGE_URL . '?' . AWARDS_PLUGIN_ARG_AWARDCLASSID . '=' . $AwardClass->AwardClassID);
		echo Wrap($FilterAnchor,
							'li',
							array('class' => 'FilterItem ' . $CssClass));
	}
	echo '</ol>';
}

// Indicates how many columns there are in the table that shows the list of
// Awards. It's mainly used to set the "colspan" attributes of
// single-valued table rows, such as Title, or the "No Results Found" message.
$AwardsTableColumns = 2;

// The following HTML will be displayed when the DataSet is empty.
$OutputForEmptyDataSet = Wrap(T('Award not found.'),
															'td',
															array('colspan' => $AwardsTableColumns,
																		'class' => 'NoResultsFound',)
															);

$AwardsData = GetValue('AwardsData', $this->Data);
$UserAwardData = GetValue('UserAwardData', $this->Data);
$AwardClassesData = GetValue('AwardClassesData', $this->Data);
//var_dump($AwardsData);
?>
<div id="AwardsPage" class="AwardsPlugin">
	<div class="Header">
		<?php echo Wrap(T('Awards'), 'h1'); ?>
		<div class="Filters Tabs">
			<?php
				RenderAwardClassFilters($AwardClassesData, GetValue('AwardClassID', $this->Data));
			?>
		</div>
	</div>
	<div class="Content">
		<table class="AwardsList">
			<tbody>
				<?php
					if(empty($AwardsData)) {
						echo Wrap($OutputForEmptyDataSet, 'tr');
					}
					else {
						foreach($AwardsData as $Award) {
							echo '<tr>';
							//var_dump($Award);die();
							$AwardImage = Img($Award->AwardImageFile,
																array('alt' => $Award->AwardName,
																			'class' => 'AwardImage Medium ' . $Award->AwardClassName));

							$AwardName = Wrap($Award->AwardName, 'h3', array('class' => 'AwardName'));
							$TotalTimesAwarded = Wrap(T('x') . '&nbsp;' . $Award->TotalTimesAwarded,
																				'p',
																				array('class' => 'TotalTimesAwarded',
																							'title' => sprintf(T('%d User(s) earned this Award'),
																																 $Award->TotalTimesAwarded)));
							echo Wrap($AwardImage . $AwardName . $TotalTimesAwarded,
												'td',
												array('class' => 'Name Cell'));

							$AwardDescription = Wrap($Award->AwardDescription, 'span', array('class' => 'AwardDescription'));
							echo Wrap($AwardDescription,
												'td',
												array('class' => 'Description Cell'));
							echo '</tr>';
						}
					}
				?>
			</tbody>
		</table>
	</div>
</div>
