# Aqui tes o FOL

(o resto da gaita xa é cousa túa)

[![Build Status](https://travis-ci.org/fol-project/fol.svg?branch=master)](https://travis-ci.org/fol-project/fol)

FOL é un (micro)framework escrito en PHP por Oscar Otero. Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis de aquí lle interesa o proxecto.

Requerimentos:

* PHP 5.5
* Composer


## Instalación

Crea o teu proxecto:

```
$ composer create-project fol/fol o-meu-proxecto
```

Unha vez instalado, créase automaticamente un arquivo `.env` a partir de `.env.example`. Nese arquivo gárdanse as variables de entorno máis sensibles (contrasinais, etc), e esta ingnorado por git.

## App

A clase `App\App` (aloxada en app/App.php) é a que xestiona a páxina web. Implementa a interface [container-interop](https://github.com/container-interop/container-interop) a modo de *service provider:*

```php
$app = new App\App();

//Rexistrar servizos directamente:

$app->register('database', function () {
	$config = $app->config->get('database');
	return new MyDatabaseClass($config);
});

$database = $app->get('database');

//Outros métodos
$app->getNamespace(); // App\App
$app->getNamespace('Controllers'); // App\App\Controllers

$app->getPath(); // /www/my-site/app
$app->getPath('subdirectory'); // /www/my-site/app/subdirectory

$app->getUrl(); // http://localhost
$app->getUrl('styles.css'); // http://localhost/styles.css

$app->getUrlHost(); // http://localhost
$app->getUrlPath('styles.css'); // /styles.css
```

## Liña de comandos

Fol usa [Robo](https://github.com/Codegyre/Robo) como xestor de tarefas. Polo que edita o arquivo `RoboFile.php` para meter aí os comandos que queiras. Se non tes robo instalado globalmente, podes executar o que instala localmente composer `vendor/bin/robo`


## Configuración do servidor

### Server de php

Para usar o servidor que trae o propio php, podes lanzar o seguinte comando:

```
$ robo server
```

Agora en http://localhost:8000 deberías ver algo.


En Apache
---------
Ainda que funciona sen facer nada, o mellor é establecer como documentRoot o directorio public:

```
<Directory "/var/www/fol/public">
	Order allow,deny
	Allow from all
</Directory>
```

Se queres meter o teu proxecto nun subdirectorio (por exemplo http://localhost/fol) podes poñer o proxecto fora do documentRoot e crear un alias:

```
<IfModule alias_module>
	Alias /nome-proxecto /os-meus-proxectos/fol
</IfModule>

<Directory "/os-meus-proxectos/fol/public">
	Order allow,deny
	Allow from all
</Directory>
```


En Nginx
--------
Tes que editar o arquivo de configuración (nginx/sites-enabled/default):

```
server {
	root /var/www/fol/public;

	charset utf-8;

	location ~* \.php$ {
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_index index.php;
		include fastcgi_params;
	}

	# Deny access for hidden
	location ~ /\. {
		deny all;
	}

	# Manage all request
	location / {
		try_files $uri @public;
	}

	# Headers for assets
	location ~* .*\.(css|js|jpg|jpeg|gif|png|ico|cur|gz|svg|svgz|mp4|ogg|ogv|webm|htc|woff|eot|ttf)$ {
		expires 1M;
		access_log off;
		add_header Cache-Control "public";
		try_files $uri @public;
	}

	# This is the public location, called in each request
	location @public {
		if (!-f $request_filename) {
			rewrite ^(.*)$ /index.php last;
		}
	}
}
```
