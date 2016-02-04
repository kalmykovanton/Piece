<?php

namespace Piece\Abstraction;

/**
 * Class ViewEngineAbstract.
 * @package Piece\Abstraction
 */
abstract class ViewEngineAbstract
{
    /**
     *
     *
     * @var string
     */
    protected $viewName = '';

    /**
     *
     *
     * @var string
     */
    protected $templateName = '';

    /**
     * @param $view
     * @param array $params
     * @param null|integer $statusCode
     * @return mixed
     */
    abstract public function render($view, array $params = [], $statusCode = null);

    /**
     * @param array $params
     * @return string
     */
    abstract protected function prepareView(array $params = []);

    /**
     * @param string $inside
     * @param array $params
     * @return string
     */
    abstract protected function prepareTemplate($inside = '', array $params = []);
}
