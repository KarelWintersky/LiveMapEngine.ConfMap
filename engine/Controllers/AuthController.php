<?php

namespace Confmap\Controllers;

use Arris\AppLogger;
use Arris\AppRouter;
use Arris\DelightAuth\Auth\AttemptCancelledException;
use Arris\DelightAuth\Auth\AuthError;
use Arris\DelightAuth\Auth\EmailNotVerifiedException;
use Arris\DelightAuth\Auth\InvalidEmailException;
use Arris\DelightAuth\Auth\InvalidPasswordException;
use Arris\DelightAuth\Auth\TooManyRequestsException;
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
        $this->template->setTemplate("_auth.tpl");
    }

    /**
     * Показ формы логина
     *
     * @return void
     */
    public function view_form_login()
    {
        $this->template->assign("inner_template", 'auth/login.tpl');
    }

    /**
     * Коллбэк логина
     *
     * @return void
     */
    public function callback_login()
    {
        $expire = _env( 'AUTH.EXPIRE_TIME', 86400, 'int');

        try {
            App::$auth->login($_REQUEST['email'], $_REQUEST['password'], $expire);

            // echo 'User is logged in';

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
    public function callback_logout()
    {
        if (!App::$auth->isLoggedIn()) {
            die('Hacking attempt!'); //@todo: logging
        }

        $u_id = App::$auth->getUserId();
        $u_email = App::$auth->getEmail();

        App::$auth->logOut();

        AppLogger::scope('main')->debug("Logged out user {$u_id} ($u_email)");

        App::$flash->addMessage("success", "Успешно вышли из системы");

        App::$template->setRedirect( AppRouter::getRouter('view.frontpage') );
    }

}