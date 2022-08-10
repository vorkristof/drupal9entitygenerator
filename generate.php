<?php

if (count($argv) == 1) {
	echo "Add a file as argument which contains the entity data.\n";
	exit;
}
$data = [];
$file = fopen($argv[1], "r") or die("Unable to open file!");
do {
	$line = fgets($file);
	$data[] = $line;
} while(!feof($file));
fclose($file);

$title = $data[0];
$title = trim(preg_replace('/\s\s+/', '', $title));
unset($data[0]);
array_pop($data);
$output = "";


foreach ($data as $line) {
	$var = explode(":", $line);
	$var[1] = trim(preg_replace('/\s\s+/', '', $var[1]));
	
	if (count($var) > 1) {
		$output .= comment($var[0], $var[1]);
		$output .= "protected $" . $var[0] . ";\n\n";
	}
}

$output .= "public static function baseFieldDefinitions(EntityTypeInterface \$entity_type) {\n";


foreach ($data as $line) {
	$var = explode(":", $line);
	$var[1] = trim(preg_replace('/\s\s+/', '', $var[1]));
	
	if (count($var) > 1) {
		$output .= "\t\$fields['" . $var[0] . "'] = BaseFieldDefinition::create('" . $var[1] . "')\n";
		$output .= "\t\t->setLabel('" . $var[0] . "')\n";
		$output .= "\t\t->setDescription(t('The " . str_replace('_', ' ', ucfirst($var[0])) . " of the " . $title . " entity.'))\n";
		$output .= "\t\t->setReadOnly(TRUE);\n\n";
	}
}

$output .= "\treturn \$fields;\n}\n\n";

foreach ($data as $line) {
	$var = explode(":", $line);
	$var[1] = trim(preg_replace('/\s\s+/', '', $var[1]));
	
	if (count($var) > 1) {
		$name = explode('_', $var[0]);
		$functionName = "";
		foreach ($name as $n) {	
			$functionName .= ucfirst($n);
		}
		$output .= "/**\n *{@inheritdoc}\n */\n";
		$output .= "public function get" . $functionName . "() {\n";
		$output .= "\treturn \$this->" . $var[0] . ";\n";
		$output .= "}\n\n";
	}
}

$outfile = fopen("output.txt", "w") or die("Unable to open file!");
fwrite($outfile, $output);
fclose($outfile);

function comment($name, $type) {
	global $title;
	return "/**\n *  The " . $title . " " . $name . ".\n * \n *  @var " . $type . "\n */\n";
}


