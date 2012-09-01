<section class="galeria">
	<header>
		<h1><a href="<?php echo $this->App->url; ?>"><?php echo $this->Controller->titulo; ?></a> / <?php echo $galeria['nome']; ?></h1>
		<a id="engadir-fotos-boton" class="boton" href="#">Engadir fotos...</a>
	</header>

	<div id="engadir-fotos-input" class="zona-dragdrop hidden" data-url="<?php echo $this->App->url; ?>subir-fotos" data-nome="<?php echo $galeria['nome']; ?>">
		Arrastra aqui as fotos que queiras subir
		<p>Solo imaxes (jpg). Máximo 100 megas e 100 arquivos ao mesmo tempo</p>

		<progress max="100" class="hidden"></progress>
	</div>

	<ul class="fotos">
		<?php foreach ($fotos as $foto): ?>
		<li>
			<a href="<?php echo $this->App->assetsUrl.'fotos/'.$galeria['nome'].'/'.$foto; ?>" class="fancybox" rel="galeria">
				<img src="<?php echo $this->App->assetsUrl.'cache/fotos/'.$galeria['nome'].'/resize,300__'.$foto; ?>" alt="">
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
</section>