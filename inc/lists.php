<strong>Lists</strong>
<?php if($userexist){
    
    $existing_list = array();

        if ($userexist->lists) {

            ?>
                <ul>
                <?php foreach ($userexist->lists as $lists) {
                    $existing_list[] = $lists->id;
                    ?>
                    <li><strong><?php echo $lists->name; ?></strong>
                     <a href="<?php echo $serverurl . 'helpscout_userengage_action?action=removefromlist&userid=' . $userexist->id . '&listid=' . $lists->id . '&v=AspxM5sEuZPdcDhAAM9f2kEcAn8=';?>">REMOVE</a></li> 

                <?php } ?>
                </ul>
            <?php  } ?>
            
            <?php


             if (isset($all_lists->results) && $all_lists->results) { 

                ?>
                <ul>
                <?php foreach ($all_lists->results as $list) {

                    if (!in_array($list->id, $existing_list)) {
                        ?>
                        <li><strong><?php echo $list->name; ?></strong> <a href="<?php echo  $serverurl . 'helpscout_userengage_action?action=addtolist&listid=' . $list->id . '&userid=' . $userexist->id . '&v=AspxM5sEuZPdcDhAAM9f2kEcAn8=';?>">ADD</a></li>
              <?php          
                    }
                }
               ?> 
                </ul><br>
  <?php               
            }
} else {            
    if (isset($all_lists->results) && $all_lists->results) {
     ?>       
          <ul>
          <?php  
          foreach ($all_lists->results as $list) {
            ?>
              <li><strong><?php echo $list->name; ?></strong> <a href="<?php echo $serverurl . 'helpscout_userengage_action?action=addtolist&listid=' . $list->id . '&first_name=' . $first_name . '&last_name=' . $last_name . '&email=' . $email . '&v=AspxM5sEuZPdcDhAAM9f2kEcAn8=';?>">ADD</a></li>
        <?php  } ?> 
          </ul><br>
     
<?php } 
    }
?>