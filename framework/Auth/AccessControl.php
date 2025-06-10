<?php

namespace LiveMapEngine\Auth;

use App\App;
use Arris\DelightAuth\Auth\Auth;
use Arris\DelightAuth\Auth\Exceptions\DatabaseError;

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

    /**
     * @throws DatabaseError
     */
    public function __construct()
    {
        $this->auth = new Auth(App::$pdo);

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