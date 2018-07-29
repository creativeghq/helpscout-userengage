<?php if (isset($events->results) && $events->results) {
?>	
<strong>Events</strong><br>
<ul>
<?php	
foreach ($events->results as $event) {
?>	
   <li><strong><?php echo $event->event;?></strong></li><br>
<?php    
}
?>
</ul><br>
<?php } ?>