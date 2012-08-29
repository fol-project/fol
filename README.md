Aqui tes o FOL
==============
(o resto da gaita xa é cousa túa)

FOL é framework escrito en PHP por Oscar Otero (http://oscarotero.com) como exercicio de deseño e como ferramenta para desenvolver experimentos e proxectos persoais. A intención é ter algo manexable, moi flexible e que permita xuntar librerías externas. Vamos, un microframework.
Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis lle interesa o proxecto. Aínda así, a documentación básica que hai en forma de comentarios no código está en inglés (cutre, of course).

* Rápido e lixeiro: Só carga as cousas que precisa en cada momento.
* Escrito en PHP 5.4. (Sempre é moito mellor e máis divertido traballar con versións novas que antigas)
* Lóxica sinxela e fácil de entender.
* Lévase ben con librarías externas: Calquera libraría que use o estándar PSR-0 para o autoloader non debería dar problemas. Ademáis é 100% compatible con Composer

Cal é a situación actual?
-------------------------

Penso que funciona ben, pero habería que probalo no mundo real, con proxectos reais. Aínda así está en beta ata que non faga algun proxecto con el.


Documentación rápida (para saber por onde van os tiros):
========================================================

No directorio raíz de FOL existen tres carpetas: apps, libs e assets.

* Na carpeta libs gardaranse as bibliotecas que uses nos teus proxectos (Composer xa as vai gardar aí automaticamente), entre elas as propias de Fol (no directorio Fol).
* Na carpeta apps está a aplicación ou aplicacións que forman o sitio web. Ou sexa, o propio código do teu sitio (plantillas, datos, etc)
* Na carpeta assets gárdanse todos os arquivos accesibles directamente dende o navegador (arquivos css, js, imaxes, etc). Ademáis, dentro existe unha subcarpeta chamada "cache" onde se gardarán os arquivos cacheados (son arquivos que precisan ser preprocesados antes). Por exemplo, se tes un preprocesador de CSS ou JS, gardaríase aí o resultado procesado, así como imaxes redimensionadas automaticamente.

O arquivo bootstrap.php é o que inicia o framework. Define 3 constantes:

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
A clase Errors rexistra todos os erros que se produzan ao longo da execución do script e lanza unha ErrorException. Deste modo centralízanse nun só lugar todos os erros que se produzan para poder manexalos moito mellor.


Apps
----

As aplicacións manexan o código específico do noso sitio web. Podes meter todo o sitio web nunha soa aplicación ou dividilo en distintas aplicacións (unha para o blog, outra para galeria de fotos, etc). Unha aplicación non é máis que unha clase que se instancia e se executa. Isto permite executar aplicacións unha dentro doutra, extendelas, etc. As aplicacións deben extender á clase Fol\App para que teñan dispoñibles as seguintes propiedades:

* $App->namespace: Devolve o namespace donde está aloxada a aplicación (Apps\Blog)
* $App->name: Devolve o nome da aplicación (Blog)
* $App->path: Devolve a ruta onde está aloxada a aplicación no servidor (www/apps/Blog/)
* $App->url: Devolve a url para acceder á raiz desa aplicación (p.e: /blog/)
* $App->assetsPath: Devolve a ruta onde está aloxada a carpeta de assets no servidor (www/assets/). Isto permite crear carpetas (ou subcarpetas) personalizadas para cada aplicación
* $App->assetsUrl: Devolve a url onde está aloxada a carpeta de assets (/public/)

Todos os frameworks e microframeworks teñen un sistema de Router, que permite especificar as rutas do noso sitio web e que functións (ou controladores) se asignan a cada ruta.
Aínda que Fol ten tamén unha clase para iso, non forma parte da aplicación, senón que é algo externo. O único que se require é que teña unha función chamada handle() que busca e executa o controlador apropiado para cada ruta pero esa función pode ser programada para que esa busca e configuración de controladores sexa como ti queiras. Podes usar a clase que ven en Fol (moi sinxeliña), construír ti mesmo a túa propia clase de router, buscar unha clase que che mole máis ou nin sequera usar router. Feel free :)

Para crear unha nova aplicación, debemos crear un directorio dentro da carpeta apps co nome da nosa aplicación e crear dentro un arquivo chamado App.php co seguinte código:

```php
namespace Apps\Blog;

class App extends \Fol\App {

	public function __construct () {
		//Codigo para executar no constructor da nosa aplicación (cargar a configuración, instanciar clases básicas, etc)
	}

	public function handle ($path) {
		//Codigo para manexar unha ruta concreta
	}
}

//Agora instanciamos a aplicación manualmente:
$Aplicacion = new Apps\Blog\App();

//ou usando unha función estática
$Aplicacion = Fol\App::create('Blog');

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


Router
------

A clase Router sirve para buscar a función (ou controladores) que se ten que executar por cada petición http. É unha clase moi sinxeliña que busca máis a rapidez e comodidade á hora de crear novos controladores (non precisas definir de anteman as rutas) que algo moi completo (e complexo).

A maioría dos frameworks MVC requiren que teñas que especificar que controladores usar en cada url, usando expresións regulares, etc. Neste caso non sería asi. O sistema é moito máis sinxelo, que busca directamente o controlador apropiado dependendo da url actual, sen necesidade de definir nada.

Existe unha clase controlador por defecto que é [app_namespace]\Controllers\Index e logo podes crear ti novas clases con máis controladores. Asi, se por exemplo, se a url actual é "blog/post/23", primeiro buscará se pode executar a función [app_namespace]\Controllers\Index::blog('post', 23) e se non se pode, mirará de executar [app_namespace]\Controllers\Blog::post(23).

A clase Router xa se encarga de examinar o controlador e ver se se pode usar ou non. Se a clase non se pode instanciar (p.e. é abstracta), ou a función é privada ou non existen todos os parámetros que se precisan, dará un erro 404 de que a páxina non existe.

Para poder usar un ou outro tipo de enrutamento, estes estan gardados en traits (unha das novidades de PHP 5.4). Polo tanto, podes crear varios traits con funcions distintas e logo especificar que traits queres usar na túa aplicación. Estes traits estan gardados en Fol\AppsTraits, e o trait que proporciona o sinxelo enrutamento de Fol está gardado en Fol\AppsTraits\SimpleRouter.

```php
namespace Apps\Web;

class App extends \Fol\App {
	use \Fol\AppsTraits\SimpleRouter;
}
```

```php
$Web = new Apps\Web\App();

$Request = Fol\Http\Request::createFromGlobals();

$Response = $Web->handle($Request);

$Response->send();
```

A clase Router tamén analiza a documentación de cada controlador para poder afinar máis cando executar ese controlador ou non. Un exemplo:

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