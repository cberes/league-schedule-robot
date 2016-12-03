<?php

class PasswordReset extends DatabaseRow {

#region Auto-generated fields

private $id;
private $userid;
private $count;
private $secret;
private $timestamp;
private $fields;
public function __construct() {
    parent::__construct();
    $this->fields = array("id", "userid", "count", "secret", "timestamp");
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
       case 'count':
           $this->SetCount($value);
           $this->count->ResetChanged();
           break;
       case 'secret':
           $this->SetSecret($value);
           $this->secret->ResetChanged();
           break;
       case 'timestamp':
           $this->SetTimestamp($value);
           $this->timestamp->ResetChanged();
           break;
    }
}
public static function GetPasswordResetRow(IDatabase $db, $id, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `PasswordReset` WHERE `id` = $id;");
    if (!$res || $res->Count() == 0) return null;
    $row = $res->NextRowObject("PasswordReset");
    return $row;
}
public static function GetPasswordResetRows(IDatabase $db, $whereString, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `PasswordReset` WHERE $whereString;");
    if (!$res || $res->Count() == 0) return null;
    $rows = array();
    while ($row = $res->NextRowObject("PasswordReset"))
        $rows[] = $row;
    return $rows;
}
public function GetId() {
    return isset($this->id) ? $this->id->GetValue() : null ;
}
public function GetUserid() {
    return isset($this->userid) ? $this->userid->GetValue() : null ;
}
public function GetCount() {
    return isset($this->count) ? $this->count->GetValue() : null ;
}
public function GetSecret() {
    return isset($this->secret) ? $this->secret->GetValue() : null ;
}
public function GetTimestamp() {
    return isset($this->timestamp) ? $this->timestamp->GetValue() : null ;
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
public function SetCount($value) {
    if (isset($this->count))
        $this->count->SetValue($value);
    else
    {
        $this->count = new NumberField('count', 'Count', false, $value);
    }
}
public function SetSecret($value) {
    if (isset($this->secret))
        $this->secret->SetValue($value);
    else
    {
        $this->secret = new TextField('secret', 'Secret', false, 20);
        $this->secret->SetValue($value);
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
public function Delete(IDatabase $db) {
    return "DELETE FROM `PasswordReset` WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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
return "INSERT INTO `PasswordReset` (`" . implode("`,`", $changedFields) . "`) VALUES (" . implode(",", $fieldValues) . ");";
}
public function Update(IDatabase $db) {
$fieldValues = array();
foreach ($this->fields as $field)
{
    if (isset($this->{$field}) && $this->{$field}->IsChanged() && strcmp($field, "id") != 0)
        $fieldValues[] = $field . " = " . $this->{$field}->ToQueryString($db);
}
if (count($fieldValues) == 0) return null;
return "UPDATE `PasswordReset` SET " . implode(", ", $fieldValues) . " WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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
