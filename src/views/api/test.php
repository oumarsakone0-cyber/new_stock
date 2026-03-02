<?php

$connection = fsockopen("mail.aliadjame.com", 465, $errno, $errstr, 10);

if (!$connection) {
    echo "Erreur connexion : $errstr ($errno)";
} else {
    echo "Connexion réussie !";
    fclose($connection);
}