<?php

/* ana_wd_commons.php
 */


###########################################################################

$notewiki["dawiki"] = 0;
$notewiki["svwiki"] = 0;
$notewiki["nowiki"] = 0;
$notewiki["nnwiki"] = 0;
$notewiki["fiwiki"] = 0;

$notewiki["enwiki"] = 0;
$notewiki["dewiki"] = 0;
$notewiki["nlwiki"] = 0;

$notewiki["frwiki"] = 0;
$notewiki["eswiki"] = 0;
$notewiki["ptwiki"] = 0;
$notewiki["itwiki"] = 0;

$notewiki["plwiki"] = 0;
$notewiki["ruwiki"] = 0;
$notewiki["be_x_oldwiki"] = 0;

###########################################################################

$globe_def["http://www.wikidata.org/entity/Q2"] = "jord";
$globe_def["earth"] = "jord";
$globe_def[""] = "jord";
$globe_def["http://www.wikidata.org/entity/Q405"] = "månen";
$globe_def["http://www.wikidata.org/entity/Q111"] = "mars";

###########################################################################

function note_item($egenskab)
{
	if($egenskab->m['0']=="novalue")
		return 0;

	if(!isset($egenskab->m['3']->{'entity-type'})) {}
	else if($egenskab->m['3']->{'entity-type'}=="item")
		return $egenskab->m['3']->{'numeric-id'};
	print_r($egenskab);
	exit(1);

}

###########################################################################

function find_iw_link($wikidata, $wiki)
{
	if(isset($wikidata->sitelinks->$wiki)) {
		if(is_string($wikidata->sitelinks->$wiki))
			return $wikidata->sitelinks->$wiki;
		if(is_string($wikidata->sitelinks->$wiki->title))
			return $wikidata->sitelinks->$wiki->title;
		echo "** $wiki\n";
	}
	else
		echo "** $wiki mangler\n";
	print_r($wikidata);
	exit(1);
}

###########################################################################

function haand_pro($pro, $tag, $item_id, $cur_item)
{
	$cur="P$pro";
	if(!isset($cur_item->claims->$cur))
		return;

	$sidst="";
	foreach($cur_item->claims->$cur as $cur_pro=>$cur_claim) {
		if(!isset($cur_claim->mainsnak)) {
			echo "fejl-mainsnak-mis " . strtr($item_id, ' ', '_') . " p$pro\n";
			print_r($cur_claim);
		}
		else if(!isset($cur_claim->mainsnak->datatype) && !isset($cur_claim->mainsnak->datavalue->type)) {
			echo "fejl-datatype-mis " . strtr($item_id, ' ', '_') . " p$pro\n";
			print_r($cur_claim);
		}
		else {
			if(isset($cur_claim->mainsnak->datatype))
				$cur_datatype=$cur_claim->mainsnak->datatype;
			else if(isset($cur_claim->mainsnak->datavalue->type))
				$cur_datatype=$cur_claim->mainsnak->datavalue->type;
			else
				die("fejl\n");

			switch ($cur_claim->mainsnak->snaktype) {
			case "value":
				switch($cur_datatype) {
				case "string":
					$sidst=$cur_claim->mainsnak->datavalue->value;
					if($tag!="")
						echo "$tag " . strtr($item_id, ' ', '_') . " $sidst\n";
					break;
				case "wikibase-item":
				case "wikibase-entityid":
					if(isset($cur_claim->mainsnak->datavalue->value->{'numeric-id'})) {
						$sidst=$cur_claim->mainsnak->datavalue->value->{'numeric-id'};
						if($tag!="")
							echo "$tag " . strtr($item_id, ' ', '_') . " $sidst\n";
					}
					else {
						echo "stop $item_id\n";
						print_r($cur_claim);
						exit(1);
					}
					break;
				case "globe-coordinate":
					if(isset($globe_def["$cur_claim->mainsnak->datavalue->value->globe"])) {
						$cur_globe=$globe_def["$cur_claim->mainsnak->datavalue->value->globe"];

						$latitude=$cur_claim->mainsnak->datavalue->value->latitude;
						$longitude=$cur_claim->mainsnak->datavalue->value->longitude;
						$sidst="$latitude $longitude";
						if($tag!="")
							echo "$tag-$cur_globe " . strtr($dawiki_link, ' ', '_') . " $sidst\n";
					}
					else {
						echo "fejl-geo-$tag " . strtr($dawiki_link, ' ', '_') . " globe:" . $cur_claim->mainsnak->datavalue->value->globe . "\n";
						print_r($cur_claim);
					}
					break;
				case "statement":
					# $sidst=$cur_claim->mainsnak->datavalue->value->{'numeric-id'};
					$snak=$cur_claim->mainsnak->snaktype;
					echo "$tag-$snak " . strtr($item_id, ' ', '_') . "\n";
					break;
				default:
					echo "fejl-type " . strtr($item_id, ' ', '_') . " p$pro $cur_datatype\n";
					print_r($cur_claim);
				}
				break;
			case "somevalue":
			case "novalue":
				$snaktype=$cur_claim->mainsnak->snaktype;
				if($tag=="")
					echo "$snaktype p$pro $item_id $cur_datatype\n";
				else
					echo "$snaktype p$pro $item_id $cur_datatype $tag\n";
				break;
			default:
				echo "stop $item_id\n";
				print_r($cur_claim);
				exit(1);
			}
		}
	}
	return $sidst;
}

###########################################################################

