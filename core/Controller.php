<?php

class Controller
{
    public function model($model)
    {
        $modelFile = dirname(__DIR__) . "/app/models/{$model}.php";

        if (!file_exists($modelFile)) {
            throw new Exception("Model {$model} não encontrado em {$modelFile}.");
        }

        require_once $modelFile;

        if (!class_exists($model)) {
            throw new Exception("Classe do model {$model} não encontrada.");
        }

        return new $model();
    }

    public function view($view, $data = [])
    {
        extract($data);

        $viewPath = dirname(__DIR__) . "/app/views/{$view}.php";

        if (!file_exists($viewPath)) {
            die("View '{$view}' não encontrada em {$viewPath}");
        }

        require_once $viewPath;
    }

    protected function redirect($route)
    {
        header('Location: ' . BASE_URL . $route);
        exit;
    }
}