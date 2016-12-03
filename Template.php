<?php

class Template
{
    private $body;
    private $head;
    private $title;
    private $showNav;
    private $showUserLinks;
    private $loadFunc;
    
    public function __construct($showNav = true)
    {
        $this->showNav = $showNav;
        $this->showUserLinks = false;
    }

    private function OnLoad($default)
    {
        echo isset($this->loadFunc) ? ' onload="' . $this->loadFunc . '"' : $default;
    }

    private function PrintBody($default)
    {
        echo isset($this->body) ? $this->body : $default;
    }
    
    private function PrintHead($default)
    {
        echo isset($this->head) ? $this->head : $default;
    }
    
    private function PrintTitle($default)
    {
        echo isset($this->title) ? $this->title : $default;
    }

    public function SetBody($value)
    {
        $this->body = $value;
    }
    
    public function SetHead($value)
    {
        $this->head = $value;
    }
    
    public function SetLoadFunc($value)
    {
        $this->loadFunc = $value;
    }
    
    public function SetShowUserLinks($value)
    {
        $this->showUserLinks = $value;
    }
    
    public function SetTitle($value)
    {
        $this->title = $value;
    }
    
    public function PrintMarkup()
    {
?>
<!DOCTYPE html>
<html>
<head>
<title><?php $this->printTitle('Template'); ?></title>
<meta charset="UTF-8" />
<meta name="description" content="Track and view multiple league and sports team schedules in one place. Receive email updates when game and meeting times change." />
<meta name="author" content="Corey Beres" />
<link href="css/main.css" rel="stylesheet" type="text/css" />
<link href="css/normalize.css" rel="stylesheet" type="text/css" />
<link href="css/layout.css" rel="stylesheet" type="text/css" />
<link href="css/widgets.css" rel="stylesheet" type="text/css" />
<script src="js/ajax.js" type="text/javascript"></script>
<script src="js/util.js" type="text/javascript"></script>
<?php $this->printHead(null); ?>
</head>
<body<?php $this->OnLoad(''); ?>>

<div id="header">
    <div id="title">
        League Robot
    </div>
    <?php if ($this->showNav): ?>
    <div id="navigation">
        <a href="index.php">Home</a>
        &bull; <a href="howitworks.php">How It Works</a>
        &bull; <a href="comingsoon.php">Coming Soon</a>
        <?php if ($this->showUserLinks): ?>
        &bull; <a href="settings.php">Settings</a>
        &bull; <a href="logout.php">Log Out</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <div id="subtitle">
        View your teams' schedules in one place, and have your game times emailed to you.
    </div>
</div>

<div id="content">
<?php $this->printBody(null); ?>
</div>

<div id="footer">
    &copy; 2012 Corey Beres.
</div>

</body>
</html>
<?php
    }
}
?>
