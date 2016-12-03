<?php
require_once('BasePage.php');
require_once('Template.php');

class ChangePassword extends BasePage
{
    public $template;
    
    private $fields;
    
    public function __construct()
    {
        parent::__construct(false, true);
        
        $this->fields = array('oldpassword', 'password', 'passwordconfirm');
                
        // attempt to save the company
        $this->Commit($errors);
        
        // put any errors into HTML form
        $errorString = null;
        if (isset($errors))
            $errorString = $this->ShowErrors($errors);
        
        // put the company into some HTML
        $markup = $this->View($errorString);
          
        // fill in the template  
        $this->template = new Template(false);
        $this->template->SetTitle('Password Change');
        $script = <<<SCRIPT
            <script type="text/javascript">
            function onPasswordChange(element)
            {
                var other = document.getElementById('password');
                var note = document.getElementById('passwordnote');
                var note2 = document.getElementById('passwordconfirmnote');
                if (element.value.length < 5)
                {
                    note.innerText = 'Password must be at least five characters.';
                    return;
                }
                else
                    note.innerText = '';
                if (other.value.length >= 5 && element.value != other.value)
                    note2.innerText = 'Passwords must match.';
                else
                    note2.innerText = '';
            }
            function onPasswordConfirmChange(element)
            {
                var other = document.getElementById('password');
                var note = document.getElementById('passwordconfirmnote');
                var text = '';
                if (element.value != other.value)
                    text = 'Passwords must match.';
                note.innerText = text;
            }
            </script>
SCRIPT;
        $this->template->SetHead($script);
        $this->template->SetBody($markup);
    }
    
    private function IsPasswordValid($password)
    {
        return preg_match('/^.{5,}$/', $password) > 0;
    }
    
    public function View($errors = null)
    {
        // generate the form's html
        return <<<LOAD
<h1>Change your Password</h1>
$errors
<form id="form" name="changepassword" action="changepassword.php" method="post">
<table class="center content">
    <tr>
        <th>
            Current Password:
        </th>
        <td colspan="2">
            <input type="password" id="oldpassword" name="oldpassword" maxlength="25" class="medium" />
        </td>
    </tr>
    <tr>
        <th>
            Password:
        </th>
        <td>
            <input type="password" id="password" name="password" maxlength="25" class="medium" onchange="onPasswordChange(this);" />
        </td>
        <td id="passwordnote" class="clear"> </td>
    </tr>
    <tr>
        <th>
            Confirm Password:
        </th>
        <td>
            <input type="password" id="passwordconfirm" name="passwordconfirm" maxlength="25" class="medium" onchange="onPasswordConfirmChange(this);" />
        </td>
        <td id="passwordconfirmnote" class="clear"> </td>
    </tr>
    <tr>
        <th>
            <a href="#" onclick="onClearButtonClick('form');">Clear</a>
        </th>
        <td colspan="2">
            <input id="submit" type="submit" value="Update" />
        </td>
    </tr>
</table>
</form>
LOAD;
    }
    
    public function Commit(&$errors)
    {
        // make sure all the fields were submitted
        foreach ($this->fields as $field)
        {
            if (!isset($_POST[$field]))
                return false;
        }
        
        // check the old password
        $oldpassword = sha1($_POST['oldpassword']);
        if (strcmp($this->User->GetPassword(), $oldpassword) != 0)
        {
            $errors = array('The password you entered is incorrect.');
            return false;
        }
        
        // check the new password
        if (!$this->IsPasswordValid($_POST['password']))
        {
            $errors = array('Password must be at least five characters.');
            return false;
        }
        
        // check that they entered the same password twice
        if (strlen($_POST['password'], $_POST['passwordconfirm']) != 0)
        {
            $errors = array('The passwords you entered do not match.');
            return false;
        }
        
        // change the password
        $this->User->SetPassword(sha1($_POST['password']));
                
        if (!$user->Validate($errors))
            return false;
        
        // update
        $this->Database->Execute($this->User->Update($this->Database));
        $this->SetUser($this->User);
        $errors = array('Your password has been changed.');
        return true;
    }
    
    public function ShowErrors($errors)
    {
        if ($errors != null && count($errors) > 0)
            return '<p class="errors">' . implode('<br/>' . PHP_EOL, $errors) . '</p>';
        return null;
    }
}


$change = new ChangePassword();
$change->template->PrintMarkup();

?>
