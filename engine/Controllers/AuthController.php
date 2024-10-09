<?php

namespace Confmap\Controllers;

use Arris\AppLogger;
use Arris\AppRouter;
use Arris\DelightAuth\Auth\Exceptions\AttemptCancelledException;
use Arris\DelightAuth\Auth\Exceptions\AuthError;
use Arris\DelightAuth\Auth\Exceptions\EmailNotVerifiedException;
use Arris\DelightAuth\Auth\Exceptions\InvalidEmailException;
use Arris\DelightAuth\Auth\Exceptions\InvalidPasswordException;
use Arris\DelightAuth\Auth\Exceptions\TooManyRequestsException;
use Confmap\App;
use Confmap\Exceptions\AccessDeniedException;
use Psr\Log\LoggerInterface;

/**
 * Страницы и коллбэки авторизации
 */
class AuthController extends \Confmap\AbstractClass
{
    public function __construct($options = [], LoggerInterface $logger = null)
    {
        parent::__construct($options, $logger);
    }

    /**
     * Показ формы логина
     *
     * @return void
     */
    public function view_form_login(): void
    {
        $this->template->setTemplate("auth/login.tpl");
    }

    /**
     * Коллбэк логина
     *
     * @return void
     */
    public function callback_login(): void
    {
        $expire = _env( 'AUTH.EXPIRE_TIME', 86400, 'int');

        try {
            App::$acl->auth->login($_REQUEST['email'], $_REQUEST['password'], $expire);
        } catch (InvalidEmailException $e) {
            throw new AccessDeniedException('Неправильный E-Mail');
        } catch (InvalidPasswordException $e) {
            throw new AccessDeniedException('Неправильный пароль');
        } catch (EmailNotVerifiedException $e) {
            throw new AccessDeniedException('E-Mail не подтвержден, либо аккаунт не активирован');
        } catch (TooManyRequestsException $e) {
            throw new AccessDeniedException('Слишком много попыток авторизации. Подождите немного');
        } catch (AttemptCancelledException|AuthError $e) {
            throw new AccessDeniedException('Другая проблема: <br>' . $e->getMessage());
        }

        $ip = config('auth.ipv4');
        AppLogger::scope('main')->debug("Logged in user {$_REQUEST['email']} from {$ip}");

        App::$flash->addMessage("success", "Успешно залогинились");

        App::$template->setRedirect(AppRouter::getRouter('admin.index') );

    }

    /**
     * Коллбэк логаута, хотя переход на него делается через GET
     *
     * @return void
     * @throws AuthError
     */
    public function callback_logout(): void
    {
        if (!App::$acl->isLoggedIn()) {
            die('Hacking attempt!');
        }

        $u_id = App::$acl->auth->getUserId();
        $u_email = App::$acl->auth->getEmail();

        App::$acl->auth->logOut();

        AppLogger::scope('main')->debug("Logged out user {$u_id} ($u_email)");

        App::$flash->addMessage("success", "Успешно вышли из системы");

        App::$template->setRedirect( AppRouter::getRouter('view.frontpage') );
    }

}

# -eof- #