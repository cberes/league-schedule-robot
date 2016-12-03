<?php

class Encryption
{
    public static function Decrypt($data, $key)
    {
        // open it
        $td = mcrypt_module_open('rijndael-256', '', 'cbc', '');
        
        // create the IV
        //$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $iv = null;
        $ivlen = mcrypt_enc_get_iv_size($td);
        $activlen = strlen($key);
        if ($activlen > $ivlen)
            $iv = substr($key, 0, $ivlen);
        else if ($activlen < $ivlen)
            $iv = str_pad($key, $ivlen, '0');
        
        // make sure the key is good
        $keylen = mcrypt_enc_get_key_size($td);
        $actkeylen = strlen($key);
        if ($actkeylen > $keylen)
            $key = substr($key, 0, $keylen);
        else if ($actkeylen < $keylen)
            $key = str_pad($key, $keylen, '0');
        
        // decrypt the data
        mcrypt_generic_init($td, $key, $iv);
        $plain_text = mdecrypt_generic($td, self::urlsafe_b64decode($data));
        mcrypt_generic_deinit($td);
        
        // close it
        mcrypt_module_close($td);
        
        return $plain_text;
    }
    
    public static function Encrypt($text, $key)
    {
        // open it
        $td = mcrypt_module_open('rijndael-256', '', 'cbc', '');
        
        // create the IV
        //$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $iv = null;
        $ivlen = mcrypt_enc_get_iv_size($td);
        $activlen = strlen($key);
        if ($activlen > $ivlen)
            $iv = substr($key, 0, $ivlen);
        else if ($activlen < $ivlen)
            $iv = str_pad($key, $ivlen, '0');
        
        // make sure the key is good
        $keylen = mcrypt_enc_get_key_size($td);
        $actkeylen = strlen($key);
        if ($actkeylen > $keylen)
            $key = substr($key, 0, $keylen);
        else if ($actkeylen < $keylen)
            $key = str_pad($key, $keylen, '0');
        
        // encrypt the text
        mcrypt_generic_init($td, $key, $iv);
        $encrypted_data = mcrypt_generic($td, $text);
        mcrypt_generic_deinit($td);
        
        // close it
        mcrypt_module_close($td);
        
        return self::urlsafe_b64encode($encrypted_data);
    }

    private static function urlsafe_b64encode($string)
    {
      $data = base64_encode($string);
      $data = str_replace(array('+','/','='),array('-','.','_'),$data);
      return $data;
    }
    
    private static function urlsafe_b64decode($string)
    {
      $data = str_replace(array('-','.','_'),array('+','/','='),$string);
      $mod4 = strlen($data) % 4;
      if ($mod4) {
        $data .= substr('====', $mod4);
      }
      return base64_decode($data);
    }
}

?>