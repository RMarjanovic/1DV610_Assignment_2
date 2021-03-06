<?php

class LoginController
{
    private static $cookieName = 'LoginView::CookieName';
    private static $cookiePassword = 'LoginView::CookiePassword';

    public function checkIfLoggedIn()
    {
        if(isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] == true && isset($_SESSION['browserInfo']) && $_SESSION['browserInfo']  == $_SERVER['HTTP_USER_AGENT'])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function attemptLogin($username, $password, $isLoginAttempt, $stayLoggedIn)
    {
        $db = new Database();
        if($isLoginAttempt)
        {
            if($this->checkUserInput($username, $password) === true && !empty($username) && !empty($password) && !isset($_SESSION['isLoggedIn']))
            {

                $loginSucceded = $db->findUser($username, $password);

                if($loginSucceded == true)
                {
                    $_SESSION['UserName'] = $username;
                    $_SESSION['Password'] = $password;
                    $_SESSION['isLoggedIn'] = true;
                    $_SESSION['browserInfo'] = $_SERVER['HTTP_USER_AGENT'];
                    $message = 'Welcome';
                }
                else
                {
                    $message = 'Wrong name or password';
                }

                if($this->checkIfLoggedIn() && $stayLoggedIn)
                {
                    $message = 'Welcome and you will be rembered';
			        setcookie(self::$cookieName, $_SESSION['UserName'], time() + 3600);
                    $passwordCookie = password_hash($password, PASSWORD_DEFAULT);
                    setcookie(self::$cookiePassword,  $passwordCookie, time() + 3600);
                    $db->addCookie($username, $passwordCookie);
                }
                return $message;
            }
            else if(isset($_SESSION['isLoggedIn']))
            {
                return '';
            }
            else
            {
                return $this->checkUserInput($username, $password);
            }
        }
        else
        {
            //TODO: Currently not working. cookiePassword is not being received correctly.
            if(isset($_COOKIE['LoginView::CookieName']) && isset($_COOKIE['LoginView::CookiePassword']) && !isset($_SESSION['isLoggedIn']))
            {
                $cookiePassword = $db->fetchCookie($username);
                if(isset($_COOKIE['LoginView::CookiePassword']) && $_COOKIE['LoginView::CookiePassword'] == $cookiePassword)
                {
                    $_SESSION['isLoggedIn'] = true;
                    $_SESSION['UserName'] = $_COOKIE['LoginView::CookieName'];
                    $_SESSION['Password'] = $_COOKIE['LoginView::CookiePassword'];
                    return 'Welcome back with cookie';
                }
                else if(isset($_SESSION['isLoggedIn']))
                {
                    return '';
                }
                else
                {
                    return 'Wrong information in cookies';
                }
            }
        }
    }

    public function logout()
    {
        if(isset($_POST['LoginView::Logout']))
        {
            if (isset($_SESSION['UserName']) && isset($_SESSION['isLoggedIn']))
            {
                unset($_SESSION['isLoggedIn']);
                setcookie('LoginView::CookieName', "", time() - 3600);
                setcookie('LoginView::CookiePassword', "", time() - 3600);
                return 'Bye bye!';
            }
        }
    }

    private function checkUserInput($username, $password)
    {
        if(empty($username))
        {
            return 'Username is missing';
        }
        else if(empty($password))
        {
            return 'Password is missing';
        }
        else
        {
            return true;
        }
    }
}
?>