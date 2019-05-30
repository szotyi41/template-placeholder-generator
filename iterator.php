<?php

require_once 'vendor/autoload.php';

define('PREP_DIR', './prepared/');
define('DEMO_DIR', './prepared/demo/');
define('TEMPLATE_DIR', './');

if(!is_dir(PREP_DIR)) mkdir(PREP_DIR);
if(!is_dir(DEMO_DIR)) mkdir(DEMO_DIR);

if(empty($argv[1])) {
	echo 'Set file as first argument';
	return;
}

$file = TEMPLATE_DIR . $argv[1];

$template = new TemplateIterator($file, true);
$template->save(DEMO_DIR . 'index.html');

$template = new TemplateIterator($file, false);
$template->save(PREP_DIR . 'index.html');

