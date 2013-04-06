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
				echo Wrap(T('Here you can view the list of Awards earned by a specific User, ' .
										'and revoke one or more of them, if needed.'), 'p');
			?>
		</div>
		<ul>
			<li><?php
				// TODO Display search box to find User
				// TODO Display list of Awards earned by a User

				echo Wrap('Page not yet implemented.', 'h2');

				// TODO Display Revoke button for each Award
				// TODO Display, next to each Award, how many times it has been awarded
			?></li>
		</ul>
		<?php
			 echo $this->Form->Close();
		?>
	</div>
</div>
<?php include('awards_admin_footer.php'); ?>
