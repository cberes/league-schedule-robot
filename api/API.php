<?php

class API
{
    // 
    const DATE_FORMAT = 'n/j/Y';
    
    //
    const TIME_FORMAT = 'g:i A';
    
    // database connection
    protected $Database;
    
    public function __construct()
    {
        // error handler
        set_error_handler(['API', 'ErrorHandler']);
        
        // set the default timezone
        date_default_timezone_set('America/New_York');
                
        // connect to the database
        $this->Database = new MySqlDatabase(Utility::GetResource('dbhost'),
            Utility::GetResource('dbuser'), Utility::GetResource('dbpass'),
            Utility::GetResource('dbname'));
    }
    
    public function __destruct()
    {
        if ($this->Database != null)
            $this->Database->__destruct();
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
}

?>
