<strong>Tags</strong><br>
<?php if($userexist){
$existing_tags = array();
?>
<?php 
if ($userexist->tags) {
?>    
    <ul>
    <?php 
    foreach ($userexist->tags as $tags) {
        $existing_tags[] = $tags->id;
     ?>   
        <li><strong><?php echo $tags->name; ?> </strong><a href="<?php echo $serverurl . 'helpscout_userengage_action?action=removetag&tag=' . $tags->name . '&userid=' . $userexist->id . '&v=AspxM5sEuZPdcDhAAM9f2kEcAn8=';?>"> REMOVE</a></li>
   <?php } ?>
    </ul>
<?php } ?>
<?php
if (isset($all_tags->results) && $all_tags->results) {
?>      
    <ul>
   <?php 
    foreach ($all_tags->results as $tag) {
        if (!in_array($tag->id, $existing_tags)) {
     ?>           
           <li><strong><?php echo $tag->name ; ?></strong> <a href="<?php echo $serverurl . 'helpscout_userengage_action?action=addtotag&tag=' . $tag->name . '&userid=' . $userexist->id . '&v=AspxM5sEuZPdcDhAAM9f2kEcAn8='?>">ADD</a></li>
    <?php       
        }
    }
    ?>
    </ul><br>

<?php
  } 
}else{

    if (isset($all_tags->results) && $all_tags->results) {
    ?>  
        <ul>
       <?php 
        foreach ($all_tags->results as $tag) {
          ?>        
            <li><strong><?php echo $tag->name; ?></strong> <a href="<?php echo $serverurl . 'helpscout_userengage_action?action=addtotag&tag=' . $tag->name . '&first_name=' . $first_name . '&last_name=' . $last_name . '&email=' . $email . '&v=AspxM5sEuZPdcDhAAM9f2kEcAn8=';?>">ADD</a></li>
       <?php 
        }
         ?>     
        </ul><br>
 <?php 
    }  
 }
 ?>
