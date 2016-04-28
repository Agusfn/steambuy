<?php

ini_set('max_execution_time', 1000);
header( 'Content-type: text/html; charset=utf-8' );

echo 'Begin ...<br />';
for( $i = 0 ; $i < 10 ; $i++ )
{
    echo $i . '<br />';
    flush();
    ob_flush();
    sleep(1);
}
echo 'End ...<br />';
?>