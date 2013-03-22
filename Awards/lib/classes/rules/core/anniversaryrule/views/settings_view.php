<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/* Rules Settings have to be "moved" manually to Form Values because they are
 * decoded from their JSON format and, therefore, returned as properties of an
 * object.
 */
$RuleSettings = GetValue('AnniversaryRule', $this->Data['RulesSettings']);

$AnniversarySettings = GetValue('Anniversary', $RuleSettings);
$this->Form->SetFormValue('Anniversary_Enabled', (int)GetValue('Enabled', $AnniversarySettings));
$this->Form->SetFormValue('Anniversary_Years', GetValue('Years', $AnniversarySettings));

?>
<div class="Rule">
	<ul>
		<li>
			<div class="Anniversary">
			<?php
				echo Wrap('Anniversary', 'h6');
				AnniversaryRule::RenderRuleField($this->Form->CheckBox('Anniversary_Enabled', T('User has been registered for at least X years.')));
				AnniversaryRule::RenderRuleField($this->Form->TextBox('Anniversary_Years',
																														array('class' => 'InputBox Numeric')));
			?>
			</div>
		</li>
	</ul>
</div>
