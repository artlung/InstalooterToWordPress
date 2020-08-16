<?php

require_once 'InstalooterToWordPress.php';

$I2W = new InstalooterToWordPress();
$I2W->setDumpFolder("artlung_instagram");

if ($handle = opendir($I2W->getDumpFolder())) {
    while (false !== ($filename = readdir($handle))) {
        list($key, $extension) = explode('.', $filename);

        switch($extension) {
            case InstalooterToWordPress::jpg:
                $I2W->saveJpg($key, $filename);
                break;
            case InstalooterToWordPress::json:
                $I2W->saveJSON($key, file_get_contents($I2W->getDumpFolder() . '/' . $filename));
                break;
            case InstalooterToWordPress::mp4:
                $I2W->saveMp4($key, $filename);
                break;
            case '':
                break;
            default:
                throw new \Exception('Unexpected value for extension');
        }
    }
    closedir($handle);
}

//$I2W->dumpData();
//$I2W->dumpTypeNames();
//$I2W->dumpTitles();

$I2W->printWordPressXml();