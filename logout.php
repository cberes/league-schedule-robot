<?php
require_once('autoloader.php');

class LogOut extends BasePage
{
    public $template;
    
    public function __construct()
    {
        parent::__construct(true, false);
        
        // log out the user
        if ($this->User != null)
            $this->SetUser(null);
        
        $markup = $this->View();
        
        // fill in the template  
        $this->template = new Template();
        $this->template->SetTitle('League Robot - Log Out');
        $this->template->SetBody($markup);
        $this->template->PrintMarkup();
    }
    
    public function View()
    {        
        // generate the form's html
        ob_start();
?>

<div class="section">
    <h1>Thank you for using League Robot</h1>
    <p>
        You have been logged out. Return to the <a href="index.php">main page</a> to log in again. 
    </p>
</div>

<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}

$page = new LogOut();

?>
