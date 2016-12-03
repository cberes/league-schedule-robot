<?php
require_once('autoloader.php');

class ScheduleCollector extends BasePage
{
    public $template;
        
    private $loginFields;
        
    private $teamFields;
        
    private $userFields;
    
    const SCHEDULES_MAX_COUNT = 10;
    
    public function __construct()
    {
        parent::__construct(true, false);
        
        $this->loginFields = ['loginEmail', 'loginPassword'];
        
        $this->teamFields = ['teamUrl', 'teamName', 'teamUnique', 'teamColor', 'teamId'];
        
        $this->userFields = ['email', 'password', 'fname', 'lname'];
        
        // schedule stuff
        $schedules = null;
        $itinerary = null;
        
        // errors
        $errorString = null;
        $loginErrorString = null;
           
        // attempt a login if the the user tried to log in
        $loginUser = true;
        $loginErrors = [];
        foreach ($this->loginFields as $field)
        {
            if (!isset($_POST[$field]))
                $loginUser = false;
        }
        if ($loginUser)
        {
            $this->LoginUser($_POST['loginEmail'], $_POST['loginPassword'],
                Utility::TextOrNull('loginRemember', $_POST), $loginErrors);
            
            // put any errors into HTML
            if (isset($loginErrors) && count($loginErrors) > 0)
                $loginErrorString = $this->ShowErrors($loginErrors);
        }
        
        $showNewUserMessage = false;
        if (!$loginUser)
        {            
            // save and parse the schedules
            $showNewUserMessage = $this->User == null;
            $this->Commit($schedules, $itinerary, $errors);
            $showNewUserMessage = $showNewUserMessage && $this->User != null;
            
            // get the schedule
            $output = $this->GetSchedule($schedules, $itinerary);
            if ($this->async)
            {
                if (!Utility::IsEmpty($output))
                    echo $output;
                return;
            }
            
            // put any errors into HTML
            if (isset($errors))
                $errorString = $this->ShowErrors($errors);
        }
            
        if (($schedules == null || count($schedules) == 0) && $this->User != null)
        {
            // query for existing schedules
            $schedules = Schedule::GetScheduleRows($this->Database, 'userid = ' . $this->User->GetId());
            if ($schedules != null)
            {
                for ($s = 0; $s < count($schedules); ++$s)   
                    $schedules[$s]->UpdateSchedule();
            }
        }
        
        // put the company into some HTML
        $markup = $this->View($schedules, $showNewUserMessage, $errorString, $loginErrorString);
        
        // get the script to insert into the page
        ob_start();
?>
<script type="text/javascript">
//<![CDATA[
    function onRemoveLinkClick(index)
    {
        var teamName = document.getElementById('teamName_' + index);
        var teamUnique = document.getElementById('teamUnique_' + index);
        var teamUrl = document.getElementById('teamUrl_' + index);
        
        if (teamName != null) teamName.value = '';
        if (teamUnique != null) teamUnique.value = '';
        if (teamUrl != null) teamUrl.value = '';
    }
    function onSelectScheduleChange(element)
    {
        var index = element.selectedIndex;
        var schedule = element.options[index].value;
        var table = document.getElementById('schedule');
        var rowCount = table.rows.length;
        for (var i = 1; i < rowCount; ++i) // skip head row
            table.rows[i].style.display = (index == 0 || table.rows[i].cells[0].innerHTML.trim() == schedule) ? 'table-row' : 'none';
    }
    function replacePlaceholder()
    {
        if (isPlaceholderSupported()) return;
        document.getElementById('scheduleFormHeader').style.display = 'table-row';
        var element = document.getElementById('labelLoginEmail');
        if (element) element.style.display = 'block';
        element = document.getElementById('labelLoginPassword');
        if (element) element.style.display = 'block';
        element = document.getElementById('labelEmail');
        if (element) element.style.display = 'block';
        element = document.getElementById('labelPassword');
        if (element) element.style.display = 'block';
        element = document.getElementById('labelName');
        if (element) element.style.display = 'block';
    }
//]]>
</script>
<?php
        $script = ob_get_contents();
        ob_end_clean();
          
        // fill in the template  
        $this->template = new Template();
        $this->template->SetTitle('League Robot - Sports League Game Tracker');
        $this->template->SetBody($markup);
        $this->template->SetHead($script);
        $this->template->SetLoadFunc('replacePlaceholder();');
        if ($schedules != null && count($schedules) > 0)
        {
            $this->template->SetLoadFunc("replacePlaceholder();AjaxPost('index.php', GetRequestParams('scheduleForm'), PopulateFormAdvanced, '"
                . self::PROGRESS_UPDATE_ID . "', 'loading');");
        }
        $this->template->SetShowUserLinks($this->User != null);
        $this->template->PrintMarkup();
    }
    
