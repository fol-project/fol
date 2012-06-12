<!doctype html>

<html>
	<head>
		<title>Demo</title>

		<link rel="stylesheet" href="<?php echo BASE_HTTP.'public/css/csans.css'; ?>">
		<link rel="stylesheet" href="<?php echo BASE_HTTP.'public/css/styles.css'; ?>">

		<style type="text/css">
			<?php echo $this->App->handle("styles/$name", 'GET', $options)->getContent(); ?>
		</style>
	</head>

	<body>
		<div class="menu">
			<a href="<?php echo BASE_HTTP.$this->App->getHttpPath()."styles/$name".'?'.http_build_query($options); ?>">Get styles</a>

			<form>
				<?php
				echo $this->render('fieldset.php', [
					'legend' => 'Wrapper',
					'inputs' => $options['wrapper'],
					'key' => 'wrapper'
				]);

				echo $this->render('fieldset.php', [
					'legend' => 'Selectors',
					'inputs' => $options['selectors'],
					'key' => 'selectors'
				]);

				foreach ($options['breaks'] as $breakname => $break) {
					echo $this->render('fieldset.php', [
						'legend' => $breakname,
						'inputs' => $break,
						'key' => 'breaks['.$breakname.']'
					]);
				}
				?>
				<button type="submit">Enviar</button>
			</form>
		</div>

		<?php echo $this->render('layout'); ?>
	</body>
</html>