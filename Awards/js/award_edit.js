jQuery(document).ready(function(){
	var TabsList = $('<ul>');

	// Add a link, which will be transformed into a Tab, for each Rule
	$('.Tab').each(function() {
		var Element = $(this);
		// Find the Label to use to create a Tab for the Group
		var Label = Element.find('.Label').first().html();
		Element.find('.Label').hide();

		// Add a link that will become a Tab
		var MenuLink = $('<a>')
			.attr('href', '#' + Element.attr('id'))
			.html(Label)

		TabsList.append($('<li>').html(MenuLink));
	});

	// Prepend the Tabs just before the first Rule Group
	var TabsElement = $('.AwardsPlugin').find('.Tabs').first();
	TabsElement.prepend(TabsList);
	TabsElement.tabs();
});
