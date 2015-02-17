# Aqui tes o FOL

(o resto da gaita xa é cousa túa)

[![Build Status](https://travis-ci.org/oscarotero/fol.png?branch=master)](https://travis-ci.org/oscarotero/fol)

FOL é un (micro)framework escrito en PHP por Oscar Otero (http://oscarotero.com) como exercicio de deseño e como ferramenta para experimentar.
Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis daquí lle interesa o proxecto. De todos xeitos, os comentarios dentro do código están en inglés.

Requerimentos:

* PHP 5.5
* Composer


# Instalación

Para instalalo precisas ter [composer](https://getcomposer.org/). Despois simplemente executa:

```
$ composer create-project fol/fol o-meu-proxecto
```

Unha vez instalado, hai que configurar unha serie de variables de entorno collidas do arquivo `env.php` e gardadas en `env.local.php`. Para facelo dende a liña de comandos executa:

```
$ php fol install
```

As variables que configuras son as seguintes:

* ENVIRONMENT: O nome do entorno de desenvolvemento. Pode se calquera nome. Por defecto é "development".
* BASE_URL: A url usada para acceder ao directorio "public" dende o navegador. Por defecto é "http://localhost" pero se a instalación se fixo nun subdirectorio ou noutro host, debes modificalo para, por exemplo: http://localhost/o-meu-proxecto/public
* SECURE_KEY: Clave secreta que podes usar para cifrar datos, etc. Por defecto xa se xenera unha de xeito aleatorio.

En calquera momento podes cambiar manualmente esa configuración editando o arquivo `env.local.php`.


# Documentación rápida

Unha vez instalado, atoparás os seguintes directorios:

* vendor: usado por composer para instalar aí todos os paquetes e dependencias
* app: onde se garda a túa aplicación (plantillas, controladores, modelos, tests, etc).
* public: todos os arquivos accesibles publicamente (css, imaxes, js, componentes de bower, etc) ademáis do "front controller" (index.php).


## App

A clase `App\App` (aloxada en app/App.php) é a que xestiona a túa páxina web. Básicamente encárgase de servir como contenedor de servizos, ou sexa: bases de datos, modelos, controladores, xestión de plantillas, e todo tipo de clases/servizos usados na web. Para iso implementa a interface [container-interop](https://github.com/container-interop/container-interop) o que permite poder usar calquera contenedor de dependencias. Por exemplo:

```php
$app = new App\App();

//Rexistrar servizos directamente:

$app->register('database', function () {
	$config = $app->config->get('database');
	return new MyDatabaseClass($config);
});

if ($app->has('database')) {
	$database = $app->get('database');
}

//Rexistrar outros sub-contenedores:

$container = new Container();
$container->register('templates', function () {
	return new MyTemplatesEngine('path/to/templates');
});

$app->add($containerInterop);

$templates = $app->get('templates');
```

Tamén serve para gardar a configuración deses servizos ou de calquera outra cousa. Existe a propiedade `$app->config` que carga e xestiona todo tipo de configuracions.

Ademáis tamén ten unha serie de métodos básicos:

* `$app->getNamespace()`: Devolve o namespace da aplicación (ou sexa "App"). Ademáis podes usalo para que che devolva outros namespaces ou clases relativas. Por exemplo `$app->getNamespace('Controllers\\Index')` devolve "App\Controllers\Index".
* `$app->getPath()`: Devolve o path onde está aloxada a aplicación. Podes usar argumentos para que che devolva rutas de arquivos ou subdirectorios. Por exemplo: `$app->getPath('arquivos/123', '3.pdf')` devolve algo parecido a "/var/www/o-meu-proxecto/app/arquivos/123/3.pdf"
* `$app->getUrl()`: O mesmo que getPath pero para devolver rutas http do directorio público. Útil para acceder a arquivos css, javascript, etc. `$app->getPublicUrl('assets/css', 'subdirectorio')` devolvería algo parecido a "http://localhost/o-meu-proxecto/public/assets/css/subdirectorio". Por defecto colle o valor que definiches como BASE_URL en env.local.php, pero podes cambialo usando `$app->setUrl()`.
* `$app->getEnvironment()`: Devolve o nome do entorno actual (development, production, etc). Por defecto colle o valor definido como ENVIRONMENT en env.local.php, pero podes cambialo usando `$app->setEnvironment()`. Útil para cargar distintas configuracións en distintos entornos. 

Por último ten dous métodos máis que sirven para executar a túa aplicación:

* `$app->runHttp()` Executa un `Http\Request` e devolve un `Http\Response`, ou sexa o típico
* `$app->runCli()` Executa a aplicación como liña de comandos. Para iso usase o sistema de tarefas de [Robo](https://github.com/Codegyre/Robo) xunto con algunhas [tarefas propias de FOL](https://github.com/fol-project/tasks). Todas estas tarefas estan creadas e pódense configurar en Tasks.php.

Todas esas tarefas podes lanzalas dende o comando `fol`, por exemplo, para listalas todas:

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
