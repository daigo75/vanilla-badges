<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

?>
<fieldset class="Rule">
	<ul>
		<li>
			<div class="Discussions">
			<?php
				echo Wrap('Discussions', 'h3');
				PostCountRule::RenderRuleField($this->Form->CheckBox('Discussions_Enabled', T('User started at least X Discussions')));
				PostCountRule::RenderRuleField($this->Form->TextBox('Discussions_Amount',
																														array('class' => 'InputBox Numeric')));
			?>
			</div>
		</li>
		<li>
			<div class="Comments">
			<?php
				echo Wrap('Comments', 'h3');
				PostCountRule::RenderRuleField($this->Form->CheckBox('Comments_Enabled', T('User posted at least X Comments')));
				PostCountRule::RenderRuleField($this->Form->TextBox('Comments_Amount',
																														array('class' => 'InputBox Numeric')));
			?>
			</div>
		</li>
		<?php
			// TODO Implement interface for "at/every" X questions
			// TODO Implement interface for "at/every" X answers
		?>
	</ul>
</fieldset>
