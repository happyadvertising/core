<?php
/**
 * @package Basic App Core
 * @license MIT License
 * @link    http://basic-app.com
 */
namespace BasicApp\Core;

interface BaseControllerActionInterface
{

    function run(array $options = []);

    function render(string $view, array $params = []) : string;

}