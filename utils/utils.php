<?php

    global $SERVER_ADDRESS; $SERVER_ADDRESS = 'localhost';

    class UTILITIES
    {
        // CREATES A SESSION ID
        public static function CreateSessionId() : string 
        {
            $salt = TEXT::GetRandomString(rand(10, 18));
            $sid = bin2hex($salt.time().uniqid().$salt);
            $sid = hash('sha256', $sid);
            $sid = strlen($sid) > 27 ? substr($sid, 0, rand(19, 27)) : $sid;
            while (file_exists('C:\xampp\tmp\sess_'.$sid)) 
            {
                $salt = TEXT::GetRandomString(rand(10, 18));
                $sid = bin2hex($salt.time().uniqid().$salt);
                $sid = hash('sha256', $sid);
                $sid = strlen($sid) > 27 ? substr($sid, 0, rand(19, 27)) : $sid;
            }
            return $sid;
        }

        // CREATE A NEW 'PDO' DATABASE CONNECTION TO THE GIVEN DATABASE
        public static function PDO_DB_Connection(string $database, string $host = 'localhost', 
            string $user = 'root', string $password = '3f55ZuvWrrt955') : PDO 
        {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            $pdo = new PDO("mysql:charset=utf8mb4;mysql:host=$host;dbname=$database", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
            return $pdo;
        }
    }

    class TEXT
    {
        // RETURNS A RANDOM STRING IN A SPECIFIED LENGTH
        public static function GetRandomString(int $length, bool $letter = false) : string 
        {
            $chars = 
            '01234567dflgjfhf2d4hfg54juj4sf4ew5qg4ehw5h5s4es8g4we89ABCDEFG4535434553453HIJKLdsgdfjhk37569345kj3453khjlhgMNOPQRSDSGHdfhfdhTUVW1437952XdfhasghgljklYZ635DSfdhGFDG34abDSHcdefSDGghijklSD79845631245SDFGFHmnopq42233rstuvSDF6437gj98122Hw45454xygeskdvuz';
            $random_string = '';
            for ($i = 0; $i < $length; $i++)
            {
                $random_index = rand(0, 246);
                $random_string = $random_string.$chars[$random_index];
            }
            if ($letter === true) 
            {
                $letters = 'abjkdybnifldirubcyvusrfbJirubcyvsdDsdhgFssadfSHfvdsfgdsfgdsfHfDHdfHfhsDJuFFKuilOIDRSawdfKyJsDajGHkjWYartKrokYUuYAwegufuesugfuybnifldirubcyvusrfbJirubcyvusKHCRIirubcyvusNCRUIRV';
                for ($i = 0; $i < 7; $i++)
                {
                    $random_index = rand(0, 90);
                    $random_string = $letters[$random_index].$random_string;
                }                
            }
            return $random_string;
        }
    }

?>