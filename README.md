Aqui tes o FOL
==============
(o resto da gaita xa é cousa túa)

FOL é framework escrito en PHP por Oscar Otero (http://oscarotero.com) como exercicio de deseño e como ferramenta para desenvolver experimentos e proxectos persoais. A intención é ter algo manexable, moi flexible e que permita xuntar librerías externas. Vamos, un microframework.
Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis daquí lle interesa o proxecto. Aínda así, a documentación básica que hai en forma de comentarios no código está en inglés (cutre, of course).

Características:

* Super rápido e lixeiro: Só carga as cousas que precisa en cada momento (lazy loader).
* Escrito en PHP 5.4. (Sempre é moito mellor e máis divertido traballar con versións novas que antigas)
* Lóxica sinxela e fácil de entender.
* Lévase ben con librarías externas: Calquera libraría que use o estándar PSR-0 non debería dar problemas (e se non o usa, podes definir as rutas manualmente). Ademáis é 100% compatible con Composer.

Por claridade, todas as instancias de clases comezan por maiúscula e o resto de variables en minúscula. Ou sexa:

```php
$Request = new Request();
$request = 'hello';

$Request->Get->get(); //"Get" é un obxecto e "get" unha funcion (podería ser tamén unha propiedade)
```

Cal é a situación actual?
-------------------------

Penso que funciona ben, pero habería que probalo no mundo real, con proxectos reais. Aínda así está en beta ata que non faga algun proxecto con el.


Documentación rápida (para saber por onde van os tiros):
========================================================

No directorio raíz de FOL existen tres carpetas: apps, libs e assets.

* Na carpeta libs gardaranse as bibliotecas externas, dependencias, etc, que uses nos teus proxectos (Composer xa as vai gardar aí automaticamente), entre elas as propias de Fol (no directorio Fol).
* Na carpeta apps está a aplicación ou aplicacións que forman o sitio web. Ou sexa, o propio código do sitio (plantillas, datos, etc). Por defecto hai unha aplicación chamada "Web".
* Na carpeta assets gárdanse todos os arquivos accesibles directamente dende o navegador (css, js, imaxes, etc). Na subcarpeta libs gardaríanse todas as bibliotecas externas (por exemplo, jquery, bootstrap, backbone, etc) e que poderían ser compartidas por varias aplicacións. Logo cada aplicación ten a súa propia subcarpeta para arquivos específicos. Por defecto temos a carpeta "web" para gardar os arquivos da aplicación "Web". Ademáis, dentro existe unha subcarpeta chamada "cache" onde se gardarían os arquivos cacheados (arquivos que precisan ser preprocesados antes como por exemplo arquivos less, sass, coffescript, imaxes redimensionadas dinamicamente, etc). A idea é que cando chames por un arquivo cacheado, por exemplo "/assets/web/cache/estilos.css" se ese arquivo non existe redirixe ao arquivo "/assets/web/cache/index.php" que se ocupa de xenerar o arquivo e gardalo con ese nome, polo que a proxima vez que se chame xa existe e non o ten que volver a xenerar. A redireccion faise usando un arquivo .htaccess.

O arquivo bootstrap.php na raíz é o que inicia o framework e define 3 constantes:

* FOL_VERSION: A versión actual do framework
* BASE_PATH: A ruta base onde está aloxado o teu sitio web (ruta interna do servidor). Por exemplo "/var/www/o-meu-sitio/"
* BASE_URL: A ruta base onde está aloxado o sitio web (ruta http do navegador). Por exemplo se accedemos por http://localhost/o-meu-sitio, o seu valor sería "/o-meu-sitio/"

Ademáis carga as clases Fol\Loader e Fol\Errors, para xestionar a carga de librarías e erros que haxa:

Loader
------

Serve para cargar automaticamente o resto de clases empregando o estándar PSR-0. Tamén é compatible co sistema de autoloader de Composer.
Ademais podemos rexistrar directorios específicos para calquera namespace ou clase concreta. Por exemplo todas as clases que se atopan no namespace App podemos configurar para que as busque na carpeta apps, fora de libraries.

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

Todos os frameworks e microframeworks teñen un sistema de enrutamento, que permite especificar as rutas do noso sitio web e que functións (ou controladores) se asignan a cada ruta.
Fol tamén proporciona esa funcionalidade, pero dun xeito moito máis sinxelo e flexible. Ademáis non forma parte da clase App, senón que é algo externo, polo que permite usar ese sistema ou crear o teu router personalizado (ou pasar de todo e non usar controladores).

Para crear unha nova aplicación, debemos crear un directorio dentro da carpeta apps co nome da nosa aplicación e crear dentro un arquivo chamado App.php co seguinte código:

```php
namespace Apps\Blog;

class App extends \Fol\App {

	public function __construct () {
		//Codigo para executar no constructor da nosa aplicación (cargar a configuración, instanciar clases básicas, etc)
	}

	public function handle ($request) {
		//Codigo que busca e executa un controlador dependendo do "request"
	}
}

//Agora instanciamos a aplicación:
$Aplicacion = new Apps\Blog\App();

//E executamos a aplicación
$Aplicacion->handle('/blog/view/34');
```

Existen ademáis diversos "traits" para extender as funcionalidades das apps (máis adiante explicoo co Router)


HTTP
----

Aínda que podes usar calquera outro servizo, Fol contén dunha serie de clases para traballar con Http, é dicir: recoller os "request" ou peticións http e todas as súas variables (cabeceiras, get, post, cookies, files, etc) e xerar "responses" ou respostas. Para iso temos a clase Fol\Http\Request e Fol\Http\Response.

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

Esta clase xenera as respostas que se enviarán ao navegador do usuario:

```php
$Response = new Fol\Http\Response;

//A clase Response contén dentro outros obxectos para xestionar partes específicas:

$Response->Headers; //Para enviar cabeceiras
$Response->Cookies; //Para enviar cookies

//Tamén podemos engadirlle o contido ou body da resposta:
$Response->setContent('texto de resposta');

//E finalmente para enviar a resposta ao servidor, podemos usar a función "send":
$Response->send();
```

Se coñeces o framework Symfony2 verás que o sistema é moi parecido (aínda que máis simplificado)


Router
------

A clase Router sirve para buscar a función (ou controladores) que se ten que executar por cada petición http. É unha clase moi sinxeliña que busca máis a rapidez e comodidade á hora de crear novos controladores (non precisas definir de anteman as rutas) que algo moi completo (e complexo).

A maioría dos frameworks MVC requiren que teñas que especificar que controladores usar en cada url, usando expresións regulares, etc. Neste caso non sería asi. O sistema é moito máis sinxelo, que busca directamente o controlador apropiado dependendo da url actual, sen necesidade de definir nada.

Existe unha clase controlador por defecto que é [app_namespace]\Controllers\Index e logo podes crear ti novas clases con máis controladores. Asi, por exemplo, se a url actual é "blog/post/23", primeiro buscará se pode executar a función [app_namespace]\Controllers\Index::blog('post', 23) e se non se pode, mirará de executar [app_namespace]\Controllers\Blog::post(23). Deste xeito podes crear todas as tuas rutas dentro do controlador Index ou agrupar determinadas rutas noutros controladores.

A clase Router xa se encarga de examinar o controlador e ver se se pode usar ou non. Se a clase non se pode instanciar (p.e. é abstracta), ou a función é privada ou non existen todos os parámetros que se precisan, dará un erro 404 de que a páxina non existe.

Fol trae unha serie de "traits" (unha das novidades de PHP 5.4) para as aplicacións e estan aloxados no namespace Fol\AppsTraits. Un deles é Fol\AppsTraits\SimpleRouter que engade o método "handle" para manexar as peticións e cargar os controladores deste xeito. Polo tanto para poder empregalo deberías definir a túa aplicación deste xeito:

```php
namespace Apps\Web;

class App extends \Fol\App {
	use \Fol\AppsTraits\SimpleRouter;
}
```

E logo cargala así:

```php
use Fol\Http\Request;

$Web = new Apps\Web\App();

$Response = $Web->handle(Request::createFromGlobals());

$Response->send();
```

SimpleRouter tamén analiza a documentación de cada controlador para poder afinar máis cando executar ese controlador ou non. Un exemplo:

```php
namespace Apps\Web\Controllers;

/**
 * @router method get
 */
class Saudo {

	/**
	 * @router method get post
	 * @router scheme http
	 * @router ajax true
	 */
	public function ola () {
		echo 'Ola mundo';
	}

	public function adeus () {
		echo 'Adeus mundo';
	}
}
```

Este exemplo define un controlador chamado Saudo con dous métodos: ola e adeus. A url "saudo/ola" executará o primeiro método e "saudo/adeus" o segundo.
Os comentarios que aparecen xusto antes do método "ola" definen que só se executará se estamos chamando a páxina co método GET ou POST, o scheme "http" e por ajax. Se non se cumple algunha desas condicións (chamamos a ese controlador sen ser por ajax, ou usamos https) xeneraríase un erro 404 de páxina non atopada.
Podes meter comentarios que afecten a todos os métodos metendo comentarios encima da clase. No exemplo definimos que esa clase só funcionan co método GET, polo que nin "ola" nin "adeus" se executarán chamándoos por POST (aínda que "ola" sí permite POST, a clase non o permite polo que non se executa)
A maneira de facer anotacións nos controladores sempre é igual: comezando polo tag @router seguido do nome da propiedade (method, scheme, ajax, port, ip) e o valor ou valores separados por un espazo

Outro trait de enrutamento é Fol\AppsTraits\PreprocessedFileRouter, que serve para preprocesar arquivos (assets). Este trait engade un novo método á applicación chamado "handleFile" e o que fai é coller o controlador [app_namespace]\Controllers\Files e executar o método que se chame igual que a extensión do arquivo preprocesado. Por exemplo:

Engadimos ese trait a maiores na nosa aplicación:

```php
namespace Apps\Web;

class App extends \Fol\App {
	use \Fol\AppsTraits\SimpleRouter;
	use \Fol\AppsTraits\PreprocessedFileRouter;
}
```

Definimos un controlador Files para manexar as peticións de arquivos:

```php
namespace Apps\Web\Controllers;

class Files {

	public function css ($file) {
		//codigo para preprocesar o arquivo css $file e devolver o resultado
	}

	public function js () {
		//codigo para preprocesar o arquivo js $file e devolver o resultado
	}

	public function jpg () {
		//codigo para preprocesar o arquivo jpg $file e devolver o resultado
	}
}
```

Logo en assets/web/cache/index.php (ou sexa o arquivo ao que se redirixe cando queremos cargar un arquivo que non existe en cache), executamos a petición do seguinte modo:

```php
use Fol\Http\Request;

$Web = new Apps\Web\App();

$Response = $Web->handleFile(Request::createFromGlobals());

$Response->send();
```

Cando a petición é "assets/web/cache/estilos.less.css", executa o controlador Apps\Web\Controllers\Files::css('estilos.less.css'); e esa función xa se encargaría de cargar o arquivo orixinal (que sería "assets/web/estilos.less.css"), procesalo e gardalo en "assets/web/cache/estilos.less.css", polo que a seguinte vez xa devolvería directamente ese arquivo cacheado sen volver a procesalo (a non ser que esteamos en fase de desenvolvemento polo que podemos facer que se procese en cada petición).
