<?php
require_once('autoloader.php');

class MissingPage extends BasePage
{
    public $template;
    
    public function __construct()
    {
        parent::__construct(false, false);
        
        $markup = $this->View();
        
        // fill in the template  
        $this->template = new Template();
        $this->template->SetTitle('League Robot - Error');
        $this->template->SetBody($markup);
        $this->template->PrintMarkup();
    }
    
    public function View()
    {        
        // generate the form's html
        ob_start();
?>

<div class="section-dark">
    <h1>Page Not Found</h1>
    <p>
        We couldn't find what you were looking for. If you think this is a mistake, you can post some <em>Wanted</em> posters, or email us at 
        <span class="select" onclick="selectText(this);">help&#64;leaguerobot&#46;com</span>.
    </p>
    <p>
        Return to the <a href="index.php">main page</a>.
    </p>
</div>

<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}

$page = new MissingPage();

?>
