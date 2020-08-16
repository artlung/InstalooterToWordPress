<?php

require_once 'InstalooterToWordPress.php';

$I2W = new InstalooterToWordPress();
$I2W->setDumpFolder("instalooter_dumps");
$I2W->setWordPressExportFolder("wordpress_imports");
$I2W->setUrlForImages("https://example.org/where/will/your/images/go/");
$I2W->run();