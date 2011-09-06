<?php



function ldap_escape($str, $for_dn = false) {

    // see:
    // RFC2254
    // http://msdn.microsoft.com/en-us/library/ms675768(VS.85).aspx
    // http://www-03.ibm.com/systems/i/software/ldap/underdn.html

    if  ($for_dn)
        $metaChars = array(',','=', '+', '<','>',';', '\\', '"', '#');
    else
        $metaChars = array('*', '(', ')', '\\', chr(0));

    $quotedMetaChars = array();
    foreach ($metaChars as $key => $value) $quotedMetaChars[$key] = '\\'.str_pad(dechex(ord($value)), 2, '0');
    $str=str_replace($metaChars,$quotedMetaChars,$str); //replace them
    return ($str);
}

?>
