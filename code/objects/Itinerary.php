<?php

class Itinerary extends DatabaseRow {

#region Auto-generated fields

private $id;
private $userid;
private $timestamp;
private $scheduleHash;
private $fields;
public function __construct() {
    parent::__construct();
    $this->fields = array("id", "userid", "timestamp", "scheduleHash");
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
       case 'scheduleHash':
           $this->SetScheduleHash($value);
           $this->scheduleHash->ResetChanged();
           break;
    }
}
public static function GetItineraryRow(IDatabase $db, $id, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `Itinerary` WHERE `id` = $id;");
    if (!$res || $res->Count() == 0) return null;
    $row = $res->NextRowObject("Itinerary");
    return $row;
}
public static function GetItineraryRows(IDatabase $db, $whereString, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `Itinerary` WHERE $whereString;");
    if (!$res || $res->Count() == 0) return null;
    $rows = array();
    while ($row = $res->NextRowObject("Itinerary"))
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
public function GetScheduleHash() {
    return isset($this->scheduleHash) ? $this->scheduleHash->GetValue() : null ;
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
public function SetScheduleHash($value) {
    if (isset($this->scheduleHash))
        $this->scheduleHash->SetValue($value);
    else
    {
        $this->scheduleHash = new TextField('scheduleHash', 'Schedule Hash', false, 20);
        $this->scheduleHash->SetValue($value);
    }
}
public function Delete(IDatabase $db) {
    return "DELETE FROM `Itinerary` WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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
return "INSERT INTO `Itinerary` (`" . implode("`,`", $changedFields) . "`) VALUES (" . implode(",", $fieldValues) . ");";
}
public function Update(IDatabase $db) {
$fieldValues = array();
foreach ($this->fields as $field)
{
    if (isset($this->{$field}) && $this->{$field}->IsChanged() && strcmp($field, "id") != 0)
        $fieldValues[] = $field . " = " . $this->{$field}->ToQueryString($db);
}
if (count($fieldValues) == 0) return null;
return "UPDATE `Itinerary` SET " . implode(", ", $fieldValues) . " WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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

private $games;

public function AddSchedule($schedule)
{
    if ($schedule != null && $schedule->GetGameCount() > 0)
    {
        if ($this->games == null) $this->games = [];
        $this->games = array_merge($this->games, $schedule->GetGames());
    }
}

public function BuildHash()
{
    if ($this->games == null || count($this->games) == 0)
        return 0;
    
    $text = '';
    $charsToEscape = '"';
    foreach ($this->games as $g)
    {
        // add slashes to the team names
        $visitors = $g->GetVisitingTeams(); // deep copy
        for ($i = 0; $i < count($visitors); ++$i)
            $visitors[$i] = addcslashes($visitors[$i], $charsToEscape);
        // add this game to the itinerary text
        $text .= sprintf('"%s", "%s", "%s", "%s", "%s"', $g->GetTime()->format('Y/m/d'),
            $g->GetTime()->format('H:i'), addcslashes($g->GetLocation(), $charsToEscape),
            addcslashes($g->GetHomeTeam(), $charsToEscape), implode('", "', $visitors)) . PHP_EOL;
    }
    return hex2bin(sha1($text));
}

public function GetGameCount()
{
    if ($this->games == null) $this->games = [];
    return count($this->games);
}

public function GetGames()
{
    if ($this->games == null) $this->games = [];
    return $this->games;
}

public function Sort($asc = true)
{
    if ($this->games == null) return;
    if ($asc)
        usort($this->games, ['Utility', 'CompareGames']);
    else
        usort($this->games, ['Utility', 'CompareGamesReverse']);
}

public function WriteXml()
{
    $xml = '<itinerary>' . PHP_EOL;
    if ($this->games != null)
    {
        foreach ($this->games as $g)
            $xml .= $g->WriteXml() . PHP_EOL;
    }
    $xml .= '</itinerary>';
    return $xml;
}

}
?>
