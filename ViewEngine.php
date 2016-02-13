<?php

namespace Piece;

use \InvalidArgumentException;
use \RuntimeException;

/**
 * Class ViewEngine.
 * @package Piece
 */
class ViewEngine
{
    /**
     * Path to views folder.
     *
     * @var string
     */
    protected $viewsFolder = '';

    /**
     * Current view name.
     *
     * @var string
     */
    protected $viewName = '';

    /**
     * Params given to the current view.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Stores divided content
     * derived from view file.
     *
     * @var array
     */
    protected $viewsParts = [];

    /**
     * Template name.
     *
     * @var string
     */
    protected $templateName = '';

    /**
     * Template engine settings.
     *
     * @var array
     */
    protected $settings;

    /**
     * ViewEngine constructor.
     *
     * @param array $settings   ViewEngine settings.
     */
    public function __construct(array $settings)
    {
        // check view engine settings
        if (!
            isset($settings['viewsFolder'], $settings['fileExtension'])
        ) {
            throw new RuntimeException(
                'Some settings are not found, please, refer to documentation.');
        }
        // store given settings
        $this->settings = $settings;
    }

    /**
     * Render view over the transferred view filename
     * and parameters.
     *
     * @param string $viewName      View name.
     * @param array $params         View parameters.
     * @return string               Rendered HTML.
     */
    public function render($viewName, array $params = [])
    {
        // check if view name is a string
        if (! is_string($viewName)) {
            throw new InvalidArgumentException('View name must be a string.');
        }
        // filter views folder path
        $this->viewsFolder = $this->filterPathToViewsFolder($this->settings['viewsFolder']);
        // filter view name
        $this->viewName = $this->filterFilename($viewName);
        // check path to view
        $this->checkPath($this->viewsFolder, $this->viewName);
        // secure given params (if exists)
        if (! empty($params)) {
            $this->params = $this->secureParams($params);
        }
        // performs view file
        $viewContent = $this->prepareView($this->params);
        // separates content derived from the view
        $this->viewsParts = $this->separateViewContent($viewContent);
        // filter template name
        $this->templateName = $this->filterFilename($this->viewsParts[1]);
        // check path to tempalte
        $this->checkPath($this->viewsFolder, $this->templateName);
        // get view body
        $viewBody = ($this->viewsParts[2]) ? $this->viewsParts[2] : '';
        // render full HTML content
        return $this->prepareTemplate($viewBody, $this->params);
    }

    /**
     * This method import variables from given array
     * of parameters (if exists) and performs view file content.
     *
     * @param array $params     Given parameters.
     * @return string           View content.
     */
    protected function prepareView(array $params)
    {
        return $this->compileHtml($this->viewsFolder, $this->viewName, $params);
    }

    /**
     * This method import variables from given array
     * of parameters (if exists), inject view body and
     * performs tempalte file contents.
     *
     * @param string $viewBody  Current view's body.
     * @param array $params     Given parameters.
     * @return string           Rendered HTML.
     * @throws RuntimeException on error while template parsing.
     */
    protected function prepareTemplate($viewBody, array $params)
    {
        // compile HTML
        $templateContent = $this->compileHtml($this->viewsFolder, $this->templateName, $params);

        // replace include tag in template on view body content
        $html = preg_replace('/@embed;/', $viewBody, $templateContent);

        if (! $html) {
            throw new RuntimeException('An error occurred while template parsing.');
        }

        return $html;
    }

    /**
     * This method inject given parameters into view
     * or template files, perform internal зрз  scripts
     * and compile html.
     *
     * @param string $folderName    View's folder name.
     * @param string $fileName      Filename.
     * @param array $params         Given parameters.
     * @return string               HTML.
     */
    protected function compileHtml($folderName, $fileName, array $params)
    {
        // import variables from an array of parameters
        if (! empty($params)) {
            extract($params);
        }
        // start buffer
        ob_start();
        // include given file
        include $folderName . $fileName;
        // close buffer and return content
        return ob_get_clean();
    }

    /**
     * This method separates content derived from the view
     * on two parts: template name and the main view content.
     *
     * @param string $viewContent   View body.
     * @return array                Separated view body.
     * @throws RuntimeException if arise error while view parsing.
     */
    protected function separateViewContent($viewContent)
    {
        $viewsParts = [];

        if (
            ! preg_match('/@template\(\'([a-zA-Z.\/]+)\'\);\s*((\s*.*\s*)*)/', $viewContent, $viewsParts)
        ) {
            throw new RuntimeException('An error occurred while view parsing.');
        }

        return $viewsParts;
    }

    /**
     * Secure params given to the current view.
     * Convert all applicable characters to HTML entities.
     * @see https://en.wikipedia.org/wiki/Cross-site_scripting
     * @see http://php.net/manual/en/function.htmlentities.php
     *
     * @param array $params     Given params.
     * @return array            Filtered params.
     */
    protected function secureParams(array $params = [])
    {
        array_walk_recursive($params, function(&$value, $key){
            $value = htmlentities($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        });

        return $params;
    }

    /**
     * Check if valid given path.
     *
     * @param string $pathToFolder     Path to views folder.
     * @param string $filename         Filename.
     * @throws InvalidArgumentException if not valid.
     */
    protected function checkPath($pathToFolder, $filename)
    {
        if (! file_exists($pathToFolder . $filename)) {
            throw new RuntimeException(sprintf('Given path %s not valid.', $pathToFolder . $filename));
        }
    }

    /**
     * Filter given filename.
     *
     * @param string $filename  Filename.
     * @return string           Filtered filename.
     */
    protected function filterFilename($filename)
    {
        return ltrim($filename, DIRECTORY_SEPARATOR) . $this->settings['fileExtension'];
    }

    /**
     * Filter path to views folder.
     *
     * @param string $path      Path to views folder.
     * @return string           Filtered path to views folder.
     */
    protected function filterPathToViewsFolder($path)
    {
        if (! is_string($path)) {
            throw new InvalidArgumentException('Path to views folder must be a string.');
        }

        return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}
