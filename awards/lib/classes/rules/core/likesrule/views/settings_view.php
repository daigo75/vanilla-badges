<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/* Rules Settings have to be "moved" manually to Form Values because they are
 * decoded from their JSON format and, therefore, returned as properties of an
 * object.
 */
$RuleSettings = GetValue('LikesRule', $this->Data['RulesSettings']);

$ReceivedLikesSettings = GetValue('ReceivedLikes', $RuleSettings);
$this->Form->SetFormValue('ReceivedLikes_Enabled', (int)GetValue('Enabled', $ReceivedLikesSettings));
$this->Form->SetFormValue('ReceivedLikes_Amount', GetValue('Amount', $ReceivedLikesSettings));

$MissingRuleRequirements = GetValue('LikesRule', GetValue('MissingRuleRequirements', $this->Data), array());

//var_dump($MissingRuleRequirements);die();
$ExtraCssClass = empty($MissingRuleRequirements) ? '' : 'Disabled';
?>
<div class="Rule clearfix <?php echo $ExtraCssClass; ?>">
	<div class="Fields">
		<ul>
			<li>
				<div class="ReceivedLikes">
				<?php
					//echo Wrap(T('ReceivedLikes'), 'h4');
					LikesRule::RenderRuleField($this->Form->CheckBox('ReceivedLikes_Enabled', T('User received at least X Likes')));
					LikesRule::RenderRuleField($this->Form->TextBox('ReceivedLikes_Amount',
																													 array('class' => 'InputBox Numeric')));
				?>
				</div>
			</li>
		</ul>
	</div>
	<?php BaseAwardRule::RenderMissingRequirements($MissingRuleRequirements); ?>
</div>
