<?php

class Itinerary
{
    private $games;
    
    public function __construct()
    {
        $this->games = [];
    }
    
    public function AddSchedule($schedule)
    {
        if ($schedule != null)
            $this->games = array_merge($this->games, $schedule->GetGames());
    }
    
    public function GetGameCount()
    {
        return count($this->games);
    }
    
    public function GetGames()
    {
        return $this->games;
    }
    
    public function Sort()
    {
        usort($this->games, ['Utility', 'CompareGames']);
    }
}

?>