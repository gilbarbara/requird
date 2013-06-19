/*
Plugin Name: Requird
Plugin URI: http://kollectiv.org/requird
Description: Require fields for WP-Admin
Writer: Gil Barbara
Version: 1.0.1
Writer URI: http://kollectiv.org
*/

jQuery(document).ready(function($) {

	var fieldContainers = {
		title: '#titlediv',
		editor: '#postdivrich',
		excerpt: '#postexcerpt',
		thumbnail: '#postimagediv'
	};

	$('form').on('submit', function(){
		var post_type = $('#post_type').val(),
			requirdFields = requirdOptions[post_type],
			requirdCustom,
			requirdMessage = '<div class="requird-fields" style="color:red; font-weight: bold; font-size: 18px; margin:10px;">Field Required</div>',
			requirdErrors = 0,
			$parent,
			$field;

		if (typeof(requirdFields) != 'undefined' && requirdFields.length) {
			for(key in requirdFields) {
				$parent = $(fieldContainers[requirdFields[key]]);
				if ($parent.size()) {
					if (requirdFields[key] == 'editor') {
						if (!tinyMCE.activeEditor.getContent().length) {
							if (!$parent.find('.requird-fields').size()) $parent.append(requirdMessage);
							requirdErrors++;
						}
						else {
							$parent.find('.requird-fields').remove();
						}
					}
					else if (requirdFields[key] == 'thumbnail') {
						if (!$('#remove-post-thumbnail').size()) {
							if (!$parent.find('.requird-fields').size()) $parent.append(requirdMessage);
							requirdErrors++;
						}
						else {
							$parent.find('.requird-fields').remove();
						}
					}
					else {
						if (!$parent.find(':input').val().length) {
							if (!$parent.find('.requird-fields').size()) $parent.append(requirdMessage);
							requirdErrors++;
						}
						else if ($parent.find(':input').val().length) {
							$parent.find('.requird-fields').remove();
						}
					}
				}
			}
		}
		if (typeof(requirdOptions[post_type+'-custom']) != 'undefined' && requirdOptions[post_type+'-custom'].length) {
			requirdCustom = requirdOptions[post_type+'-custom'].split(',');

			if (requirdCustom.length) {
				for (key in requirdCustom) {
					$parent = $('#'+requirdCustom[key]+'div');
					$field = $('#'+requirdCustom[key]);
					if ($parent.size()) {
						if (!$field.val().length) {
							if (!$parent.find('.requird-fields').size()) $parent.append(requirdMessage);
							requirdErrors++;
						}
						else if ($field.length) {
							$parent.find('.requird-fields').remove();
						}
					}
				}
			}
		}

		if (requirdErrors) {
			$('#publishing-action').find('.spinner').hide();
			$('#publish').removeClass('button-primary-disabled');
			return false;
		}
	});
});