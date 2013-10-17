Aqui tes o FOL
==============
(o resto da gaita xa é cousa túa)

FOL é framework escrito en PHP por Oscar Otero (http://oscarotero.com) como exercicio de deseño e como ferramenta para desenvolver experimentos e proxectos persoais. A intención é ter algo manexable, moi flexible e que permita xuntar librerías externas. Vamos, un microframework.
Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis daquí lle interesa o proxecto. Aínda así, a documentación básica que hai en forma de comentarios no código está en inglés (ou algo parecido).

Características:

* Rápido e lixeiro.
* Escrito en PHP 5.4.
* Pensado para combinar con bibliotecas externas e compatible con Composer.
```

Instalación
===========

O mellor xeito de instalalo é usando composer, primeiro instalas o framework (con create-project) e logo metes unha app baleira para comezar a traballar:

```
$ composer create-project fol/fol o-meu-proxecto
$ cd o-meu-proxecto
$ composer require fol/web
```

Unha vez feito isto, deberías poder ver algo no navegador (http://localhost/o-meu-proxecto).


Documentación rápida
====================

No directorio raíz de FOL existen dúas carpetas: libs e web

* Na carpeta libs gardaranse as bibliotecas externas, dependencias, e o propio código do Fol.
* Na carpeta web (ou a que escolleras para instalar a app) está a túa aplicación, ou sexa: plantillas, datos, etc, que forman o teu sitio web. Podes crear todas as aplicacións que queiras, cada unha na súa carpeta.

O arquivo bootstrap.php na raíz é o que inicia o framework e define 4 constantes:

* FOL_VERSION: A versión actual do framework
* BASE_PATH: A ruta base onde está aloxado o teu sitio web (ruta interna do servidor). Por exemplo "/var/www/o-meu-proxecto" (sen barra ao final)
* BASE_URL: A ruta base onde está aloxado o sitio web (ruta http do navegador). Por exemplo se accedemos por http://localhost/o-meu-proxecto, o seu valor sería "/o-meu-proxecto" (sen barra ao final)
* BASE_ABSOLUTE_URL: A parte da url para definir urls absolutas (por exemplo: http://localhost)
* ACCESS_INTERFACE: Se estamos executando fol por cli, sería "cli" senón "http"

Loader
------

Serve para cargar automaticamente o resto de clases empregando o estándar PSR-0. Tamén se ocupa de executar o autoloader de Composer.

#### Exemplo

```php
include(BASE_PATH.'libs/Fol/Loader.php');

Loader::register(); //Carga o autoload
Loader::setLibrariesPath(BASE_PATH.'libs'); //Define o directorio onde se gardan as bibliotecas
Loader::registerNamespace('Apps\\Web', BASE_PATH.'web'); //Definimos que o namespace Apps\Web é a carpeta apps (para que carge as aplicacións do noso sitio nese directorio)
Loader::registerComposer(); //Executa o autoloader de Composer (se o atopa)
```

Errors
------
A clase Errors rexistra os erros que se poidan producir na execución dos scripts e lanza callbacks. Deste modo centralízanse todos os erros para poder manexalos mellor.

#### Exemplo

```php
Errors::register(); //Inicia o rexistro de erros

//Rexistra unha función que se execute cando hai un erro. (podes rexistrar cantas funcións queiras)
Errors::pushHandler(function ($exception) {
	var_dump($exception);
	die();
});

//Mostra os erros en pantalla con toda a información útil:
Errors::displayErrors();
```

Tamén se pode rexistrar unha clase logger para gardar os erros en logs (Recomendo esta: https://github.com/Seldaek/monolog). Para iso debes definir a clase instanciada que se ocupe de xestionar os logs. O único requerimento é que a clase debe implementar a interface Psr\Log\LoggerInterface (https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)

Exemplo usando monolog:

```php
use Fol\Errors;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

//Iniciamos o rexistro de erros
Errors::register();

//Instanciamos o Logger de monolog
$log = new Logger('name');

//Asignamos o handler
$log->pushHandler(new StreamHandler(BASE_PATH.'/logs/debug.log', Logger::DEBUG));

//Asignamos agora o logger, para que lle pase os erros que vaia vendo
Errors::setLogger($log);
```

Apps
----

As aplicacións manexan o código do noso sitio web. Podes meter todo o sitio web nunha soa aplicación ou dividilo en distintas aplicacións (unha para o blog, outra para galeria de fotos, etc). Unha aplicación non é máis que unha clase que se instancia e se executa. Isto permite executar aplicacións unha dentro doutra, estendelas, etc. As aplicacións deben estender á clase Fol\App para que teñan dispoñibles as seguintes propiedades:

* $app->name: Devolve o nome da aplicación ("Web")
* $app->namespace: Devolve o namespace donde está aloxada a aplicación ("Apps\Web")
* $app->path: Devolve a ruta absoluta onde está aloxada a aplicación no servidor ("/var/www/web")
* $app->url: Devolve a url relativa para acceder á raiz desa aplicación (habería que engadirlle ao principio a constante BASE_URL) (p.e: "/")
* $app->assetsPath: Devolve a ruta onde está aloxada a carpeta de assets no servidor ("/var/www/web/assets").
* $app->assetsUrl: Devolve a url onde está aloxada a carpeta de assets (habería que engadirlle ao principio a constante BASE_URL) ("/web/assets")

Para crear unha aplicación, podemos crear un directorio novo e meter dentro un arquivo chamado App.php. Tamén podemos usar a aplicación que existe por defecto (chamada "Web") e que está dentro da carpeta "/web":

```php
namespace Apps\Web;

class App extends \Fol\App {

	public function __construct () {
		//Contructor da applicación (cargar a configuración, instanciar clases básicas, etc)
	}

	public function __invoke () {
		//Función que se executa ao invocar a app
	}
}
```

Ten en conta que as aplicacións cárganse co estándar PSR-0, igual que calquera outra biblioteca. A única diferencia é que se aloxan noutra carpeta distinta a libs. Polo tanto, a aplicación \Apps\Web\App, estaría no arquivo "/web/App.php". Se queres aloxar as aplicacións noutro directorio distinto, só tes que configurar a clase Loader para que busque o namespace "Apps\\Web" noutro directorio distinto. Esa configuración atópase no arquivo index.php:

```php
//Rexistramos a ubicación da raíz da aplicación (todas as aplicacións comezan polo namespace "Apps" + o nome da aplicación):
Loader::registerNamespace('Apps\\Web', BASE_PATH.'/web');

//Agora instanciamos a aplicación:
$aplicacion = new \Apps\Web\App();

//executamos a aplicación e mandamos o resultado
$aplicacion()->send();
```

Fol proporciona unha serie de utilidades mínimas para comezar a traballar. Se queres algo máis completo, podes instalalo vía composer. As utilidades básicas son clases que permiten crear un sistema MVC, xestionar "requests" e "responses", manexo de sesións, plantillas de php e carga de arquivos de configuración.

* Http: Conxunto de clases para manexar requests e responses (con headers, variables, cookies, etc).
* Router: Conxunto de clases para definir rutas asociadas a controladores
* App: Clase base estendida por todas as apps
* Config: Clase para cargar configuracións dende arquivos php
* Errors: Clase para xestionar erros (silencialos, debuguealos, etc)
* Session: Clase para manexar a sesión (inicializar, gardar datos, destruír, etc)
* Templates: Clase para cargar e renderizar plantillas. Son plantillas puras de php.
* Upload: Clase para xestionar a subida ou carga de arquivos. Soporta subidas por POST (variable $_FILES), cargar dende unha url ou pasandolle directamente o arquivo en base64.

Algunhas das clases máis importantes:

Fol\Http\Request
----------------

Con esta clase podemos recoller os datos dunha petición http e acceder a eles. Para crear o obxecto Request, podemos usar a función estática createFromGlobals():

```php
$request = Fol\Http\Request::createFromGlobals();

//Agora xa podemos acceder a todos os datos desta petición:

$request->get; //Obxecto que contén todos os parámetros enviados por GET
$request->get->get('nome'); //Devolve o parámetro enviado por GET 'nome'
$request->get->set('nome', 'novo-valor'); //Modifica o valor do parámetro 'nome'

//Outros obxectos dentro de Request son:

$request->post; //Para os parámetros POST
$request->server; //Para as variables do servidor (o equivalente a $_SERVER)
$request->headers; //Para as cabeceiras http
$request->cookies; //Cookies enviadas
$request->files; //Arquivos enviados
$request->parameters; //Para gardar parámetros manualmente
```

Tamén podemos crear requests sen usar as variables globais, util para facer subrequests ou testear a aplicación:

```php
//Creamos unha petición post pasandolle os datos para xerar un novo post
$request = Fol\Http\Request::create('/posts/create', 'POST', ['titulo' => 'Novo post']);

//Executamos esta petición na nosa app e obtemos a resposta:
$response = $app($request);
```


Fol\Http\Response
-----------------

Esta clase xenera as respostas que se enviarán ao navegador do usuario.

```php
//Xeramos un response dende o request. Isto é útil xa que xa lle mete o content-type adecuado, aínda que poderíamos crear un dende cero se o preferimos asi.
$response = $request->generateResponse();

//A clase Response contén dentro outros obxectos para xestionar partes específicas:
$response->headers; //Para enviar cabeceiras
$response->cookies; //Para enviar cookies

//Tamén podemos engadirlle o contido ou body da resposta:
$response->setContent('texto de resposta');

//E finalmente para enviar a resposta ao servidor, podemos usar a función "send":
$response->send();
```

Fol\Router\Router
-----------------

Xenera as distintas rutas do noso sitio web. Podemos definir esas rutas no contructor da nosa app:

```php
namespace Apps\Web;

use Fol\Http\Request;
use Fol\Router\Router;
use Fol\Router\RouteFactory;

class App extends \Fol\App {

	public function __construct () {
		//Creamos o enrutador
		$routeFactory = new RouteFactory($this);
		$this->router = new Router($routeFactory);

		//Definimos as distintas rutas (nome da ruta, url, controlador e outras opcions)
		$this->router->map('index', '/', 'Index::index', ['methods' => 'GET']);
		$this->router->map('contacto', '/about', 'Index::about');
	}

	public function __invoke () {
		//Creamos o request collendo os datos globais
		$request = Request::createFromGlobals();

		//Executamos a ruta e devolvemos a resposta
		return $this->router->handle($request, $this);
	}
}
```

Cando se fai unha petición http, o servidor (apache, ngnix, etc) redirixe todo a index.php e dende alí instanciase a nosa app e executase esa petición. A función Request::createFromGlobals() detecta se estamos en "cli" ou en "http" e xenera a petición collendo as variables dende $_GET, $_POST, $_FILES, etc (no caso de http) ou dende a variable $argv (no caso de cli). Iso permitenos executar a nosa web dende liña de comandos e facer tests para ver se todo funciona ben. Para iso debemos executar directamente o arquivo index.php pasándolle o método (GET, POST, PUT, DELETE, etc), a url e outras variables. Se non se especifica método, colle GET por defecto.

Facer unha petición GET por liña de comandos:

```
$ php index.php /posts/list
```

Facer unha petición GET por liña de comandos pasándolle parámetros:

```
$ php index.php "/posts/lists?order=id&page=2"
```
ou tamén:
```
$ php index.php /posts/lists GET --order id --page 2
```

Facer unha petición POST pasándolle tamén parámetros:

```
$ php index.php /posts/create POST --title "Título do posts"
```


INSTALACIÓN
===========

En Apache
---------
Unha vez descargado o FOL, xa debería funcionar, non hai que facer nada especial.

En Nginx
--------
Hai que configurar o rewrite, polo que tes que editar o arquivo de configuración (nginx/sites-enabled/default):

```
server {
	listen 80;

	charset utf-8;

	location / {
		rewrite ^(.*)$ /index.php last;
	}

	#App rewrite

	location /web/assets/ {
		rewrite ^(/web/assets/.*)$ $1 break;
	}

	location /web/assets/cache/ {
		if (!-f $request_filename) {
			rewrite ^/web/assets/cache/(.*)$ /web/assets/cache/index.php last;
		}
	}
}
```
