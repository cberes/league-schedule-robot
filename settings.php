<?php
require_once('autoloader.php');

class Settings extends BasePage
{
    public $template;
        
    private $emailSettingsFields;
        
    private $emailFields;
        
    private $generalSettingsFields;
        
    private $passwordFields;
    
    private $longDateFormats;
    
    private $shortDateFormats;
    
    private $timeFormats;
    
    public function __construct()
    {
        parent::__construct(true, true);
        
        // date/time formats
        $this->longDateFormats = ['l, F j, Y', 'D, M j, Y', 'F j, Y', 'M j, Y', 'j F Y', 'Y F j',
            'j M Y', 'Y M j'];
        $this->shortDateFormats = ['Y/m/d', 'n/j/Y', 'n/j/y', 'j/n/y'];
        $this->timeFormats = ['H:i', 'g:i A'];
        
        // the checkboxes will be unset if not checked
        $this->emailSettingsFields = ['saveEmailSettings'];
        
        $this->emailFields = ['email', 'saveEmailAddress', 'password'];
        
        $this->generalSettingsFields = ['longDateFormat', 'shortDateFormat', 'timeFormat', 'saveGeneralSettings'];
        
        $this->passwordFields = ['newPassword', 'confirmNewPassword', 'savePassword', 'password'];
        
        // figure out what we're doing
        $emailSettingsChanged = true;
        foreach ($this->emailSettingsFields as $field)
        {
            if (!isset($_POST[$field]))
            {
                $emailSettingsChanged = false;
                break;
            }
        }
        $emailChanged = true;
        foreach ($this->emailFields as $field)
        {
            if (!isset($_POST[$field]))
            {
                $emailChanged = false;
                break;
            }
        }
        $generalSettingsChanged = true;
        foreach ($this->generalSettingsFields as $field)
        {
            if (!isset($_POST[$field]))
            {
                $generalSettingsChanged = false;
                break;
            }
        }
        $passwordChanged = true;
        foreach ($this->passwordFields as $field)
        {
            if (!isset($_POST[$field]))
            {
                $passwordChanged = false;
                break;
            }
        }
        
        // get the user's localization settings
        $loc = Localization::GetLocalizationRows($this->Database, 'userid = ' . $this->User->GetId());
        if ($loc != null)
            $loc = $loc[0];
        
        // get the user's email settings
        $emailSettings = ContactSettings::GetContactSettingsRows($this->Database, "type = '"
            . ContactSettings::TYPE_EMAIL . "' AND userid = " . $this->User->GetId());
        if ($emailSettings != null)
            $emailSettings = $emailSettings[0];
        
        // the user should always have these
        if ($loc == null || $emailSettings == null)
            header('Location: ' . Utility::GetResource('loginurl'));
        
        // process the form
        if ($emailSettingsChanged)
        {
            echo $this->CommitEmailSettings($emailSettings);
            return;
        }
        else if ($emailChanged)
        {
            echo $this->CommitEmailAddress($emailSettings);
            return;
        }
        else if ($generalSettingsChanged)
        {
            echo $this->CommitGeneralSettings($loc);
            return;
        }
        else if ($passwordChanged)
        {
            echo $this->CommitPassword();
            return;
        }
       
        // print the whole page
        $markup = $this->View($loc, $emailSettings);
        
        // get the script to insert into the page
        ob_start();
?>
<script type="text/javascript">
//<![CDATA[
    function disableSubmit()
    {
        disableDefaultAction('saveEmailSettings', 'click');
        disableDefaultAction('saveEmailAddress', 'click');
        disableDefaultAction('saveGeneralSettings', 'click');
        disableDefaultAction('savePassword', 'click');
        disableDefaultAction('passwordOK', 'click');
        disableDefaultAction('passwordCancel', 'click');
    }
    function onDailyEmailFrequencyCheckboxChange(element)
    {
        var otherBox = document.getElementById('emailOnChange');
        if (element.checked)
            otherBox.checked = false;
        otherBox.disabled = element.checked;
    }
//]]>
</script>
<?php
        $script = ob_get_contents();
        ob_end_clean();
        
        // fill in the template  
        $this->template = new Template();
        $this->template->SetTitle('League Robot - Settings');
        $this->template->SetBody($markup);
        $this->template->SetHead($script);
        $this->template->SetLoadFunc("disableSubmit();");
        $this->template->SetShowUserLinks(true);
        $this->template->PrintMarkup();
    }

