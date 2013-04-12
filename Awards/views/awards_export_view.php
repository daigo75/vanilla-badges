<?php if (!defined('APPLICATION')) exit();
/*
{licence}
*/

?>
<div class="AwardsPlugin Export">
	<div class="Header">
		<?php include('awards_admin_header.php'); ?>
	</div>
	<div class="Content">
		<?php
			echo $this->Form->Open();
			echo $this->Form->Errors();
		?>
		<div class="Info"><?php
			echo Wrap(T('Here you can export your Awards and Award Classes to an ' .
									'external file, which you can then import in another forum.'),
								'p');
		?></div>
		<div class="clearfix">
			<div class="Column">
				<ul>
					<li><?php
						echo $this->Form->Label(T('Export Label (optional)'), 'ExportLabel');
						echo Wrap(T('This information is for your reference ' .
												'only.'),
											'div',
											array('class' => 'Info',));
						echo $this->Form->TextBox('ExportLabel');
					?></li>
					<li><?php
						echo $this->Form->Label(T('Export Description (optional)'), 'ExportDescription');
						echo Wrap(T('This information is for your reference ' .
												'only.'),
											'div',
											array('class' => 'Info',));
						echo $this->Form->TextBox('ExportDescription',
																			array('MultiLine' => true,
																						'class' => 'TextBox'));
					?></li>
				</ul>
			</div>
			<div class="Column">
				<?php
					echo Wrap(T('Options'),
										'h4',
										array('class' => 'OptionsLabel'));
				?>
				<ul>
					<li><?php
						echo $this->Form->Checkbox('ExportClasses', T('Include Award Classes.'));
									echo Wrap(T('Here you can export your Awards and Award Classes to an ' .
									'external file, which you can then import in another forum.'),
								'span');
					?></li>
				</ul>
			</div>
		</div>
		<div class="Buttons">
			<?php
				echo $this->Form->Button(T('Export'), array('Name' => 'Export',));
			?>
		</div>
		<?php
			 echo $this->Form->Close();
		?>
	</div>
	<?php include('awards_admin_footer.php'); ?>
</div>
