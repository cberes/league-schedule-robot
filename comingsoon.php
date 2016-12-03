<?php
require_once('autoloader.php');

class ComingSoon extends BasePage
{
    public $template;
    
    public function __construct()
    {
        parent::__construct(false, false);
        
        $markup = $this->View();
        
        // fill in the template  
        $this->template = new Template();
        $this->template->SetTitle('League Robot - Coming Soon');
        $this->template->SetBody($markup);
        $this->template->PrintMarkup();
    }
    
    public function View()
    {        
        // generate the form's html
        ob_start();
?>

<div class="section">
    <h1>Coming Soon</h1>
    <ul>
        <li>Support for iCal schedules.</li>
        <li>Change your email address and password.</li>
        <li>Manage your email settings.</li>
    </ul>
</div>

<div class="section-dark">
    <h1>Contact Us</h1>
    <p>
        Request new features and tell us about bugs by emailing
        <span class="select" onclick="selectText(this);">help&#64;leaguerobot&#46;com</span>.
    </p>
</div>

<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}

$page = new ComingSoon();

?>
