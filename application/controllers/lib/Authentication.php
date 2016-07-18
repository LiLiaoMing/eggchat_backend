<?php

class Authentication
{
    public static function check($token)
    {
        if ($token) {
            if ($token == "free")
                return true;
        }
        return false;
    }
}