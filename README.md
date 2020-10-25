# MyRoute

MyRoute é uma ferramenta desenvolvida em PHP para realizar um controle muito simples de rotas

# Instalação

```bash
$ composer require 89bsilva/my-route
```

# Primeiro Passo

Importar o autoload do composer

```php
<?php
require './vendor/autoload.php';
```

# Criando o obejto MyRoute

Ao instânciar a classe será criado o arquivo .htaccess com as configurações de reescrita de url para o arquivo index.php que deverá ser criado manualmente.
Obs.: Caso o arquivo .htaccess já exista não será criado e para que o MyRoute funcione é necessário a reescrita de url para o arquivo de entrada com o valor da reescrita em $_GET['MyRouteURL']

```php
<?php
$routes = new MyRoute();
```

# Criando as Rotas

O MyRoute aceita e controla os tipos de requisições: GET, POST, PUT, DELETE e OPTIONS.

Para criar as rotas são disponibilizado os seguintes métodos:

```php
<?php
    #Método Utilizado para criar uma rota que responde a todos os tipos de requisições
    all(string $url, string $pageFilePath)

    #Método Utilizado para criar uma rota que responde a requisições do tipo GET
    get(string $url, string $pageFilePath)
    
    #Método Utilizado para criar uma rota que responde a requisições do tipo POST
    post(string $url, string $pageFilePath)
    
    #Método Utilizado para criar uma rota que responde a requisições do tipo PUT
    put(string $url, string $pageFilePath)
    
    #Método Utilizado para criar uma rota que responde a requisições do tipo DELETE
    delete(string $url, string $pageFilePath)
   
    #Método Utilizado para criar uma rota que responde a requisições do tipo OPTIONS
    options(string $url, string $pageFilePath)
        
    #Método Utilizado para criar uma rota que responde a requisições dos tipos passados em $to 
    to(array $to, string $url, string $pageFilePath)
        
    #Método Utilizado para proteger uma rota já criada com uma das chamadas acima
    guard(string $route, string $filePath)

```

Para criar é necessário informar no parâmetro **"$url"** o endereço da rota que deseja criar e no parâmetro **"$pageFilePath"** o endereço do arquivo que será incluido para lidar com a requisição
Obs.: O MyRoute leva em consideração como endereço base para fazer o include o nível anterior da pasta vendor do composer. 
As rotas seguem a ordem em que foram criadas, o MyRoute devolve a primeira rota que os critérios foram atendidos. 

#### Exemplo:

* Criar as rotas:
    * **/**, controlada pelo arquivo: **home.html**, quando o tipo de requisição for: **"GET"**
    * **/contato**, controlada pelo arquivo: **contato.html**, quando o tipo de requisição for: **"GET"**
    * **/contato/salvar**, controlada pelo arquivo: **salvar.php**, quando o tipo de requisição for: **"POST"** ou **"PUT"**

```php
<?php
$routes->get('/', 'home.html');
$routes->get('/contato', 'contato.html');
$routes->to(array('POST', 'PUT'), '/contato/salvar', 'salvar.php');
``` 

É possível utilizar uma classe e um método para controlar uma rota, para isso será necessário colocar **":"** logo após a extensão do arquivo e o nome da classe que será instanciada depois colocar  **"="** e o método que deverá ser chamado
Obs.: Para funcionar o nome da classe e o do método dever ser informado igualmente está no arquivo.

#### Exemplo:

* Criar as rotas:
    * **/**, controlada pela classe: **"MeuSite"**, metodo: **"home"** no arquivo: **site.php**, quando o tipo de requisição for: **"GET"**
    * **/contato**, controlada pela classe: **"MeuSite"**, metodo: **"contato"** no arquivo: **site.php**, quando o tipo de requisição for: **"GET"**
    * **/contato/salvar**, controlada pela classe: **"MeuSite"**, metodo: **"salvar"** no arquivo: **site.php**, quando o tipo de requisição for: **"POST"**

```php
<?php
$routes->get('/', 'site.php:MeuSite=home');
$routes->get('/contato', 'site.php:MeuSite=contato');
$routes->post('/contato/salvar', 'site.php:MeuSite=salvar');
``` 

É possível receber valores pela url, para isso será necessário colocar **":"** no inicio da identificação da rota, assim o que seria o endereço será o nome da variável disponível na inclusão do arquivo.
Obs.: Se for passado um nome de classe e método a variável será passada como argumento na chamada do método.

