<?php
require_once('autoloader.php');

date_default_timezone_set('America/New_York');
//$schedule = ScheduleParser::ParseSchedule('pointstreak.txt');
// $s1 = new Schedule('pointstreak.txt', 'Shennanigans', 'Performax');
// $s2 = new Schedule('leaguelineup.txt', 'Jesse & The Rippers', 'OP');
// $s3 = new Schedule('ash.txt', 'Barbaric', 'Mineral Springs');
$s1 = Schedule::BuildSchedule('http://www.leaguelineup.com/schedules.asp?sid=30516073&url=fysportsarena&divisionid=619884&teamid=4307814', 'Jesse & The Rippers', 'Mondays');
$s2 = Schedule::BuildSchedule('http://www.pointstreak.com/stats/am/players-leagues-schedule.html?leagueid=79&seasonid=9635&view=all', 'Shennanigans', 'Ice');
var_dump($s2);
$s3 = Schedule::BuildSchedule('http://www.americanstreethockey.com/schedule.php?league=495&team=19&submit=Produce+Report', 'Barbaric', 'Saturdays');

$itinerary = new Itinerary();
$itinerary->AddSchedule($s1);
$itinerary->AddSchedule($s2);
$itinerary->AddSchedule($s3);
//$itinerary->AddSchedule($s3);
$itinerary->Sort();

//var_dump($itinerary);

?>
