# Aqui tes o FOL

(o resto da gaita xa é cousa túa)

[![Build Status](https://travis-ci.org/fol-project/fol.svg?branch=master)](https://travis-ci.org/fol-project/fol)
[![PPM Compatible](https://raw.githubusercontent.com/php-pm/ppm-badge/master/ppm-badge.png)](https://github.com/php-pm/php-pm)

FOL é un (micro)framework escrito en PHP por Oscar Otero. Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis de aquí lle interesa.

Requerimentos:

* PHP 5.5+
* Composer
* Node 4+


## Instalación

```bash
composer create-project fol/fol o-meu-proxecto
cd o-meu-proxecto
npm install
cp .env.example .env
mkdir -m 0777 data
mkdir -m 0777 data/logs
```

## App

A clase `App\App` (aloxada en app/App.php) é a que xestiona a páxina web. Mira [fol-core](https://github.com/fol-project/core) para máis información.

## Liña de comandos

Fol usa [Robo](http://robo.li/) como xestor de tarefas. Polo que edita o arquivo `RoboFile.php` para meter aí os comandos que queiras.

Só hai un comando definido por defecto que é `robo run`, que o que fai é lanzar un servidor de php, executa gulp e usa [BrowserSync](http://browsersync.io/) para sincronizar os cambios.

## Deploy

Podes usar [Deployer](http://deployer.org/) para facer deploy ao servidor que queiras. Xa inclúe un arquivo `deploy.php` con toda a configuración que podes personalizar.

## Gulp

Tamén trae un arquivo gulp preparado para xestionar os css/js/imgs. Os arquivos orixinais gárdanse no directorio "assets" e gulp procésaos e pásaos ao directorio "public".

## PHP-PM

Se queres, tamén podes usar [php-pm](https://github.com/php-pm/php-pm) para lanzar a web. Tes que instalar o [Psr-7 bridge](https://github.com/php-pm/php-pm-psr7):

```bash
# change minimum-stability to dev in your composer.json (until we have a version tagged): "minimum-stability": "dev"

composer require php-pm/php-pm:dev-master
composer require php-pm/psr7-adapter
php vendor/bin/ppm config --bootstrap=App\\PPM
php vendor/bin/ppm start
```

## Configuración do servidor

### En Apache

Xa hai un arquivo .htaccess preparado, simplemente tes que permitir usalo:

```
<Directory "/var/www/fol/public">
	AllowOverride All
</Directory>
```

Se queres meter o teu proxecto nun subdirectorio (por exemplo http://localhost/blog) podes poñer o proxecto fora do documentRoot e crear un alias:

```
<IfModule alias_module>
	Alias /blog /var/www/blog/public
</IfModule>

<Directory "/var/www/blog/public">
	Options +FollowSymLinks
	AllowOverride All
</Directory>
```
Para que alias funcione, necesitas usar a directiva `RewriteBase` no .htaccess:

```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /blog/

    # Handle front controller
    RewriteCond %{REQUEST_FILENAME}/index.html !-f
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### En Nginx

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
