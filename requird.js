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
		var requird = { 
			post_type: $('#post_type').val(),
			fields: requirdOptions[$('#post_type').val()],
			message: '<div class="requird-fields" style="color:red; font-weight: bold; font-size: 18px; margin:10px 0;">' + requirdOptions.error_message + '</div>',
			errors: 0
		};

		if (typeof(requird.fields) != 'undefined' && requird.fields.length) {
			for(key in requird.fields) {
				if (requird.fields.hasOwnProperty(key)) {
					requird.$parent = $(fieldContainers[requird.fields[key]]);
					requird.$tag = $('#tagsdiv-'+requird.fields[key]);
					requird.$category = $('#'+requird.fields[key]+'checklist');
					if (requird.$parent.size()) {
						if (requird.fields[key] === 'editor') {
							// todo: add plain editor (content), add categories and tags in the section
							if ((tinyMCE.activeEditor && !tinyMCE.activeEditor.getContent().length) || !$('#content').val().length) {
								if (!requird.$parent.find('.requird-fields').size()) requird.$parent.append(requird.message);
								requird.errors++;
							}
							else {
								requird.$parent.find('.requird-fields').remove();
							}
						}
						else if (requird.fields[key] === 'thumbnail') {
							if (!$('#remove-post-thumbnail').size()) {
								if (!requird.$parent.find('.requird-fields').size()) requird.$parent.find('.inside').append(requird.message);
								requird.errors++;
							}
							else {
								requird.$parent.find('.requird-fields').remove();
							}
						}
						else {
							if (!requird.$parent.find(':input').val().length) {
								if (!requird.$parent.find('.requird-fields').size()) requird.$parent.find('.inside').prepend(requird.message);
								requird.errors++;
							}
							else if (requird.$parent.find(':input').val().length) {
								requird.$parent.find('.requird-fields').remove();
							}
						}
					}
					else if (requird.$tag.size()) {
						requird.$parent = $('#'+requird.fields[key]);
						if(!requird.$parent.find('.tagchecklist').children().size()) {
							if (!requird.$parent.find('.requird-fields').size()) requird.$parent.append(requird.message);
							requird.errors++;
						} else {
							requird.$parent.find('.requird-fields').remove();
						}
					}
					else if(requird.$category.size()) {
						requird.$parent = $('#taxonomy-'+requird.fields[key]);
						if(!requird.$category.find(':input[type=checkbox]:checked').size()) {
							if (!requird.$parent.find('.requird-fields').size()) $('#'+requird.fields[key]+'-all').after(requird.message);
							requird.errors++;
						} else if (requird.$category.size()) {
							requird.$parent.find('.requird-fields').remove();
						}
					}
				}
			}
		}
		if (typeof(requirdOptions[requird.post_type+'-custom']) != 'undefined' && requirdOptions[requird.post_type+'-custom'].length) {
			requird.custom = requirdOptions[requird.post_type+'-custom'].split(',');
			console.log(requird);
			if (requird.custom.length) {
				for (key in requird.custom) {
					if (requird.custom.hasOwnProperty(key)) {
						requird.$parent = $('#'+requird.custom[key]+'div');
						requird.$custom = $('#'+requird.custom[key]);
						if (requird.$custom.size() && requird.$custom.is(':input')) {
							requird.$parent = $('#'+requird.custom[key]+'div');
							if (!requird.$custom.val().length) {
								if (!requird.$parent.find('.requird-fields').size()) requird.$parent.find('.inside').append(requird.message);
								requird.errors++;
							} else {
								requird.$parent.find('.requird-fields').remove();
							}
						}
					}
				}
			}
		}
		if (requird.errors) {
			$('#publishing-action').find('.spinner').hide();
			$('#publish').removeClass('button-primary-disabled');
			return false;
		}
	});
});