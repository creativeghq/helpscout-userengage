<strong><a href="<?php echo $profile_link; ?>" target="_blank">Profile</a></strong> | Score: <strong><?php echo $userexist->score; ?></strong><br>
<?php 
$unsubscribed = 'False';
if ($userexist->unsubscribed) {
    $unsubscribed = 'True';
}
?>
Unsubscribed: <strong><?php echo $unsubscribed; ?></strong><br>
Last Contacted: <strong><?php
if($userexist->last_contacted){
	echo date('d/m/Y H:i:s', strtotime($userexist->last_contacted));
} 	
?></strong><br>
Last seen: <strong><?php
if($userexist->last_seen){
	echo date('d/m/Y H:i:s', strtotime($userexist->last_seen));
}
?></strong><br>