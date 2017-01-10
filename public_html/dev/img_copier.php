<?php


$files = glob('../data/img/game_imgs/*.{jpg}', GLOB_BRACE);

foreach($files as $file) {
   
   	$newSmallImg = imagecreatetruecolor(198, 93);
	imagecopyresampled($newSmallImg, imagecreatefromjpeg($file), 0, 0, 0, 0, 198, 93, 460, 215);
	imagejpeg($newSmallImg, "../data/img/game_imgs/small/".basename($file), 98);
   
}

?>