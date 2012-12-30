<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

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
		<fieldset id="something" name="something">
			<legend>
				<h3><?php echo T('Awards List'); ?></h3>
				<p>
					<?php
					echo T('Here you can see the list of configured Awards.');
					?>
				</p>
			</legend>
			<ul>
				<li><?php
					// TODO Display list of configured Awards
					// TODO Display Add button
					// TODO Display Edit button
					// TODO Display Clone button
					// TODO Display, next to each Award, how many times it has been awarded

					//echo $this->Form->Label(T('Awards Level'), 'Plugin.Awards.LogLevel');
					//echo Wrap(T('Select the Log Level. Messages with a level lower than the one selected ' .
					//						'will be ignored. <strong>Example</strong>: if you select "<i>Warning</i>", ' .
					//						'messages logged as <i>Trace</i>, <i>Debug</i> and <i>Info</i> will be ignored.'),
					//					'div',
					//					array('class' => 'Info',));
					//echo $this->Form->DropDown('Plugin.Awards.LogLevel',
					//													 $AwardsLevels,
					//													 array('id' => 'AwardsLevel',
					//																 'value' => $CurrentAwardsLevel,));

					echo $this->Form->CheckBox('Rule[]', '', array('value' => 'SomeRule',
																										'id' => 'RuleX'));
					echo $this->Form->TextBox('Field1', array('name' => 'SomeRule_Field1'));
					echo $this->Form->TextBox('Field2', array('name' => 'SomeRule_Field2'));
					echo $this->Form->TextBox('Field3', array('name' => 'SomeRule_Field3'));

					echo $this->Form->CheckBox('Rule[]', '', array('value' => 'SomeOtherRule',
																										'id' => 'RuleY'));
					echo $this->Form->TextBox('Field1', array('name' => 'SomeOtherRule_Field1'));
					echo $this->Form->TextBox('Field2', array('name' => 'SomeOtherRule_Field2'));
					echo $this->Form->TextBox('Field3', array('name' => 'SomeOtherRule_Field3'));
				?></li>
			</ul>
		</fieldset>
		<?php
			 echo $this->Form->Close('Save');
		?>
	</div>
</div>
