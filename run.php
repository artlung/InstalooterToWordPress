<?php

require_once 'InstalooterToWordPress.php';

$I2W = new InstalooterToWordPress();
// Use the default locations for these
$I2W->setDumpFolder("instalooter_dumps");
$I2W->setWordPressExportFolder("wordpress_imports");

// where are you going to put the dump folder?
$I2W->setUrlForImages("https://example.org/where/will/your/images/go/");

// I like to add a category for an import, so if it goes bad I can find them quickly and batch delete
$I2W->addCategory('instagram-import');

// adding a tag is nice
$I2W->addTag('via-instalooter-to-wordpress');

$I2W->run();