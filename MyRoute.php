<?php
/**
 * Classe para controle simples de rotas
 * 
 * @author Bruno Silva Santana <ibrunosilvas@gmail.com>
 */
class MyRoute
{
    public $routes;
    protected $baseDir;

    public function __construct($prefixPageFilePath = '')
    {
        $myRouteDir    = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $packgeDir     = dirname($myRouteDir) . DIRECTORY_SEPARATOR;
        $vendorDir     = dirname($packgeDir) . DIRECTORY_SEPARATOR;
        $baseDir       = dirname($vendorDir) . DIRECTORY_SEPARATOR;
        
        if(!file_exists($baseDir . '.htaccess')) {
            copy($myRouteDir . DIRECTORY_SEPARATOR . '.htaccess' , $baseDir . '.htaccess'); 
        }

        $this->baseDir = $baseDir;
        $this->routes  = array(
            'GET'     => array(),
            'POST'    => array(),
            'PUT'     => array(),
            'DELETE'  => array(),
            'OPTIONS' => array()
        );
    }

    public function listAllRegistered()
    {
        $routesRegistered = array();

        foreach ($this->routes as $methodName => $route) {
            if(empty($route)) {
                continue;
            }

            $routesRegistered[$methodName] = $route;
        }

        return $routesRegistered;
    }

    public function all($url, $pageFilePath)
    {
        foreach ($this->routes as $methodName => $route) {
            $this->set($url, $pageFilePath, $methodName);
        }
    }

    public function to(array $to, $url, $pageFilePath)
    {
        foreach ($to as $methodName) {
            $this->set($url, $pageFilePath, strtoupper($methodName));
        }
    }

    public function get($url, $pageFilePath)
    {
        $this->set($url, $pageFilePath, 'GET');
    }

    public function post($url, $pageFilePath)
    {
        $this->set($url, $pageFilePath, 'POST');
    }

    public function put($url, $pageFilePath)
    {
        $this->set($url, $pageFilePath, 'PUT');
    }

    public function delete($url, $pageFilePath)
    {
        $this->set($url, $pageFilePath, 'DELETE');
    }

    public function options($url, $pageFilePath)
    {
        $this->set($url, $pageFilePath, 'OPTIONS');
    }

    protected function set($url, $pageFilePath, $requestMethod)
    {
        $hasClass  = strpos($pageFilePath, ':');
        $filePath  = !!$hasClass ?  substr($pageFilePath, 0, $hasClass) : $pageFilePath;
        $settings  = $this->getRouteURLSettings($url);
        $variables = $settings['variables'];
        $remaining = $settings['remaining'];
        
        unset($settings['variables']);
        unset($settings['remaining']);

        $this->routes[$requestMethod][] = array(
            'url'         => $url,
            'urlSettings' => $settings,
            'variables'   => $variables,
            'remaining'   => $remaining,
            'filePath'    => $this->baseDir . $filePath,
            'className'   => $this->getClassName($pageFilePath),
            'methodName'  => $this->getMethodName($pageFilePath),
        );
    }
    
    public function activate()
    {
        $url    = '/';
        $url   .= isset( $_GET['url'])     ? strip_tags(trim(filter_input(INPUT_GET,'url', FILTER_SANITIZE_URL))) : '/';
        $url    = substr($url, -1) === '/' ? substr($url, 0, -1)                                                  : $url;
        $method = $_SERVER['REQUEST_METHOD'];

        unset($_GET['url']);

        if(!array_key_exists($method, $this->routes)) {
            http_response_code(400);
            exit('<h1 style="padding:10px;">Error 400 Bad Request</h1>');
        }

        $key = $this->findActiveRouteKey(explode('/', $url), $method);
        
        if($key < 0) {
            http_response_code(404);
            exit('<h1 style="padding:10px;">Error 404 Not Found</h1>');
        }

        $this->setActiveRouteVariables(explode('/', $url), $key, $method);
        $this->loadActiveRouteFile($this->routes[$method][$key]);
        
        return $key;
    }

    protected function findActiveRouteKey($urlRequest, $method)
    {
        $indexMatch = -1;

        foreach ($this->routes[$method] as $key => $route) {
            $isMatch = $this->match($urlRequest, $route['urlSettings']);

            if($isMatch) {
                $indexMatch = $key;
                break;
            }
        }

        return $indexMatch;
    }

    protected function loadActiveRouteFile($route)
    {
        $remaining = $route['remaining'];

        extract($route['variables']);

        include_once $route['filePath'];

        if($route['className']) {

            $routerClass = new $route['className']();
            $methodCall  = '$routerClass->' . $route['methodName'] . '(';

            foreach ($route['variables'] as $varName => $value) {
                $methodCall .= '$' . $varName . ",";
            }

            $methodCall .= '$remaining);';
            
            eval($methodCall);
        }
    }
    
    protected function setActiveRouteVariables($urlRequest, $indexRoute, $method)
    {
        foreach ($this->routes[$method][$indexRoute]['variables'] as $varName => $pos) {
            $this->routes[$method][$indexRoute]['variables'][$varName] = $urlRequest[$pos];
        }
        
        $this->routes[$method][$indexRoute]['remaining'] = array_slice($urlRequest, $this->routes[$method][$indexRoute]['remaining']);
    }

    protected function match($urlRequest, $urlSettings)
    {
        $isMatch = true;

        foreach ($urlSettings as $key => $setting) {
            if(
                (!$setting['goFree'] && !isset($urlRequest[$key]))
                OR (!$setting['goFree'] && !isset($urlSettings[$key + 1]) && isset($urlRequest[$key + 1]))
                OR (!$setting['goFree'] && !$setting['isVar'] && $urlRequest[$key] != $setting['value'])
                OR ($setting['isVar'] && empty($urlRequest[$key]))
            ) {
                $isMatch = false;
                break;
            }
        }

        return $isMatch;
    }

    protected function getRouteURLSettings($url)
    {
        $explodeURL = explode('/', $url);
        $settings   = array();
        $variables  = array();
        $remaining  = -1;
        
        foreach ($explodeURL as $k => $string) {
            $isVar  = substr($string, 0, 1) === ':';
            $goFree = $string === '?';

            if($isVar) {
                $variables[substr($string, 1)] = $k;
            }

            if($goFree) {
                $remaining = $k;
            }

            $settings[$k] = array(
                'value'     => $string,
                'isVar'     => $isVar,
                'goFree'    => $goFree,
            );
        }

        $settings['variables'] = $variables;
        $settings['remaining'] = $remaining;

        return $settings;
    }

    protected function getClassName($pageFilePath)
    {
        $explodePageFilePath = explode(':', $pageFilePath);
        $className = isset($explodePageFilePath[1]) ? $explodePageFilePath[1] : false;
        $className = is_string($className) && strpos($className, '=') !== false ? substr($className, 0, strpos($className, '=')) : $className;
        
        return $className;
    }
    
    protected function getMethodName($pageFilePath)
    {
        $explodePageFilePath = explode('=', $pageFilePath);
        $methodName = isset($explodePageFilePath[1]) ? $explodePageFilePath[1] : 'index';
        
        return $methodName;
    }
}