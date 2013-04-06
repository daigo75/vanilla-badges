<?php if(!defined('APPLICATION')) exit();
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
		<div class="Info">
			<?php
				echo Wrap(T('Here you can configure various settings for the Awards ' .
										'Plugin.'), 'p');
			?>
		</div>
		<ul>
			<li><?php
				echo $this->Form->Label(T('Minimum length for search fields'), 'Plugin.Awards.MinSearchLength');
				echo Wrap(T('Specify the minimum amount of characters that will have to be entered ' .
										'in Plugin\'s search fields to trigger the search.'),
									'div',
									array('class' => 'Info',));
				echo $this->Form->TextBox('Plugin.Awards.MinSearchLength',
																	array('class' => 'InputBox Numeric',));
			?></li>
		</ul>
		<?php
			 echo $this->Form->Close('Save');
		?>
	</div>
</div>
<?php include('awards_admin_footer.php'); ?>
