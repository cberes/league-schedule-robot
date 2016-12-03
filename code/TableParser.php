<?php

class TableParser
{
    public static function GetSource($uri)
    {
        $source = null;
        if (isset($_SERVER['HTTP_USER_AGENT']))
            ini_set('user_agent', $_SERVER['HTTP_USER_AGENT']);
        else
            ini_set('user_agent', RandomUserAgent::Get());
        if ($stream = fopen($uri, 'r'))
        {
            $source = stream_get_contents($stream);
            if ($source === false) $source = null;
            fclose($stream);
        }
        return $source;
    }
    
    public static function GetTables($source)
    {
        return self::GetXmlElements($source, 'table');
    }
    
    public static function GetTableRows($table)
    {
        return self::GetXmlElements($table, 'tr');
    }
    
    public static function GetTableCells($row)
    {
        return self::GetXmlElements($row, 'td|th');
    }
    
    public static function GetTableCellValue($cell)
    {
        return trim(preg_replace('#(&nbsp;)+#mi', ' ', htmlspecialchars_decode(strip_tags($cell))));
    }
    
    private static function GetXmlElements($xml, $element)
    {
        // match the specified elements
        $count = preg_match_all('#<\s*(' . $element . ')(>|\s+.*?>)\s*(.*?)\s*<\s*/\s*\1\s*>#smi',
            $xml, $matches, PREG_PATTERN_ORDER);
        // return the matches
        if ($count > 0 && count($matches) > 3)
            return $matches[3];
        else return null;
    }
}

?>
