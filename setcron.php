<?php
$path = dirname( __FILE__ ) . '/';
exec("ps aux |grep movieengine1.php | grep -v grep", $command_output, $result);
if(!empty($command_output)) {
exit;
} else {
exec("/usr/bin/php {$path}movieengine1.php");
}
?>