function ana_item($tekst, $wikidata)
{
global $notewiki;

	# find id
	if(isset($wikidata->id))
		$item_id=$wikidata->id;
	else if(is_array($wikidata->entity)) {
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
	if(isset($wikidata->sitelinks->commonswiki)) {
		$commonswiki_link = find_iw_link($wikidata, "commonswiki");

		# claims
		if(isset($wikidata->claims)) {
			if(($tmp=haand_pro(373, 'commonscat_p373', $item_id, $wikidata))) {
				$last_373_val=$tmp;
			}

			if(($tmp=haand_pro(935, 'commons_p935', $item_id, $wikidata))) {
				$last_935_val=$tmp;
			}
		}

		# check after
		if(substr($commonswiki_link, 0, 9) == "Category:") {
			if(!isset($last_373_val))
				echo "commonscat_p373_missing $item_id " . strtr($commonswiki_link, ' ', '_') . "\n";
			else if("Category:$last_373_val" != $commonswiki_link)
					echo "commonscat_p373_diff $item_id " . strtr($commonswiki_link, ' ', '_') . " " . strtr($last_373_val, ' ', '_') . "\n";
		} else {
			if(!isset($last_935_val))
				echo "commons_p935_missing $item_id " . strtr($commonswiki_link, ' ', '_') . "\n";
			else if($last_935_val != $commonswiki_link)
				echo "commons_p935_diff $item_id " . strtr($commonswiki_link, ' ', '_') . " " . strtr($last_935_val, ' ', '_') . "\n";
		}
	}

	# gennemgå wiki, som der er interesse for
	foreach (array_keys($notewiki) as $cur_wiki) {
		if(isset($wikidata->sitelinks->$cur_wiki)) {
			$indwiki["$cur_wiki"]=find_iw_link($wikidata, "$cur_wiki");
			echo "$cur_wiki " . $indwiki["$cur_wiki"] . "\n";
		}
	}

	if(isset($indwiki)) {
		if(($tmp=haand_pro(21, '', $item_id, $wikidata)))
			$note_sex=$tmp;
		if(($tmp=haand_pro(31, '', $item_id, $wikidata)))
			$note_er=$tmp;
		if(($tmp=haand_pro(301, '', $item_id, $wikidata)))
			$note_hovedartikel=$tmp;
		if(($tmp=haand_pro(910, '', $item_id, $wikidata)))
			$note_hovedkategori=$tmp;

		if(isset($indwiki["svwiki"]) && isset($note_sex))
			echo "sv_sex " .  strtr($indwiki["svwiki"], ' ', '_') . " " . $note_sex . "\n";

		foreach (array_keys($indwiki) as $cur_wiki) {
			if(isset($note_hovedartikel))
				echo "${cur_wiki}_hovedartikel " . strtr($indwiki["$cur_wiki"], ' ', '_') . " " . $note_hovedartikel . "\n";
			if(isset($note_hovedkategori))
				echo "${cur_wiki}_hovedkategori " . strtr($indwiki["$cur_wiki"], ' ', '_') . " " . $note_hovedkategori . "\n";
		}

		if(!isset($note_er)) {
			if(isset($indwiki["enwiki"]) && strpos($indwiki["enwiki"], ":"))
				echo "ny_p31 enwiki:" . strtr($indwiki["enwiki"], ' ', '_') . "\n";
			else if(isset($indwiki["dewiki"]) && strpos($indwiki["dewiki"], ":"))
				echo "ny_p31 dewiki:" . strtr($indwiki["dewiki"], ' ', '_') . "\n";
			else foreach (array_keys($indwiki) as $cur_wiki) {
				if(strpos($indwiki["$cur_wiki"], ":")) {
					echo "ny_p31 $cur_wiki:" . strtr($indwiki["$cur_wiki"], ' ', '_') . "\n";
					break;
				}
			}
		}
	}
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
		ana_itemx($tekst);
	}
	else if($namespace==0 && $print_r_mode) {
		$wikidata = json_decode($tekst);
		print_r($wikidata);
	}

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

function load_xml()
{
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
}

###########################################################################

function ana_itemx($tekst)
{
	if($tekst=="") return;
	if($tekst=="]") return;
	if($tekst=="[") return;

	$wikidata = json_decode($tekst);
	if(isset($wikidata->type)) {
		if($wikidata->type =="item")
			ana_item($tekst, $wikidata);
		else if($wikidata->type =="property") {
			#ana_pro($tekst, $wikidata);
		}
		else if($wikidata->type !="item") {
			echo "xx andet 1 item\n";
			print_r($wikidata);
			echo "xx andet 1 slut item\n";
			return;
		}
		#echo "xx ny item\n";
		#print_r($wikidata);
		#echo "xx slut item\n";
	} else {
		echo "xx andet 2 item\n";
		print_r($wikidata);
		echo "xx andet 2 slut item\n";
		return;
	}
}

###########################################################################

function laes_json()
{
	$handle = fopen("php://stdin", "r");
	while(!feof($handle))
	{
		$linie = fgets($handle);
		ana_itemx(trim($linie));
	}
	fclose($handle);
}

###########################################################################

	$file = "xxx";
	$print_r_mode = 0;

	$opts = getopt("f:r");

	foreach ($opts as $opt => $opt_val) switch ($opt) {
	case 'r'; $print_r_mode=1; break;
	case 'f'; $file = $opt_val; break;
	default:
		echo "fejl: optfejl $opt\n";
		exit(1);
	}

	if($argc == 2) {
		$file = $argv[1];
	}
	laes_json();
?>