#### Exemplo:

* Criar as rotas:
    * **/api-class/user/id** onde id será variável, controlada pela classe: **"Api"**, metodo: **"read"** no arquivo: **api-class.php**, quando o tipo de requisição for: **"GET"**
    * **/api/user/id** onde id será variável, controlada pelo arquivo: **api.php**, quando o tipo de requisição for: **"GET"**

```php
<?php
#Será criado a váriavel $id e estará disponível para o arquivo api.php
$routes->get('/api/user/:id', 'api.php'); 

#Será chamado o método read de Api e passado $id como argumento da chamada. Api->read($id)
$routes->get('/api-class/user/:id', 'api-class.php:Api=read');
``` 

Também é possível criar uma rota não exata, para isto para colocar **"?"** como rota que a partir daí a rota aceitará qualquer caminho.
Obs.: Será disponibilizado uma variável (ou passado como argumento da chamada do método) $remaining com um array onde cada elemento será uma parte da rota após o sinal de ?.

#### Exemplo:

* Criar as rotas:
    * **/?** controlada pela classe: **"NotFound"**, metodo: **"noExist"** no arquivo: **404.php**, para qualquer tipo de requisição 

```php
<?php
/**
 * Se foi solicitado a rota: /a/b/c/d e não houver nenhuma configuração de rota anterior a rota abaixo
 * o MyRoute irá chamar o método noExist da Classe NotFound e passará o seguinte array como argumento. array('a','b','c','d')
*/
$routes->all('/?', '404.php:NotFound=noExist'); 
``` 

# Protegendo Rotas

Para proteger rotas é necessário chamar o método **"guard($route, $filePath)"** da instância MyRoute.
Antes dchamar o método **"guard()"** é necessário ter uma rota já criada.
O parâmetro $route é a URL que se deseja guardar.
O parâmetro $filePath é o endereço do arquivo que será incluido para realizar a proteção. Esse arquivo deve conter uma função/método que será chamado antes do MyRoute carregar o arquivo responsável por responder a rota. Essa função/método deve lidar com autorização e caso seja permitido entrar na rota deve ser retornado um valor TRUE, somente assim o MyRoute prosseguirá com as ações para entregar o arquivo responsável por responder a rota

#### Exemplo:
* Proteger a rota: **/api/?** utilizando o arquivo: **"api.php"** com uma função: **"proteger"**

```php
<?php
$route->guard('/api/?', 'api.php@proteger');
```

#### Exemplo:
* Proteger a rota: **/api/?** utilizando o arquivo: **"guardas.php"** com uma classe: **"GuardasDeRotas"** e o método: **"api"**

```php
<?php
$route->guard('/api/?', 'guardas.php:GuardasDeRotas=api');
```

Para facilitar a proteção de rotas é possível nomear as rotas, assim ao invés de passar a URL passa o nome da rota para o método guard.
Para nomear uma rota deve colocar o nome que deseja entre [] no início da URL da rota.
Obs.: As váriaveis de rota serão passadas na chamada da função/método

#### Exemplo:
* Criar a rota **"/agenda/:data/gerar-pdf"** nomear a rota para **"gPDF"**, controlada pela classe: **"MeuSite"**, metodo: **"agenda"** no arquivo: **site.php**, quando o tipo de requisição for: **"GET"**
* Proteger a rota: **/agenda/:data/gerar-pdf** utilizando o arquivo: **"guardas.php"** com uma classe: **"GuardasDeRotas"** e o método: **"gerarPDF"**

```php
<?php
#Criando a rota
$route->get('[gPDF]/agenda/:data/gerar-pdf', 'site.php:MeuSite=agenda');

#Criando um guarda para a rota
$route->guard('gPDF', 'guardas.php:GuardasDeRotas=gerarPDF');
```

# Último Passo

Logo após terminar de criar as rotas é necessário chamar o método **"activate"**. Após isso o MyRote estará configurado e ativo!

```php
<?php
$routes->activate(); 
``` 
### Autor

Bruno Silva Santana - <ibrunosilvas@gmail.com> - <https://github.com/ibrunosilvas>

### Licença

MyDatabase está licenciado sob a licença MIT - consulte o arquivo `LICENSE` para mais detalhes.
