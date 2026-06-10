<?php

class App
{
    // map pages to controllers
    private array $controllerMap = [
        'home'         => 'HomeController',
        'login'        => 'AuthController',
        'register'     => 'AuthController',
        'logout'       => 'AuthController',
        'books'        => 'BookController', //if page=book go to bookcontroller
        'orders'       => 'OrderController',
        'clubs'        => 'ClubController',
        'events'       => 'EventController',
        'admin'        => 'AdminController',
        'reports'      => 'ReportController',
        'dashboard'    => 'HomeController',
        'api'          => 'BookController',   // AJAX search endpoint
        'loans'        => 'ClubController',
        'circles'      => 'ClubController',
        'notifications'=> 'HomeController',
        'settings'     => 'AdminController',
        'disputes'     => 'DisputeController',
    ];

    // main function to run the app
    public function run(): void
    {
      // get page and action from url
        $page   = $_GET['page']   ?? 'home'; //index.php?page=books&action=show
      
        // set default action if no action is provided in URL
        $defaultAction = (isset($this->controllerMap[$page]) && $page !== 'home') ? $page : 'index';
        $action = $_GET['action'] ?? $defaultAction;

        // url inputs cleaning
        $page   = preg_replace('/[^a-z0-9_]/', '', strtolower($page)); //make thew page name in lowercase
        $action = preg_replace('/[^a-zA-Z0-9_]/', '', $action); //make the action 

       // get controller name from page
        $controllerName = $this->controllerMap[$page] ?? 'HomeController';
        //dynamic direct to controllers
        $controllerFile = __DIR__ . "/../app/controllers/{$controllerName}.php";

        if (!file_exists($controllerFile)) {
            $this->show404("Controller file not found: {$controllerName}");
            return;
        }

        require_once $controllerFile;

       //making sure that the controller exist
        if (!class_exists($controllerName)) {
            $this->show404("Controller class {$controllerName} not found.");
            return;
        }
        
        $controller = new $controllerName();

        
        if (method_exists($controller, $action)) {
            $controller->$action(); //call the needed action
        } elseif (method_exists($controller, 'index')) {
            $controller->index();
        } else {
            $this->show404("Action '{$action}' not found in {$controllerName}"); // if no valid action show error page
        }
    }

    private function show404(string $message = ''): void
    {
        http_response_code(404);
        echo "<!DOCTYPE html><html><head><title>404 Not Found</title>
              <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
              </head><body class='bg-light d-flex align-items-center justify-content-center' style='min-height:100vh'>
              <div class='text-center'>
                <h1 class='display-1 fw-bold text-muted'>404</h1>
                <p class='lead'>Page Not Found</p>
                <p class='text-muted small'>{$message}</p>
                <a href='" . BASE_URL . "index.php?page=home' class='btn btn-primary'>Go Home</a>
              </div></body></html>";
    }
}
