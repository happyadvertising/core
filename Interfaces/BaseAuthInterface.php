<?php
/**
 * @copyright Copyright (c) 2018-2019 Basic App Dev Team
 * @link http://basic-app.com
 * @license MIT License
 */
namespace BasicApp\Interfaces;

interface BaseAuthInterface
{

    public static function userHasPermission($user, string $permission);

    public static function getLoginUrl();

    public static function getLogoutUrl();

    public static function getCurrentUserId();

    public static function getCurrentUser();

    public static function checkPassword(string $password, string $hash);

    public static function encodePassword(string $password);

    public static function login($user, $expire = null);

    public static function logout();

}