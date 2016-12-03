#!/usr/bin/php
<?php
require_once('Mail.php');
require_once('Mail/mime.php');
require_once('autoloader.php');

class Emailer
{
    // 
    const DATE_FORMAT = 'n/j/Y';
    
    //
    const TIME_FORMAT = 'g:i A';
    
    public static function Run($onchange, $frequency)
    {
        // build the contact-settings where clause
        $where = "`type` = '" . ContactSettings::TYPE_EMAIL
            . "' AND (`lastContact` IS NULL OR TIMEDIFF(NOW(), `lastContact`) > '12:00:00')";
        if ($onchange)
            $where .= ' AND `notifyOnChange` = 1';
        else
            $where .= ' AND `frequency` & ' . $frequency . ' <> 0';
        
        // connect to the database
        $db = new MySqlDatabase(Utility::GetResource('dbhost'),
            Utility::GetResource('dbuser'), Utility::GetResource('dbpass'),
            Utility::GetResource('dbname'));
        
        // get email settings rows
        $settings = ContactSettings::GetContactSettingsRows($db,
            "$where AND EXISTS (SELECT `id` FROM `User` WHERE `id` = `ContactSettings`.`userid` AND `active` = 1)");
        $userCount = count($settings);
            
        // get localization settings
        $locales = Localization::GetLocalizationRows($db,
            "`userid` in (SELECT `userid` FROM `ContactSettings` WHERE $where) order by `userid`");
        $localeCount = count($locales);
        $localesByUser = [];
        for ($i = 0; $i < $localeCount; ++$i)
            $localesByUser[$locales[$i]->GetUserid()] =& $locales[$i];
            
        // get schedules
        $schedules = Schedule::GetScheduleRows($db,
            "`userid` in (SELECT `userid` FROM `ContactSettings` WHERE $where) order by `userid`");
        $scheduleCount = count($schedules);
        $schedulesByUser = [];
        for ($i = 0; $i < $scheduleCount; ++$i)
        {
            $uid = $schedules[$i]->GetUserid();
            if (!array_key_exists($uid, $schedulesByUser))
                $schedulesByUser[$uid] = [];
            $schedulesByUser[$uid][] =& $schedules[$i];
        }
            
        // get itineraries
        $itineraries = Itinerary::GetItineraryRows($db,
            "`userid` in (SELECT `userid` FROM `ContactSettings` WHERE $where) order by `userid`, `timestamp`");
        $itineraryCount = count($itineraries);
        $itinerariesByUser = [];
        for ($i = 0; $i < $itineraryCount; ++$i)
            $itinerariesByUser[$itineraries[$i]->GetUserid()] =& $itineraries[$i];
        
        // order: on change, daily, weekly, monthly
        for ($i = 0; $i < $userCount; ++$i)
        {
            $uid = $settings[$i]->GetUserid();
            if (!array_key_exists($uid, $schedulesByUser))
                continue;
            
            // get this user's schedules
            $userSchedules = $schedulesByUser[$uid];
            $userScheduleCount = count($userSchedules);
            
            // get the user's itinerary
            $itinerary = null;
            $insertItinerary = false;
            if (array_key_exists($uid, $itinerariesByUser))
                $itinerary = $itinerariesByUser[$uid];
            if ($itinerary == null)
            {
                $insertItinerary = true;
                $itinerary = new Itinerary();
                $itinerary->SetUserid($uid);
            }
            
            // add the games to a single itinerary
            for ($s = 0; $s < $userScheduleCount; ++$s)
            {
                // get the games in this schedule
                $userSchedules[$s]->UpdateSchedule();
                // add the games to the itinerary
                $itinerary->AddSchedule($userSchedules[$s]);
            }
            $itinerary->Sort();
            
            // determine if the schedule was changed
            $oldHash = $itinerary->GetScheduleHash();
            $newHash = $itinerary->BuildHash();
            $changed = $oldHash != $newHash;
            $itinerary->SetScheduleHash($newHash);
            
            // send the email of the schedule (check if it was changed, if necessary)
            $emailSent = false;
            if ((!$onchange || $changed) && $itinerary->GetGameCount() > 0)
            {
                // get this user's localization settings
                $locale = null;
                if (array_key_exists($uid, $localesByUser))
                    $locale = $localesByUser[$uid];
                
                // generate the table of games
                $table = self::BuildScheduleTable($itinerary->GetGames(), $locale);
                
                // build the email's HTML body
                $html = '<html><body>';
                if ($changed)
                    $html .= '<p>Changes to your schedule were detected. Your contact preferences indicate that you wish to be contacted about changes to your schedule.</p>';
                $html .= '<p>Here is your schedule, courtesy of <a href="http://leaguerobot.com">LeageRobot.com</a>:</p>';
                $html .= $table;
                
                // send the email
                $emailSent = self::SendEmail($html, 'Schedules can be viewed only in HTML.',
                    //Utility::GetResource('schedule_email_subject'), Utility::GetResource('schedule_email_from'),
                    'Your LeagueRobot.com Schedule', 'schedules@leaguerobot.com',
                    $settings[$i]->GetContact()) === true;
            }
            
            // commit the changes to the database
            if ($emailSent)
            {
                $settings[$i]->SetLastContact(new DateTime());
                $db->Execute($settings[$i]->Update($db));
            }
            foreach ($userSchedules as $s)
                $db->Execute($s->Update($db));
            if ($insertItinerary)
                $db->Insert($itinerary->Insert($db), $newid);
            else
                $db->Execute($itinerary->Update($db));
        }
    }
    
