<?php
require_once('autoloader.php');

class ResetPassword extends BasePage
{
    public $template;
    
    private $fields;
    
    public function __construct()
    {
        parent::__construct(true, false);
        
        // form fields
        $this->fields = ['secret', 'email', 'password', 'confirmPassword', 'savePassword'];
        
        // go to the home page
        if ($this->User != null)
            header('Location: ' . Utility::GetResource('homeurl'));
        
        // get the secret; try get and post
        $secret = Utility::TextOrNull('r', $_GET);
        if (!$secret)
            $secret = Utility::TextOrNull('secret', $_POST);
        
        // procress the form
        if ($this->Commit($errors))
            header('Location: ' . Utility::GetResource('loginurl'));
        else if (!$errors && !$secret)
            $errors = ['Invalid or missing password-reset key.'];
        
        // output the page
        $markup = $this->View($secret, $errors);
        
        // fill in the template  
        $this->template = new Template();
        $this->template->SetTitle('League Robot - Reset Password');
        $this->template->SetBody($markup);
        $this->template->PrintMarkup();
    }
    
    public function Commit(&$errors)
    {
        $errors = null;
        // make sure all the fields were submitted
        foreach ($this->fields as $field)
        {
            if (!isset($_POST[$field]))
                return false;
        }
        
        // get the code
        $code = $_POST['secret'];
        if (!Utility::ParseSecret($code, $secret, $rid) || !is_numeric($rid))
        {
            $errors = ['The password-reset key was invalid1.'];
            return false;
        }
        
        // get the reset
        $reset = PasswordReset::GetPasswordResetRow($this->Database, $rid);
        if ($reset == null || $reset->GetSecret() != $secret)
        {
            $errors = ['The password-reset key was invalid2.'];
            return false;
        }
        
        // get the user
        $user = User::GetUserRow($this->Database, $reset->GetUserid());
        if ($user == null)
        {
            $errors = ['The password-reset key was invalid3.'];
            return false;
        }
        
        // check the password reset date
        $now = new DateTime();
        if (strcmp($now->diff($reset->GetTimestamp()->add(new DateInterval('P5D'))->modify('midnight'))->format('%R'), '-') == 0)
        {
            $this->Database->Execute($reset->Delete($this->Database));
            $errors = ['Your password request change has expired.'];
            return false;
        }
        
        $errors = [];
        $updatePassword = true;
        
        // get the email address; it must match the user's email address
        $email = Utility::TextOrNull('email', $_POST);
        if (strcasecmp($email, $user->GetEmail()) != 0)
        {
            $errors[] = 'The email address did not match the one we have on record.';
            $updatePassword = false;
        }
        
        // get the new passwords
        $p1 = $_POST['password'];
        $p2 = $_POST['confirmPassword'];
        // passwords must match
        if ($p1 != $p2)
        {
            $errors[] = 'The new passwords must match.';
            $updatePassword = false;
        }
        // password length
        if (!$this->IsPasswordValid($p1))
        {
            $errors[] = 'The new password must be at least 5 characters long.';
            $updatePassword = false;
        }
        
        // get the user and try updating the field
        $user->SetPassword(hex2bin(sha1($p1)));
        if (!$user->Validate($errors2))
        {
            $errors = array_merge($errors, $errors2);
            $updatePassword = false;
        }
        
        // update the user
        if ($updatePassword)
        {
            // commit the user
            $this->Database->Execute($user->Update($this->Database));
            // delete password-reset row
            $this->Database->Execute($reset->Delete($this->Database));
            $errors[] = 'Your password was changed successfully.';
            return true;
        }
        return false;
    }
    
    public function View($secret, $errors)
    {
        // generate the form's html
        ob_start();
?>

<div class="section">
    <form id="passwordForm" name="passwordForm" action="resetpassword.php" method="post">
    <input id="secret" name="secret" type="hidden" value="<?= $secret ?>" />
    <h1>Change your Password</h1>
    <p>
        If you know your password, return to the <a href="index.php">main page</a> to log in.
    </p>
    <?= self::FormatErrors($errors) ?>
    <table class="form-fields">
        <tr>
            <th>
                <label for="email">Email address:</label>
            </th>
            <td>
                <input id="email" name="email" type="email" maxlength="100" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="password">New password:</label>
            </th>
            <td>
                <input id="password" name="password" type="password" maxlength="100" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="confirmPassword">New password (again):</label>
            </th>
            <td>
                <input id="confirmPassword" name="confirmPassword" type="password" maxlength="100" />
            </td>
        </tr>
    </table>
    <p>
        <input id="savePassword" name="savePassword" type="submit" value="Save Password" />
    </p>
    </form>
</div>

<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private static function FormatErrors($errors)
    {
        if ($errors != null && count($errors) > 0)
            return '<p class="errors">' . implode('<br/>' . PHP_EOL, $errors) . '</p>';
        return null;
    }
}

$page = new ResetPassword();

?>
