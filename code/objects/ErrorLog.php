<?php

class ErrorLog extends DatabaseRow {

#region Auto-generated fields

private $id;
private $fixed;
private $timestamp;
private $type;
private $file;
private $line;
private $message;
private $context;
private $fields;
public function __construct() {
    parent::__construct();
    $this->fields = array("id", "fixed", "timestamp", "type", "file", "line", "message", "context");
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
       case 'fixed':
           $this->SetFixed($value);
           $this->fixed->ResetChanged();
           break;
       case 'timestamp':
           $this->SetTimestamp($value);
           $this->timestamp->ResetChanged();
           break;
       case 'type':
           $this->SetType($value);
           $this->type->ResetChanged();
           break;
       case 'file':
           $this->SetFile($value);
           $this->file->ResetChanged();
           break;
       case 'line':
           $this->SetLine($value);
           $this->line->ResetChanged();
           break;
       case 'message':
           $this->SetMessage($value);
           $this->message->ResetChanged();
           break;
       case 'context':
           $this->SetContext($value);
           $this->context->ResetChanged();
           break;
    }
}
public static function GetErrorLogRow(IDatabase $db, $id, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `ErrorLog` WHERE `id` = $id;");
    if (!$res || $res->Count() == 0) return null;
    $row = $res->NextRowObject("ErrorLog");
    return $row;
}
public static function GetErrorLogRows(IDatabase $db, $whereString, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `ErrorLog` WHERE $whereString;");
    if (!$res || $res->Count() == 0) return null;
    $rows = array();
    while ($row = $res->NextRowObject("ErrorLog"))
        $rows[] = $row;
    return $rows;
}
public function GetId() {
    return isset($this->id) ? $this->id->GetValue() : null ;
}
public function GetFixed() {
    return isset($this->fixed) ? $this->fixed->GetValue() : null ;
}
public function GetTimestamp() {
    return isset($this->timestamp) ? $this->timestamp->GetValue() : null ;
}
public function GetType() {
    return isset($this->type) ? $this->type->GetValue() : null ;
}
public function GetFile() {
    return isset($this->file) ? $this->file->GetValue() : null ;
}
public function GetLine() {
    return isset($this->line) ? $this->line->GetValue() : null ;
}
public function GetMessage() {
    return isset($this->message) ? $this->message->GetValue() : null ;
}
public function GetContext() {
    return isset($this->context) ? $this->context->GetValue() : null ;
}
public function SetId($value) {
    if (isset($this->id))
        $this->id->SetValue($value);
    else
    {
        $this->id = new NumberField('id', 'Id', false, $value);
    }
}
public function SetFixed($value) {
    if (isset($this->fixed))
        $this->fixed->SetValue($value);
    else
    {
        $this->fixed = new BooleanField('fixed', 'Fixed', false, $value);
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
public function SetType($value) {
    if (isset($this->type))
        $this->type->SetValue($value);
    else
    {
        $this->type = new NumberField('type', 'Type', false, $value);
    }
}
public function SetFile($value) {
    if (isset($this->file))
        $this->file->SetValue($value);
    else
    {
        $this->file = new TextField('file', 'File', false, 256);
        $this->file->SetValue($value);
    }
}
public function SetLine($value) {
    if (isset($this->line))
        $this->line->SetValue($value);
    else
    {
        $this->line = new NumberField('line', 'Line', false, $value);
    }
}
public function SetMessage($value) {
    if (isset($this->message))
        $this->message->SetValue($value);
    else
    {
        $this->message = new TextField('message', 'Message', false, 1024);
        $this->message->SetValue($value);
    }
}
public function SetContext($value) {
    if (isset($this->context))
        $this->context->SetValue($value);
    else
    {
        $this->context = new TextField('context', 'Context', true, 1024);
        $this->context->SetValue($value);
    }
}
public function Delete(IDatabase $db) {
    return "DELETE FROM `ErrorLog` WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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
return "INSERT INTO `ErrorLog` (`" . implode("`,`", $changedFields) . "`) VALUES (" . implode(",", $fieldValues) . ");";
}
public function Update(IDatabase $db) {
$fieldValues = array();
foreach ($this->fields as $field)
{
    if (isset($this->{$field}) && $this->{$field}->IsChanged() && strcmp($field, "id") != 0)
        $fieldValues[] = $field . " = " . $this->{$field}->ToQueryString($db);
}
if (count($fieldValues) == 0) return null;
return "UPDATE `ErrorLog` SET " . implode(", ", $fieldValues) . " WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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
