<?php

namespace Framework;

use App\Controllers\ErrorController;

class Router {
    protected $routes = [];

    /**
     * 
     * add a new route
     * @param string $method
     * @param string $uri
     * @param string $action
     * @return void 
     */

    public function registerRoute($method, $uri, $action){
      list($controller, $controllerMethod) = explode('@', $action);

        $this->routes [] = [
            'method'=> $method,
            'uri'=> $uri,
            'controller'=> $controller,
            'controllerMethod'=> $controllerMethod
        ];
    }

    /**
     * Add a GET Route
     * 
     * @param string $uri
     * @param string $controller
     * @retun void
     */

     public function get($uri, $controller){
        $this->registerRoute('GET', $uri, $controller);
     }

     
    /**
     * Add a POST Route
     * 
     * @param string $uri
     * @param string $controller
     * @retun void
     */

     public function post($uri, $controller){
        $this->registerRoute('POST', $uri, $controller);
     }

     
    /**
     * Add a PUT Route
     * 
     * @param string $uri
     * @param string $controller
     * @retun void
     */

     public function put($uri, $controller){
        $this->registerRoute('PUT', $uri, $controller);
    }

    
    /**
     * Add a DELETE Route
     * 
     * @param string $uri
     * @param string $controller
     * @retun void
     */

     public function delete($uri, $controller){
        $this->registerRoute('DELETE', $uri, $controller);
     }

     /**
      * Route the request
      *
      * @param string $uri
      * @param string $method
      * @return void
      */
      public function route($uri){
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        //check for _method input 
        if($requestMethod === 'POST' && isset($_POST['_method'])){
          //overwrite the request method with the value of _method
          $requestMethod = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {

          //split the current uri into segments

          $uriSegments = explode('/', trim($uri, '/'));

          //split the route URI into segments
          $routeSegments = explode('/', trim($route['uri'], '/'));

          $match = true; 

          //check if the number segments matches
          if (count($uriSegments) === count($routeSegments) && strtoupper($route['method'] === $requestMethod)){
            $params = [];

            $match = true;

            for($i = 0; $i < count($uriSegments); $i++){
              //if the uri's do not match and there is no param
              if($routeSegments[$i] !== $uriSegments[$i] && !preg_match('/\{(.+?)\}/', $routeSegments[$i])){
                $match = false;
                break;
              }

              //check for the param and add to the param array
              if(preg_match('/\{(.+?)\}/', $routeSegments[$i], $matches)){
                $params[$matches [1]] = $uriSegments[$i];
              }
            }

            if($match){
                //extract controller and controller method
              $controller = 'App\\Controllers\\'.$route['controller'];
              $controllerMethod = $route['controllerMethod'];

              //instatiate the controller and call the method
              $controllerInstance = new $controller();
              $controllerInstance->$controllerMethod($params);
              return;
            }
          }
        }

       ErrorController::notFound();
      }
}