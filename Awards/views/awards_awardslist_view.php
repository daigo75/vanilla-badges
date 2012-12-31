<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

	// Indicates how many columns there are in the table that shows the list of
	// configured Awards. It's mainly used to set the "colspan" attributes of
	// single-valued table rows, such as Title, or the "No Results Found" message.
	$AwardsTableColumns = 6;

	// The following HTML will be displayed when the DataSet is empty.
	$OutputForEmptyDataSet = Wrap(T('No Awards configured.'),
																'td',
																array('colspan' => $AwardsTableColumns,
																			'class' => 'NoResultsFound',)
																);
?>
<div class="AwardsPlugin">
	<div class="Header">
		<?php include('awards_admin_header.php'); ?>
	</div>
	<div class="Content">
		<?php
			echo $this->Form->Open();
			echo $this->Form->Errors();
		?>
		<h3><?php echo T('User Statistics API Awards'); ?></h3>
		<div class="Info">
			<?php
				echo Wrap(T('Here you can configure the Awards that can be assigned to the Users.'), 'p');
			?>
		</div>
		<div class="FilterMenu">
		<?php
			echo Anchor(T('Add Award'), AWARDS_PLUGIN_AWARDCLASS_ADDEDIT_URL, 'Button');
		?>
		</div>
		<table id="AwardsList" class="display AltRows">
			<thead>
				<tr>
					<th><?php echo T('Award Name'); ?></th>
					<th><?php echo T('Icon'); ?></th>
					<th><?php echo T('Class'); ?></th>
					<th><?php echo T('Description'); ?></th>
					<th><?php echo T('Enabled?'); ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>
				<?php
					// TODO Display list of configured Awards
					// TODO Display Add button
					// TODO Display Edit button
					// TODO Display Clone button
					// TODO Display, next to each Award, how many times it has been awarded

					$AwardsDataSet = $this->Data['AwardsDataSet'];

					// If DataSet is empty, just print a message.
					if(empty($AwardsDataSet)) {
						echo $OutputForEmptyDataSet;
					}
					// TODO Implement Pager.
					// Output the details of each row in the DataSet
					foreach($AwardsDataSet as $Award) {
						echo "<tr>\n";
						// Output Award Name and Description
						echo Wrap(Gdn_Format::Text($Award->AwardName), 'td', array('class' => 'AwardName',));

						echo Wrap(Img($Award->AwardImage,
													array('class' => 'AwardImage ' . $Award->AwardClassName,)),
											'td',
											array('class' => 'AwardName',));

						echo Wrap(Gdn_Format::Text($Award->AwardClassName), 'td', array('class' => 'AwardClassName',));
						echo Wrap(Gdn_Format::Text($Award->AwardName), 'td', array('class' => 'AwardName',));

						// Output "Enabled" indicator
						$EnabledText = ($Award->IsEnabled == 1) ? T('Yes') : T('No');

						// Display a convenient link to enable/disable the Award with a single click
						$EnabledText = Anchor(Gdn_Format::Text($EnabledText),
																	sprintf('%s?%s=%d&%s=%d',
																					AWARDS_PLUGIN_AWARD_ENABLE_URL,
																					LOGGER_ARG_APPENDERID,
																					$Award->AwardID,
																					AWARDS_PLUGIN_ARG_AWARDID,
																					($Award->IsEnabled == 1 ? 0 : 1)),
																	'EnableLink',
																	array('title' => T('Click here to change Award status (Enabled/Disabled).'),)
																	);

						echo Wrap($EnabledText,
											'td',
											array('class' => 'Enabled',)
											);

						echo "<td class=\"Buttons\">\n";
						// Output Add/Edit button
						echo Anchor(T('Edit'),
												sprintf('%s?%s=%s',
																AWARDS_PLUGIN_AWARDCLASS_ADDEDIT_URL,
																AWARDS_PLUGIN_ARG_AWARDID,
																Gdn_Format::Url($Award->AwardID)),
												'Button AddEditAward');
						// Output Delete button
						echo Anchor(T('Delete'),
												sprintf('%s?%s=%s',
																AWARDS_PLUGIN_AWARDCLASS_DELETE_URL,
																AWARDS_PLUGIN_ARG_AWARDID,
																Gdn_Format::Url($Award->AwardID)),
												'Button DeleteAward');
						echo "</td>\n";
						echo "</tr>\n";
					}
				?>
			 </tbody>
		</table>
		<?php
			 echo $this->Form->Close();
		?>
	</div>
</div>
