<?php

/* ana_wd_commons.php
 */


###########################################################################

function find_iw_link($wikidata, $wiki)
{
	if(isset($wikidata->links->$wiki)) {
		if(is_string($wikidata->links->$wiki))
			return $wikidata->links->$wiki;
		if(is_string($wikidata->links->$wiki->name))
			return $wikidata->links->$wiki->name;
		echo "** $wiki\n";
	}
	else
		echo "** $wiki mangler\n";
	print_r($wikidata);
	exit(1);
}

###########################################################################

function startElement($parser, $name, $attrs) 
{
global $cur, $tekst, $cur_wiki, $cur_ns;

    $cur=$name;
    $tekst="";
    switch($name) {
    case "MEDIAWIKI":
	# print_r($attrs);
	if(isset($attrs["XML:LANG"])) {
		$cur_wiki = $attrs["XML:LANG"];
		# echo "lang: $cur_wiki\n";
	}
	break;
    case "NAMESPACE":
	if(isset($attrs["KEY"])) {
		$cur_ns = $attrs["KEY"];
	}
	break;
    }
}

###########################################################################

function endElement($parser, $name) 
{
global $cur, $tekst;
global $id, $tekst, $title, $kommentar, $namespace;
global $interwiki;
global $cur_wiki, $cur_ns;
global $print_r_mode;
global $dk_adm_type, $dk_adm_enhed;

    switch($cur) {
    case "ID": $id = $tekst; break;
    case "TITLE": $title = $tekst; break;
    case "COMMENT": $kommentar = $tekst; break;
    case "NS": $namespace = $tekst; break;
    case "NAMESPACE":
	$namespace_tekst["$cur_ns"] = $tekst;
	# echo "Namespace $cur_ns '$tekst'\n";
	break;
    case "TEXT":
	$pos = strpos($title, ":");
	if($pos) {
		$type = substr($title, 0, $pos);
		switch($type) {
		case "Wikipedia":
		case "Skabelon":
		case "Kategori":
		case "Hjælp":
		case "Portal":
		case "MediaWiki":
		case "WP":
			return;
		default:
			# echo "# ukendt type $type\n";
			break;
		}
	}
	if($namespace==0 && $print_r_mode==0) {
		$wikidata = json_decode($tekst);
		if(is_array($wikidata->entity)) {
			if($wikidata->entity[0]=="item" && isset($wikidata->entity[1])) {
				$item_id=$wikidata->entity[1];
			} else {
				echo "xxxx";
				print_r($wikidata->entity);
				echo "xxxx\n";
				$item_id="Qxx";
			}
		} else if(is_string($wikidata->entity)) {
			$item_id=$wikidata->entity;
		}

		# print_r($xx);
		if(isset($wikidata->links->commonswiki)) {
			$commonswiki_link = find_iw_link($wikidata, "commonswiki");

			$commonscat_found=0;
			$commons_found=0;

			# claims
			if(isset($wikidata->claims)) {
				# tjek egenskaber
				foreach($wikidata->claims as $cur_claim) {
					# print_r($cur_claim);
					switch($cur_claim->m['1']) {
					case 373:
						if(isset($cur_claim->m['3'])) {
							# echo "commonscat " . strtr($commonswiki_link, ' ', '_') . " " . $cur_claim->m['3'] . "\n";
							$last_cat_val=$cur_claim->m['3'];
						}
						else
							echo "commonscat_p373_emtry $item_id " . strtr($commonswiki_link, ' ', '_') . "\n";
						$commonscat_found=1;
						break;
					case 935:
						if(isset($cur_claim->m['3'])) {
							# echo "commons " . strtr($commonswiki_link, ' ', '_') . " " . $cur_claim->m['3'] . "\n";
							$last_val=$cur_claim->m['3'];
						}
						else
							echo "commons_p935_emtry $item_id " . strtr($commonswiki_link, ' ', '_') . "\n";
						$commonscat_found=1;
						break;
					}
				}

			}

			# check after
			if(substr($commonswiki_link, 0, 9) == "Category:") {
				if($commonscat_found==0)
					echo "commonscat_p373_missing $item_id " . strtr($commonswiki_link, ' ', '_') . "\n";
				else if(!isset($last_cat_val)) {}
				else if("Category:$last_cat_val" != $commonswiki_link)
					echo "commonscat_p373_diff $item_id " . strtr($commonswiki_link, ' ', '_') . " " . strtr($last_cat_val, ' ', '_') . "\n";
			} else {
				if($commonscat_found==0)
					echo "commonscat_p935_missing $item_id " . strtr($commonswiki_link, ' ', '_') . "\n";
				else if(!isset($last_val)) {}
				else if($last_val != $commonswiki_link)
					echo "commonscat_p935_diff $item_id " . strtr($commonswiki_link, ' ', '_') . " " . strtr($last_val, ' ', '_') . "\n";
			}
		}
	}
	else if($namespace==0 && $print_r_mode) {
		$wikidata = json_decode($tekst);
		print_r($wikidata);
	}

    #default:
    #	echo "## $cur $tekst\n";
    #	break;
    }
    $cur="";
}

###########################################################################

function characterData($parser, $data) 
{
global $cur, $tekst;
    switch($cur) {
    case "TITLE":
    case "TEXT":
    case "COMMENT":
    case "ID":
    case "NS":
    case "NAMESPACE":
    	$tekst .= $data;
	break;
    }
}

###########################################################################

	$cur_database = "dawiki";
	$cur_wiki='nds';
	$cur_wiki='nds-nl';
	$file = "xxx";
	$print_r_mode = 0;

	$opts = getopt("d:f:r");

	foreach (array_keys($opts) as $opt) switch ($opt) {
	case 'd':
	    // Do something with s parameter
	    $cur_database = $opts['d'];
	    break;
	case 'r';
	    $print_r_mode=1;
	    break;
	case 'f';
	    $file = $opts['f'];
	    break;
	default:
		echo "fejl: optfejl $opt\n";
		exit(1);
	}

	if($argc == 2) {
		$file = $argv[1];
	}

	$xml_parser = xml_parser_create();
	// use case-folding so we are sure to find the tag in $map_array
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
	xml_set_element_handler($xml_parser, "startElement", "endElement");
	xml_set_character_data_handler($xml_parser, "characterData");
	if (!($fp = fopen($file, "r"))) {
	die("could not open XML input");
	}

	while ($data = fread($fp, 4096)) {
		if (!xml_parse($xml_parser, $data, feof($fp))) {
			die(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($xml_parser)),
				xml_get_current_line_number($xml_parser)));
		}
	}
	xml_parser_free($xml_parser);
?>
