<?php

class ScheduleParser
{
    // these need to be public
    const DATE_HEADER = 'DATE';
    const TIME_HEADER = 'TIME';
    const LEAGUE_HEADER = 'LEAGUE';
    const LOCATION_HEADER = 'LOCATION';
    const HOME_HEADER = 'HOME';
    const AWAY_HEADER = 'AWAY';
    const SCORE_HEADER = 'SCORE';
    
    // this would be private
    const MIN_COLUMNS = 3;
    
    public static function ParseSchedule($uri)
    {
        // look up the source in the cache
        $source = apc_fetch($uri);
        if ($source === false)
        {
            // get the source
            $source = TableParser::GetSource($uri);
            // cache it
            apc_store($uri, $source, Utility::CACHE_LENGTH_SOURCE_EXTERNAL);
        }
        //$source = TableParser::GetSource($uri);
        if ($source == null || $source === false) return null;
        
        // get the tables
        $tables = TableParser::GetTables($source);
        if ($tables == null) return null;
        
        // look up if the uri needs headers
        $headers = self::GetHeader($uri);
        
        $schedule = null;
        foreach ($tables as $table)
        {
            // parse the table
            $temp = self::ParseGrid($table, $headers);
            // keep the table if it has more rows than the previous table
            if ($temp != null && ($schedule == null || count($temp) > count($schedule)))
                $schedule = $temp; // deep copy
                
            // try the other method of parsing
            $temp = self::ParseTable($table, $headers);
            // keep the table if it has more rows than the previous table
            if ($temp != null && ($schedule == null || count($temp) > count($schedule)))
                $schedule = $temp; // deep copy
        }
        return $schedule;
    }
    
    private static function GetHeader($uri)
    {
        // get the header lookup key: prefer the host, then the filename
        $key = null;
        $parts = parse_url($uri);
        if (array_key_exists('host', $parts))
            $key = $parts['host'];
        else if (array_key_exists('path', $parts))
            $key = basename($parts['path']);
        else return null;
        
        // look up the header string
        $headerString = Utility::GetResource($key . '_headers');
        
        // split the headers by the pipe
        if ($headerString != null)
            return explode('|', $headerString);
        return null;
    }
    
    // private static function ParseGrid($table)
    // {
        // // get the rows in the table
        // $rows = TableParser::GetTableRows($table);
        // if ($rows == null) return null;
//         
        // // some schedules are stupid and have the date in its own row above the games that day
        // $dateMapped = false;
        // $date = null;
//         
        // // the headers for mapped columns, and the count for the array
        // $headers = null;
        // $headerCount = 0;
//         
        // // the data row, and the row count
        // $dataRows = [];
        // $rowCount = 0;
//         
        // // iterate through the rows
        // foreach ($rows as $row)
        // {
            // // get the table cells
            // $cells = TableParser::GetTableCells($row);
            // if ($cells == null) continue;
//     
            // // get the row count
            // $cellCount = count($cells);
//             
            // // map the headers
            // if ($headers == null && $cellCount >= self::MIN_COLUMNS)
            // {
                // // map the columns
                // $headers = self::MapColumns($cells);
                // if ($headers != null)
                // {
                    // // success
                    // $headerCount = $cellCount;
                    // $dateMapped = in_array(self::DATE_HEADER, $headers);
                // }
                // continue;
            // }
//             
            // // if the are no headers, try again with the next row
            // if ($headers == null) continue;
//             
            // if ($cellCount == 1 && !$dateMapped)
            // {
                // // assume the date is 
                // $value = TableParser::GetTableCellValue($cells[0]);
                // // TODO: parse the date so we know it's valid
                // $date = $value;
                // continue;
            // }
//             
            // // the data must have a certain amount of columns
            // if ($cellCount < self::MIN_COLUMNS) continue;
//             
            // // fill in the data
            // $dataRow = [];
            // for ($i = 0; $i < $cellCount && $i < $headerCount; ++$i)
            // {
                // // get the datum only if the column is mapped
                // if ($headers[$i] != null)
                    // $dataRow[$headers[$i]] = TableParser::GetTableCellValue($cells[$i]);
            // }
//             
            // if (!$dateMapped)
            // {
                // if ($date != null) // fill in the date for this row
                    // $dataRow[self::DATE_HEADER] = $date;
                // else // row is invalid
                    // continue;
            // }
//             
            // // add the row
            // $dataRows[$rowCount++] = $dataRow;
        // }
//         
        // // end result is array of ['DATE', 'TIME', 'HOME', 'AWAY', 'LOCATION', 'SCORE'] as strings
        // if ($rowCount == 0)
            // return null;
        // return $dataRows;
    // }
    
    private static function ParseGrid($table, $headers)
    {
        // get the rows in the table
        $grid = self::ReadGrid($table);
        if ($grid == null) return null;
        
        // add a header row
        if ($headers != null)
            array_unshift($grid, $headers);
        
        return self::ParseDataGrid($grid);
    }
    
