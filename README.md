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
Obs.: Caso o arquivo .htaccess já exista não será criado e para que o MyRoute funcione é necessário a reescrita de url para o arquivo de entrada com o valor da reescrita em $_GET['url']

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

```

Para criar é necessário informar no parâmetro **"$url"** o endereço da rota que deseja criar e no parâmetro **"$pageFilePath"** o endereço do arquivo que será incluido para lidar com a requisição
Obs.: O MyRoute leva em consideração como endereço base para fazer o include o nível anterior da pasta vendor do composer. 
As rotas seguem a ordem em que foram criadas, o MyRoute devolve a primeira rota que os critérios foram atendidos. 

#### Exemplo:

* Criar as rotas:
    * **/**, controlada pelo arquivo: **home.html**, quando o tipo de requisição for: **"GET"**
    * **/contato**, controlada pelo arquivo: **contato.html**, quando o tipo de requisição for: **"GET"**
    * **/contato/salvar**, controlada pelo arquivo: **salvar.php**, quando o tipo de requisição for: **"POST"**

```php
<?php
$routes->get('/', 'home.html');
$routes->get('/contato', 'contato.html');
$routes->post('/contato/salvar', 'salvar.php');
``` 

É possível utilizar uma classe e um método para controlar uma rota, para isso será necessário colocar **":"** logo após a extensão do arquivo e o nome da classe que será instanciada depois colocar  **"="** e o método que deverá ser chamado
Obs.: Para funcionar o nome da classe e o do método dever ser informado igualmente está no arquivo.

#### Exemplo:

* Criar as rotas:
    * **/**, controlada pela classe: **"MeuSite"**, metodo: **"home"** no arquivo: **site.php**, quando o tipo de requisição for: **"GET"**
    * **/contato**, controlada pela classe: **"MeuSite"**, metodo: **"contato"** no arquivo: **site.php**, quando o tipo de requisição for: **"GET"**
    * **/contato**, controlada pela classe: **"MeuSite"**, metodo: **"salvar"** no arquivo: **site.php**, quando o tipo de requisição for: **"GET"**

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
