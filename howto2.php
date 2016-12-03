<?php
require_once('autoloader.php');

class HowTo2 extends BasePage
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
    <h1>View and save your schedule</h1>
    <p>
        If the schedule could be found, it will appear below the text fields.
        Currently, we support schedules from leaguelineup.com, pointstreak.com, and americanstreethockey.com.
        However, more websites may work with League Robot.
    </p>
    <p>
        The date, time, location (if one was found), team names, and a link to the schedule will be shown for each game. Events in the past appear in italics.
        Your next event is shown in bold text with a border around it.
    </p>
    <p style="text-align: center;">
        <a href="img/schedule1.png" title="Create an account to save your schedule." rel="lightbox">
            <img src="img/schedule1.png" alt="Create an account to save your schedule." class="framed" style="width: 50%; max-width: 1000px; margin: 0 auto;" />
        </a>
    </p>
    <p>
        Once you enter a schedule, you can create an account. Creating an account allows us to save your schedules for you. When you need to check your schedules, open up League Robot and log in.
        We'll load the latest version of each of your schedules.
    </p>
    <p>
        Additionally, we can send you updates to your schedules on a daily, weekly, or monthly basis.
        We can also send you an email whenever your schedule changes. You'll never need to check your schedules online again!
    </p>
</div>

<div class="section">
    <h1>Enter more schedules</h1>
    <p>
        You can enter up to ten schedules to track. All events will appear in a single grid.
        Events from each schedule will appear with a different text color to differentiate events from different schedules.
    </p>
    <p>
        If you'd like to remove a schedule from your itinerary, click the <em>Remove</em> link. When you're done making changes, click the <em>Save Changes</em> button.
        Schedules will not be removed until you save your changes. If you want to replace a schedule, you can simply paste a new URL or team name over the schedule you want to replace.
    </p>
</div>

<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}

$page = new HowTo2();

?>
