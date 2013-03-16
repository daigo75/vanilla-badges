jQuery(document).ready(function(){
	var GroupList = $('<ul>');

	// Add a link, which will be transformed into a Tab, for each Rule
	$('.RuleGroup').each(function() {
		var Element = $(this);
		var Label = Element.find('.Label').first().html();

		var MenuLink = $('<a>')
			.attr('href', '#' + Element.attr('id'))
			.html(Label)
		GroupList.append($('<li>').html(MenuLink));
	});

	var TabsElement = $('#AwardRules').find('.Groups.Tabs').first();
	TabsElement.prepend(GroupList);
	TabsElement.tabs();
});
