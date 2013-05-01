Aqui tes o FOL
==============
(o resto da gaita xa é cousa túa)

FOL é framework escrito en PHP por Oscar Otero (http://oscarotero.com) como exercicio de deseño e como ferramenta para desenvolver experimentos e proxectos persoais. A intención é ter algo manexable, moi flexible e que permita xuntar librerías externas. Vamos, un microframework.
Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis daquí lle interesa o proxecto. Aínda así, a documentación básica que hai en forma de comentarios no código está en inglés (ou algo parecido).

Características:

* Rápido e lixeiro.
* Escrito en PHP 5.4. (Sempre é moito mellor e máis divertido traballar con versións novas que antigas)
* Lévase ben con bibliotecas externas: Calquera bibliotecas que use o estándar PSR-0 non debería dar problemas (e se non o usa, podes definir as rutas manualmente). Ademáis é 100% compatible con Composer.

Por claridade, todas as instancias de clases comezan por maiúscula e o resto de variables en minúscula (camelCase). Ou sexa:

```php
$Request = new Request();
$request = 'hello';

$Request->Get->get(); //"Get" é un obxecto e "get" unha funcion
```


Documentación rápida
====================

No directorio raíz de FOL existen dúas carpetas: libs e apps

* Na carpeta libs gardaranse as bibliotecas externas, dependencias, etc, que uses nos teus proxectos (Podelas instalar con Composer ou manualmente). Tamén se atopan as propias bibliotecas de Fol.
* Na carpeta apps están as aplicacións por defecto que forman o sitio web. Ou sexa, o propio código do sitio (plantillas, datos, etc). Por defecto existe unha aplicación chamada "Web", aínda que podes crear máis aplicacións.

O arquivo bootstrap.php na raíz é o que inicia o framework e define 3 constantes:

* FOL_VERSION: A versión actual do framework
* BASE_PATH: A ruta base onde está aloxado o teu sitio web (ruta interna do servidor). Por exemplo "/var/www/o-meu-sitio" (sen barra ao final)
* BASE_URL: A ruta base onde está aloxado o sitio web (ruta http do navegador). Por exemplo se accedemos por http://localhost/o-meu-sitio, o seu valor sería "/o-meu-sitio" (sen barra ao final)
* BASE_ABSOLUTE_URL: A parte da url para definir urls absolutas (por exemplo: http://localhost)

Ademáis carga as clases Fol\Loader e Fol\Errors, para xestionar a carga de bibliotecas e erros que haxa:

Loader
------

Serve para cargar automaticamente o resto de clases empregando o estándar PSR-0. Tamén é compatible co sistema de autoloader de Composer.
Ademais podemos rexistrar directorios específicos para calquera namespace ou clase concreta. Por exemplo todas as clases que se atopan no namespace App podemos configurar para que as busque na carpeta apps.

#### Exemplo

```php
include(BASE_PATH.'libs/Fol/Loader.php');

Loader::register(); //Carga o autoload
Loader::setLibrariesPath(BASE_PATH.'libs'); //Define o directorio onde se gardan as bibliotecas
Loader::registerNamespace('Apps', BASE_PATH.'apps'); //Definimos que o namespace Apps está na carpeta apps (para que carge as aplicacións do noso sitio nese directorio)
Loader::registerComposer(); //Detecta as bibliotecas descargadas con Composer
```

Errors
------
A clase Errors rexistra todos os erros que se produzan ao longo da execución do script e lanza unha ErrorException. Deste modo centralízanse nun só lugar todos os distintos tipos de erros que se produzan para poder manexalos moito mellor.


Apps
----

As aplicacións manexan o código específico do noso sitio web. Podes meter todo o sitio web nunha soa aplicación ou dividilo en distintas aplicacións (unha para o blog, outra para galeria de fotos, etc). Unha aplicación non é máis que unha clase que se instancia e se executa. Isto permite executar aplicacións unha dentro doutra, extendelas, etc. As aplicacións deben extender á clase Fol\App para que teñan dispoñibles as seguintes propiedades:

* $App->namespace: Devolve o namespace donde está aloxada a aplicación (Apps\Blog)
* $App->name: Devolve o nome da aplicación (Blog)
* $App->path: Devolve a ruta onde está aloxada a aplicación no servidor (/www/apps/Blog/)
* $App->url: Devolve a url para acceder á raiz desa aplicación (p.e: /blog/)
* $App->assetsPath: Devolve a ruta onde está aloxada a carpeta de assets no servidor (/www/assets/blog/).
* $App->assetsUrl: Devolve a url onde está aloxada a carpeta de assets (/assets/blog/)

Para crear unha nova aplicación, debemos crear un directorio dentro da carpeta apps co nome da nosa aplicación e crear dentro un arquivo chamado App.php co seguinte código:

```php
namespace Apps\Blog;

class App extends \Fol\App {

	public function __construct () {
		//Contructor da applicación (cargar a configuración, instanciar clases básicas, etc)
	}

	public function handle ($Request) {
		//Función para manexar peticións (por exemplo nun sistema MVC)
	}
}

//Agora instanciamos a aplicación:
$Aplicacion = new \Apps\Blog\App();

//E executamos a aplicación
$Aplicacion->handle(\Fol\Http\Request::createFromGlobals());
```

Ten en conta que as aplicacións se cargan co estándar PSR-0, igual que calquera outra biblioteca. A única diferencia é que se aloxan na carpeta apps, en vez de libs. Polo tanto, a aplicación \Apps\Blog\App, estaría no arquivo apps/Blog/App.php. Se queres aloxar as aplicacións noutro directorio distinto, só tes que configurar a clase Loader para que busque o namespace "Apps" noutro directorio distinto. Esa configuración atópase en bootstrap.php, na raíz:

```php
Loader::registerNamespace('Apps', BASE_PATH.'myNewAppsDirectory');
```

HTTP
====

Fol contén dunha serie de clases para traballar con Http, é dicir: recoller os "request" ou peticións http e todas as súas variables (cabeceiras, get, post, cookies, files, etc) e xerar "responses" ou respostas. Para iso temos a clase Fol\Http\Request e Fol\Http\Response.

Request
-------

Con esta clase podemos recoller os datos dunha petición http e acceder a eles. Para crear o obxecto Request, podemos usar a función estática createFromGlobals():

```php
$Request = Fol\Http\Request::createFromGlobals();

//Agora xa podemos acceder a todos os datos desta petición:

$Request->Get; //Obxecto que contén todos os parámetros enviados por GET
$Request->Get->get('nome'); //Devolve o parámetro enviado por GET 'nome'
$Request->Get->set('nome', 'novo-valor'); //Modifica o valor do parámetro 'nome'

//Outros obxectos dentro de Request son:

$Request->Post; //Para os parámetros POST
$Request->Server; //Para as variables do servidor (o equivalente a $_SERVER)
$Request->Headers; //Para as cabeceiras http
$Request->Cookies; //Cookies enviadas
$Request->Files; //Arquivos enviados
$Request->Parameters; //Para gardar parámetros manualmente
```


Response
--------

Esta clase xenera as respostas que se enviarán ao navegador do usuario.
Aínda que se pode crear unha instancia de maneira individual, a clase Request xa xenera automáticamente unha clase response. Por exemplo, se no Request facemos unha petición de json (Content-Type: text/json) a clase Response automaticamente aplica ese content type.

```php
//Collemos o response xenerado polo request:
$Response = $Request->Response;

//A clase Response contén dentro outros obxectos para xestionar partes específicas:
$Response->Headers; //Para enviar cabeceiras
$Response->Cookies; //Para enviar cookies

//Tamén podemos engadirlle o contido ou body da resposta:
$Response->setContent('texto de resposta');

//E finalmente para enviar a resposta ao servidor, podemos usar a función "send":
$Response->send();
```

Rutas
-----

Para xenerar as distintas rutas do noso documento, podemos usar a clase Fol\Http\Router. Por exemplo, na nosa applicación, no constructor podemos definir as rutas:

```php
namespace Apps\Blog;

use Fol\Http\Router;

class App extends \Fol\App {

	public function __construct () {
		//Instanciamos o enrutador, pasandolle a url base da aplicación.
		$this->Router = new Router($this->url);

		//Definimos as distintas rutas (nome da ruta, url, controlador e outras opcions)
		$this->Router->map('index', '/', 'Index::index', ['methods' => 'GET']);
		$this->Router->map('contacto', '/about', 'Index::about');
	}

	public function handle ($Request) {
		//Resolvemos a petición actual e devolvemos o resultado
		return $this->handleRequest($this->Router, $Request);
	}
}
```
Cada petición http que se fai comezase executando o arquivo index.php que é o que inicializa as variables necesarias e instancia a app por defecto. Ese arquivo tamén se pode executar en liña de comandos o que facilita a execución de determinadas operacións directamente ou usando crons.

Execución dunha ruta en liña de comandos (/posts/list):

```
$ php index.php /posts/list
```

Execución dunha ruta con parámetros get (/posts/lists?order=id&page=2)

```
$ php index.php /posts/lists GET --order id --page 2
```

Execución dunha ruta con parámetros post:

```
$ php index.php /posts/create POST --title "Título do posts"
```

Para definir unha ruta que solo se execute en consola podes indicalo nas preferencias da ruta co parámetro "only-cli":

```php
$Router->map('lista-usuarios', 'users/list', 'Index::listUsers', ['only-cli' => true]);
```
