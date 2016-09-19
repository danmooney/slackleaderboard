<?php

function remove_emojis($text)
{
    // Match Emoticons
    $regex_emojis = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regex_emojis, '', $text);

    // Match Miscellaneous Symbols and Pictographs
    $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regex_symbols, '', $clean_text);

    // Match Transport And Map Symbols
    $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regex_transport, '', $clean_text);

    // Match Miscellaneous Symbols
    $regex_misc = '/[\x{2600}-\x{26FF}]/u';
    $clean_text = preg_replace($regex_misc, '', $clean_text);

    // Match Dingbats
    $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
    $clean_text = preg_replace($regex_dingbats, '', $clean_text);

	$clean_text = trim(preg_replace('#([^a-z0-9\' ])#i', '', $clean_text));

    return $clean_text;
}