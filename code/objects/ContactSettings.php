<?php

class ContactSettings extends DatabaseRow {

// EMAIL type
const TYPE_EMAIL = 'E';

// FREQUENCY masks
const FREQUENCY_DAILY = 1;
const FREQUENCY_WEEKLY = 2;
const FREQUENCY_MONTHLY = 4;

#region Auto-generated fields

private $id;
private $userid;
private $contact;
private $type;
private $frequency;
private $notifyOnChange;
private $lastContact;
private $fields;
public function __construct() {
    parent::__construct();
    $this->fields = array("id", "userid", "contact", "type", "frequency", "notifyOnChange", "lastContact");
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
       case 'contact':
           $this->SetContact($value);
           $this->contact->ResetChanged();
           break;
       case 'type':
           $this->SetType($value);
           $this->type->ResetChanged();
           break;
       case 'frequency':
           $this->SetFrequency($value);
           $this->frequency->ResetChanged();
           break;
       case 'notifyOnChange':
           $this->SetNotifyOnChange($value);
           $this->notifyOnChange->ResetChanged();
           break;
       case 'lastContact':
           $this->SetLastContact($value);
           $this->lastContact->ResetChanged();
           break;
    }
}
public static function GetContactSettingsRow(IDatabase $db, $id, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `ContactSettings` WHERE `id` = $id;");
    if (!$res || $res->Count() == 0) return null;
    $row = $res->NextRowObject("ContactSettings");
    return $row;
}
public static function GetContactSettingsRows(IDatabase $db, $whereString, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `ContactSettings` WHERE $whereString;");
    if (!$res || $res->Count() == 0) return null;
    $rows = array();
    while ($row = $res->NextRowObject("ContactSettings"))
        $rows[] = $row;
    return $rows;
}
public function GetId() {
    return isset($this->id) ? $this->id->GetValue() : null ;
}
public function GetUserid() {
    return isset($this->userid) ? $this->userid->GetValue() : null ;
}
public function GetContact() {
    return isset($this->contact) ? $this->contact->GetValue() : null ;
}
public function GetType() {
    return isset($this->type) ? $this->type->GetValue() : null ;
}
public function GetFrequency() {
    return isset($this->frequency) ? $this->frequency->GetValue() : null ;
}
public function GetNotifyOnChange() {
    return isset($this->notifyOnChange) ? $this->notifyOnChange->GetValue() : null ;
}
public function GetLastContact() {
    return isset($this->lastContact) ? $this->lastContact->GetValue() : null ;
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
public function SetContact($value) {
    if (isset($this->contact))
        $this->contact->SetValue($value);
    else
    {
        $this->contact = new TextField('contact', 'Contact', false, 100);
        $this->contact->SetValue($value);
    }
}
public function SetType($value) {
    if (isset($this->type))
        $this->type->SetValue($value);
    else
    {
        $this->type = new TextField('type', 'Type', false, 1);
        $this->type->SetValue($value);
    }
}
public function SetFrequency($value) {
    if (isset($this->frequency))
        $this->frequency->SetValue($value);
    else
    {
        $this->frequency = new NumberField('frequency', 'Frequency', false, $value);
    }
}
public function SetNotifyOnChange($value) {
    if (isset($this->notifyOnChange))
        $this->notifyOnChange->SetValue($value);
    else
    {
        $this->notifyOnChange = new BooleanField('notifyOnChange', 'Notify On Change', false, $value);
    }
}
public function SetLastContact($value) {
    if (isset($this->lastContact))
        $this->lastContact->SetValue($value);
    else
    {
        $this->lastContact = new DateTimeField('lastContact', 'Last Contact', true, $value);
    }
}
public function Delete(IDatabase $db) {
    return "DELETE FROM `ContactSettings` WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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
return "INSERT INTO `ContactSettings` (`" . implode("`,`", $changedFields) . "`) VALUES (" . implode(",", $fieldValues) . ");";
}
public function Update(IDatabase $db) {
$fieldValues = array();
foreach ($this->fields as $field)
{
    if (isset($this->{$field}) && $this->{$field}->IsChanged() && strcmp($field, "id") != 0)
        $fieldValues[] = $field . " = " . $this->{$field}->ToQueryString($db);
}
if (count($fieldValues) == 0) return null;
return "UPDATE `ContactSettings` SET " . implode(", ", $fieldValues) . " WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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
