<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/
	// Indicates how many columns there are in the table that shows the list of
	// configured Award Classes. It's mainly used to set the "colspan" attributes of
	// single-valued table rows, such as Title, or the "No Results Found" message.
	$AwardClassesTableColumns = 6;

	// The following HTML will be displayed when the DataSet is empty.
	$OutputForEmptyDataSet = Wrap(T('No Award Classes configured.'),
																'td',
																array('colspan' => $AwardClassesTableColumns,
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
		<h3><?php echo T('Configured Award Classes'); ?></h3>
		<div class="Info">
			<?php
				echo Wrap(T('Here you can configure the Award Classes. Classes are useful to group the ' .
										'Awards, for example to distinguish between the ones easyto obtain from the ' .
										'more difficult ones.'), 'p');
			?>
		</div>
		<div class="FilterMenu">
		<?php
			echo Anchor(T('Add Award Class'), AWARDS_PLUGIN_AWARDCLASS_ADDEDIT_URL, 'Button');
		?>
		</div>
		<table id="AwardClassesList" class="display AltRows">
			<thead>
				<tr>
					<th class="Image"><?php echo T('Background Image'); ?></th>
					<th class="Name"><?php echo T('Award Class Name'); ?></th>
					<th class="RankPoints"><?php echo T('Rank Points'); ?></th>
					<th class="Description"><?php echo T('Description'); ?></th>
					<th class="TotalAwardsUsingClass"><?php echo T('Awards using Class'); ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>
				<?php
					// TODO Display list of configured Award Classes
					// TODO Display Clone button
					$AwardClassesDataSet = $this->Data['AwardClassesDataSet'];

					// If DataSet is empty, just print a message.
					if(empty($AwardClassesDataSet)) {
						echo $OutputForEmptyDataSet;
					}
					// TODO Implement Pager.
					// Output the details of each row in the DataSet
					foreach($AwardClassesDataSet as $AwardClass) {
						echo "<tr>\n";
						if(empty($AwardClass->AwardClassImageFile)) {
							echo Wrap(Gdn_Format::Text(T('None')), 'td');
						}
						else {
							echo Wrap(Img($AwardClass->AwardClassImageFile,
														array('class' => 'AwardClassImage Medium ',)),
												'td',
												array('class' => 'Image',));
						}
						// Output Award Class Name and Description
						echo Wrap(Gdn_Format::Text($AwardClass->AwardClassName), 'td', array('class' => 'AwardClassName',));
						echo Wrap(Gdn_Format::Text($AwardClass->RankPoints), 'td', array('class' => 'RankPoints Numeric',));

						echo Wrap(Gdn_Format::Text($AwardClass->AwardClassDescription), 'td', array('class' => 'AwardClassDescription',));
						echo Wrap(Gdn_Format::Text($AwardClass->TotalAwardsUsingClass), 'td', array('class' => 'TotalAwardsUsingClass',));

						echo "<td class=\"Buttons\">\n";
						// Output Add/Edit button
						echo Anchor(T('Edit'),
												sprintf('%s?%s=%s',
																AWARDS_PLUGIN_AWARDCLASS_ADDEDIT_URL,
																AWARDS_PLUGIN_ARG_AWARDCLASSID,
																Gdn_Format::Url($AwardClass->AwardClassID)),
												'Button AddEditAwardClass');
						if($AwardClass->TotalAwardsUsingClass <= 0) {
							// Output Delete button
							echo Anchor(T('Delete'),
													sprintf('%s?%s=%s',
																	AWARDS_PLUGIN_AWARDCLASS_DELETE_URL,
																	AWARDS_PLUGIN_ARG_AWARDCLASSID,
																	Gdn_Format::Url($AwardClass->AwardClassID)),
													'Button DeleteAwardClass disabled');
						}
						else {
							echo Wrap(T('Cannot delete'),
												'span',
												array('class' => 'Disabled',
															'title' => sprintf(T('Award Class "%s" cannot be deleted ' .
																									 'because there are still Awards using it.'),
																								 GetValue('AwardClassName', $AwardClass))));
						}
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
