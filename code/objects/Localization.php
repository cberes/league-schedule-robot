<?php

class Localization extends DatabaseRow {

#region Auto-generated fields

private $id;
private $userid;
private $timezone;
private $longDateFormat;
private $shortDateFormat;
private $timeFormat;
private $fields;
public function __construct() {
    parent::__construct();
    $this->fields = array("id", "userid", "timezone", "longDateFormat", "shortDateFormat", "timeFormat");
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
       case 'timezone':
           $this->SetTimezone($value);
           $this->timezone->ResetChanged();
           break;
       case 'longDateFormat':
           $this->SetLongDateFormat($value);
           $this->longDateFormat->ResetChanged();
           break;
       case 'shortDateFormat':
           $this->SetShortDateFormat($value);
           $this->shortDateFormat->ResetChanged();
           break;
       case 'timeFormat':
           $this->SetTimeFormat($value);
           $this->timeFormat->ResetChanged();
           break;
    }
}
public static function GetLocalizationRow(IDatabase $db, $id, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `Localization` WHERE `id` = $id;");
    if (!$res || $res->Count() == 0) return null;
    $row = $res->NextRowObject("Localization");
    return $row;
}
public static function GetLocalizationRows(IDatabase $db, $whereString, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `Localization` WHERE $whereString;");
    if (!$res || $res->Count() == 0) return null;
    $rows = array();
    while ($row = $res->NextRowObject("Localization"))
        $rows[] = $row;
    return $rows;
}
public function GetId() {
    return isset($this->id) ? $this->id->GetValue() : null ;
}
public function GetUserid() {
    return isset($this->userid) ? $this->userid->GetValue() : null ;
}
public function GetTimezone() {
    return isset($this->timezone) ? $this->timezone->GetValue() : null ;
}
public function GetLongDateFormat() {
    return isset($this->longDateFormat) ? $this->longDateFormat->GetValue() : null ;
}
public function GetShortDateFormat() {
    return isset($this->shortDateFormat) ? $this->shortDateFormat->GetValue() : null ;
}
public function GetTimeFormat() {
    return isset($this->timeFormat) ? $this->timeFormat->GetValue() : null ;
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
public function SetTimezone($value) {
    if (isset($this->timezone))
        $this->timezone->SetValue($value);
    else
    {
        $this->timezone = new TextField('timezone', 'Timezone', false, 32);
        $this->timezone->SetValue($value);
    }
}
public function SetLongDateFormat($value) {
    if (isset($this->longDateFormat))
        $this->longDateFormat->SetValue($value);
    else
    {
        $this->longDateFormat = new TextField('longDateFormat', 'Long Date Format', false, 10);
        $this->longDateFormat->SetValue($value);
    }
}
public function SetShortDateFormat($value) {
    if (isset($this->shortDateFormat))
        $this->shortDateFormat->SetValue($value);
    else
    {
        $this->shortDateFormat = new TextField('shortDateFormat', 'Short Date Format', false, 10);
        $this->shortDateFormat->SetValue($value);
    }
}
public function SetTimeFormat($value) {
    if (isset($this->timeFormat))
        $this->timeFormat->SetValue($value);
    else
    {
        $this->timeFormat = new TextField('timeFormat', 'Time Format', false, 10);
        $this->timeFormat->SetValue($value);
    }
}
public function Delete(IDatabase $db) {
    return "DELETE FROM `Localization` WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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
return "INSERT INTO `Localization` (`" . implode("`,`", $changedFields) . "`) VALUES (" . implode(",", $fieldValues) . ");";
}
public function Update(IDatabase $db) {
$fieldValues = array();
foreach ($this->fields as $field)
{
    if (isset($this->{$field}) && $this->{$field}->IsChanged() && strcmp($field, "id") != 0)
        $fieldValues[] = $field . " = " . $this->{$field}->ToQueryString($db);
}
if (count($fieldValues) == 0) return null;
return "UPDATE `Localization` SET " . implode(", ", $fieldValues) . " WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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

}
?>