    private static function BuildScheduleTable($games, $locale)
    {
        if ($games == null) return null;
        $gameCount = count($games);
        if ($gameCount == 0) return null;
        
        // get this user's date/time formats
        $dateFormat = self::DATE_FORMAT;
        $timeFormat = self::TIME_FORMAT;
        if ($locale != null)
        {
            $dateFormat = $locale->GetShortDateFormat();
            $timeFormat = $locale->GetTimeFormat();
        }
        
        // colors
        $colors = ['red', 'darkorange', 'green', 'darkblue', 'purple', '#222', 'saddlebrown', 'darkred', 'darkkhaki', 'royalblue'];
        $colorsByGroup = [];
        $nextColorIndex = 0;
        
        $nextGame = false;
        
        // generate the form's html
        ob_start();
?>

<table style="text-align: center; width: 100%;">
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
<?php
    // get the color for the row
    $color = 'black';
    if (array_key_exists($games[$g]->GetGroupId(), $colorsByGroup))
        $color = $colorsByGroup[$games[$g]->GetGroupId()]; // get this group's color
    else if ($games[$g]->GetGroupId() != null)
    {
        // get a new color
        $color = $colors[$nextColorIndex];
        $colorsByGroup[$games[$g]->GetGroupId()] = $color;
        // set the index of the color array for the next color
        $nextColorIndex = ($nextColorIndex + 1)  % count($colors);
    }
?>
        <tr style="color: <?= $color ?>;
            <?php if ($games[$g]->GetTime() != null && $games[$g]->GetTime() < new DateTime()): ?>
                font-style: italic;
            <?php elseif ($games[$g]->GetTime() != null && !$nextGame): ?>
                <?php $nextGame = true; ?>
                border-bottom: thin dashed; border-top: thin double; font-weight: bold;
            <?php endif; ?>
            ">
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
                <a href="<?= $games[$g]->GetSource() ?>" title="View schedule source.">
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
    
    private static function SendEmail($html, $text, $subject, $from, $to)
    {
        //$file = '/home/richard/example.php';
        $crlf = "\n";
        $hdrs = array(
                      'From'    => $from,
                      'Subject' => $subject
                      );
        
        $mime = new Mail_mime(array('eol' => $crlf));
        
        $mime->setTXTBody($text);
        $mime->setHTMLBody($html);
        //$mime->addAttachment($file, 'text/plain');
        
        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);
        
        $mail = Mail::factory('mail');
        // $to can be an array
        return $mail->send($to, $hdrs, $body);
    }
}

// need to set the timezone
date_default_timezone_set('America/New_York');
    
// read command line arguments and run the emailer
if (count($argv) == 2 && strlen($argv[1]) > 0)
{
    
    // get the frequench for which we're running the email
    switch (strtoupper(substr($argv[1], 0, 1)))
    {
        case 'D':
            Emailer::Run(false, ContactSettings::FREQUENCY_DAILY);
            return 0;
        case 'W':
            Emailer::Run(false, ContactSettings::FREQUENCY_WEEKLY);
            return 0;
        case 'M':
            Emailer::Run(false, ContactSettings::FREQUENCY_MONTHLY);
            return 0;
        case 'C':
            Emailer::Run(true, 0);
            return 0;
    }
}
    
/* notes:
 * both on-change and weekly/monthly users:
 * if on-change first and nothing changed
 *      will run, but will not send an email
 *      will run again for weekly
 * if weekly first and nothing changed
 *      will run for weekly
 *      will not run for on-change
 */

// run the emailer based on the current day
$now = new DateTime();

// if it's the first of the month, run for users with monthly settings
if ($now->format('j') == 1)
    Emailer::Run(false, ContactSettings::FREQUENCY_MONTHLY);

// if it's the first day of the week (Monday), run for users with weekly settings
if ($now->format('N') == 1)
    Emailer::Run(false, ContactSettings::FREQUENCY_WEEKLY);

// always run for users with daily and on-change settings
Emailer::Run(false, ContactSettings::FREQUENCY_DAILY);
Emailer::Run(true, 0);

return 0;

?>