    private function CommitEmailAddress(ContactSettings &$es)
    {
        $errors = [];
        $updateEmail = true;
        // verify the user
        $pw = $_POST['password'];
        if ($this->User->GetPassword() != hex2bin(sha1($pw)))
        {
            $errors[] = 'The password you entered did not match the one we have on record.';
            $updateEmail = false;
        }
        
        // get the new email
        $email = Utility::TextOrNull('email', $_POST);
        // current email address
        if (strcasecmp($email, $this->User->GetEmail()) == 0)
        {
            $errors[] = 'This is your current email address.';
            $updateEmail = false;
        }
        // no one else can be registered with this email
        if ($this->FindUsersByEmail($email) != null)
        {
            $errors[] = 'A user is already registered with that email address.';
            $updateEmail = false;
        }
        
        // get the user and try updating the field
        $user = User::GetUserRow($this->Database, $this->User->GetId());
        $user->SetEmail($email);
        if (!$user->Validate($errors2))
        {
            $errors = array_merge($errors, $errors2);
            $updateEmail = false;
        }
        
        // update the user
        if ($updateEmail)
        {
            $this->Database->Execute($user->Update($this->Database));
            $this->SetUser($user);
            
            // update the email contact
            $es->SetContact($email);
            $this->Database->Execute($es->Update($this->Database));
        }
        
        $pairs = [];
        $pairs[] = 'loading-email.style.display=none';
        $pairs[] = 'email.value=';
        if ($updateEmail)
        {
            // success
            $pairs[] = 'success.style.display=block';
            $pairs[] = 'success.style.opacity=1';
            $pairs[] = self::RESPONSE_KEY_SCRIPT . "=FadeEffect.init('success', 0, 0, 2500);";
            $pairs[] = 'emailErrors.style.display=none';
        }
        else
        {
            // failure
            $pairs[] = 'failure.style.display=block';
            $pairs[] = 'failure.style.opacity=1';
            $pairs[] = self::RESPONSE_KEY_SCRIPT . "=FadeEffect.init('failure', 0, 0, 2500);";
            $pairs[] = 'emailErrors.style.display=block';
            $pairs[] = 'emailErrors.innerHTML=' . self::FormatErrors($errors);
        }
        return implode(self::SEPARATOR, $pairs);
    }

    private function CommitEmailSettings(ContactSettings &$es)
    {
        // contact frequency
        $es->SetFrequency(self::GetFrequency(Utility::TextOrNull('emailFrequency', $_POST)));
        // notify on change
        $es->SetNotifyOnChange((bool)Utility::TextOrNull('emailOnChange', $_POST));
        
        // commit the changes
        $this->Database->Execute($es->Update($this->Database));
        
        $pairs = [];
        $pairs[] = 'loading-emailSettings.style.display=none';
        $pairs[] = 'success.style.display=block';
        $pairs[] = 'success.style.opacity=1';
        $pairs[] = self::RESPONSE_KEY_SCRIPT . "=FadeEffect.init('success', 0, 0, 2500);";
        return implode(self::SEPARATOR, $pairs);
    }

    private function CommitGeneralSettings(Localization &$loc)
    {
        // long date format
        $ldf = Utility::TextOrNull('longDateFormat', $_POST);
        if (in_array($ldf, $this->longDateFormats))
            $loc->SetLongDateFormat($ldf);
        // short date format
        $sdf = Utility::TextOrNull('shortDateFormat', $_POST);
        if (in_array($sdf, $this->shortDateFormats))
            $loc->SetShortDateFormat($sdf);
        // time format
        $tf = Utility::TextOrNull('timeFormat', $_POST);
        if (in_array($tf, $this->timeFormats))
            $loc->SetTimeFormat($tf);
        
        // commit the changes
        $this->Database->Execute($loc->Update($this->Database));
        
        $pairs = [];
        $pairs[] = 'loading-general.style.display=none';
        $pairs[] = 'success.style.display=block';
        $pairs[] = 'success.style.opacity=1';
        $pairs[] = self::RESPONSE_KEY_SCRIPT . "=FadeEffect.init('success', 0, 0, 2500);";
        return implode(self::SEPARATOR, $pairs);
    }

    private function CommitPassword()
    {
        $errors = [];
        $updatePassword = true;
        // verify the user
        $pw = $_POST['password'];
        if ($this->User->GetPassword() != hex2bin(sha1($pw)))
        {
            $errors[] = 'The password you entered did not match the one we have on record.';
            $updatePassword = false;
        }
        
        // get the new passwords
        $p1 = $_POST['newPassword'];
        $p2 = $_POST['confirmNewPassword'];
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
        $user = User::GetUserRow($this->Database, $this->User->GetId());
        $user->SetPassword(hex2bin(sha1($p1)));
        if (!$user->Validate($errors2))
        {
            $errors = array_merge($errors, $errors2);
            $updatePassword = false;
        }
        
        // update the user
        if ($updatePassword)
        {
            $this->Database->Execute($user->Update($this->Database));
            $this->SetUser($user);
        }
        
        $pairs = [];
        $pairs[] = 'loading-password.style.display=none';
        $pairs[] = 'newPassword.value=';
        $pairs[] = 'confirmNewPassword.value=';
        if ($updatePassword)
        {
            // success
            $pairs[] = 'success.style.display=block';
            $pairs[] = 'success.style.opacity=1';
            $pairs[] = self::RESPONSE_KEY_SCRIPT . "=FadeEffect.init('success', 0, 0, 2500);";
            $pairs[] = 'passwordErrors.style.display=none';
        }
        else
        {
            // failure
            $pairs[] = 'failure.style.display=block';
            $pairs[] = 'failure.style.opacity=1';
            $pairs[] = self::RESPONSE_KEY_SCRIPT . "=FadeEffect.init('failure', 0, 0, 2500);";
            $pairs[] = 'passwordErrors.style.display=block';
            $pairs[] = 'passwordErrors.innerHTML=' . self::FormatErrors($errors);
        }
        return implode(self::SEPARATOR, $pairs);
    }

