<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

$RequiredWritableDirs = array(
	AWARDS_PLUGIN_AWARDS_PICS_PATH,
	AWARDS_PLUGIN_AWARDCLASSES_PICS_PATH,
	dirname(AWARDS_PLUGIN_AWARDCLASSES_CSS_FILE),
	AWARDS_PLUGIN_EXPORT_PATH,
	PATH_UPLOADS . '/' . AWARDS_PLUGIN_IMPORT_PATH,
);

?>
<div class="Aelia AwardsPlugin StatusPage">
	<div class="Header">
		<?php include('awards_admin_header.php'); ?>
	</div>
	<div class="Content">
		<div id="Directories">
			<?php
				echo Wrap(T('Directories'), 'h4');
				echo '<ul>';
				foreach($RequiredWritableDirs as $Dir) {
					if(is_writable($Dir)) {
						$DirStatus = T('Writable');
						$CssClass = 'Writable';
					}
					else {
						$DirStatus = T('Not writable');
						$CssClass = 'NotWritable';
					}
					$DirStatus = Wrap($DirStatus,
										'span',
										array('class' => $CssClass));
					echo Wrap(sprintf('%s: %s',
														realpath($Dir),
														$DirStatus),
										'li');
				}
				echo '</ul>';
			?>
		</div>
	</div>
</div>
<?php include('awards_admin_footer.php'); ?>
