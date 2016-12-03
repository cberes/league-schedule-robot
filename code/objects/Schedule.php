<?php

class Schedule extends DatabaseRow {

#region Auto-generated fields

private $id;
private $userid;
private $timestamp;
private $active;
private $name;
private $league;
private $team;
private $url;
private $scanDate;
private $fields;
public function __construct() {
    parent::__construct();
    $this->fields = array("id", "userid", "timestamp", "active", "name", "league", "team", "url", "scanDate");
    // mysqli->fetch_object() sets the members directly
    foreach ($this->fields as $field)
    {
        if (isset($this->{$field}))
        {
            $value = $this->{$field};
            // need to unset so the setter works correctly
            unset($this->{$field});
            $this->Set($field, $value);
        }
    }
}
public function __destruct() {
    parent::__destruct();
}
public function Set($name, $value) {
    switch ($name)
    {
       case 'id':
           $this->SetId($value);
           $this->id->ResetChanged();
           break;
       case 'userid':
           $this->SetUserid($value);
           $this->userid->ResetChanged();
           break;
       case 'timestamp':
           $this->SetTimestamp($value);
           $this->timestamp->ResetChanged();
           break;
       case 'active':
           $this->SetActive($value);
           $this->active->ResetChanged();
           break;
       case 'name':
           $this->SetName($value);
           $this->name->ResetChanged();
           break;
       case 'league':
           $this->SetLeague($value);
           $this->league->ResetChanged();
           break;
       case 'team':
           $this->SetTeam($value);
           $this->team->ResetChanged();
           break;
       case 'url':
           $this->SetUrl($value);
           $this->url->ResetChanged();
           break;
       case 'scanDate':
           $this->SetScanDate($value);
           $this->scanDate->ResetChanged();
           break;
    }
}
public static function GetScheduleRow(IDatabase $db, $id, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `Schedule` WHERE `id` = $id;");
    if (!$res || $res->Count() == 0) return null;
    $row = $res->NextRowObject("Schedule");
    return $row;
}
public static function GetScheduleRows(IDatabase $db, $whereString, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `Schedule` WHERE $whereString;");
    if (!$res || $res->Count() == 0) return null;
    $rows = array();
    while ($row = $res->NextRowObject("Schedule"))
        $rows[] = $row;
    return $rows;
}
public function GetId() {
    return isset($this->id) ? $this->id->GetValue() : null ;
}
public function GetUserid() {
    return isset($this->userid) ? $this->userid->GetValue() : null ;
}
public function GetTimestamp() {
    return isset($this->timestamp) ? $this->timestamp->GetValue() : null ;
}
public function GetActive() {
    return isset($this->active) ? $this->active->GetValue() : null ;
}
public function GetName() {
    return isset($this->name) ? $this->name->GetValue() : null ;
}
public function GetLeague() {
    return isset($this->league) ? $this->league->GetValue() : null ;
}
public function GetTeam() {
    return isset($this->team) ? $this->team->GetValue() : null ;
}
public function GetUrl() {
    return isset($this->url) ? $this->url->GetValue() : null ;
}
public function GetScanDate() {
    return isset($this->scanDate) ? $this->scanDate->GetValue() : null ;
}
public function SetId($value) {
    if (isset($this->id))
        $this->id->SetValue($value);
    else
    {
        $this->id = new NumberField('id', 'Id', false, $value);
    }
}
public function SetUserid($value) {
    if (isset($this->userid))
        $this->userid->SetValue($value);
    else
    {
        $this->userid = new NumberField('userid', 'Userid', false, $value);
    }
}
public function SetTimestamp($value) {
    if (isset($this->timestamp))
        $this->timestamp->SetValue($value);
    else
    {
        $this->timestamp = new DateTimeField('timestamp', 'Timestamp', false, $value);
    }
}
public function SetActive($value) {
    if (isset($this->active))
        $this->active->SetValue($value);
    else
    {
        $this->active = new BooleanField('active', 'Active', false, $value);
    }
}
public function SetName($value) {
    if (isset($this->name))
        $this->name->SetValue($value);
    else
    {
        $this->name = new TextField('name', 'Name', false, 50);
        $this->name->SetValue($value);
    }
}
public function SetLeague($value) {
    if (isset($this->league))
        $this->league->SetValue($value);
    else
    {
        $this->league = new TextField('league', 'League', true, 50);
        $this->league->SetValue($value);
    }
}
public function SetTeam($value) {
    if (isset($this->team))
        $this->team->SetValue($value);
    else
    {
        $this->team = new TextField('team', 'Team', false, 50);
        $this->team->SetValue($value);
    }
}
public function SetUrl($value) {
    if (isset($this->url))
        $this->url->SetValue($value);
    else
    {
        $this->url = new TextField('url', 'Url', false, 256);
        $this->url->SetValue($value);
    }
}
public function SetScanDate($value) {
    if (isset($this->scanDate))
        $this->scanDate->SetValue($value);
    else
    {
        $this->scanDate = new DateTimeField('scanDate', 'Scan Date', true, $value);
    }
}
public function Delete(IDatabase $db) {
    return "DELETE FROM `Schedule` WHERE `id` = " . $this->id->ToQueryString($db) . ";";
}
public function Insert(IDatabase $db) {
$changedFields = array();
foreach ($this->fields as $field)
{
    if (isset($this->{$field}))
        $changedFields[] = $field;
}
$fieldValues = array();
foreach ($this->fields as $field)
{
    if (isset($this->{$field}))
        $fieldValues[] = $this->{$field}->ToQueryString($db);
}
if (count($fieldValues) == 0) return null;
return "INSERT INTO `Schedule` (`" . implode("`,`", $changedFields) . "`) VALUES (" . implode(",", $fieldValues) . ");";
}
public function Update(IDatabase $db) {
$fieldValues = array();
foreach ($this->fields as $field)
{
    if (isset($this->{$field}) && $this->{$field}->IsChanged() && strcmp($field, "id") != 0)
        $fieldValues[] = $field . " = " . $this->{$field}->ToQueryString($db);
}
if (count($fieldValues) == 0) return null;
return "UPDATE `Schedule` SET " . implode(", ", $fieldValues) . " WHERE `id` = " . $this->id->ToQueryString($db) . ";";
}
public function Validate(&$errors) {
    $success = true;
    $errors = array();
    foreach ($this->fields as $field)
    {
        if (isset($this->{$field}) && !$this->{$field}->Validate($error))
        {
            $errors[] = $error;
            $success = false;
        }
    }
    return $success;
}

#endregion Auto-generated fields

private $errors;

private $games;

private $isValid;

public static function BuildSchedule($uri, $team, $name, $league = null)
{
    $schedule = new Schedule();
    $schedule->errors = [];
    $schedule->games = [];
    $schedule->isValid = false;
    $schedule->SetLeague($league);
    $schedule->SetName($name);
    $schedule->SetTeam($team);
    $schedule->SetUrl($uri);
    $schedule->UpdateSchedule();
    return $schedule;
}

public function UpdateSchedule(DateTime $minDate = null)
{
    // initialize these things here again    
    $this->errors = [];
    $this->games = [];
    $this->isValid = false;
    
    // validate the schedule
    $isEmptyUrl = Utility::IsEmpty($this->GetUrl());
    $isEmptyTeam = Utility::IsEmpty($this->GetTeam());
    $isEmptyUnique = Utility::IsEmpty($this->GetName());

    // if everything is empty, don't output an error
    if ($isEmptyUrl && $isEmptyTeam && $isEmptyUnique)
    {
        $this->isValid = false;
        return false;
    }
    
    // output errors for empty URLs
    if ($isEmptyUrl)
        $this->errors[] = 'URL was missing.';
    if ($isEmptyTeam)
        $this->errors[] = 'Team name was missing.';
    if ($isEmptyUnique)
        $this->errors[] = 'Unique name was missing.';
    
    // if anything is empty, don't parse the schedule
    if ($isEmptyUrl || $isEmptyTeam || $isEmptyUnique)
    {
        $this->isValid = false;
        return false;
    }
    
    // validate the URL
    if (filter_var($this->GetUrl(), FILTER_VALIDATE_URL) === false // check valid url
        || substr_compare($this->GetUrl(), 'http', 0, 4, true) != 0) // must begin with http
    {
        $this->errors[] = 'URL was invalid.';
        $this->isValid = false;
        return false;
    }
    
    // parse the schedule   
    $dataRows = ScheduleParser::ParseSchedule($this->GetUrl());
    if ($dataRows == null)
    {
        $this->errors[] = 'The schedule could not be parsed.';
        $this->isValid = false;
        return false;
    }
    
    // create games from the data rows
    foreach ($dataRows as $row)
    {
        $game = $this->CreateGame($row);
        if ($game != null && ($minDate == null || $game->GetTime() >= $minDate))
        {
            $game->SetSource($this->GetUrl());
            $this->games[] = $game;
        }
    }
    
    // if there were no games, show an error, but do not mark the schedule as invalid
    // if a minimum date was specified, there might have been games, but they were filtered
    if ($minDate == null && count($this->games) == 0)
    {
        $this->errors[] = 'No games were found.';
        return false;
    }
        
    $this->Sort();
    $this->SetScanDate(new DateTime());
    $this->isValid = true;
    return true;
}

public function IsValid()
{
    return $this->isValid;
}

public function GetErrorCount()
{
    return count($this->errors);
}

public function GetErrors()
{
    return $this->errors;
}

public function GetGameCount()
{
    return count($this->games);
}

public function GetGames()
{
    return $this->games;
}

public function Sort()
{
    usort($this->games, ['Utility', 'CompareGames']);
}

private function CreateGame($data)
{
    // teams must be specified
    $home = null;
    $away = null;
    $team = $this->GetTeam();
    if (array_key_exists(ScheduleParser::HOME_HEADER, $data)
        && array_key_exists(ScheduleParser::AWAY_HEADER, $data))
    {
        // our team should be one of the teams
        $away = $data[ScheduleParser::AWAY_HEADER];
        $home = $data[ScheduleParser::HOME_HEADER];
        if (!stristr($away, $team) && !stristr($home, $team))
            return null;
    }
    else return null;
    
    // if the league is specified, it must match
    $league = $this->GetLeague();
    if ($league != null && array_key_exists(ScheduleParser::LEAGUE_HEADER, $data)
        && !stristr($data[ScheduleParser::LEAGUE_HEADER], $league))
        return null;
    
    // get the league
    $league = null;
    if (array_key_exists(ScheduleParser::LEAGUE_HEADER, $data))
        $league = $data[ScheduleParser::LEAGUE_HEADER];
    
    // find the date and time   
    $time = null; 
    if (array_key_exists(ScheduleParser::DATE_HEADER, $data)
        && array_key_exists(ScheduleParser::TIME_HEADER, $data))
    {
        $guessedYear = false;
        $now = new DateTime();
        $parsedDate = date_parse($data[ScheduleParser::DATE_HEADER]);
        $parsedTime = date_parse($data[ScheduleParser::TIME_HEADER]);
        
        // check for the parts that are absolutely necessary
        if ($parsedDate['day'] === false || $parsedDate['month'] === false
            || $parsedTime['hour'] === false || $parsedTime['minute'] === false)
            return null;
            
        // date might have the year implied
        if ($parsedDate['year'] === false)
        {
            // current year
            $thisYear = $now->format('Y');
            
            // try to guess by the weekday if it's set
            if (isset($parsedDate['relative']) && isset($parsedDate['relative']['weekday']) && $parsedDate['relative']['weekday'] !== false)
            {
                // get the weekday
                $weekday = $parsedDate['relative']['weekday'];
                
                // iterate through a few years
                for ($y = $thisYear - 3; $y < $thisYear + 4; ++$y)
                {
                    // get the date on the year we've iterated to
                    $date = new DateTime();
                    $date->setDate($y, $parsedDate['month'], $parsedDate['day']);
                    
                    // compare the date's weekday in that year to the weekday we found
                    if ($date->format('w') == $weekday)
                    {
                        $parsedDate['year'] = $y;
                        break;
                    }
                }
            }
            
            // just use the current year
            if ($parsedDate['year'] === false)
            {
                $parsedDate['year'] = $thisYear;
                $guessedYear = true;
            }
        }
        
        $time = new DateTime();
        $time = $time->setDate($parsedDate['year'], $parsedDate['month'], $parsedDate['day']);
        $time = $time->setTime($parsedTime['hour'], $parsedTime['minute']);
        
        if ($guessedYear)
        {
            $gameCount = count($this->games);
            if ($gameCount > 0 && $this->games[$gameCount - 1]->GetTime() > $time)
            {
                // if we guessed the year, but the game is earlier than the last game,
                // increment the year
                $time = $time->setDate($parsedDate['year'] + 1, $parsedDate['month'],
                    $parsedDate['day']);
            }
        }
    }
    if ($time == null) return null;
    
    // location
    $location = null;
    if (array_key_exists(ScheduleParser::LOCATION_HEADER, $data))
        $location = $data[ScheduleParser::LOCATION_HEADER];
    
    // create the game now
    $game = new Game($time, !Utility::IsEmpty($league) ? $league : $this->GetName(), $location);
    $game->AddTeam($home, true);
    $game->AddTeam($away, false);
    $game->SetGroupId($this->GetName());
    return $game;
}

}
?>
