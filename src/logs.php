<?php
$logFile = file_get_contents('logs.txt');
echo nl2br(str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $logFile));