/**
{licence}
*/
jQuery(document).ready(function(){
	function SelectUser(User) {
		if(!User) {
			return;
		}

		var UserProfileLink = $('<a>')
			.attr('href', gdn.definition('WebRoot') + '/profile/' + User.UserID + '/' + User.UserName)
			.text(gdn.definition('View_Profile'));

		$('<div>')
			.attr('id', User.UserID)
			.addClass('SelectedUser')
			.html(UserProfileLink)
			.prependTo("#SelectedUsers");

		$("#SelectedUsers").scrollTop(0);
	}
	$('#Form_UserName').autocomplete({
		source: function(request, response) {
			minLength: 2,
			$.ajax({
				url: gdn.definition('WebRoot') + '/user/search/' + request.term,
				dataType: 'json',
				data: {
				},
				success: function(data) {
					response($.map(data.Users, function(item) {
						return {
							label: item.UserName + ' &lt;' + item.EmailAddress + '&gt;' +
										 '<a href="/profile/"' + item.UserID + '/' + item.UserName + '">View Profile</a>',
							value: item.UserID
						}
					}));
				}
			});
		},
		select: function(event, ui) {
			if(ui.item) {
				SelectUser(ui.item);
			}
		},
		open: function() {
			$(this).removeClass("ui-corner-all").addClass("ui-corner-top");
		},
		close: function() {
			$(this).removeClass("ui-corner-top").addClass("ui-corner-all");
		}
	});
});
