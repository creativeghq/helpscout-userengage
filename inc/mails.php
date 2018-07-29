<?php if (isset($all_emails) && $all_emails) { ?>
<strong>Emails</strong><br>
	<ul>

<?php 
foreach ($all_emails as $emails) {
?>	
    <li><strong>Email name </strong><?php echo $emails->subject; ?></li><br>
    <li><strong>Clicked </strong><?php
    if($emails->clicked_at){
    	echo date('d/m/Y', strtotime($emails->clicked_at));
    }else {
    	echo 'Not clicked';
    }
     ?></li><br>
    <li><strong>Opened </strong><?php 
    if($emails->opened_at){
    	echo date('d/m/Y H:i:s', strtotime($emails->opened_at));	
    } else {
    	echo 'Not opened';
    }
     ?></li><br>
    <li><strong>Sent </strong><?php
    	if($emails->sent_at){
    		echo date('d/m/Y H:i:s', strtotime($emails->sent_at));	
    	} else {
    		echo 'Not sent';
    	}
     ?></li>
 <?php }  ?>   

</ul>
<?php } ?>