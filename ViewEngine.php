<?php

namespace Piece;

use InvalidArgumentException;
use RuntimeException;
use Piece\Abstraction\ViewEngineAbstract;
use Piece\Helpers\ViewEngineHelper;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewEngine.
 * @package Piece
 */
class ViewEngine extends ViewEngineAbstract
{
    use ViewEngineHelper;

     /**
     *
     *
     * @var ResponseInterface instance
     */
    private $response;

    // dummy
    private $viewFolder = '';

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        // dummy
        $this->viewFolder = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $view
     * @param array $params
     * @param null|integer $statusCode
     * @return string
     */
    public function render($view, array $params = [], $statusCode = null)
    {
        //
        if (! is_string($view)) {
            throw new InvalidArgumentException('View name must be a string.');
        }

        //
        if (! $this->checkPath($this->viewFolder . $view) ) {
            throw new RuntimeException('View not found in views directory.');
        }

        //
        $this->viewName = $view;

        //
        $params = $this->secureParams($params);

        //
        $viewParts = [];

        // подготавливем вьюху
        $viewContent = $this->prepareView($params);

        // Выбираем из вьюхи имя темплейта, тело вьюхи
        $success = preg_match('/@template\(\'([a-zA-Z.\/]+)\'\);\s*((\s*.*\s*)*)/', $viewContent, $viewParts);

        if (! $success) {
            throw new RuntimeException('An error occurred while view parsing.');
        }

        // имя шаблона
        $this->templateName = $viewParts[1];
        // контент вьюхи
        $insideContent = $viewParts[2];

        //
        $htmlContent = $this->prepareTemplate($insideContent, $params);

        //
        if (! is_null($statusCode)) {
            $this->response = $this->response->withStatus($statusCode);
        }

        //
        $this->response->getBody()->write($htmlContent);

        //
        $this->response->send();
    }

    /**
     * @param array $params
     * @return string
     */
    protected function prepareView(array $params = [])
    {
        // извлекаем переменные
        if (! empty($params)) {
            extract($params);
        }

        // подключаем вьюху
        ob_start();
        include $this->viewFolder . $this->viewName;
        // закрываем буфер, сохраняем содержимое вьюхи
        $content = ob_get_clean();

        return $content;
    }

    /**
     * @param string $inside
     * @param array $params
     * @return string
     */
    protected function prepareTemplate($inside = '', array $params = [])
    {
        if (! is_string($inside)) {
            throw new InvalidArgumentException('View body must be a string.');
        }

        //
        if (! $this->checkPath($this->viewFolder . $this->templateName) ) {
            throw new RuntimeException('Template not found in views directory.');
        }

        // извлекаем переменные
        if (! empty($params)) {
            extract($params);
        }

        //
        ob_start();
        include $this->viewFolder . $this->templateName;
        $collectedContent = ob_get_clean();

        $htmlContent = preg_replace('/@embed;/', $inside, $collectedContent);

        if (! $htmlContent) {
            throw new RuntimeException('An error occurred while template parsing.');
        }

        return $htmlContent;
    }

    /**
     * This is dummy method,
     * it is not part of the PSR-7: HTTP message interfaces.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        // Dummy act.
    }
}
