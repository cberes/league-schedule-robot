<?php

class Schedule
{
    private $games;
    private $league;
    private $name;
    private $team;
    
    public function __construct($uri, $team, $name, $league = null)
    {
        $this->games = [];
        $this->league = $league;
        $this->name = $name;
        $this->team = $team;
     
        // parse the schedule   
        $dataRows = ScheduleParser::ParseSchedule($uri);
        if ($dataRows == null) return;
        
        // create games from the data rows
        foreach ($dataRows as $row)
        {
            $game = self::CreateGame($row);
            if ($game != null)
                $this->games[count($this->games)] = $game;
        }
        $this->Sort();
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
    
    private function CreateGame($data)
    {
        // teams must be specified
        $home = null;
        $away = null;
        if (array_key_exists(ScheduleParser::HOME_HEADER, $data)
            && array_key_exists(ScheduleParser::AWAY_HEADER, $data))
        {
            // our team should be one of the teams
            $away = $data[ScheduleParser::AWAY_HEADER];
            $home = $data[ScheduleParser::HOME_HEADER];
            if (!stristr($away, $this->team) && !stristr($home, $this->team))
                return null;
        }
        else return null;
        
        // if the league is specified, it must match
        if ($this->league != null && array_key_exists(ScheduleParser::LEAGUE_HEADER, $data)
            && !stristr($data[ScheduleParser::LEAGUE_HEADER], $this->league))
            return null;
        
        // get the league
        $league = null;
        if (array_key_exists(ScheduleParser::LEAGUE_HEADER, $data))
            $league = $data[ScheduleParser::LEAGUE_HEADER];
        
        // find the date and time   
        $time = null; 
        if (array_key_exists(ScheduleParser::DATE_HEADER, $data)
            && array_key_exists(ScheduleParser::TIME_HEADER, $data))
        {
            $guessedYear = false;
            $now = new DateTime();
            $parsedDate = date_parse($data[ScheduleParser::DATE_HEADER]);
            $parsedTime = date_parse($data[ScheduleParser::TIME_HEADER]);
            
            // check for the parts that are absolutely necessary
            if ($parsedDate['day'] === false || $parsedDate['month'] === false
                || $parsedTime['hour'] === false || $parsedTime['minute'] === false)
                return null;
                
            // date might have the year implied
            if ($parsedDate['year'] === false)
            {
                $parsedDate['year'] = $now->format('Y');
                $guessedYear = true;
            }
            
            $time = new DateTime();
            $time = $time->setDate($parsedDate['year'], $parsedDate['month'], $parsedDate['day']);
            $time = $time->setTime($parsedTime['hour'], $parsedTime['minute']);
            
            if ($guessedYear)
            {
                $gameCount = count($this->games);
                if ($gameCount > 0 && $this->games[$gameCount - 1]->GetTime() > $time)
                {
                    // if we guessed the year, but the game is earlier than the last game,
                    // increment the year
                    $time = $time->setDate($parsedDate['year'] + 1, $parsedDate['month'],
                        $parsedDate['day']);
                }
            }
        }
        if ($time == null) return null;
        
        // location
        $location = null;
        if (array_key_exists(ScheduleParser::LOCATION_HEADER, $data))
            $location = $data[ScheduleParser::LOCATION_HEADER];
        
        // create the game now
        $game = new Game($time, $league ?: $this->name, $location);
        $game->AddTeam($home, true);
        $game->AddTeam($away, false);
        $game->SetGroupId($this->name);
        return $game;
    }
}

?>