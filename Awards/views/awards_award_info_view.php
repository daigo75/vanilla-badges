<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/
	// Indicates how many columns there are in the table that shows the list of
	// configured Awards. It's mainly used to set the "colspan" attributes of
	// single-valued table rows, such as Title, or the "No Results Found" message.
	$AwardsTableColumns = 7;

	// The following HTML will be displayed when the DataSet is empty.
	$OutputForEmptyDataSet = Wrap(T('No Awards configured.'),
																'td',
																array('colspan' => $AwardsTableColumns,
																			'class' => 'NoResultsFound',)
																);

	function RenderUserInfo($User) {
		$UserObj = UserBuilder($User, '');
		$UserPhoto = UserPhoto($UserObj, 'UserPhoto');
		$UserLink = UserAnchor($UserObj);

		echo Wrap($UserPhoto . $UserLink,
							'div');
	}
?>
<div class="AwardsPlugin">
	<div class="AwardImage">
		<img src="/vanilla/plugins/Awards/design/images/awards/green_pepper.png" class="AwardImage Large Bronze" alt="Green Pepper" title="Green Pepper">
	</div>
	<div class="AwardDetails">
		<h1>Award Name</h1>
		<p>Award Description</p>
	</div>
	<div class="YouEarned">
		<?php
			echo $UserPhoto = UserPhoto(UserBuilder(Gdn::Session()->User), 'UserPhoto');
			echo Wrap(sprintf(T('You earned this Award on %s'),
												Gdn_Format::Date(now(), T('Date.DefaultFormat'))));

		?>
	</div>
	<div class="RecentRecipients">

	</div>
</div>
