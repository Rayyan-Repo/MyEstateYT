<?php
$fp = @fsockopen('smtp.gmail.com', 587, $errno, $errstr, 10);
if($fp){ fclose($fp); echo 'SMTP 587: OK'; }
else echo 'SMTP 587: FAILED - '.$errstr;
