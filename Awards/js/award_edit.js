/**
{licence}
*/
jQuery(document).ready(function(){
	var TabsList = $('<ul>');
	var OriginalAwardImage = $('.AwardImageWrapper .AwardImage').attr('src');

	// Add a link, which will be transformed into a Tab, for each Rule
	$('.Tab').each(function() {
		var Element = $(this);
		// Find the Label to use to create a Tab for the Group
		var Label = Element.find('.Label').first()
		Label.hide();

		// Extract the text from the Label
		var LabelText = Label.html();

		// Add a link that will become a Tab
		var MenuLink = $('<a>')
			.attr('href', '#' + Element.attr('id'))
			.html(LabelText)

		TabsList.append($('<li>').html(MenuLink));
	});

	// Prepend the Tabs just before the first Rule Group
	var TabsElement = $('.AwardsPlugin').find('.Tabs').first();
	TabsElement.prepend(TabsList);
	TabsElement.tabs();

	// Handle clearing of new image, restoring original one
	$('.AwardImageWrapper').delegate('#RestoreImage', 'click', function() {
		ClearFileField('#Form_Picture');
		$('.AwardImageWrapper').removeClass('Preview');
		$('.AwardImageWrapper .AwardImage').attr('src', OriginalAwardImage);
	});

	// Display a Preview when a new Image has been selected
	$('#Form_Picture').change(function() {
		if($(this).val()) {
			$('.AwardImageWrapper').addClass('Preview');
			$('#Form_PreUploadedImageFile').val('');
		}
	});

	// Configure and initialise the Server Side File Browser, which can be used
	// to select a previously uploaded image
	var ServerSideFileBrowserCfg = {
		root: '/',
		script: 'browsedir'
	};
	$('#ServerSideBrowser').fileTree(ServerSideFileBrowserCfg, function(SelectedFile) {
		ClearFileField('#Form_Picture');

		var ImageFile = gdn.definition('path_uploads') + SelectedFile;
		$('#Form_PreUploadedImageFile').val(ImageFile);
		$('.AwardImageWrapper .AwardImage').attr('src', gdn.url('/uploads' + SelectedFile));
		$('.AwardImageWrapper').addClass('Preview');
  });

});
