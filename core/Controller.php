<?php
//every controller must inherit from this class

class Controller
{
    // Load a view file and inject data into it
    public function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . "/../app/views/{$view}.php";

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            http_response_code(404);
            echo "View not found: {$view}";
        }
    }

    // Redirect to a specific URL
    public function redirect(string $url): void
    {
        header("Location: " . $url);
        exit();
    }

    // Return a JSON response for API or AJAX calls
    public function json($data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}