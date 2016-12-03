<?php

class Utility
{
    const CACHE_LENGTH_SOURCE_EXTERNAL = 10800; // 3 hours
    
    // matches #aaa, #3A3A3A, #3A3A3A4E, or color names
    const REGEX_COLOR = '/^\s*(#[a-fA-F0-9]{3}|#[a-fA-F0-9]{6}|#[a-fA-F0-9]{6}|[a-zA-Z]+)\s*$/';
    
    //
    const SECRET_KEY = self::GetResource('secret_key');
    
    // contents of the resources file
    private static $resourceContents;
    
    //
    public static function BuildDataString($data)
    {
        $pairs = array();
        foreach ($data as $key => $value)
            $pairs[] = $key . '=' . $value;
        return implode(self::SEPARATOR, $pairs);
    }
    
    public static function BuildSecret($binSecret, $id)
    {
        return Encryption::Encrypt(bin2hex($binSecret) . '|' . $id, self::SECRET_KEY);
    }
    
    public static function ParseSecret($code, &$binSecret, &$id)
    {
        // decrypt the code
        $plainTextCode = Encryption::Decrypt($code, self::SECRET_KEY);
        
        // parse the code
        $values = explode('|', $plainTextCode);
        if (count($values) == 2)
        {
            $binSecret = hex2bin(trim($values[0]));
            $id = trim($values[1]);
            return true;
        }
        return false;
    }
    
    public static function CompareGames(Game $a, Game $b)
    {
        if ($a->GetTime() < $b->GetTime())
            return -1;
        else if ($a->GetTime() > $b->GetTime())
            return 1;
        else
        {
            // compare location, then league
            $cmp = strcasecmp($a->GetLocation(), $b->GetLocation());
            if ($cmp == 0)
                $cmp = strcasecmp($a->GetLeague(), $b->GetLeague());
            return $cmp;
        }
    }
    
    public static function CompareGamesReverse(Game $a, Game $b)
    {
        return Utility::CompareGames($b, $a);
    }

    // queries for data and repeats it in the template
    // TODO pass static function as callback, call with call_user_func()
    // see http://php.net/manual/en/function.call-user-func.php
    public static function DataRepeat($db, $query, $template, $header = '', $footer = '')
    {
        // query for the data
        $res = $db->Select($query);
        if (!$res || $res->Count() == 0) return '';
            
        // append the header
        $markup = $header . PHP_EOL;
        
        // append each item
        while ($data = $res->NextRowAssoc())
        {
            // copy the template
            $item = $template;
            
            // get the array keys
            $keys = array_keys($data);
            
            // look through the template for instances of {NAME}
            // and replace {NAME} with $data['NAME']
            // With PREG_OFFSET_CAPTURE set, each match in the array becomes a
            // two-element array with array[0] as the math and array[1] as the offset.
            // XXX: This isn't perfect; if the replaced string is inserted multiple
            // times and it includes a string like {NAME}, we will try to replace
            // that string.
            $offset = 0;
            while (($count = preg_match('/{([\w\d]+)}/', $item, $matches,
                PREG_OFFSET_CAPTURE, $offset)) != 0)
            {
                if (in_array($matches[1][0], $keys))
                {   // found the item; replace it with the datum
                    $item = str_replace($matches[0][0], $data[$matches[1][0]], $item);
                    $offset = $matches[0][1] + strlen($data[$matches[1][0]]);
                }
                else // leave the key in the string and skip it
                    $offset = $matches[0][1] + strlen($matches[0][0]);
            }
            
            // append the item
            $markup .= $item . PHP_EOL;
        }
        
        // free the data
        $res->Free();
        
        // append the footer
        $markup .= $footer . PHP_EOL;
        
        // return the string
        return $markup;
    }
    
    // gets a resource from the resources files
    public static function GetResource($name)
    {
        // get the file contents if we have not already
        if (is_null(self::$resourceContents))
            self::$resourceContents = file_get_contents('../resources.xml');
        if (self::$resourceContents === FALSE)
            return null;
        
        // find the value in xml (s = DOTALL, i = case insensitive)
        $name = trim($name);
        if (preg_match("|<\\s*resource\\s+name\\s*=\\s*\"$name\"\\s*>\\s*(.+?)\\s*<\\s*/\\s*resource\\s*>|si",
            self::$resourceContents, $matches) != 0)
            return $matches[1];
        return null;
    }
    
    public static function IsEmpty($var)
    {
        return $var == null || strlen(trim($var)) == 0;
    }
    
    public static function TextOrNull($index, $array)
    {
        if (isset($array[$index]))
        {
            $value = trim(strip_tags($array[$index]));
            if (strlen($value) > 0)
                return $value;
            else
                return null;
        }
        else
            return null;
    }
    
    public static function DateToSafeString($date)
    {
        try
        {
            if ($date != null)
                return $date->format(self::DATE_FORMAT);
        }
        catch (Exception $e) { }
        return '';
    }
    
}

?>
