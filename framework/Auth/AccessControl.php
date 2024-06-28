<?php

namespace LiveMapEngine\Auth;

use Arris\DelightAuth\Auth\Auth;

/**
 * К сожалению, придется писать отдельный класс, потому что MapManager использует
 * config('auth.*') для выяснения прав доступа. И если выносить его в отдельную библиотеку -
 * надо как-то передавать ему параметры авторизации (непонятной структуры).
 *
 * Или через DI, или через опции, или еще как-то, непонятно как. Например, экземпляр класса CurrentUser
 * заполняемый при инициализации системы авторизации.
 *
 * И это означает, что этот класс тянет за собой зависимость Arris\DelightAuth\Auth\Auth,
 * которую из ядра мы отключаем.
 *
 */
class AccessControl
{
    /**
     * @var Auth
     */
    public $auth;

    /**
     * @var CurrentUser
     */
    public CurrentUser $currentUser;

    public function __construct($connection_credentials = [])
    {
        if (empty($connection_credentials['hostname'])) {
            throw new \RuntimeException("Hostname unknown");
        }

        if (empty($connection_credentials['database'])) {
            throw new \RuntimeException("Database unknown");
        }

        if (empty($connection_credentials['username'])) {
            throw new \RuntimeException("User unknown");
        }

        if (empty($connection_credentials['password'])) {
            throw new \RuntimeException("Password is empty or not set");
        }

        $this->auth = new Auth(
            new \PDO(
                sprintf(
                    "mysql:dbname=%s;host=%s;charset=utf8mb4",
                    $connection_credentials['database'], $connection_credentials['hostname']
                ),
                $connection_credentials['username'],
                $connection_credentials['password']
            )
        );

        $this->currentUser = new CurrentUser($this->auth);
    }

    /**
     * Wrapper over isLoggedIn() from Auth
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->auth->isLoggedIn();
    }

    /**
     * Wrapper over hasRole() from Auth
     *
     * @param int $role
     * @return bool
     */
    public function hasRole(int $role)
    {
        return $this->auth->hasRole($role);
    }


    public function simpleCheckCanEdit():bool
    {

    }



}