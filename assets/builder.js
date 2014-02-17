$('document').ready(function(){

	$form = $('#builder-form');

	$form.validationEngine();

	$('#name').blur(function(){
		var name = $(this).val();
		name = name.toLowerCase().replace(/ /g,"");
		$('#shortname').val(name);
	});

	$form.submit(function(){
		if (!$form.validationEngine('validate')) {
			return false;
		}
		$(this).find('.form-row, .errors').css('visibility','hidden');
		$(this).find('#loading').fadeIn(1000);
		$('#updates').html('<strong>Building project...</strong>').fadeIn(1000);
	});

	$('#toggle-plugins').click(function(){
		if ($(this).hasClass('show')) {
			$('.plugin-selection').show();
			$(this).removeClass('show').addClass('hide');
		} else {
			$('.plugin-selection').hide();
			$(this).removeClass('hide').addClass('show');
		}
	});

	$('#wordpress').change(function(){
		if (!$(this).is(':checked')) {
			$(this).removeAttr('checked');
			$('#patch-roots, #plugins').attr('disabled', 'disabled');
		} else {
			$(this).attr('checked','checked');
			$('#patch-roots, #plugins').removeAttr('disabled');
		}
	});

	if ($('#update-flag').length) {
		$('.form-row').animate({opacity:0},1000);
		$('#loading').fadeIn(1600);
		$('#updates').html('<strong>Checking for updates...</strong>').fadeIn(1600);
		$.ajax({
			url: 'ajax-updates.php',
			data: {"check":true},
			success: function(data){
				$('#updates').fadeOut(500, function(){
					$('#updates').html(data).fadeIn(500);
				});
			}
		});

		$.ajax({
			url: 'ajax-updates.php',
			data: {"update":true},
			success: function(data){
				$('#loading, #updates').stop().fadeOut(1000, function(){$('#updates').remove();});
				$('.form-row').animate({opacity:1},1000);
			}
		});
	}
});