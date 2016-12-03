<?php

class BasePage
{
    // 
    const DATE_FORMAT = 'n/j/Y';
    
    //
    const TIME_FORMAT = 'g:i A';
    
    // 
    const SEPARATOR = '(|]';
    
    //
    const PROGRESS_UPDATE_ID = '::PROGRESS_UPDATE_ID::';
    
    //
    const RESPONSE_KEY_SCRIPT = '::SCRIPT::';
    
    //
    const COOKIE_KEY = 'Anticynicism Interfiltration Loranthaceae Geomagnetician';
    
    //
    const COOKIE_NAME = 'autologin';
    
    protected $async;
    
    // database connection
    protected $Database;

    // current user object
    protected $User;
    
    public function __construct($usesSession, $requiresAuthentication, $adminsOnly = false)
    {
        // error handler
        set_error_handler(['BasePage', 'ErrorHandler']);
        
        // set the default timezone
        date_default_timezone_set('America/New_York');
        
        $this->async = (isset($_GET['async']) && $_GET['async'])
            || (isset($_POST['async']) && $_POST['async']);
                
        // connect to the database
        $this->Database = new MySqlDatabase(Utility::GetResource('dbhost'),
            Utility::GetResource('dbuser'), Utility::GetResource('dbpass'),
            Utility::GetResource('dbname'));
    
        // start the session if necessary
        if ($usesSession || $requiresAuthentication || $adminsOnly)
            session_start();
         
        // find the user, if necessary, or redirect to the homepage
        $this->User = null;
        if ($usesSession || $requiresAuthentication || $adminsOnly)
        {
            // get the user from the session
            if (isset($_SESSION['UserId']) && is_numeric($_SESSION['UserId']))
                $this->User = User::GetUserRow($this->Database, $_SESSION['UserId']);
            // check the cookie for a remembered user
            if ($this->User == null && ($user = self::LoadUserFromCookie($this->Database)) != null)
                $this->SetUser($user);
            // if there is no user and we need one, go to the login page
            if ($this->User == null && ($requiresAuthentication || $adminsOnly))
            {
                $_SESSION['UserId'] = null;
                header('Location: ' . Utility::GetResource('loginurl'));
            }
        }
    }
    
    public function __destruct()
    {
        if ($this->Database != null)
            $this->Database->__destruct();
    }
    
    public function CheckEmail($email)
    {
        // look up the user by email
        $email = trim(strip_tags($email));
        $users = $this->FindUsersByEmail($email);
        if ($users != null && count($users) > 0)
            echo 'emailnote=A user has already registered with that email address.';
        else
            echo 'emailnote=';
    }

    protected function FindUsersByEmail($email)
    {
        $email = $this->Database->Escape(trim($email));
        return User::GetUserRows($this->Database, "`email` = '$email'");
    }
    
    protected function IsPasswordValid($password)
    {
        return preg_match('/^.{5,}$/', $password) > 0;
    }
    
    protected function CreateUser($email, $password, &$errors)
    {
        if (!$this->IsPasswordValid($password))
        {
            $errors = ['Your password must be at least 5 characters long.'];
            return false;
        }
        
        if ($email) $email = trim($email);
        if ($this->FindUsersByEmail($email) != null)
        {
            $errors = ['A user with that email address has already registered.'];
            return false;
        }
        
        $user = new User();
        $user->SetEmail($this->Database->Escape($email));
        $user->SetPassword(hex2bin(sha1($password)));
        
        return $user;
        return true;
    }

    public static function ErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        try
        {
            $db = new MySqlDatabase(Utility::GetResource('dbhost'),
                Utility::GetResource('dbuser'), Utility::GetResource('dbpass'),
                Utility::GetResource('dbname'));
            
            $context = null;
            if ($errcontext)
            {
                // get the error context in a string
                ob_start();
                var_dump($errcontext);
                $context = ob_get_contents();
                ob_end_clean();
            }
            
            $log = new ErrorLog();
            $log->SetContext($context);
            $log->SetFile($errfile);
            $log->SetLine($errline);
            $log->SetMessage($errstr);
            $log->SetType($errno);
            $db->Insert($log->Insert($db), $newid);
        } 
        // catch any exceptions ... we don't want to keep making errors
        catch (Exception $e) { }
    
        // execute PHP internal error handler
        return false;
    }
    
    protected static function ForgetUser()
    {
        // delete the cookie by removing the value and setting the time to the past
        setcookie(self::COOKIE_NAME, '', time() - 3600);
    }
    
    protected function LoginUser($email, $password, $remember, &$errors)
    {
        $email = $this->Database->Escape($email);
        $password = hex2bin(sha1($password));
        
        // look up the user
        $users = User::GetUserRows($this->Database,
            "`email` = '$email' and `password` = '$password'");
        if ($users == null || count($users) == 0)
        {
            // user could not be found
            $errors = ['The username and password you entered could not be found.'];
            return false;
        }
        
        // save the user to the session
        $this->SetUser($users[0]);
        
        // set a cookie to remember the user
        if ($remember)
            self::RememberUser($users[0]);
        
        // delete any outstanding password-reset requests for this user
        $this->Database->Execute('DELETE FROM PasswordReset WHERE userid = ' . $users[0]->GetId());
        
        return true;
    }
    
    protected static function LoadUserFromCookie($db)
    {
        if (!isset($_COOKIE[self::COOKIE_NAME]))
            return null;
        
        // decrypt the code
        $plainTextCode = Encryption::Decrypt($_COOKIE[self::COOKIE_NAME], self::COOKIE_KEY);
        
        // parse the code
        $values = explode('|', $plainTextCode);
        if (count($values) == 2 && is_numeric(trim($values[0])))
        {
            // get the user
            $user = User::GetUserRow($db, $values[0]);
            if ($user != null && $user->GetPassword() == hex2bin(trim($values[1])))
                return $user;
        }
        
        // the cookie exists, but it's invalid; delete the cookie
        self::ForgetUser();
        
        return null;
    }
    
    protected static function RememberUser(User $user)
    {
        $value = Encryption::Encrypt($user->GetId() . '|' . bin2hex($user->GetPassword()),
            self::COOKIE_KEY);
        $expire = (new DateTime())->add(new DateInterval('P14D'));
        setcookie(self::COOKIE_NAME, $value, $expire->getTimestamp());
    }
    
    protected function SetUser($user)
    {
        $this->User = $user;
        if ($user != null)
            $_SESSION['UserId'] = $user->GetId();
        else
        {
            $_SESSION['UserId'] = null;
            self::ForgetUser();
        }
    }
}

?>
