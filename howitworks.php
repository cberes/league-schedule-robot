<?php
require_once('autoloader.php');

class HowItWorks extends BasePage
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
        $this->template->SetTitle('League Robot - How It Works');
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
    <h1>How League Robot Works</h1>
    <p>
        Sports schedules, event schedules, meeting schedules: do you have multiple schedule to check weekly or monthly?
        Even just one schedule that's updated frequently? League Robot has a solution for you.
    </p>
    <p style="text-align: center;">
        <a href="img/schedule2.png" title="Example schedule" rel="lightbox">
            <img src="img/schedule2.png" alt="Example schedule" class="framed" style="width: 67%; max-width: 1000px; margin: 0 auto;" />
        </a>
    </p>
    <p>
        See all of your game and event information together in one unified display.
        League Robot automatically downloads your schedules into one place.
        Easily organize your schedule and see your next events.
    </p>
    <p>
        League Robot can even email your schedule to you every week, or whenever it detects changes to your schedule.
        Never check another website for your schedules again.
        And you'll never miss another event because you forgot to find your next game time.
    </p>
    <p>
        Learn about <a href="howto1.php">how to use League Robot.</a>
    </p>
</div>

<!--
<div class="section">
    <h1>How League Robot Works</h1>
    <p>
        League Robot tracks sports and event schedules for you, and it notifies you of changes to the schedules.
        Save yourself the time and responsibility of checking websites for game and meeting times every week by using League Robot.
        Just tell League Robot where your schedule is located and your team's name, and we can send you an email whenever we detect changes to your schedules.
    </p>
    <p style="text-align: center;">
        <a href="img/schedule2.png" title="Sample schedule" rel="lightbox-howto">
            <img src="img/schedule2.png" alt="Sample schedule" class="framed" style="width: 50%; max-width: 1000px; margin: 0 auto;" />
        </a>
    </p>
    <h2>Find your schedule</h2>
    <a href="img/url_field.png" title="Enter the schedule's URL." rel="lightbox-howto">
        <img src="img/url_field.png" alt="Enter the schedule's URL." class="framed" style="float: right; width: 33%; max-width: 600px; margin-top: 0; margin-right: 0;" />
    </a>
    <p>
        Using League Robot is simple. First, visit your website where your league's schedule is posted.
        Typically, this page will contain a table, which contains rows for information about each game or event, such as date, time, location, and names or participants.
        If you can, find the schedule that lists the entire schedule for your team only (rather than the events for the current week, or games for every team in the league).
        Copy the URL of this page from your web browser's address bar. Back on League Robot, paste the URL of your schedule in the left text field.
    </p>
    <h2 style="clear: both;">Enter your team's name</h2>
    <a href="img/team_field.png" title="Enter the team's name." rel="lightbox-howto">
        <img src="img/team_field.png" alt="Enter the team's name." class="framed" style="float: left; width: 25%; max-width: 600px; margin-top: 0; margin-left: 0;" />
    </a>
    <p>
        In the middle text field, enter (or copy and paste) the name of your team exactly as it appears on your schedule. This is so we can identify which games are relevant to you.
        Depending on your schedule, this value might not be your teams's name. It could be your name, for instance, or a different value used to 
        If you want to track two teams from the same schedule, paste the URL in the next row and enter the other team's name in the text field on that row.
    </p>
    <h2 style="clear: both;">Enter a nickname</h2>
    <a href="img/name_field.png" title="Enter a unique name." rel="lightbox-howto">
        <img src="img/name_field.png" alt="Enter a unique name." class="framed" style="float: right; width: 25%; max-width: 600px; margin-top: 0; margin-right: 0;" />
    </a>
    <p>
        Finally, enter a unique name for your team's schedule in the right text field. This value is used as something of a nickname.
        The name does not matter; we require a name purely to help you identify games for each leage.
        This name will appear with each of this team's games.
    </p>
    <h2 style="clear: both;">View and save your schedule</h2>
    <p>
        If the schedule could be determined from the website you provided, your schedule will appear below. Currently, we support schedules from leaguelineup.com, pointstreak.com, and americanstreethockey.com.
        However, more websites may work with League Robot.
        The date, time, location (if one was found), team names, and a link to the schedule will be shown for each game. Events in the past appear in italics.
        Your next event is shown in bold text with a border around it.
    </p>
    <p style="text-align: center;">
        <a href="img/schedule1.png" title="Create an account to save your schedule." rel="lightbox-howto">
            <img src="img/schedule1.png" alt="Create an account to save your schedule." class="framed" style="width: 50%; max-width: 1000px; margin: 0 auto;" />
        </a>
    </p>
    <p>
        Once you enter a schedule, you can create an account. Creating an account allows us to save your schedules for you. When you need to check your schedules, open up League Robot and log in.
        We'll load the latest version of each of your schedules.
        Additionally, we can send you updates to your schedules on a daily, weekly, or monthly basis. We can also send you an email whenever your schedule changes. You'll never had to check your schedules online again!
    </p>
    <h2 style="clear: both;">Enter more schedules</h2>
    <p>
        You can enter up to ten schedules to track. All events will appear in a single grid. Events from each schedule will appear with a different text color to differentiate events from different schedules.
    </p>
    <p>
        If you'd like to remove a schedule from your itinerary, click the <q>Remove</q> link. When you're finished making changes, click the <q>Save Changes</q> button.
        Schedules will not be removed until you save your changes. If you want to replace a schedule, you can simply paste a new URL or team name over the schedule you want to replace.
    </p>
</div>
-->

<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}

$page = new HowItWorks();

?>