    private static function ReadGrid($table)
    {
        // get the rows in the table
        $rows = TableParser::GetTableRows($table);
        if ($rows == null) return null;
        
        // iterate through the rows
        $rowCount = count($rows);
        for ($r = 0; $r < $rowCount; ++$r)
        {
            $cells = TableParser::GetTableCells($rows[$r]);
            if ($cells == null) continue;
            
            $cellCount = count($cells);
            for ($c = 0; $c < $cellCount; ++$c)
                $cells[$c] = TableParser::GetTableCellValue($cells[$c]);
            $rows[$r] = $cells;
        }
        return $rows;
    }
    
    private static function ParseDataGrid($rows)
    {
        // some schedules are stupid and have the date in its own row above the games that day
        $dateMapped = false;
        $date = null;
        
        // the headers for mapped columns, and the count for the array
        $headers = null;
        $headerCount = 0;
        
        // the data row, and the row count
        $dataRows = [];
        $rowCount = 0;
        
        // iterate through the rows
        foreach ($rows as $row)
        {
            // get the table cells
            $cells = &$row;
    
            // get the row count
            $cellCount = count($cells);
            
            // map the headers
            if ($headers == null && $cellCount >= self::MIN_COLUMNS)
            {
                // map the columns
                $headers = self::MapColumns($cells);
                if ($headers != null)
                {
                    // success
                    $headerCount = $cellCount;
                    $dateMapped = in_array(self::DATE_HEADER, $headers);
                }
                continue;
            }
            
            // if the are no headers, try again with the next row
            if ($headers == null) continue;
            
            if ($cellCount == 1 && !$dateMapped)
            {
                // assume its a date
                // TODO: parse the date so we know it's valid
                $date = $cells[0];
                continue;
            }
            
            // the data must have a certain amount of columns
            if ($cellCount < self::MIN_COLUMNS) continue;
            
            // fill in the data
            $dataRow = [];
            for ($i = 0; $i < $cellCount && $i < $headerCount; ++$i)
            {
                // get the datum only if the column is mapped
                if ($headers[$i] != null)
                    $dataRow[$headers[$i]] = $cells[$i];
            }
            
            if (!$dateMapped)
            {
                if ($date != null) // fill in the date for this row
                    $dataRow[self::DATE_HEADER] = $date;
                else // row is invalid
                    continue;
            }
            
            // add the row
            $dataRows[$rowCount++] = $dataRow;
        }
        
        // end result is array of ['DATE', 'TIME', 'HOME', 'AWAY', 'LOCATION', 'SCORE'] as strings
        if ($rowCount == 0)
            return null;
        return $dataRows;
    }
    
    private static function ConvertTableToDataGrid($table)
    {
        // get the rows in the table
        $rows = TableParser::GetTableRows($table);
        if ($rows == null) return null;
        
        // iterate through the rows
        $dataRows = [];
        $rowCount = 0;
        foreach ($rows as $row)
        {
            $cells = TableParser::GetTableCells($row);
            if ($cells == null) continue;
            
            foreach ($cells as $cell)
            {
                $value = TableParser::GetTableCellValue($cell);
                $dataRows[$rowCount++] = explode("\n", $value);
                $cellCount = count($dataRows[$rowCount - 1]);
                for ($c = 0; $c < $cellCount; ++$c)
                    $dataRows[$rowCount - 1][$c] = trim($dataRows[$rowCount - 1][$c]);
            }
        }
        
        if ($rowCount == 0)
            return null;
        return $dataRows;
    }
    
    private static function ParseTable($table, $headers)
    {
        // read the table as a grid
        $grid = self::ConvertTableToDataGrid($table);
        
        // add a header row
        if ($headers != null)
            array_unshift($grid, $headers);
        
        // read the table as a grid
        return self::ParseDataGrid($grid);
    }
    
    private static function MapColumns($cells)
    {
        $foundTime = false;
        $foundHome = false;
        $foundAway = false;
        
        $cellCount = count($cells);
        for ($i = 0; $i < $cellCount; ++$i)
        {
            $value = $cells[$i];
            if (stristr($value, 'DATE'))
            {
                $cells[$i] = self::DATE_HEADER;
            }
            else if (stristr($value, 'TIME'))
            {
                $cells[$i] = self::TIME_HEADER;
                $foundTime = true;
            }
            else if (stristr($value, 'HOME'))
            {
                $cells[$i] = self::HOME_HEADER;
                $foundHome = true;
            }
            else if (stristr($value, 'SCORE'))
            {
                $cells[$i] = self::SCORE_HEADER;
            }
            else if (stristr($value, 'AWAY') || stristr($value, 'VISITOR'))
            {
                $cells[$i] = self::AWAY_HEADER;
                $foundAway = true;
            }
            else if (stristr($value, 'LOCATION') || stristr($value, 'VENUE'))
            {
                $cells[$i] = self::LOCATION_HEADER;
            }
            else if (stristr($value, 'DIV') || stristr($value, 'LEAGUE'))
            {
                $cells[$i] = self::LEAGUE_HEADER;
            }
            else
                $cells[$i] = null;
        }
        
        if (!$foundAway || !$foundHome || !$foundTime)
            return null;
        return $cells;        
    }
}

?>
