<?php

namespace Confmap\Middlewares;

use Arris\Helpers\Server;
use Confmap\AbstractClass;
use Confmap\App;
use Confmap\Exceptions\AccessDeniedException;

class AuthMiddleware extends AbstractClass
{
    /**
     *
     *
     * @param $uri
     * @param $route_info
     * @return void
     */
    public function check_not_logged_in($uri, $route_info): void
    {
        if (App::$acl->isLoggedIn()) {
            Server::redirect('/');
        }
    }

    /**
     * @param $uri
     * @param $route_info
     * @return void
     */
    public function check_is_logged_in($uri, $route_info): void
    {
        if (!App::$acl->isLoggedIn()) {
            throw new AccessDeniedException("Вы не авторизованы. <br><br>Возможно, истекла сессия авторизации.");
        }
    }

    /**
     * @param $uri
     * @param $route_info
     * @return void
     */
    public function check_is_admin_logged($uri, $route_info): void
    {
        if (!App::$acl->isLoggedIn() && App::$acl->auth->hasRole(\LiveMapEngine\Auth\AuthRoles::ADMIN)) {
            throw new AccessDeniedException("У вас недостаточный уровень допуска. <br><br>Возможно, истекла сессия авторизации.");
        }

    }



}