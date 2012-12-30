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
		<fieldset>
			<legend>
				<h3><?php echo T('Award Classes'); ?></h3>
				<p>
					<?php
					echo T('Here you can see the list of configured Award Classes.');
					?>
				</p>
			</legend>
			<ul>
				<li><?php
					// TODO Display list of configured Award Classes
					// TODO Display Add button
					// TODO Display Edit button
					// TODO Display Clone button
					// TODO Display, next to each Class, how many Awards belong to it

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
				?></li>
			</ul>
		</fieldset>
		<?php
			 echo $this->Form->Close('Save');
		?>
	</div>
</div>
