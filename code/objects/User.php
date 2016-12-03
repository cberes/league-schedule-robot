<?php

class User extends DatabaseRow {

#region Auto-generated fields

private $id;
private $active;
private $timestamp;
private $email;
private $password;
private $firstName;
private $lastName;
private $fields;
public function __construct() {
    parent::__construct();
    $this->fields = array("id", "active", "timestamp", "email", "password", "firstName", "lastName");
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
       case 'active':
           $this->SetActive($value);
           $this->active->ResetChanged();
           break;
       case 'timestamp':
           $this->SetTimestamp($value);
           $this->timestamp->ResetChanged();
           break;
       case 'email':
           $this->SetEmail($value);
           $this->email->ResetChanged();
           break;
       case 'password':
           $this->SetPassword($value);
           $this->password->ResetChanged();
           break;
       case 'firstName':
           $this->SetFirstName($value);
           $this->firstName->ResetChanged();
           break;
       case 'lastName':
           $this->SetLastName($value);
           $this->lastName->ResetChanged();
           break;
    }
}
public static function GetUserRow(IDatabase $db, $id, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `User` WHERE `id` = $id;");
    if (!$res || $res->Count() == 0) return null;
    $row = $res->NextRowObject("User");
    return $row;
}
public static function GetUserRows(IDatabase $db, $whereString, $fields = "*") {
    $res = $db->Select("SELECT $fields FROM `User` WHERE $whereString;");
    if (!$res || $res->Count() == 0) return null;
    $rows = array();
    while ($row = $res->NextRowObject("User"))
        $rows[] = $row;
    return $rows;
}
public function GetId() {
    return isset($this->id) ? $this->id->GetValue() : null ;
}
public function GetActive() {
    return isset($this->active) ? $this->active->GetValue() : null ;
}
public function GetTimestamp() {
    return isset($this->timestamp) ? $this->timestamp->GetValue() : null ;
}
public function GetEmail() {
    return isset($this->email) ? $this->email->GetValue() : null ;
}
public function GetPassword() {
    return isset($this->password) ? $this->password->GetValue() : null ;
}
public function GetFirstName() {
    return isset($this->firstName) ? $this->firstName->GetValue() : null ;
}
public function GetLastName() {
    return isset($this->lastName) ? $this->lastName->GetValue() : null ;
}
public function SetId($value) {
    if (isset($this->id))
        $this->id->SetValue($value);
    else
    {
        $this->id = new NumberField('id', 'Id', false, $value);
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
public function SetTimestamp($value) {
    if (isset($this->timestamp))
        $this->timestamp->SetValue($value);
    else
    {
        $this->timestamp = new DateTimeField('timestamp', 'Timestamp', false, $value);
    }
}
public function SetEmail($value) {
    if (isset($this->email))
        $this->email->SetValue($value);
    else
    {
        $this->email = new TextField('email', 'Email', false, 100);
        $this->email->SetValue($value);
    }
}
public function SetPassword($value) {
    if (isset($this->password))
        $this->password->SetValue($value);
    else
    {
        $this->password = new TextField('password', 'Password', false, 20);
        $this->password->SetValue($value);
    }
}
public function SetFirstName($value) {
    if (isset($this->firstName))
        $this->firstName->SetValue($value);
    else
    {
        $this->firstName = new TextField('firstName', 'First Name', true, 50);
        $this->firstName->SetValue($value);
    }
}
public function SetLastName($value) {
    if (isset($this->lastName))
        $this->lastName->SetValue($value);
    else
    {
        $this->lastName = new TextField('lastName', 'Last Name', true, 50);
        $this->lastName->SetValue($value);
    }
}
public function Delete(IDatabase $db) {
    return "DELETE FROM `User` WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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
return "INSERT INTO `User` (`" . implode("`,`", $changedFields) . "`) VALUES (" . implode(",", $fieldValues) . ");";
}
public function Update(IDatabase $db) {
$fieldValues = array();
foreach ($this->fields as $field)
{
    if (isset($this->{$field}) && $this->{$field}->IsChanged() && strcmp($field, "id") != 0)
        $fieldValues[] = $field . " = " . $this->{$field}->ToQueryString($db);
}
if (count($fieldValues) == 0) return null;
return "UPDATE `User` SET " . implode(", ", $fieldValues) . " WHERE `id` = " . $this->id->ToQueryString($db) . ";";
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
