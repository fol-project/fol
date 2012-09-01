<section class="index">
	<header>
		<h1><?php echo $this->Controller->titulo; ?></h1> <a id="crear-galeria-boton" class="boton" href="#">Nova galería de prendas...</a>
		
		<form id="crear-galeria-formulario" class="hidden" action="<?php echo $this->App->url ?>nova-galeria" method="post">
			<fieldset>
				<input type="text" name="nome" placeholder="Nome da galería">
				<button class="boton">Crear</button>
			</fieldset>
		</form>
	</header>

	<ul class="galerias">
		<?php foreach ($galerias as $galeria): ?>
		<li>
			<a href="<?php echo $this->App->url.'galeria/'.$galeria; ?>"><?php echo $galeria; ?></a>
		</li>
		<?php endforeach; ?>
	</ul>
</section>