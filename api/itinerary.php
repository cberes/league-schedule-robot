<?php
require_once('../autoloader.php');

class API_Itinerary extends API
{
    private $userid;
    private $futureOnly;
    private $ascending;
    
    public function __construct()
    {
        parent::__construct();
        $this->userid = 0;
        $this->futureOnly = true;
        $this->ascending = true;
    }
    
    public function SetParameters()
    {
        $this->userid = 5;
        $this->futureOnly = true;
        $this->ascending = true;
    }
    
    public function Execute()
    {
        $schedules = Schedule::GetScheduleRows($this->Database, 'userid = ' . $this->userid);
        
        if ($schedules == null || count($schedules) == 0)
            return;
        
        // query for the user's itinerary
        $itinerary = null;
        $itineraries = Itinerary::GetItineraryRows($this->Database, 'userid = ' . $this->userid, 'id');
        if ($itineraries != null)
            $itinerary = $itineraries[0];
        
        // get the current time
        $now = new DateTime();
        
        // add the schedules to the itinerary
        if ($itinerary == null)
            $itinerary = new Itinerary();
        foreach ($schedules as $s)
        {
            $s->UpdateSchedule($this->futureOnly ? $now : null);
            $itinerary->AddSchedule($s);
            
            // update
            $this->Database->Execute($s->Update($this->Database));
        }
        $itinerary->Sort($this->ascending);
        
        // save the schedule contents
        $itinerary->SetScheduleHash($itinerary->BuildHash());
        
        if ($itinerary->GetId() != null) // update
            $this->Database->Execute($itinerary->Update($this->Database));
        else if (!$this->async) // no asynchronous inserts
        {
            // set the user id   
            $itinerary->SetUserid($this->User->GetId());
            
            // insert
            $this->Database->Insert($itinerary->Insert($this->Database), $newid);
            $itinerary->SetId($newid);
        }

        header('Content-type: text/xml');
        echo '<?xml version="1.0" ?>' . PHP_EOL;
        echo $itinerary->WriteXml();
    }
}

$api = new API_Itinerary();
$api->SetParameters();
$api->Execute();
?>