    private static function GetFrequency($value)
    {
        // possible contact frequency values
        $freqs = [];
        $freqs['Daily'] = ContactSettings::FREQUENCY_DAILY;
        $freqs['Weekly'] = ContactSettings::FREQUENCY_WEEKLY;
        $freqs['Monthly'] = ContactSettings::FREQUENCY_MONTHLY;
        
        if (array_key_exists($value, $freqs))
            return $freqs[$value];
        else return 0;
    }
    
    public function View(Localization $loc, ContactSettings $es)
    {
        $now = new DateTime();
        
        // generate the form's html
        ob_start();
?>

<div id="success" class="success" style="display: none; opacity: 1;">
    Your settings were saved.
</div>

<div id="failure" class="failure" style="display: none; opacity: 1;">
    There was an error saving your settings.
</div>

<div id='popup' class="popup" style="display: none;">
    <div>
        <form id="passwordForm" name="passwordForm" action="settings.php" method="post">
        <input type="hidden" id="passwordFormExtraData" name="passwordFormExtraData" />
        <h1>Save Account Changes</h1>
        <p>You must enter your password before your changes are saved.</p>
        <p>
            <label for="password">Password:</label>
            <input id="password" name="password" type="password" maxlength="100" class="dark" />
        </p>
        <div class="buttons">
            <input type="submit" id="passwordOK" name="passwordOK" value="OK"
                onclick="onPasswordTestSubmit('popup', 'passwordFormExtraData', 'passwordForm');" />
            <input type="submit" id="passwordCancel" name="passwordCancel" value="Cancel" onclick="closePopup(this, 'popup', 'passwordForm');" />
        </div>
        </form>
    </div>
</div>

<div class="section">
    <form id="generalForm" name="generalForm" action="settings.php" method="post">
    <h1>General Settings</h1>
    <table class="form-fields">
        <tr>
            <th>
                <label for="longDateFormat">Long date format:</label>
            </th>
            <td>
                <select id="longDateFormat" name="longDateFormat">
                <?php foreach ($this->longDateFormats as $format): ?>
                    <option value="<?= $format ?>"
                    <?php if ($format == $loc->GetLongDateFormat()): ?>
                        selected="selected"
                    <?php endif; ?>
                    ><?= $now->format($format) ?></option>
                <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <label for="shortDateFormat">Short date format:</label>
            </th>
            <td>
                <select id="shortDateFormat" name="shortDateFormat">
                <?php foreach ($this->shortDateFormats as $format): ?>
                    <option value="<?= $format ?>"
                    <?php if ($format == $loc->GetShortDateFormat()): ?>
                        selected="selected"
                    <?php endif; ?>
                    ><?= $now->format($format) ?></option>
                <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <label for="timeFormat">Time format:</label>
            </th>
            <td>
                <select id="timeFormat" name="timeFormat">
                <?php foreach ($this->timeFormats as $format): ?>
                    <option value="<?= $format ?>"
                    <?php if ($format == $loc->GetTimeFormat()): ?>
                        selected="selected"
                    <?php endif; ?>
                    ><?= $now->format($format) ?></option>
                <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>
    <p>
        <input id="saveGeneralSettings" name="saveGeneralSettings" type="submit" value="Save Settings"
            onclick="AjaxPost('settings.php', GetRequestParams('generalForm'), PopulateFormAdvanced, '<?= self::PROGRESS_UPDATE_ID ?>', 'loading-general');" />
        <span id="loading-general" style="visibility: hidden;">
            Loading
            <img src="img/loading.gif" alt="loading" />
        </span>
    </p>
    </form>
</div>

<div class="section">
    <form id="emailSettingsForm" name="emailSettingsForm" action="settings.php" method="post">
    <h1>Email Settings</h1>
    <p>
        Your email updates are sent to <span id="contactEmail"><?= $es->GetContact() ?></span>.
    </p>
    <p>
        Email updates will be sent<br/>&nbsp;&nbsp;&nbsp;&nbsp;
        <input id="emailFrequencyDaily" name="emailFrequency" value="Daily" type="checkbox" onclick="onSingletonCheckboxGroupClick(this);" onchange="onDailyEmailFrequencyCheckboxChange(this);"
        <?php if ($es->GetFrequency() & ContactSettings::FREQUENCY_DAILY): ?>
            checked="checked"
        <?php endif; ?>
        />
        <label for="emailFrequencyDaily">Daily</label>&nbsp;&nbsp;&nbsp;&nbsp;
        <input id="emailFrequencyWeekly" name="emailFrequency" value="Weekly" type="checkbox" onclick="onSingletonCheckboxGroupClick(this);"
        <?php if ($es->GetFrequency() & ContactSettings::FREQUENCY_WEEKLY): ?>
            checked="checked"
        <?php endif; ?>
        />
        <label for="emailFrequencyWeekly">Weekly</label>&nbsp;&nbsp;&nbsp;&nbsp;
        <input id="emailFrequencyMonthly" name="emailFrequency" value="Monthly" type="checkbox" onclick="onSingletonCheckboxGroupClick(this);"
        <?php if ($es->GetFrequency() & ContactSettings::FREQUENCY_MONTHLY): ?>
            checked="checked"
        <?php endif; ?>
        />
        <label for="emailFrequencyMonthly">Monthly</label>
    </p>
    <p>
        <input id="emailOnChange" name="emailOnChange" value="OnChange" type="checkbox"
        <?php if ($es->GetNotifyOnChange()): ?>
            checked="checked"
        <?php endif; ?>
        />
        <label for="emailOnChange">Receive emails when changes to your schedule are detected.</label>
    </p>
    <p>
        <input id="saveEmailSettings" name="saveEmailSettings" type="submit" value="Save Email Settings"
            onclick="AjaxPost('settings.php', GetRequestParams('emailSettingsForm'), PopulateFormAdvanced, '<?= self::PROGRESS_UPDATE_ID ?>', 'loading-emailSettings');" />
        <span id="loading-emailSettings" style="visibility: hidden;">
            Loading
            <img src="img/loading.gif" alt="loading" />
        </span>
    </p>
    </form>
</div>

<div class="section">
    <form id="emailAddressForm" name="emailAddressForm" action="settings.php" method="post">
    <h1>Email Address</h1>
    <div id="emailErrors" style="display: none;"></div>
    <p>
        <label for="email">New email address:</label>
        <input id="email" name="email" type="email" maxlength="100" />
    </p>
    <p>
        <!-- onclick="AjaxPost('settings.php', GetMultipleRequestParams('emailAddressForm', 'passwordForm'), PopulateFormAdvanced, '<?= self::PROGRESS_UPDATE_ID ?>', 'loading-email');" /> -->
        <input id="saveEmailAddress" name="saveEmailAddress" type="submit" value="Save Email Address"
            onclick="onSubmitPasswordRequired('popup', 'passwordFormExtraData', 'AjaxPost(\'settings.php\', GetMultipleRequestParams(\'emailAddressForm\', \'passwordForm\'), PopulateFormAdvanced, \'<?= self::PROGRESS_UPDATE_ID ?>\', \'loading-email\')');" />
        <span id="loading-email" style="visibility: hidden;">
            Loading
            <img src="img/loading.gif" alt="loading" />
        </span>
    </p>
    </form>
</div>

<div class="section">
    <form id="newPasswordForm" name="newPasswordForm" action="settings.php" method="post">
    <h1>Password</h1>
    <div id="passwordErrors" style="display: none;"></div>
    <table class="form-fields">
        <tr>
            <th>
                <label for="newPassword">New password:</label>
            </th>
            <td>
                <input id="newPassword" name="newPassword" type="password" maxlength="100" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="confirmNewPassword">New password (again):</label>
            </th>
            <td>
                <input id="confirmNewPassword" name="confirmNewPassword" type="password" maxlength="100" />
            </td>
        </tr>
    </table>
    <p>
        <!-- onclick="AjaxPost('settings.php', GetMultipleRequestParams('newPasswordForm', 'passwordForm'), PopulateFormAdvanced, '<?= self::PROGRESS_UPDATE_ID ?>', 'loading-password');" /> -->
        <input id="savePassword" name="savePassword" type="submit" value="Save Password"
            onclick="onSubmitPasswordRequired('popup', 'passwordFormExtraData', 'AjaxPost(\'settings.php\', GetMultipleRequestParams(\'newPasswordForm\', \'passwordForm\'), PopulateFormAdvanced, \'<?= self::PROGRESS_UPDATE_ID ?>\', \'loading-password\')');" />
        <span id="loading-password" style="visibility: hidden;">
            Loading
            <img src="img/loading.gif" alt="loading" />
        </span>
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

$page = new Settings();

?>
