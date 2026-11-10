<?php
// Build-time helper: generates the resized game image variants the pages
// reference (224x105, 240x112, 320x149) from the full-size images.
// Usage: php gen-thumbs.php <game_imgs_dir>
$dir = rtrim($argv[1], "/");
$sizes = array("224x105" => array(224, 105), "240x112" => array(240, 112), "320x149" => array(320, 149));
foreach ($sizes as $name => $wh) {
    if (!is_dir("$dir/$name")) mkdir("$dir/$name", 0755, true);
}
$n = 0;
foreach (glob("$dir/*.jpg") as $file) {
    $src = @imagecreatefromjpeg($file);
    if (!$src) { fwrite(STDERR, "skip $file\n"); continue; }
    $sw = imagesx($src); $sh = imagesy($src);
    foreach ($sizes as $name => $wh) {
        $dst = imagecreatetruecolor($wh[0], $wh[1]);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $wh[0], $wh[1], $sw, $sh);
        imagejpeg($dst, "$dir/$name/" . basename($file), 90);
        imagedestroy($dst);
    }
    imagedestroy($src);
    $n++;
}
echo "Generated variants for $n images\n";
