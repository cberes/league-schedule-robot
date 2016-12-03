<?php

class Game
{
    private $groupId;
    private $hostIndex;
    private $league;
    private $location;
    private $source;
    private $teams;
    private $time;
    
    public function __construct(DateTime $time, $league, $location = null)
    {
        $this->hostIndex = -1;
        $this->league = $league;
        $this->location = $location;
        $this->time = $time;
        $this->teams = [];
    }
    
    public function AddTeam($name, $host = false)
    {
        $i = count($this->teams);
        $this->teams[$i] = $name;
        if ($host)
            $this->hostIndex = $i;
    }
    
    public function GetGroupId()
    {
        return $this->groupId;
    }
    
    public function GetHomeTeam()
    {
        if ($this->hostIndex < count($this->teams))
            return $this->teams[$this->hostIndex];
        else
            return null;
    }
    
    public function GetLeague()
    {
        return $this->league;
    }
    
    public function GetLocation()
    {
        return $this->location;
    }
    
    public function GetTeamCount()
    {
        return count($this->teams);
    }
    
    public function GetSource()
    {
        return $this->source;
    }
    
    public function GetTime()
    {
        return $this->time;
    }
    
    public function GetVisitingTeams()
    {
        if ($this->hostIndex < count($this->teams))
        {
            // deep copy
            $visitors = $this->teams;
            // remove the the host element and reorder the array
            array_splice($visitors, $this->hostIndex, 1);
            return $visitors;
        }
        else
            return $this->teams;
    }
    
    public function SetGroupId($id)
    {
        $this->groupId = $id;
    }
    
    public function SetLeague($league)
    {
        $this->league = $league;
    }
    
    public function SetLocation($location)
    {
        $this->location = $location;
    }
    
    public function SetSource($source)
    {
        $this->source = $source;
    }
    
    public function SetTime(DateTime $time)
    {
        $this->time = $time;
    }
    
    public function WriteXml()
    {
        // build the XML for the visiting teams
        $visitors = $this->GetVisitingTeams();
        $visitorsXml = null;
        if ($visitors != null && count($visitors) > 0)
        {
            // escape the values
            $count = count($visitors);
            for ($i = 0; $i < $count; ++$i)
                $visitors[$i] = htmlspecialchars($visitors[$i]);
            
            // build the XML in one shot
            $visitorsXml = '<value>' . implode('</value>' . PHP_EOL . '<value>', $visitors) . '</value>' . PHP_EOL;
        }
        
        // build the XML    
        $xml  = '<game>' . PHP_EOL;
        $xml .= '<groupid' . ($this->groupId == null ? '/>' : '>' . htmlspecialchars($this->groupId) . '</groupid>') . PHP_EOL;
        $xml .= '<host' . ($this->league == null ? '/>' : '>' . htmlspecialchars($this->league) . '</host>') . PHP_EOL;
        $xml .= '<league' . ($this->league == null ? '/>' : '>' . htmlspecialchars($this->league) . '</league>') . PHP_EOL;
        $xml .= '<location' . ($this->location == null ? '/>' : '>' . htmlspecialchars($this->location) . '</location>') . PHP_EOL;
        $xml .= '<source' . ($this->source == null ? '/>' : '>' . htmlspecialchars($this->source) . '</source>') . PHP_EOL;
        $xml .= '<time' . ($this->time == null ? '/>' : '>' . $this->time->format('r') . '</time>') . PHP_EOL;
        $xml .= '<visitors' . ($visitorsXml == null ? '/>' : '>' . $visitorsXml . '</visitors>') . PHP_EOL;
        $xml .= '</game>';
        return $xml;
    }
}

?>