    public function GetSchedule($schedules, $itinerary)
    {
        // get the field values
        $colors = isset($_POST['teamColor']) ? $_POST['teamColor'] : null;
        $colorCount = $colors != null ? count($colors) : 0;
        $teamCount = count($schedules);
        
        // build each set of values into a schedule
        $colorsByName = [];
        $scheduleOptions = ['<option>All</option>'];
        $fieldsFilledIn = false;
        $output = false;
        for ($t = 0; $t < $teamCount; ++$t)
        {
            // if these three key fields are blank, then the schedule is blank
            // and there's nothing to output
            if ($schedules[$t]->GetUrl() != null && $schedules[$t]->GetName() != null && $schedules[$t]->GetTeam() != null)
                $output = true;
            
            if (!$schedules[$t]->IsValid()) continue;
            if ($t >= $colorCount) continue;
                        
            // clean up the values
            $color = null;
            if (preg_match(Utility::REGEX_COLOR, $colors[$t]) == 1)
                $colorsByName[$schedules[$t]->GetName()] = $colors[$t];
            
            // save the team name
            $scheduleOptions[] = '<option>' . htmlspecialchars($schedules[$t]->GetName()) . '</option>';
        }
        
        // build the table
        $table = null;
        if ($itinerary != null && $itinerary->GetGameCount() > 0)
            $table = $this->BuildScheduleTable($itinerary->GetGames(), $colorsByName);
        
        // the output
        $pairs = [];
        $pairs[] = 'loading.style.visibility=hidden';
        
        // if every schedule was blank, or there were no schedules, output nothing
        if (!$output)
            return implode(self::SEPARATOR, $pairs);
        
        if ($table == null)
        {
            $pairs[] = 'selectScheduleDiv.style.display=none';
            $pairs[] = 'scheduleDiv.innerHTML=No games found or no valid schedules found.';
        }
        else
        {
            $pairs[] = 'selectScheduleDiv.style.display=block';
            $pairs[] = 'selectSchedule.innerHTML=' . implode(PHP_EOL, $scheduleOptions);
            $pairs[] = 'scheduleDiv.innerHTML=' . $table;
        }
        $pairs[] = 'scheduleWrapper.style.display=block';
        return implode(self::SEPARATOR, $pairs);
    }
    
