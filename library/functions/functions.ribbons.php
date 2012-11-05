<?php 
function ribbons($abc_id) {
  global $mysqli;
  
  //GET INFO
  $awarded_medals = array();
  $medals_query = "SELECT * FROM abc_medal_awards WHERE user_id = $abc_id ORDER BY medal_id";
  $medals_result = $mysqli->query($medals_query);
  while ($medals_assoc = $medals_result->fetch_assoc()) {
    $medalid = $medals_assoc['medal_id'];
    $awarded_medals[] = $medalid;
    }
  $medals_result->free();
  
  //GET DIMENSIONS
  $medals = array_count_values($awarded_medals);
  $num_rows = ceil(count($medals)/5);
  $width = 189;
  $height = $num_rows * 13 + ($num_rows - 1);
   
  //CREATE IMAGE & COLORS
  $image = imagecreatetruecolor($width, $height);
  imagesavealpha($image, true);
  imagealphablending($image, true);
  $white = imagecolorallocatealpha($image, 255, 255, 255, 127);
  imagefill($image, 0, 0, $white);
  imagecolortransparent($image, $white);
  $shadow = imagecolorallocatealpha($image, 0, 0, 0, 40);
  
  //ADD MEDALS
  $row = 0;
  $medal = 0;
  foreach($medals as $medal_id => $num_awarded) {
    //GET MEDAL & DATA
    $medal_query = "SELECT * FROM abc_medals WHERE medal_id = $medal_id";
    $medal_result = $mysqli->query($medal_query);
    $medal_assoc = $medal_result->fetch_assoc();
    $medal_result->free();
    $medal_img_path = $medal_assoc['medal_ribbon'];
    $medal_img_attr = getimagesize($medal_img_path);
    $medal_img = imagecreatefromstring(file_get_contents($medal_img_path));
    imagealphablending($medal_img, true);
    imagesavealpha($medal_img, true);  
    
    $x = $medal * 38;
    $y = $row * 14;
    
    //LAST ROW GETS CENTERED
    if ($row + 1 == $num_rows) {
      $num_lastrow = count($medals) - ($num_rows - 1) * 5;
      $width_lastrow = $num_lastrow * 37 + ($num_lastrow - 1) * 1;
      $x_lastrow = (189 - $width_lastrow) / 2;
      
      $x = $x_lastrow + $medal * 38;
      }
    
    imagecopyresized($image, $medal_img, $x, $y, 0, 0, 37, 13, $medal_img_attr[0], $medal_img_attr[1]);
    
    //DECORATION FOR MORE THAN ONE AWARDS
    if ($num_awarded > 1) {
      //GET STAR COLOR
      if ($num_awarded > 7) {
        $deco_path = "images/star_gold.png";
        $deco_number = $num_awarded - 7;
        }
      elseif ($num_awarded > 4) {
        $deco_path = "images/star_silver.png";
        $deco_number = $num_awarded - 4;
        }
      else {
        $deco_path = "images/star_bronze.png";
        $deco_number = $num_awarded - 1;
        }
      $deco_img = imagecreatefrompng($deco_path);
      imagealphablending($deco_img, true);
      imagesavealpha($deco_img, true);
      
      //CENTERING
      $gap = 1;
      $deco_width = 11 * $deco_number + $gap * ($deco_number - 1);
      $deco_x = $x + ((37 - $deco_width) / 2);
      
      //COPYING OF STARS
      $deco_index = 0;
      while ($deco_index < $deco_number) {
        imagecopyresized($image, $deco_img, ($deco_x + $deco_index * (11 + $gap)), 1, 0, 0, 10, 11, 13, 13);
        $deco_index++;
        }      
      }
      
    if ($medal == 4) {
      $medal = 0;
      $row++;
      }
    else {
      $medal++;
      }
    }
    
  //SAVE IMAGE
  $user_query = "SELECT * FROM abc_users WHERE abc_user_id = $abc_id";
  $user_result = $mysqli->query($user_query);
  $user_assoc = $user_result->fetch_assoc();
  $campaign_id = $user_assoc["campaign_id"];
  
  imagegif($image, "images/cache/medals/$campaign_id/$abc_id.gif");
  }
?>