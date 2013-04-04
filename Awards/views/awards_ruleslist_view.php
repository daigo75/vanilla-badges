<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/
// Indicates how many columns there are in the table that shows the list of
// configured Award Rules. It's mainly used to set the "colspan" attributes of
// single-valued table rows, such as Title, or the "No Results Found" message.
$AwardRulesTableColumns = 5;

// The following HTML will be displayed when the DataSet is empty.
$OutputForEmptyDataSet = Wrap(T('No Award Rules loaded.'),
															'td',
															array('colspan' => $AwardRulesTableColumns,
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
		<h3><?php echo T('Loaded Award Rules'); ?></h3>
		<div class="Info">
			<?php
				echo Wrap(T('Here you can see a list of currently loaded Award Rules.'), 'p');
			?>
		</div>
		<table id="AwardRulesList" class="display AltRows">
			<thead>
				<tr>
					<th class="Name"><?php echo T('Name'); ?></th>
					<th class="Version"><?php echo T('Version'); ?></th>
					<th class="Description"><?php echo T('Description'); ?></th>
					<th class="Group"><?php echo T('Group'); ?></th>
					<th class="Type"><?php echo T('Type'); ?></th>
				</tr>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>
				<?php
					$Rules = GetValue('Rules', $this->Data);

					// If DataSet is empty, just print a message.
					if(empty($Rules)) {
						echo Wrap($OutputForEmptyDataSet, 'tr');
					}

					// Output the details of each row in the DataSet
					foreach($Rules as $Rule) {
						echo "<tr>\n";
						// Output Rule Name, Version and Description
						echo Wrap(Gdn_Format::Text(GetValue('Label', $Rule)), 'td', array('class' => 'Name',));
						echo Wrap(Gdn_Format::Text(GetValue('Version', $Rule, T('N/A'))), 'td', array('class' => 'Version',));
						echo Wrap(Gdn_Format::Text(GetValue('Description', $Rule, T('N/A'))), 'td', array('class' => 'Description',));
						echo Wrap(Gdn_Format::Text(GetValue('Group', $Rule, T('N/A'))), 'td', array('class' => 'Group',));
						echo Wrap(Gdn_Format::Text(GetValue('Type', $Rule, T('N/A'))), 'td', array('class' => 'Type',));
						echo "</tr>\n";
					}
				?>
			 </tbody>
		</table>
		<?php
			 echo $this->Form->Close('Save');
		?>
	</div>
</div>
<?php include('awards_admin_footer.php'); ?>
