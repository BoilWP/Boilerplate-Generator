jQuery(window).load(function(){
	var advanced = 'hide';
	var github_updater = 'hide';

	jQuery('a.advanced-options').on('click', function(){

		if( advanced == 'hide' ) {
			jQuery('.advanced-control').show();
			advanced = 'show';
		}
		else if( advanced == 'show' ) {
			jQuery('.advanced-control').hide();
			advanced = 'hide';
		}

		return false;
	});

	jQuery('input[type="radio"][name="support_github"]').on('click', function(){

		if( github_updater == 'hide' ) {
			jQuery('.github-branch').show();
			github_updater = 'show';
		}
		else if( github_updater == 'show' ) {
			jQuery('.github-branch').hide();
			github_updater = 'hide';
		}

	});

});