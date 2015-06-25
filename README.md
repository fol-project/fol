# Aqui tes o FOL

(o resto da gaita xa é cousa túa)

[![Build Status](https://travis-ci.org/fol-project/fol.svg?branch=master)](https://travis-ci.org/fol-project/fol)

FOL é un (micro)framework escrito en PHP por Oscar Otero. Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis de aquí lle interesa o proxecto. De todos xeitos, os comentarios dentro do código están en inglés.

Requerimentos:

* PHP 5.5
* Composer


# Instalación

Para instalalo precisas ter [composer](https://getcomposer.org/). Despois simplemente executa:

```
$ composer create-project fol/fol o-meu-proxecto
```

Unha vez instalado, créase automaticamente un arquivo `.env`. Se non se creou, podes facelo ti a partir de `.env.example`. Nese arquivo gárdanse as variables de entorno máis sensibles (contrasinais, etc), e esta ingnorado por git.

# Documentación rápida

Unha vez instalado, atoparás os seguintes directorios:

* vendor: usado por composer para instalar aí todos os paquetes e dependencias
* app: onde se garda a túa aplicación (plantillas, controladores, modelos, tests, etc).
* public: todos os arquivos accesibles publicamente (css, imaxes, js, componentes de bower, etc) ademáis do "front controller" (index.php).

## App

A clase `App\App` (aloxada en app/App.php) é a que xestiona a páxina web, xunto con todos os servizos que precise (bases de datos, plantillas, controladores, etc). Implementa a interface [container-interop](https://github.com/container-interop/container-interop) o que permite poder combinala con outros colectores:

```php
$app = new App\App();

//Rexistrar servizos directamente:

$app->register('database', function () {
	$config = $app->config->get('database');
	return new MyDatabaseClass($config);
});

$database = $app->get('database');

//Rexistrar outros sub-colectores, por exemplo php-di:
$builder = new \DI\ContainerBuilder();
$builder->setDefinitionCache(new Doctrine\Common\Cache\ArrayCache());

$app->add($builder->build());

$class = $app->get('My\\Class');
```

Tamén serve para gardar a configuración deses servizos ou de calquera outra cousa. Existe a propiedade `$app->config` que carga e xestiona todo tipo de configuracions que se gardan en app/config

Ademáis tamén ten unha serie de métodos básicos:

* `$app->getNamespace()`: Devolve o namespace da aplicación (ou sexa "App"). Ademáis podes usalo para que che devolva outros namespaces ou clases relativas. Por exemplo `$app->getNamespace('Controllers\\Index')` devolve "App\Controllers\Index".
* `$app->getPath()`: Devolve o path onde está aloxada a aplicación. Podes usar argumentos para que che devolva rutas de arquivos ou subdirectorios. Por exemplo: `$app->getPath('arquivos/123', '3.pdf')` devolve algo parecido a "/var/www/o-meu-proxecto/app/arquivos/123/3.pdf"
* `$app->getUrl()`: O mesmo que getPath pero para devolver rutas http do directorio público. Útil para acceder a arquivos css, javascript, etc. `$app->getUrl('assets/css', 'subdirectorio')` devolvería algo parecido a "http://localhost/o-meu-proxecto/public/assets/css/subdirectorio". Por defecto colle o valor que definiches como `APP_URL` ou `APP_CLI_SERVER_URL` (depende de que servidor uses) en .env, pero podes cambialo usando `$app->setUrl()`.

Por último ten dous métodos estáticos para executar a aplicación:

* `App\App::runHttp()` Executa a aplicación nunha contorna http
* `App\App::runCli()` Executa a aplicación como liña de comandos. Para iso usase o sistema de tarefas de [Robo](https://github.com/Codegyre/Robo) xunto con algunhas [tarefas propias de FOL](https://github.com/fol-project/tasks). Todas estas tarefas estan creadas e pódense configurar en Tasks.php. O arquivo `fol` permite executar a aplicación por liña de comandos:

```
$ php fol list
```

CONFIGURACIÓN DO SERVIDOR
=========================

Server de php
-------------
Para usar o servidor que trae o propio php, podes lanzar o seguinte comando:

```
$ php fol server
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
