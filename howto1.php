<?php
require_once('autoloader.php');

class HowTo1 extends BasePage
{
    public $template;
    
    public function __construct()
    {
        parent::__construct(false, false);
        
        $markup = $this->View();
        
        // get the script to insert into the page
        ob_start();
?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script type="text/javascript" src="js/slimbox/js/slimbox2.js"></script>
<link rel="stylesheet" href="js/slimbox/css/slimbox2.css" type="text/css" media="screen" />
<?php
        $scripts = ob_get_contents();
        ob_end_clean();
        
        // fill in the template  
        $this->template = new Template();
        $this->template->SetTitle('League Robot - How To');
        $this->template->SetBody($markup);
        $this->template->SetHead($scripts);
        $this->template->PrintMarkup();
    }
    
    public function View()
    {        
        // generate the form's html
        ob_start();
?>

<div class="section">
    <h1>Find your schedule</h1>
    <a href="img/url_field.png" title="Enter the schedule's URL." rel="lightbox-howto">
        <img src="img/url_field.png" alt="Enter the schedule's URL." class="framed" style="float: right; width: 33%; max-width: 600px; margin-top: 0; margin-right: 0;" />
    </a>
    <p>
        Using League Robot is simple. First, visit your website where your league's schedule is posted.
        If you can, find the schedule that lists every event or game for your team only.
    </p>
    <p>
        Typically, this page will contain a table, which contains rows for information about each game or event, such as the date, time, location, and names or participants.
        Copy the URL of this page from your web browser's address bar. Back on League Robot, paste the URL of your schedule in the left text field.
    </p>
</div>

<div class="section">
    <h1>Enter your team's name</h1>
    <a href="img/team_field.png" title="Enter the team's name." rel="lightbox-howto">
        <img src="img/team_field.png" alt="Enter the team's name." class="framed" style="float: left; width: 33%; max-width: 600px; margin-top: 0; margin-left: 0;" />
    </a>
    <p>
        In the middle text field, enter (or copy and paste) the name of your team exactly as it appears on your schedule. 
        Depending on your schedule, this value might not be your teams's name. It could be your name, for instance, or a different value used to identify your events.
    </p>
    <p>
        This name is used so we can identify which games are relevant to you.
        If you want to track two teams from the same schedule, enter one name in this field, paste the URL in the next row, and enter the other team's name in the text field on that row.
    </p>
</div>

<div class="section">
    <h1>Enter a nickname</h1>
    <a href="img/name_field.png" title="Enter a unique name." rel="lightbox-howto">
        <img src="img/name_field.png" alt="Enter a unique name." class="framed" style="float: right; width: 33%; max-width: 600px; margin-top: 0; margin-right: 0;" />
    </a>
    <p>
        Finally, enter a unique name for your team's schedule in the right-most text field. This value is used as something of a nickname.
        The specific value has no effect on your schedule.
    </p>
    <p>
        The nickname is required purely to help you identify games for each league. 
        It will appear with each of this team's games.
    </p>
    <p>
        Learn about <a href="howto2.php">how to view and save your schedule.</a>
    </p>
</div>

<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}

$page = new HowTo1();

?>
