<?php
require_once('autoloader.php');
require_once('code/recaptchalib.php');

class ForgotPassword extends BasePage
{
    public $template;
    
    private $fields;
    
    public function __construct()
    {
        parent::__construct(true, false);
        
        // form fields
        $this->fields = ['email', 'submit', 'recaptcha_challenge_field', 'recaptcha_response_field'];
        
        $success = $this->Commit($errors);
        
        // log out the user
        if ($this->User != null)
            header('Location: ' . Utility::GetResource('homeurl'));
        
        $markup = $this->View($success, $errors);
        
        // get the script to insert into the page
        ob_start();
?>
<script type="text/javascript">
//<![CDATA[
    var RecaptchaOptions = {
        theme : 'clean'
    };
//]]>
</script>
<style type="text/css">
/*<![CDATA[*/
    .recaptchatable input, .recaptchatable #recaptcha_response_field
    {
        background: inherit;
        border-radius: 0;
        box-shadow: none;
    }
/*]]>*/
</style>
<?php
        $script = ob_get_contents();
        ob_end_clean();
        
        // fill in the template  
        $this->template = new Template();
        $this->template->SetTitle('League Robot - Forgot Password');
        $this->template->SetBody($markup);
        $this->template->SetHead($script);
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
        
        $resp = recaptcha_check_answer(Utility::GetResource('recaptcha_private'),
            $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
        if (!$resp->is_valid)
        {
            // failed the CAPTCHA
            // see also $resp->error
            $errors = ['You must correctly enter the funny-looking words.'];
            return false;
        }
        
        $email = $this->Database->Escape(Utility::TextOrNull('email', $_POST));
        
        // look up the user
        $users = User::GetUserRows($this->Database, "`email` = '$email'");
        if ($users == null || count($users) != 1)
        {
            // user could not be found
            $errors = ['A user with that email address was not found.'];
            return false;
        }
        
        // the current time
        $now = new DateTime();
        
        //
        $reset = null;
        $resets = PasswordReset::GetPasswordResetRows($this->Database, '`userid` = ' . $users[0]->GetId());
        if ($resets == null || count($resets) == 0)
        {
            $reset = new PasswordReset();
            $reset->SetUserid($users[0]->GetId());
            $reset->SetSecret(hex2bin(sha1(uniqid())));
            $this->Database->Insert($reset->Insert($this->Database), $newid);
            $reset->SetId($newid);
        }
        else if (($expired = strcmp($now->diff($resets[0]->GetTimestamp()->add(new DateInterval('P5D'))->modify('midnight'))->format('%R'), '-') == 0)
            || $resets[0]->GetCount() < 3)
        {
            $reset = $resets[0];
            if ($expired)
                $reset->SetCount(0);
            else
                $reset->SetCount($reset->GetCount() + 1);
            $reset->SetTimestamp($now);
            $this->Database->Execute($reset->Update($this->Database));
        }
        else
        {
            $errors = ['You have reached the limit for password resets. Try again in a few days.'];
            return false;
        }
        
        // send the email
        $param = Utility::BuildSecret($reset->GetSecret(), $reset->GetId());
        $url = Utility::GetResource('baseurl') . '/resetpassword.php?r=' . $param;
        $headers = 'To: ' . $users[0]->GetEmail() . PHP_EOL
            . 'From: ' . Utility::GetResource('emailfrom') . PHP_EOL
            . 'X-Mailer: PHP/' . phpversion();
        $msg = <<<EMAIL
We received a request to reset your password. If you did not request
that your password be reset, please disregard this message.

To reset your password, please click the link below or copy and paste
it into your browser.

$url

You will be prompted to enter a new password. You will then be able
to login with your new password.

If you have any questions, please email us blah blah.
EMAIL;
        if (!mail($users[0]->GetEmail(), 'Password Reset Request', $msg, $headers))
        {
            $errors = ['Could not send the email due to an internal error.'];
            return false;
        }
        
        $errors = ['An email has been sent to you.'];
        return true;
    }
    
    public function View($success, $errors)
    {
        // generate the form's html
        ob_start();
?>

<div class="section">
    <form id="forgotPassword" name="forgotPassword" action="forgotpassword.php" method="post">
    <h1>Reset your Password</h1>
    <?php if ($success): ?>
        <p>
            An email containing instructions to reset your password has been sent to you.
        </p>
        <p>
            Return to the <a href="index.php">main page</a>.
        </p>
    <?php else: ?>
        <p>
            Enter your email address, and we'll send you a link to reset your password.
        </p>
        <p>
            If you know your password, return to the <a href="index.php">main page</a> to log in.
        </p>
        <?= self::FormatErrors($errors) ?>
        <p>
            <label for="email">Email address:</label>
            <input id="email" name="email" type="email" maxlength="100" />
        </p>
        <?= recaptcha_get_html(Utility::GetResource('recaptcha_public')) ?>
        <p>
            <input id="submit" name="submit" type="submit" value="Submit" />
        </p>
    <?php endif; ?>
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

$page = new ForgotPassword();

?>
