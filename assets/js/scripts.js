$(document).ready(function () {
	$('.fancybox').fancybox({
		padding: 5
	});

	$('#crear-galeria-boton').click(function () {
		$(this).hide();
		$('#crear-galeria-formulario').show();

		return false;
	});

	$('#engadir-fotos-boton').click(function () {
		var $input = $('#engadir-fotos-input');
		var $progress = $input.find('progress').removeProp('value');

		$input.slideToggle('normal').filedrop({
			url: $input.data('url'),
			maxfiles: 200,
			maxfilesize: 100,
			allowedfiletypes: ['image/jpg', 'image/jpeg'],
			data: {
				nome: $input.data('nome')
			},
			globalProgressUpdated: function (progress) {
				$progress.prop('value', progress);
			},
			uploadStarted: function () {
				$progress.show();
			},
			afterAll: function () {
				document.location.href = document.location.href;
			}
		});

		return false;
	});
});