    private function BuildScheduleTable($games, $colorsByName)
    {
        if ($games == null) return null;
        $gameCount = count($games);
        if ($gameCount == 0) return null;
        
        // get this user's date/time formats
        $dateFormat = self::DATE_FORMAT;
        $timeFormat = self::TIME_FORMAT;
        if ($this->User != null)
        {
            $loc = Localization::GetLocalizationRows($this->Database, 'userid = ' . $this->User->GetId());
            if ($loc != null)
            {
                $dateFormat = $loc[0]->GetShortDateFormat();
                $timeFormat = $loc[0]->GetTimeFormat();
            }
        }
        
        $nextGame = false;
        
        // generate the form's html
        ob_start();
?>

<table id="schedule" style="text-align: center; width: 100%;">
    <thead>
        <tr>
            <th>
                Date
            </th>
            <th>
                Time
            </th>
            <th>
                Location
            </th>
            <th>
                Home
            </th>
            <th>
                Away
            </th>
            <th>
                League
            </th>
        </tr>
    </thead>
    <tbody>
<?php for ($g = 0; $g < $gameCount; ++$g): ?>
        <tr
            <?php if ($games[$g]->GetGroupId() != null && array_key_exists($games[$g]->GetGroupId(), $colorsByName)): ?>
                style="color: <?= $colorsByName[$games[$g]->GetGroupId()] ?>;"
            <?php endif; ?>
            <?php if ($games[$g]->GetTime() != null && $games[$g]->GetTime() < new DateTime()): ?>
                class="past"
            <?php elseif ($games[$g]->GetTime() != null && !$nextGame): ?>
                <?php $nextGame = true; ?>
                class="next"
            <?php endif; ?>
            >
            <td style="display: none;">
                <?= htmlspecialchars($games[$g]->GetGroupId()) ?>
            </td>
            <td>
                <?= $games[$g]->GetTime()->format($dateFormat) ?>
            </td>
            <td>
                <?= $games[$g]->GetTime()->format($timeFormat) ?>
            </td>
            <td>
                <?= htmlspecialchars($games[$g]->GetLocation()) ?>
            </td>
            <td>
                <?= htmlspecialchars($games[$g]->GetHomeTeam()) ?>
            </td>
            <td>
                <?= htmlspecialchars(join(', ', $games[$g]->GetVisitingTeams())) ?>
            </td>
            <td>
                <?php if ($games[$g]->GetSource() != null): ?>
                <a href="<?= $games[$g]->GetSource() ?>" title="View schedule source." target="_blank">
                <?php endif; ?>
                <?= htmlspecialchars($games[$g]->GetLeague()) ?>
                <?php if ($games[$g]->GetSource() != null): ?>
                </a>
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
<?php endfor; ?>
</table>

<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function View($schedules, $showNewUserMessage, $errors, $loginErrors)
    {
        // colors
        $colors = ['red', 'darkorange', 'green', 'darkblue', 'purple', '#222', 'saddlebrown', 'darkred', 'darkkhaki', 'royalblue'];
        
        // onchange command
        $onchange = "AjaxPost('index.php', GetRequestParams('scheduleForm'), PopulateFormAdvanced, '"
                . self::PROGRESS_UPDATE_ID . "', 'loading');";
        
        // check if the user is registered
        $isLoggedIn = $this->User != null;
        
        // get the values for the schedule here so there's less code mixed with the HTML
        $scheduleValues = [];
        $scheduleCount = $schedules != null ? count($schedules) : 0;
        for ($i = 0; $i < self::SCHEDULES_MAX_COUNT; ++$i)
        {
            $isSchedule = $scheduleCount > $i;
            $scheduleValues[$i] = [];
            $scheduleValues[$i]['ID'] = $isSchedule ? $schedules[$i]->GetId() : null;
            $scheduleValues[$i]['LEAGUE'] = $isSchedule ? htmlspecialchars($schedules[$i]->GetLeague()) : null;
            $scheduleValues[$i]['NAME'] = $isSchedule ? htmlspecialchars($schedules[$i]->GetName()) : null;
            $scheduleValues[$i]['TEAM'] = $isSchedule ? htmlspecialchars($schedules[$i]->GetTeam()) : null;
            $scheduleValues[$i]['URL'] = $isSchedule ? htmlspecialchars($schedules[$i]->GetUrl()) : null;
            $scheduleValues[$i]['ERRORS'] = $isSchedule ? $this->ShowErrors($schedules[$i]->GetErrors()) : null;
        }
        
        // TODO: add optional league name text field
        
        // generate the form's html
        ob_start();
?>

<?php if ($showNewUserMessage): ?>
<div class="popup">
    <div>
        <h1>Welcome to League Robot</h1>
        <p>Thank you for registering to use League Robot!</p>
        <p>
            By default, you are set to receive email updates about your schedule once weekly and
            and whenever your schedule changes (up to once per day). You can change these options
            on the <a href="settings.php">settings</a> page.
        </p>
        <p>
            If you need any help using this website, please contact
            <span class="select" onclick="selectText(this);">help&#64;leaguerobot&#46;com</span>.
        </p>
        <div class="buttons">
            <input type="submit" value="OK" onclick="closePopup(this, 'popup');" />
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!$isLoggedIn): ?>
<div class="scroller">
<div class="close-button" onclick="closePopup(this, 'scroller');">x</div>
<h2>Log In</h2>
<?php if (!Utility::IsEmpty($loginErrors)): ?>
    <p><?= $loginErrors ?></p>
<?php endif; ?>
<p>
    Log in to view your schedule.
</p>
<div>
<form id="loginForm" name="loginForm" action="index.php" method="post">
    <p>
        <label for="loginEmail" id="labelLoginEmail" style="display:none;">Email</label>
        <input id="loginEmail" name="loginEmail" type="email" maxlength="100" class="padded dark whole"
            placeholder="Email Address" /><br/>
        <label for="loginPassword" id="labelLoginPassword" style="display:none;">Password</label>
        <input id="loginPassword" name="loginPassword" type="password" maxlength="100" class="padded dark whole"
            placeholder="Password" />
    </p>
    <p>
        <input id="login" name="login" type="submit" value="Log in" />
        <input id="loginRemember" name="loginRemember" type="checkbox" value="remember" checked="checked" style="margin-left: 5%;" />
        <label for="loginRemember">Remember Me</label>
    </p>
    <p>
        <a href="forgotpassword.php">Forgot your password?</a>
    </p>
</form>
</div>
</div>
<?php endif; ?>

<div class="section-dark">
<h1>Team Schedules</h1>
<?php if (!Utility::IsEmpty($errors)): ?>
    <p><?= $errors ?></p>
<?php endif; ?>
<ol>
    <li>
        Find your team's schedule online.
        <ul>
            <li>Try to find the page that shows the <em>entire</em> schedule for your team only.</li>
        </ul>
    </li>
    <li>Copy the URL of schedule and paste it in a box below.</li>
    <li>Enter your team's name <em>exactly</em> as it appears in the schedule
        and a unique name to identify the league.</li>
</ol>
<p>
    Currently, we support leaguelineup.com, pointstreak.com, and americanstreethockey.com.
    Read more about <a href="howitworks.php">how it works</a>.
</p>
</div>

<form id="scheduleForm" name="scheduleForm" action="index.php" method="post">
<div class="section">
<table id="team-links" style="border-collapse: separate; border-spacing: 0 0.5em; width: 100%;">
    <tr id="scheduleFormHeader" style="display: none;">
        <th>
            Schedule URL (include http://)
        </th>
        <th>
            Team Name
        </th>
        <th>
            Unique Name
        </th>
    </tr>
<?php for ($i = 0; $i < self::SCHEDULES_MAX_COUNT; ++$i): ?>
    <tr>
        <td style="width: 50%;">
            <input id="teamUrl_<?= $i ?>" name="teamUrl[]" type="url" maxlength="256" class="url"
                value="<?= $scheduleValues[$i]['URL'] ?>" placeholder="URL of Team's Schedule (include http://)" onchange="<?= $onchange ?>" />
            <input id="teamId_<?= $i ?>" name="teamId[]" type="hidden"
                value="<?= $scheduleValues[$i]['ID'] ?>" />
        </td>
        <td style="width: 25%;">
            <input id="teamName_<?= $i ?>" name="teamName[]" type="text" maxlength="50"
                value="<?= $scheduleValues[$i]['TEAM'] ?>" placeholder="Team Name" onchange="<?= $onchange ?>" />
        </td>
        <td style="width: 25%;">
            <input id="teamUnique_<?= $i ?>" name="teamUnique[]" type="text" maxlength="50"
                value="<?= $scheduleValues[$i]['NAME'] ?>" placeholder="Unique Name" onchange="<?= $onchange ?>" />
        </td>
        <td>
            <div class="color-box" style="background: <?= $colors[$i] ?>;"> </div>
            <input id="teamColor_<?= $i ?>" name="teamColor[]" type="hidden" value="<?= $colors[$i] ?>" />
        </td>
        <td>
            <a id="teamRemove" onclick="onRemoveLinkClick('<?= $i ?>')">Remove</a>
        </td>
    </tr>
<?php if (!Utility::IsEmpty($scheduleValues[$i]['ERRORS'])): ?>
    <tr>
        <td colspan="5" class="errors">
            <?= $scheduleValues[$i]['ERRORS'] ?>
        </td>
    </tr>
<?php endif; ?>
<?php endfor; ?>
</table>
<input id="saveChanges" name="save" type="submit" value="Save Changes"
<?php if (!$isLoggedIn): ?>
    style="display: none;"
<?php endif; ?>
/>
<span id="loading" style="visibility: hidden;">
    Loading
    <img src="img/loading.gif" alt="loading" />
</span>
</div>

<div id="scheduleWrapper" style="display: none;">
<div class="fade-section">
<div id="selectScheduleDiv" style="float: right; display: none;">
    <label for="selectSchedule">Schedule:</label>
    <select id="selectSchedule" name="selectSchedule" onchange="onSelectScheduleChange(this);">
        <option />
    </select>
</div>
<h1>Your Combined Schedule</h1>
<div id="scheduleDiv"> </div>
</div>

<?php if (!$isLoggedIn): ?>
<div class="section">
<h2>Create a Profile</h2>
<p>
    Save your schedule so you can view it online later.
    We can also send you email updates whenever your schedule is updated.
</p>
<p>
    Enter your email address and a password. Also, tell us your name,
    if you want.
</p>
<div style="width: 50%;">
    <label for="email" id="labelEmail" style="display:none">Email</label>
    <input id="email" name="email" type="email" maxlength="100" class="padded whole"
        placeholder="Email Address" /><br/>
    <label for="password" id="labelPassword" style="display:none">Password</label>
    <input id="password" name="password" type="password" maxlength="100" class="padded whole"
        placeholder="Password" /><br/>
    <label for="fname" id="labelName" style="display:none">Name (First/Last)</label>
    <input id="fname" name="fname" type="text" maxlength="50" class="padded half"
        placeholder="First Name" />
    <input id="lname" name="lname" type="text" maxlength="50" class="padded half"
        placeholder="Last Name" /><br/>
    <input id="createAccount" name="create" type="submit" value="Create Account" />
</div>
</div>
<?php endif; ?>
</div>
</form>

<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function Commit(&$schedules, &$itinerary, &$errors)
    {
        // check that the necessary fields are present
        foreach ($this->teamFields as $field)
        {
            if (!isset($_POST[$field]))
                return false;
        }
        
        // get the field values
        $urls = $_POST['teamUrl'];
        $names = $_POST['teamName'];
        $uNames = $_POST['teamUnique'];
        $ids = $_POST['teamId'];
        $teamCount = count($urls);
        // TODO: get optional league name textbox; do not filter it
        
        // build each set of values into a schedule
        $schedules = [];
        for ($t = 0; $t < $teamCount; ++$t)
        {
            // ready to create the schedule
            $schedule = Schedule::BuildSchedule($urls[$t], $names[$t], $uNames[$t]);
            if (is_numeric($ids[$t]))
                $schedule->SetId((int)$ids[$t]);
            $schedules[] = $schedule;
        }
        
        $table = null;
        if (count($schedules) > 0)
        {
            // query for the user's itinerary
            $itinerary = null;
            if ($this->User != null && $this->User->GetId() != null)
            {
                $itineraries = Itinerary::GetItineraryRows($this->Database, 'userid = ' . $this->User->GetId(), 'id');
                if ($itineraries != null)
                    $itinerary = $itineraries[0];
            }
            
            // add the schedules to the itinerary
            if ($itinerary == null)
                $itinerary = new Itinerary();
            foreach ($schedules as $s)
                $itinerary->AddSchedule($s);
            $itinerary->Sort();
            
            // save the schedule contents
            $itinerary->SetScheduleHash($itinerary->BuildHash());
        }
        
        $validSchedules = false;
        for ($s = 0; $s < count($schedules); ++$s)
        {
            if ($schedules[$s]->IsValid())
                $validSchedules = true;
            // no asynchronous deletes
            else if (!$this->async && $this->User != null && $schedules[$s]->GetId() != null && $schedules[$s]->GetErrorCount() == 0)
            {
                // delete the schedule
                $this->Database->Execute($schedules[$s]->Delete($this->Database));
                array_splice($schedules, $s--, 1); // delete the element, reorder the array, and decrement the counter
            }
        }
        
        // if there are no valid schedules, exit the function
        if (!$validSchedules) return false;
        
        // make sure all the fields were submitted
        if ($this->User == null)
        {
            foreach ($this->userFields as $field)
            {
                if (!isset($_POST[$field]))
                    return false;
            }
            
            // build the user
            $user = $this->CreateUser(Utility::TextOrNull('email', $_POST), Utility::TextOrNull('password', $_POST), $errors);
            if ($user !== false)
            {
                $user->SetFirstName(Utility::TextOrNull('fname', $_POST));
                $user->SetLastName(Utility::TextOrNull('lname', $_POST));
                if ($user->Validate($errors))
                {
                    // insert the user
                    $this->Database->Insert($user->Insert($this->Database), $newid);
                    $user->SetId($newid);
                    $this->SetUser($user);
                    
                    // create default email settings
                    $emailSettings = new ContactSettings();
                    $emailSettings->SetUserid($newid);
                    $emailSettings->SetContact($user->GetEmail());
                    $emailSettings->SetType(ContactSettings::TYPE_EMAIL);
                    $emailSettings->SetFrequency(ContactSettings::FREQUENCY_WEEKLY);
                    $emailSettings->SetNotifyOnChange(true);
                    $this->Database->Insert($emailSettings->Insert($this->Database), $newwidd);
                    
                    // create default localization settings
                    $loc = new Localization();
                    $loc->SetUserid($newid);
                    $loc->SetTimezone((new DateTime())->getTimezone()->getName());
                    $loc->SetLongDateFormat('l, F j, Y');
                    $loc->SetShortDateFormat(self::DATE_FORMAT);
                    $loc->SetTimeFormat(self::TIME_FORMAT);
                    $this->Database->Insert($loc->Insert($this->Database), $newwidd);
                }
            }
        }
        
        // commit the schedules
        if ($this->User != null)
        {
            for ($s = 0; $s < count($schedules); ++$s)
            {
                if (!$schedules[$s]->IsValid()) continue;
                
                $schedules[$s]->SetUserid($this->User->GetId());
                if ($schedules[$s]->GetId() != null) // update
                    $this->Database->Execute($schedules[$s]->Update($this->Database));
                else if (!$this->async) // no asynchronous inserts
                {   // insert
                    $this->Database->Insert($schedules[$s]->Insert($this->Database), $newid);
                    $schedules[$s]->SetId($newid);
                }
            }
            
            // commit the itinerary
            if ($itinerary != null && $itinerary->GetGameCount() > 0)
            {
                $itin = Itinerary::GetItineraryRows($this->Database, 'userid = ' . $this->User->GetId(), 'id');
                if ($itin != null)
                    $itinerary->SetId($itin[0]->GetId());
                
                $itinerary->SetUserid($this->User->GetId());
                if ($itinerary->GetId() != null) // update
                    $this->Database->Execute($itinerary->Update($this->Database));
                else if (!$this->async) // no asynchronous inserts
                {   // insert
                    $this->Database->Insert($itinerary->Insert($this->Database), $newid);
                    $itinerary->SetId($newid);
                }
            }
        }
        
        return true;
    }

    private function Debug($var)
    {
        ob_start();
        var_dump($var);
        $output = ob_get_contents();
        ob_end_clean();
        file_put_contents('/home/corey/public_html/sc/debug.txt', $output);
    }
    
    public function ShowErrors($errors)
    {
        if ($errors != null && count($errors) > 0)
            return '<p class="errors">' . implode('<br/>' . PHP_EOL, $errors) . '</p>';
        return null;
    }
}

$collector = new ScheduleCollector();

?>
