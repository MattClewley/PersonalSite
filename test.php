<?php
function glyph_menu() {
	/* admin glyphtech menu */
	$items['admin/glyph'] = array(
		'title' => 'GlyphTech',
		'weight' => -4,
		'page callback' => 'system_admin_menu_block_page',
		'access arguments' => array('administer content'),
		'file' => 'system.admin.inc',
		'file path' => drupal_get_path('module', 'system'),
	);

	/* admin featured member */
	$items['admin/glyph/featured-member'] = array(
		'title' => 'Featured Member',
		'page callback' => 'drupal_get_form',
		'page arguments' => array('glyph_featured_member',2),
		'access arguments' => array('administer content'),
		'type' => MENU_CALLBACK,
	);

	/* admin featured news */
	$items['admin/glyph/featured-news'] = array(
		'title' => 'Featured News',
		'page callback' => 'drupal_get_form',
		'page arguments' => array('glyph_featured_news',2),
		'access arguments' => array('administer content'),
		'type' => MENU_CALLBACK,
	);

	/* admin page listing all pending resellers */
	$items['admin/glyph/pending-resellers'] = array(
		'title' => 'Pending Resellers',
		'page callback' => 'glyph_pending_resellers',
		'access arguments' => array('administer content'),
		'type' => MENU_CALLBACK,
	);

	/* admin approve reseller */
	$items['admin/reseller/%/approve'] = array(
		'title' => 'Approve Reseller',
		'page callback' => 'drupal_get_form',
		'page arguments' => array('glyphmod_approve_reseller', 2),
		'access arguments' => array('administer content'),
		'type' => MENU_CALLBACK,
	);

	/* ajax call for load more news */
	$items['ajax/load-more-news'] = array(
		'page callback' => 'glyph_load_more_news',
		'type' => MENU_CALLBACK,
		'access arguments' => array('access content'),
	);
	$items['ajax/countriesload'] = array(
		'page callback' => 'glyph_countriesload',
		'type' => MENU_CALLBACK,
		'access arguments' => array('access content'),
	);
	$items['ajax/regionload'] = array(
		'page callback' => 'glyph_regionload',
		'type' => MENU_CALLBACK,
		'access arguments' => array('access content'),
	);
	$items['ajax/onlineload'] = array(
		'page callback' => 'glyph_onlineload',
		'type' => MENU_CALLBACK,
		'access arguments' => array('access content'),
	);
	$items['ajax/inpersonload'] = array(
		'page callback' => 'glyph_inpersonload',
		'type' => MENU_CALLBACK,
		'access arguments' => array('access content'),
	);
	$swappymcswapswap = true;
	/* ajax call to load product attributes for add to cart */
	$items['ajax/cart/get-product-attributes'] = array(
		'page callback' => 'glyph_get_product_attributes',
		'type' => MENU_CALLBACK,
		'access arguments' => array('access content'),
	);
	$items['ajax/customsearch'] = array(
		'page callback' => 'glyph_load_customsearch',
		'type' => MENU_CALLBACK,
		'access arguments' => array('access content'),
	);
	/* ajax call to zip and download reseller files */
	$items['ajax/download/reseller'] = array(
		'page callback' => 'glyph_download_reseller',
		'type' => MENU_CALLBACK,
		'access arguments' => array('access content'),
	);

	return $items;
}

function glyph_featured_member($form, &$form_state, $arg) {
	$form = array();

	//fetch all members
	$memberArr = array();
	$sql = "select n.* from node n
			where n.type='member'
			and n.status=1
			order by n.title asc";
	$result = db_query( $sql );

	if( $result ) {
		$ctr = 1;
		foreach ($result as $k => $v) {
			$member = node_load( $v->nid );
			$memberArr[$v->nid] = $v->title;

			if ($ctr == 1) {
				$featured = $v-nid;
			}

			if ( isset($member->field_member_featured['und'][0]['value']) && $member->field_member_featured['und'][0]['value'] == 1 ) {
				$featured = $member->nid;
			}

			$ctr++;
		}
	}

	$form['title'] = array(
	  '#markup' => '<h1>Featured Member</h1>'
	);

	$form['instruction'] = array(
	  '#markup' => '<p><em>Please select the featured member from the list below.</em></p>'
	);

	$form['featured']['member'] = array(
		'#type' => 'radios',
		'#default_value' => $featured,
		'#options' => $memberArr
	);

	$form['actions'] = array(
		'submit' => array(
			'#type' => 'submit',
			'#attributes' => array(
				'data-wait' => 'Please wait...',
				'class' => array('w-button', 'red-btn', 'ep'),
				'style' => 'margin-top: 20px;'
			),
			'#value' => 'Submit',
		)
	);

	return $form;
}

function glyph_featured_member_submit($form, &$form_state) {
	$featured = node_load($form_state['values']['member']);

	if ( isset($featured->nid) ) {
		//fetch all members
		$sql = "select n.nid from node n
				where n.type='member'
				and n.status=1
				order by n.title asc";
		$result = db_query( $sql );

		if ($result) {
			foreach ($result as $k => $v) {
				$node = node_load($v->nid);

				if ( $featured->nid == $v->nid ) {
					$node->field_member_featured['und'][0]['value'] = 1;
				} else {
					$node->field_member_featured['und'][0]['value'] = 0;
				}

				node_save($node);
			}
		}

		drupal_set_message("You have successfully selected the featured member.");
	} else {
		drupal_set_message("Failed to update the featured member. Please try again.", "error");
	}

	$form_state['redirect'] = 'admin/glyph/featured-member';
}

function glyph_featured_news($form, &$form_state, $arg) {
	$form = array();

	//fetch all news
	$newsArr = array();
	$sql = "select n.* from node n
			where n.type='news'
			and n.status=1
			order by n.created desc, n.title asc";
	$result = db_query( $sql );

	if( $result ) {
		$ctr = 1;
		foreach ($result as $k => $v) {
			$news = node_load( $v->nid );
			$newsArr[$v->nid] = $v->title;

			if ($ctr == 1) {
				$featured = $v-nid;
			}

			if ( isset($news->field_is_featured['und'][0]['value']) && $news->field_is_featured['und'][0]['value'] == 1 ) {
				$featured = $news->nid;
			}

			$ctr++;
		}
	}

	$form['title'] = array(
	  '#markup' => '<h1>Featured News</h1>'
	);

	$form['instruction'] = array(
	  '#markup' => '<p><em>Please select the featured news from the list below.</em></p>'
	);

	$form['featured']['news'] = array(
		'#type' => 'radios',
		'#default_value' => $featured,
		'#options' => $newsArr
	);

	$form['actions'] = array(
		'submit' => array(
			'#type' => 'submit',
			'#attributes' => array(
				'data-wait' => 'Please wait...',
				'class' => array('w-button', 'red-btn', 'ep'),
				'style' => 'margin-top: 20px;'
			),
			'#value' => 'Submit',
		)
	);

	return $form;
}

function glyph_featured_news_submit($form, &$form_state) {
	$featured = node_load($form_state['values']['news']);

	if ( isset($featured->nid) ) {
		//fetch all news
		$sql = "select n.* from node n
				where n.type='news'
				and n.status=1
				order by n.created desc, n.title asc";
		$result = db_query( $sql );

		if ($result) {
			foreach ($result as $k => $v) {
				$node = node_load($v->nid);

				if ( $featured->nid == $v->nid ) {
					$node->field_is_featured['und'][0]['value'] = 1;
				} else {
					$node->field_is_featured['und'][0]['value'] = 0;
				}

				node_save($node);
			}
		}

		drupal_set_message("You have successfully selected the featured news.");
	} else {
		drupal_set_message("Failed to update the featured news. Please try again.", "error");
	}

	$form_state['redirect'] = 'admin/glyph/featured-news';
}

//Start bing custom map search
function glyph_load_customsearch() {
	$query = $_GET['search'];
	//echo $query;
	$sql = "select * from node n inner join field_data_gsl_addressfield a on n.nid = a.entity_id where n.status = 1 and n.type ='store_location' and n.title like '%".$query."%' or a.gsl_addressfield_postal_code like '%".$query."%' or a.gsl_addressfield_locality RLIKE '%".$query."%' or a.gsl_addressfield_administrative_area like '%".$query."%' limit 5";
	$res = db_query($sql); 
	$output = '';
	foreach($res as $k => $v) {
		$pointaddy = $v->gsl_addressfield_thoroughfare .' '. $v->gsl_addressfield_administrative_area . ' ' . $v->gsl_addressfield_country;
		$address = $v->gsl_addressfield_thoroughfare;
		$address .= ', ' .$v->gsl_addressfield_administrative_area . ' ' . $v->gsl_addressfield_country;
		$loaded = node_load($v->nid);
		$title = $loaded->title;
		
		$output .= '<div class="listaddress"><a href="#">'.$title.'</a><br/><div id="addy" class="addressthing" style="display:block;">Address: '.$address.'</div><div style="display:none;" class="addypoint">'.$point.'</div><script>
		$(document).ready(function(){
  			$(".addressthing").click(function(){
				var address2 = this.innerText;
    			compare(address2);
  			});
		});
		</script></div>';
	}
	echo $output;
}

//$storeType = '';
	
function glyph_onlineload() {
	//$query = db_query("select * from node n where n.type = 'retailer' and n.status=1");
	$storeType = $_GET['online'];
	$query;
	if($storeType == 'online') {
		$query = db_query("select * from field_data_field_online_retailer a inner join node n on a.entity_id = n.nid where n.status = 1 and n.type = 'retailer' and a.field_online_retailer_value = 1");
	}
	else{
		$query = db_query("select * from field_data_field_online_retailer a inner join node n on a.entity_id = n.nid where n.status = 1 and n.type = 'retailer' and a.field_online_retailer_value = 0");
	}
	//print($storeType);
	//$query = db_query("select * from field_data_field_online_retailer a inner join node n on a.entity_id = n.nid where n.status = 1 and n.type = 'retailer' and a.field_online_retailer_value = 1");
	$output = '';
	foreach($query as $k => $v) {
		$loaded = node_load($v->nid);
		$title = $loaded->title;
		$link = $loaded->field_retailer_link['und'][0]['value'];
		$logo = image_style_url("reseller_logo", $loaded->field_retailer_logo['und'][0]['uri']);
		//$type = $loaded->field_online_store['und'][0]['value'];
		$type = 1;
		$output .= '<a style="margin-bottom:15px;" href="'.$link.'" class="reseller-linkblock w-inline-block">
    <img src="'.$logo.'" alt="" class="image-64"><h5 class="heading-39">'.$title.'</h5>
    </a>';
		//echo $output;
	}
	print $output;
}
function glyph_inpersonload() {
	$query = db_query("select * from field_data_field_online_retailer a inner join node n on a.entity_id = n.nid where n.status = 1 and n.type = 'retailer' and a.field_online_retailer_value = 0");
	$output = '';
	foreach($query as $k => $v) {
		$loaded = node_load($v->nid);
		$title = $loaded->title;
		$link = $loaded->field_retailer_link['und'][0]['value'];
		$logo = image_style_url("reseller_logo", $loaded->field_retailer_logo['und'][0]['uri']);
		//$type = $loaded->field_online_store['und'][0]['value'];
		$type = 1;
		$output .= '<a style="margin-bottom:15px;" href="'.$link.'" class="reseller-linkblock w-inline-block">
    <img src="'.$logo.'" alt="" class="image-64"><h5 class="heading-39">'.$title.'</h5>
    </a>';
		//echo $output;
	}
	print $output;
}
	
    /*<div class="rs2019-div2" id="ajax-target" style="display:flex;flex-wrap:wrap;justify-content:space-between;">
		<?php 
		$output = '';
		$res = db_query("select * from node n where n.type='retailer' and n.status = 1");
		foreach($res as $k => $v) {
			$loaded = node_load($v->nid);
			$nid = $v->nid;
			$title = $loaded->field_display_title['und'][0]['value'];
			$logo = image_style_url("reseller_logo", $loaded->field_retailer_logo['und'][0]['uri']);
			$link = $loaded->field_retailer_link['und'][0]['value'];
			$output .= '<a style="margin-bottom:15px;" href="'.$link.'" class="reseller-linkblock w-inline-block">
    <img src="'.$logo.'" alt="" class="image-64"><h5 class="heading-39">'.$title.'</h5>
    </a>';
		}
		echo $output;
		?>
	  </div>*/

/*$output .= '<div class="rs2019-reseller-block"><a href="'.$link.'" target="_blank" class="rs2019-linkblock w-inline-block">
        	<p class="rs2019-resellername">'.$title.'</p>
        	</a>
        	<p class="rs2019-p"></p>
        	<a href="'.$link.'" target="_blank" class="rs2019-link">'.$link.'</a></div>';*/


function glyph_countriesload() {
	//global $base_url;
	$output = '';
    $country = $_GET['countries'];
	$storeType = $_GET['online'];
	if($storeType == 'online'){
		$qu = db_query("select * from field_data_field_country a inner join field_data_field_online_retailer b on a.entity_id = b.entity_id inner join node n on a.entity_id = n.nid where n.status = 1 and n.type = 'retailer' and b.field_online_retailer_value = 1 and a.field_country_value = '".$country."'");
	}
	else{
		$qu = db_query("select * from field_data_field_country a inner join field_data_field_online_retailer b on a.entity_id = b.entity_id inner join node n on a.entity_id = n.nid where n.status = 1 and n.type = 'retailer' and b.field_online_retailer_value = 0 and a.field_country_value = '".$country."'");
	}
	//$qu = db_query("select * from field_data_gsl_addressfield a inner join node n on a.entity_id = n.nid where n.status = 1 and a.gsl_addressfield_country = '".$country."'"); 
	
	//$qu = db_query("select * from field_data_field_country a inner join node n on a.entity_id = n.nid where n.status = 1 and n.type = 'retailer' and a.field_country_value = '".$country."'");
	foreach($qu as $k => $v) {
		$loaded = node_load($v->nid);
				$title = $loaded->title;
				$logo = image_style_url("reseller_logo", $loaded->field_retailer_logo['und'][0]['uri']);
				$link = $loaded->field_retailer_link['und'][0]['value'];
				$output .= '<a style="margin-bottom:15px;" href="'.$link.'" class="reseller-linkblock w-inline-block"><img src="'.$logo.'" alt="" class="image-64"><h5 class="heading-39">'.$title.'</h5></a>';
	}
	print $output;
}

/*trying to load all in from the continent */
function glyph_regionload() { 

$allcountries = array("AF" => "Afghanistan",
					   "AX" => "Åland Islands",
					   "AL" => "Albania",
		"DZ" => "Algeria",
		"AS" => "American Samoa",
		"AD" => "Andorra",
		"AO" => "Angola",
		"AI" => "Anguilla",
		"AQ" => "Antarctica",
		"AG" => "Antigua and Barbuda",
		"AR" => "Argentina",
		"AM" => "Armenia",
		"AW" => "Aruba",
		"AU" => "Australia",
		"AT" => "Austria",
		"AZ" => "Azerbaijan",
		"BS" => "Bahamas",
		"BH" => "Bahrain",
		"BD" => "Bangladesh",
		"BB" => "Barbados",
		"BY" => "Belarus",
		"BE" => "Belgium",
		"BZ" => "Belize",
		"BJ" => "Benin",
		"BM" => "Bermuda",
		"BT" => "Bhutan",
		"BO" => "Bolivia",
		"BA" => "Bosnia and Herzegovina",
		"BW" => "Botswana",
		"BV" => "Bouvet Island",
		"BR" => "Brazil",
		"IO" => "British Indian Ocean Territory",
		"BN" => "Brunei Darussalam",
		"BG" => "Bulgaria",
		"BF" => "Burkina Faso",
		"BI" => "Burundi",
		"KH" => "Cambodia",
		"CM" => "Cameroon",
		"CA" => "Canada",
		"CV" => "Cape Verde",
		"KY" => "Cayman Islands",
		"CF" => "Central African Republic",
		"TD" => "Chad",
		"CL" => "Chile",
		"CN" => "China",
		"CX" => "Christmas Island",
		"CC" => "Cocos (Keeling) Islands",
		"CO" => "Colombia",
		"KM" => "Comoros",
		"CG" => "Congo",
		"CD" => "Congo, The Democratic Republic of The",
		"CK" => "Cook Islands",
		"CR" => "Costa Rica",
		"CI" => "Cote D'ivoire",
		"HR" => "Croatia",
		"CU" => "Cuba",
		"CY" => "Cyprus",
		"CZ" => "Czech Republic",
		"DK" => "Denmark",
		"DJ" => "Djibouti",
		"DM" => "Dominica",
		"DO" => "Dominican Republic",
		"EC" => "Ecuador",
		"EG" => "Egypt",
		"SV" => "El Salvador",
		"GQ" => "Equatorial Guinea",
		"ER" => "Eritrea",
		"EE" => "Estonia",
		"ET" => "Ethiopia",
		"FK" => "Falkland Islands (Malvinas)",
		"FO" => "Faroe Islands",
		"FJ" => "Fiji",
		"FI" => "Finland",
		"FR" => "France",
		"GF" => "French Guiana",
		"PF" => "French Polynesia",
		"TF" => "French Southern Territories",
		"GA" => "Gabon",
		"GM" => "Gambia",
		"GE" => "Georgia",
		"DE" => "Germany",
		"GH" => "Ghana",
		"GI" => "Gibraltar",
		"GR" => "Greece",
		"GL" => "Greenland",
		"GD" => "Grenada",
		"GP" => "Guadeloupe",
		"GU" => "Guam",
		"GT" => "Guatemala",
		"GG" => "Guernsey",
		"GN" => "Guinea",
		"GW" => "Guinea-bissau",
		"GY" => "Guyana",
		"HT" => "Haiti",
		"HM" => "Heard Island and Mcdonald Islands",
		"VA" => "Holy See (Vatican City State)",
		"HN" => "Honduras",
		"HK" => "Hong Kong",
		"HU" => "Hungary",
		"IS" => "Iceland",
		"IN" => "India",
		"ID" => "Indonesia",
		"IR" => "Iran, Islamic Republic of",
		"IQ" => "Iraq",
		"IE" => "Ireland",
		"IM" => "Isle of Man",
		"IL" => "Israel",
		"IT" => "Italy",
		"JM" => "Jamaica",
		"JP" => "Japan",
		"JE" => "Jersey",
		"JO" => "Jordan",
		"KZ" => "Kazakhstan",
		"KE" => "Kenya",
		"KI" => "Kiribati",
		"KP" => "Korea, Democratic People's Republic of",
		"KR" => "Korea, Republic of",
		"KW" => "Kuwait",
		"KG" => "Kyrgyzstan",
		"LA" => "Lao People's Democratic Republic",
		"LV" => "Latvia",
		"LB" => "Lebanon",
		"LS" => "Lesotho",
		"LR" => "Liberia",
		"LY" => "Libyan Arab Jamahiriya",
		"LI" => "Liechtenstein",
		"LT" => "Lithuania",
		"LU" => "Luxembourg",
		"MO" => "Macao",
		"MK" => "Macedonia, The Former Yugoslav Republic of",
		"MG" => "Madagascar",
		"MW" => "Malawi",
		"MY" => "Malaysia",
		"MV" => "Maldives",
		"ML" => "Mali",
		"MT" => "Malta",
		"MH" => "Marshall Islands",
		"MQ" => "Martinique",
		"MR" => "Mauritania",
		"MU" => "Mauritius",
		"YT" => "Mayotte",
		"MX" => "Mexico",
		"FM" => "Micronesia, Federated States of",
		"MD" => "Moldova, Republic of",
		"MC" => "Monaco",
		"MN" => "Mongolia",
		"ME" => "Montenegro",
		"MS" => "Montserrat",
		"MA" => "Morocco",
		"MZ" => "Mozambique",
		"MM" => "Myanmar",
		"NA" => "Namibia",
		"NR" => "Nauru",
		"NP" => "Nepal",
		"NL" => "Netherlands",
		"AN" => "Netherlands Antilles",
		"NC" => "New Caledonia",
		"NZ" => "New Zealand",
		"NI" => "Nicaragua",
		"NE" => "Niger",
		"NG" => "Nigeria",
		"NU" => "Niue",
		"NF" => "Norfolk Island",
		"MP" => "Northern Mariana Islands",
		"NO" => "Norway",
		"OM" => "Oman",
		"PK" => "Pakistan",
		"PW" => "Palau",
		"PS" => "Palestinian Territory, Occupied",
		"PA" => "Panama",
		"PG" => "Papua New Guinea",
		"PY" => "Paraguay",
		"PE" => "Peru",
		"PH" => "Philippines",
		"PN" => "Pitcairn",
		"PL" => "Poland",
		"PT" => "Portugal",
		"PR" => "Puerto Rico",
		"QA" => "Qatar",
		"RE" => "Reunion",
		"RO" => "Romania",
		"RU" => "Russian Federation",
		"RW" => "Rwanda",
		"SH" => "Saint Helena",
		"KN" => "Saint Kitts and Nevis",
		"LC" => "Saint Lucia",
		"PM" => "Saint Pierre and Miquelon",
		"VC" => "Saint Vincent and The Grenadines",
		"WS" => "Samoa",
		"SM" => "San Marino",
		"ST" => "Sao Tome and Principe",
		"SA" => "Saudi Arabia",
		"SN" => "Senegal",
		"RS" => "Serbia",
		"SC" => "Seychelles",
		"SL" => "Sierra Leone",
		"SG" => "Singapore",
		"SK" => "Slovakia",
		"SI" => "Slovenia",
		"SB" => "Solomon Islands",
		"SO" => "Somalia",
		"ZA" => "South Africa",
		"GS" => "South Georgia and The South Sandwich Islands",
		"ES" => "Spain",
		"LK" => "Sri Lanka",
		"SD" => "Sudan",
		"SR" => "Suriname",
		"SJ" => "Svalbard and Jan Mayen",
		"SZ" => "Swaziland",
		"SE" => "Sweden",
		"CH" => "Switzerland",
		"SY" => "Syrian Arab Republic",
		"TW" => "Taiwan, Province of China",
		"TJ" => "Tajikistan",
		"TZ" => "Tanzania, United Republic of",
		"TH" => "Thailand",
		"TL" => "Timor-leste",
		"TG" => "Togo",
		"TK" => "Tokelau",
		"TO" => "Tonga",
		"TT" => "Trinidad and Tobago",
		"TN" => "Tunisia",
		"TR" => "Turkey",
		"TM" => "Turkmenistan",
		"TC" => "Turks and Caicos Islands",
		"TV" => "Tuvalu",
		"UG" => "Uganda",
		"UA" => "Ukraine",
		"AE" => "United Arab Emirates",
		"GB" => "United Kingdom",
		"US" => "United States",
		"UM" => "United States Minor Outlying Islands",
		"UY" => "Uruguay",
		"UZ" => "Uzbekistan",
		"VU" => "Vanuatu",
		"VE" => "Venezuela",
		"VN" => "Viet Nam",
		"VG" => "Virgin Islands, British",
		"VI" => "Virgin Islands, U.S.",
		"WF" => "Wallis and Futuna",
		"EH" => "Western Sahara",
		"YE" => "Yemen",
		"ZM" => "Zambia",
		"ZW" => "Zimbabwe");


$africa = array('DZ'=>'Algeria',
'AO'=>'Angola',
'BJ'=>'Benin',
'BW'=>'Botswana',
'BF'=>'Burkina Faso',
'BI'=>'Burundi',
'CM'=>'Cameroon',
'CV'=>'Cape Verde',
'CF'=>'Central African Republic',
'KM'=>'Comoros',
'CD'=>'Democratic Republic of Congo',
'DJ'=>'Djibouti',
'EG'=>'Egypt',
'GQ'=>'Equatorial Guinea',
'ER'=>'Eritrea',
'ET'=>'Ethiopia',
'GA'=>'Gabon',
'GM'=>'Gambia',
'GH'=>'Ghana',
'GN'=>'Guinea',
'GW'=>'Guinea-Bissau',
'CI'=>'Ivory Coast',
'KE'=>'Kenya',
'LS'=>'Lesotho',
'LR'=>'Liberia',
'LY'=>'Libya',
'MG'=>'Madagascar',
'MW'=>'Malawi',
'ML'=>'Mali',
'MR'=>'Mauritania',
'MU'=>'Mauritius',
'MA'=>'Morocco',
'MZ'=>'Mozambique',
'NA'=>'Namibia',
'NE'=>'Niger',
'NG'=>'Nigeria',
'CG'=>'Republic of the Congo',
'RE'=>'Reunion',
'RW'=>'Rwanda',
'SH'=>'Saint Helena',
'ST'=>'Sao Tome and Principe',
'SN'=>'Senegal',
'SC'=>'Seychelles',
'SL'=>'Sierra Leone',
'SO'=>'Somalia',
'ZA'=>'South Africa',
'SS'=>'South Sudan',
'SD'=>'Sudan',
'SZ'=>'Swaziland',
'TZ'=>'Tanzania',
'TG'=>'Togo',
'TN'=>'Tunisia',
'UG'=>'Uganda',
'EH'=>'Western Sahara',
'ZM'=>'Zambia',
'ZW'=>'Zimbabwe');

$southamerica = array(
'AR'=>'Argentina',
'AW'=>'Aruba',
'BO'=>'Bolivia',
'BR'=>'Brazil',
'CL'=>'Chile',
'CO'=>'Colombia',
'CW'=>'Curacao',
'EC'=>'Ecuador',
'FK'=>'Falkland Islands',
'GY'=>'Guyana',
'PY'=>'Paraguay',
'PE'=>'Peru',
'SR'=>'Suriname',
'TT'=>'Trinidad and Tobago',
'UY'=>'Uruguay',
'VE'=>'Venezuela');

$northamerica = array(
'CA'=>'Canada',
'MX'=>'Mexico',
'US'=>'United States');

$asia = array(
'AF'=>'Afghanistan',
'AM'=>'Armenia',
'AZ'=>'Azerbaijan',
'BH'=>'Bahrain',
'BD'=>'Bangladesh',
'BT'=>'Bhutan',
'BN'=>'Brunei',
'KH'=>'Cambodia',
'CN'=>'China',
'GE'=>'Georgia',
'HK'=>'Hong Kong',
'IN'=>'India',
'ID'=>'Indonesia',
'IR'=>'Iran',
'IQ'=>'Iraq',
'IL'=>'Israel',
'JP'=>'Japan',
'JO'=>'Jordan',
'KZ'=>'Kazakhstan',
'KW'=>'Kuwait',
'KG'=>'Kyrgyzstan',
'LA'=>'Laos',
'LB'=>'Lebanon',
'MO'=>'Macau',
'MY'=>'Malaysia',
'MV'=>'Maldives',
'MN'=>'Mongolia',
'MM'=>'Myanmar [Burma]',
'NP'=>'Nepal',
'KP'=>'North Korea',
'OM'=>'Oman',
'PK'=>'Pakistan',
'PH'=>'Philippines',
'QA'=>'Qatar',
'SA'=>'Saudi Arabia',
'SG'=>'Singapore',
'KR'=>'South Korea',
'LK'=>'Sri Lanka',
'SY'=>'Syria',
'TW'=>'Taiwan',
'TJ'=>'Tajikistan',
'TH'=>'Thailand',
'TR'=>'Turkey',
'TM'=>'Turkmenistan',
'AE'=>'United Arab Emirates',
'UZ'=>'Uzbekistan',
'VN'=>'Vietnam',
'YE'=>'Yemen'
);


$europe = array(
'AL'=>'Albania',
'AD'=>'Andorra',
'AT'=>'Austria',
'BY'=>'Belarus',
'BE'=>'Belgium',
'BA'=>'Bosnia and Herzegovina',
'BG'=>'Bulgaria',
'HR'=>'Croatia',
'CY'=>'Cyprus',
'CZ'=>'Czech Republic',
'DK'=>'Denmark',
'EE'=>'Estonia',
'FO'=>'Faroe Islands',
'FI'=>'Finland',
'FR'=>'France',
'DE'=>'Germany',
'GI'=>'Gibraltar',
'GR'=>'Greece',
'HU'=>'Hungary',
'IS'=>'Iceland',
'IE'=>'Ireland',
'IM'=>'Isle of Man',
'IT'=>'Italy',
'XK'=>'Kosovo',
'LV'=>'Latvia',
'LI'=>'Liechtenstein',
'LT'=>'Lithuania',
'LU'=>'Luxembourg',
'MK'=>'Macedonia',
'MT'=>'Malta',
'MD'=>'Moldova',
'MC'=>'Monaco',
'ME'=>'Montenegro',
'NL'=>'Netherlands',
'NO'=>'Norway',
'PL'=>'Poland',
'PT'=>'Portugal',
'RO'=>'Romania',
'RU'=>'Russia',
'SM'=>'San Marino',
'RS'=>'Serbia',
'SK'=>'Slovakia',
'SI'=>'Slovenia',
'ES'=>'Spain',
'SE'=>'Sweden',
'CH'=>'Switzerland',
'UA'=>'Ukraine',
'GB'=>'United Kingdom',
'VA'=>'Vatican'
);

$australia = array(
'AS'=>'American Samoa',
'AU'=>'Australia',
'CK'=>'Cook Islands',
'TL'=>'East Timor',
'FJ'=>'Fiji',
'PF'=>'French Polynesia',
'GU'=>'Guam',
'KI'=>'Kiribati',
'MH'=>'Marshall Islands',
'FM'=>'Micronesia',
'NR'=>'Nauru',
'NC'=>'New Caledonia',
'NZ'=>'New Zealand',
'NU'=>'Niue',
'NF'=>'Norfolk Island',
'MP'=>'Northern Mariana Islands',
'PW'=>'Palau',
'PG'=>'Papua New Guinea',
'PN'=>'Pitcairn Islands',
'WS'=>'Samoa',
'SB'=>'Solomon Islands',
'TK'=>'Tokelau',
'TV'=>'Tuvalu',
'VU'=>'Vanuatu'
);
	
	$storeType = $_GET['online'];
	$query;
	if($storeType == 'online') {
		$query = db_query("select * from field_data_field_online_retailer a inner join node n on a.entity_id = n.nid where n.status = 1 and n.type = 'retailer' and a.field_online_retailer_value = 1");
	}
	else{
		$query = db_query("select * from field_data_field_online_retailer a inner join node n on a.entity_id = n.nid where n.status = 1 and n.type = 'retailer' and a.field_online_retailer_value = 0");
	}
	//global $base_url;
	$output = '';
    $allcountry = $_GET['region'];
	$storeType = $_GET['online'];
	if($allcountry == 'all'){
		$qu;
		if($storeType == 'online') {
		  $qu = db_query("select * from node n where n.status = 1 and n.type='retailer' and n.field_online_retailer_value = 1 and a.field_online_retailer_value = 1 order by n.title ASC");
		}
		else{
		  $qu = db_query("select * from node n where n.status = 1 and n.type='retailer' and n.field_online_retailer_value = 1 and a.field_online_retailer_value = 0 order by n.title ASC");
		}
		//$qu = db_query("select * from node n where n.status = 1 and n.type='retailer' and n.field_online_retailer_value = 1 order by n.title ASC");  
			foreach($qu as $k => $v) {
				$loaded = node_load($v->nid);
				$title = $loaded->title;
				$logo = image_style_url("reseller_logo", $loaded->field_retailer_logo['und'][0]['uri']);
				$link = $loaded->field_retailer_link['und'][0]['value'];
				$output .= '<a style="margin-bottom:15px;" href="'.$link.'" class="reseller-linkblock w-inline-block">
    			<img src="'.$logo.'" alt="" class="image-64"><h5 class="heading-39">'.$title.'</h5>
    			</a>';
			}
	}
	else{
		//$allcountry = $northamerica;
		//foreach($northamerica as $b => $c){
			//$qu = db_query("select * from field_data_gsl_addressfield a inner join node n on a.entity_id = n.nid where n.status = 1 and a.gsl_addressfield_country = '".$b."'");
			//print_r($asia);
			$allcountry = $_GET['region'];
			$full = '';
			if($allcountry == "northamerica"){
				//print_r($northamerica);
				$allcountry2 = array_keys($northamerica);
				foreach($allcountry2 as $k => $v){
					$full .= $v . ',';
				}
			}
			elseif($allcountry == "asia"){
				$allcountry2 = array_keys($asia);
				foreach($allcountry2 as $k => $v){
					$full .= $v . ',';
				}
			}
			elseif($allcountry == "africa"){
				$allcountry2 = array_keys($africa);
				foreach($allcountry2 as $k => $v){
					$full .= $v . ',';
				}
			}
			elseif($allcountry == "europe"){
				$allcountry2 = array_keys($europe);
				foreach($allcountry2 as $k => $v){
					$full .= $v . ',';
				}
			}
			elseif($allcountry == "southamerica"){
				$allcountry2 = array_keys($southamerica);
				foreach($allcountry2 as $k => $v){
					$full .= $v . ',';
				}
			}
			elseif($allcountry == "australia"){
				$allcountry2 = array_keys($australia);
				foreach($allcountry2 as $k => $v){
					$full .= $v . ',';
				}
			}
			elseif($allcountry == "all"){
					$allcountry2 = array_keys($allcountries);
					foreach($allcountry2 as $k => $v){
						$full .= $v . ',';
					}
				}
			
			else {
				print "false";
			}
			rtrim($full, ',');
			$strings = explode(",",$full);
			//MXCAUS
			//'MX', 'CA', 'US'

			$str = "'".implode("','",$strings)."'";
			//print $str;
			$storeType = $_GET['online'];
			$qu;
			if($storeType == 'online') {
			  $qu = db_query("select * from field_data_field_country a inner join field_data_field_online_retailer b on a.entity_id = b.entity_id inner join node n on a.entity_id = n.nid where n.status = 1 and n.type = 'retailer' and b.field_online_retailer_value = 0 and a.field_country_value IN (".$str.")");
			}
			else{
			  $qu = db_query("select * from field_data_field_country a inner join field_data_field_online_retailer b on a.entity_id = b.entity_id inner join node n on a.entity_id = n.nid where n.status = 0 and n.type = 'retailer' and b.field_online_retailer_value = 0 and a.field_country_value IN (".$str.")");
			}
		    //$qu = db_query("select * from field_data_field_country a inner join node n on a.entity_id = n.nid where n.status = 1 and n.type = 'retailer' and a.field_country_value IN (".$str.")"); 
			foreach($qu as $k => $v) {
				/*$loaded = node_load($v->entity_id);
				$address = $v->gsl_addressfield_thoroughfare;
				$title = $loaded->title;
				$newaddress = trim($address, '"');
				$kountry = $v->gsl_addressfield_country;
				$city = $v->gsl_addressfield_locality;
				$area = $v->gsl_addressfield_administrative_area;
				$fulladd =  $address .' '. $city .', '. $area .' '. $kountry;
				//placeholding time
				$link = $loaded->gsl_props_web['und'][0]['value'];
				$output .= '<div class="rs2019-reseller-block"><a href="'.$link.'" target="_blank" class="rs2019-linkblock w-inline-block">
				<p class="rs2019-resellername">'.$title.'</p>
				<img src="/sites/all/themes/glyph2/images/flags/'.$country.'flag.png" alt="logo" class="rs2019-flag-img" width="30">
				</a>
				<p class="rs2019-p">'.$fulladd.'</p>
				<p class="rs2019-p"></p>
				<a href="'.$link.'" target="_blank" class="rs2019-link">'.$link.'</a></div>';*/
				$loaded = node_load($v->nid);
				$title = $loaded->title;
				$logo = image_style_url("reseller_logo", $loaded->field_retailer_logo['und'][0]['uri']);
				$link = $loaded->field_retailer_link['und'][0]['value'];
				$output .= '<a style="margin-bottom:15px;" href="'.$link.'" class="reseller-linkblock w-inline-block">
    			<img src="'.$logo.'" alt="" class="image-64"><h5 class="heading-39">'.$title.'</h5>
    			</a>';
			}
		
	}
	print $output;
}

	/*	$res = db_query("select * from field_data_gsl_addressfield a inner join node n on a.entity_id = n.nid where n.status = 1");
		foreach($res as $k => $v) { 
			$loaded = node_load($v->nid);
			$nid = $v->nid;
			$title = $loaded->title;
			//$logores = db_query("select a.nid as 'reseller_nid', n.nid as 'store_location_nid' from node a inner join node n on n.title = a.title and n.type = 'store_location' and a.type = 'retailer' and store_location_nid = ".$nid);
			//$logores = db_query("select * from node n where n.type='reseller' and n.location = 'usa');
			foreach($logores as $l => $a) {
				$logoload = node_load($a->reseller_nid);
				$logo = image_style_url("reseller_logo", $logoload->field_retailer_logo['und'][0]['uri']);
			}
			$link = $loaded->field_retailer_link['und'][0]['value'];
			$phone = $loaded->gsl_props_phone['und'][0]['value'];
					$web = $loaded->gsl_props_web['und'][0]['value'];
					$address = $v->gsl_addressfield_thoroughfare;
					$newaddress = trim($address, '"');
					$country = $v->gsl_addressfield_country;
					$city = $v->gsl_addressfield_locality;
					$area = $v->gsl_addressfield_administrative_area;
					$fulladd =  $address .' '. $city .', '. $area .' '. $country;
			$output .= '<div class="rs2019-reseller-block"><a href="'.$link.'" target="_blank" class="rs2019-linkblock w-inline-block">
        <p class="rs2019-resellername">'.$title.'</p>
        <img src="'.$logo.'" alt="logo" class="rs2019-flag-img" width="30"></a>
        <p class="rs2019-p">'.$fulladd.'</p>
        <p class="rs2019-p"></p>
        <a href="'.$link.'" target="_blank" class="rs2019-link">'.$link.'</a></div>';
		}
		echo $output;
*/


function glyph_load_more_news() {
	global $base_url;
	$output = '';

	if ( isset($_GET['offset']) && isset($_GET['limit']) ) {
		$totalNews = 0;
		$loadNewsLink = $base_url.'/ajax/load-more-news';
		$limit = $_GET['limit'];
		$offset = $_GET['offset'];
		$type = $_GET['type'];
		$cswap = $_GET['swapper'];

		if($type == 'all') {
			$sql = "select n.* from node n
					where n.type='news'
					and n.status=1
					or n.type = 'event'
					and n.status = 1
					order by n.created desc
					limit ".$limit." offset ".$offset;
			$rstsql = "select n.* from node n
					where n.type='news'
					and n.status=1
					or n.type = 'event'
					and n.status = 1
					order by n.created desc
					limit ".$limit." offset ".$offset;
		} elseif($type == 'news') {
			$sql = "select n.* from node n
					where n.type='news'
					and n.status=1
					order by n.created desc
					limit ".$limit." offset ".$offset;
			$rstsql = "select n.* from node n
					where n.type='news'
					and n.status=1
					order by n.created desc
					limit ".$limit." offset ".$offset;
		} elseif($type == 'event') {
			$sql = "select n.* from node n
					where n.type='event'
					and n.status=1
					order by n.created desc
					limit ".$limit." offset ".$offset;
			$rstsql = "select n.* from node n
					where n.type='event'
					and n.status=1
					order by n.created desc
					limit ".$limit." offset ".$offset;
		}
			//get news total
			$rst = db_query("select count(*) from node where type = 'news' and status = 1");
			if ($rst) {
				$totalNews = $rst->fetchField();
			}

			//set ajax load query string
			$loadNewsLink =  '/ajax/load-more-news?offset='. $offset .'&limit=11';
		}

		$result = db_query( $sql );

		$newsArr = array();
		if( $result ) {
			foreach ($result as $k => $v) {
				$newsArr[] = node_load( $v->nid );
			}
		}

		//create html output
		$ctr = 0;
		$isClosed = true;
		$offset += 11;
	    $loadNewsLink =  '/ajax/load-more-news?offset='. $offset .'&limit=11';
		foreach ($newsArr as $key => $news) {

		  $actualtotal++;
		  $title = $news->title;
		  $image = image_style_url('home_news_images',$news->field_news_header_image['und'][0]['uri']);
		  $date = date('m.d.Y', $news->created);
		  $link = drupal_get_path_alias('node/'.$news->nid);

		  if($total % 4 == 0 && $swapper == false) {
			  	  $output .= '</div><div class="teaser-article-holder">';
				  $total = 1;
				  $swapper = true;
		  } else if($total % 5 == 0 && $swapper == true) {
			  	$output .= '</div><div class="teaser-article-holder">';
				  $total = 1;
				  $swapper = false;
		  }
		if($_GET['swapper'] == 'false')  {
		  if($total % 4 > 0 and $swapper == false) {
			if($total % 2 == 0){
				$greencolor = 'dark';
				$linkcolor = 'light';
			}
			else{
				$greencolor = 'light';
				$linkcolor = 'dark';
			}

		    $topics = '';
		    $toutput = '';
			$topics = db_query("select * from field_data_field_topics t inner join node n on t.entity_id = n.nid where t.entity_id = ".$news->nid);
			foreach($topics as $t => $y) {
				$tax = taxonomy_term_load($y->field_topics_tid);
				$ttitle = $tax->name;
				$toutput .= '<div class="relevant-hashtag-link white-link">#'.$ttitle.'</div>';
			}
		  	$output .= '<div class="green-article-block '.$greencolor.'-green">
        <div class="article-teaser-div">
          <div>
            <h4 class="article-date white-date">'.$date.'</h4>
            <h1 class="large-article-teaser-heading">'.$title.'</h1><a href="'.$link.'" class="underline-link white-'.$linkcolor.'green">read this article</a></div>
          <div class="relevant-hashtag-holder white-txt">'.$toutput.'</div>
		  </div>
      	</div>';

		} else if($total % 5 > 0 and $swapper == true ) {
			  $modulo = $total % 2;
			  $bgstyle = '';
			  if($total % 2 == 0) {
				  $style = 'white';
				  $textcolor = 'black';
				  $datecolor = '';
				  $linkcolor = '';
				  $bgstyle = 'style = "background-image:linear-gradient(180deg, hsla(0, 0%, 100%, .8), hsla(0, 0%, 100%, .8)), url('.$image.')"';
			  } else {
				  $style = 'black';
				  $bgstyle = 'style = "background-image:linear-gradient(180deg, rgba(0, 0, 0, .8), rgba(0, 0, 0, .8)),  url('.$image.')"';
				  $textcolor = '';
				  $datecolor = 'white';
				  $linkcolor = 'white-lightgreen';
			  }
			  /*if($image && $style == 'white') {

			} else {

			  }*/
			  $output .= '<div class="quarter-article-block white-bg art1 " '.$bgstyle.'>
        <div class="article-teaser-div">
          <div>
            <h4 class="article-date '.$datecolor.'-date">'.$date.'</h4>
            <h1 class="large-article-teaser-heading smaller '.$textcolor.'">'.$title.'</h1><a href="'.$link.'" class="underline-link '.$linkcolor.' smaller-link-font">read this article</a></div>
        </div>
      </div>';

		  }
		} else if ($_GET['swapper'] == 'true') {
		  if($total % 4 > 0 and $swapper == true ) {
			if($total % 2 == 0){
				$greencolor = 'dark';
				$linkcolor = 'light';
			}
			else{
				$greencolor = 'light';
				$linkcolor = 'dark';
			}

		    $topics = '';
		    $toutput = '';
			$topics = db_query("select * from field_data_field_topics t inner join node n on t.entity_id = n.nid where t.entity_id = ".$news->nid);
			foreach($topics as $t => $y) {
				$tax = taxonomy_term_load($y->field_topics_tid);
				$ttitle = $tax->name;
				$toutput .= '<div class="relevant-hashtag-link white-link">#'.$ttitle.'</div>';
			}
		  	$output .= '<div class="green-article-block '.$greencolor.'-green">
        <div class="article-teaser-div">
          <div>
            <h4 class="article-date white-date">'.$date.'</h4>
            <h1 class="large-article-teaser-heading">'.$title.'</h1><a href="'.$link.'" class="underline-link white-'.$linkcolor.'green">read this article</a></div>
          <div class="relevant-hashtag-holder white-txt">'.$toutput.'</div>
		  </div>
      	</div>';

		} else if($total % 6 > 0 and $swapper == false) {
			  $modulo = $total % 2;
			  $bgstyle = '';
			  if($total % 2 == 0) {
				  $style = 'white';
				  $textcolor = 'black';
				  $datecolor = '';
				  $linkcolor = '';
				  $bgstyle = 'style = "background-image:linear-gradient(180deg, hsla(0, 0%, 100%, .8), hsla(0, 0%, 100%, .8)), url('.$image.');width:34%;"';
			  } else {
				  $style = 'black';
				  $bgstyle = 'style = "background-image:linear-gradient(180deg, rgba(0, 0, 0, .8), rgba(0, 0, 0, .8)),  url('.$image.');width:33%;"';
				  $textcolor = '';
				  $datecolor = 'white';
				  $linkcolor = 'white-lightgreen';
			  }
			  /*if($image && $style == 'white') {

			} else {

			  }*/
			  $output .= '<div class="quarter-article-block white-bg art1 " '.$bgstyle.'>
        <div class="article-teaser-div">
          <div>
            <h4 class="article-date '.$datecolor.'-date">'.$date.'</h4>
            <h1 class="large-article-teaser-heading smaller '.$textcolor.'">'.$title.'</h1><a href="'.$link.'" class="underline-link '.$linkcolor.' smaller-link-font">read this article</a></div>
        </div>
      </div>';

		  }
		}

		  $total ++;

		if($totaltotal == $actualtotal){
			  $output .= '</div><div class="replacer" id="replacer '.$offset.'"></div>';
		  }
		//hide load more button
			//echo ($offset+$limit);
		if ( $totalNews - ($offset+$limit) < 0 ) {
			$output .= '<style>.infinite-scroll{display:none;}</style>';
		}

	}
	print $output;
	exit;
}












function glyph_get_product_attributes() {
	$output = '';
	global $base_url;


	if ( isset($_POST['pid']) ) {
		$product = node_load($_POST['pid']);

		//display product title
		$output = '<h2 class="h2 grey">Choose Capacity:</h2><div class="medium-product-name">'.$product->title.'</div>';

		//attr SKU
		$attrSKU = uc_product_get_models($product->nid);
		$skuArr = array();
		foreach ($attrSKU as $k => $sku) {
			$skuArr[] = $sku;
		}

		//product image
		$output .= '<div class="prod-img"><img src="'.image_style_url('product_image',$product->uc_product_image['und'][0]['uri']).'" /></div>';

        /*
		$limit = 1;
		//loop attributes
		foreach($product->attributes as $k => $attribute) {
			$output .= '<div class="sizes product-option-list">';

			//loop attribute options
			$ctr = 0;
			$isClosed = true;
			$cartURL = '';
			foreach ($attribute->options as $k => $option) {
				// $cartURL = $base_url.'/cart/add/p'.$option->nid.'_a'.$option->aid.'o'.$option->oid.'?destination=cart';
				$cartURL = $base_url.'/cart/add/';
				$prodURL = '-p'.$option->nid.'_a'.$option->aid.'o'.$option->oid.'_q1';

				//alternate sku
				$option->sku = $product->model;
				if ( isset($skuArr[$ctr+2]) ) {
					$option->sku = $skuArr[$ctr+2];
				}

				if ($ctr % 3 == 0) {
					$output .= '<div class="w-row capacity-row">';
					$isClosed = false;
				}

				$output .= '<div class="w-col w-col-4 w-col-small-4 w-col-tiny-4 capacity-column">
								<a href="#" data-url="'.$prodURL.'" class="w-inline-block product-option">
									<h3 class="bold-h3">'.$option->name.'</h3>
									<div>'.$option->sku.'</div>
									<h4 class="price-h4">$'.number_format( ($product->price + $option->price), 2, '.', '').'</h4>
									<div class="add-button"></div>
								</a>
							</div>';

				if ($ctr > 0 && $ctr % 3 == 2) {
					$output .= '</div>';
					$isClosed = true;
				}

				$ctr++;
			}

			if (!$isClosed) {
				$output .= '</div>';
			}

			$output .= '<div></div>';

			$limit--;

			if ($limit == 0) {
				break;
			}
		}
		*/



		//check if no attribute
		if ( count($product->attributes) == 0 ) {
			$cartURL = $base_url.'/cart/add/-p'.$product->nid.'_q1';
            $addBtn = '<a href="'.$cartURL.'" class="w-button button small-shell-btn green prod-checkout-btn">Add to Cart</a>';
		} else {
		    $product_node = node_load($product->nid);
            $output .= drupal_render(drupal_get_form('uc_product_add_to_cart_form_'.$product->nid, $product_node));
		}

		//check if coming soon product
		if ( !isset($product->field_coming_soon['und'][0]['value']) || $product->field_coming_soon['und'][0]['value'] != 1 ) {

		} else {
			$addBtn = '<h3 class="green">Coming Soon</h3>';
		}

		//buttons
		$output .= '<div class="checkout-buttons-div">
						'.$addBtn.'
						<div class="prod-success-section">
							<p class="success-text">You have successfully added the product to the cart.</p>
							<a href="'.$base_url.'/cart" data-ix="close-side-bar" class="w-button button small-shell-btn green" style="transition: all 0.8s ease 0s;">View Cart</a>
							<a href="'.$base_url.'/cart/checkout" class="w-button button small-solid-btn green">Check out</a>
						</div>
					</div>
					<a href="#" data-ix="close-side-bar" class="w-inline-block close-button prod-close-btn">
						<div></div>
					</a>';

	} else {
	    if (isset($_POST['attributes'])) {
	        //get product id
	        $product_id = str_replace('uc_product_add_to_cart_form_', '', $_POST['form_id']);

	        $url = '/cart/add/p'.$product_id;
            foreach ($_POST['attributes'] as $key => $value) {
                $url .= "_a$key"."o$value";
            }
            $url .= '?destination=cart';
            header("Location: $url");
            exit;
	    } else {
		  $output = '<div class="medium-product-name">AN ERROR ENCOUNTERED RETRIEVING THE PRODUCT ATTRIBUTES</div>';
        }

        print '<pre>';
        print_r($_REQUEST);
        print '</pre>';
	}

	print $output;
}

function glyph_download_reseller() {
	global $base_url;
	$resp = array();

	if ( isset($_POST['nid']) && is_numeric($_POST['nid']) ) {
		$reseller = node_load($_POST['nid']);
		if ( isset($reseller->nid) ) {
			module_load_include('inc', 'zip_archive', 'zip_archive.class');
			$zipName = 'documents.zip';
			if ( file_exists($zipName) ) {
				unlink($zipName);
			}
			$myfile = fopen($zipName, "w");
			// $zip = new ArchiverZip($zipName);
			$zip = new ZipArchive;
			if ( $zip->open($zipName) === TRUE ) {
				foreach ($reseller->field_reseller_documents['und'] as $k => $v) {
					$doc = field_collection_item_load($v['value']);
					$file = drupal_realpath($doc->field_document['und'][0]['uri']);
					// $zip->add($file,$doc->field_document['und'][0]['filename']);
					$zip->addFile($file, $doc->field_document['und'][0]['filename']);
				}
			}
			$zip->close();
			$resp['zip'] = $base_url.'/'.$zipName;
			$resp['success'] = true;
		} else {
			$resp['success'] = false;
		}
	} else {
		$resp['success'] = false;
	}
	// print_r($zip->getArchive());
	print json_encode($resp);
	exit;
}

function glyph_get_landing_by_cat($category,$limit = -1) {
	$landingArr = array();

	$limitStr = "";
	if ($limit > 0) {
		$limitStr = "limit ".$limit;
	}
	$sql = "select * from node n
			inner join field_data_field_landing_category c
			on n.nid = c.entity_id
			where n.type = 'landing_page'
			and c.field_landing_category_value = '$category'
			and n.status = 1
			order by n.created desc ".$limitStr;
	$result = db_query($sql);

	if( $result ) {
		foreach ($result as $k => $v) {
			$landingArr[] = node_load( $v->nid );
		}
	}

	return $landingArr;
}

function glyph_form_alter(&$form, &$form_state, $form_id) {
	if ( isset($form_id) && !empty($form_id) ) {
		//Alter Contact Us form - Footer
		if ($form_id == "webform_client_form_2") {
			//Name
			$form['submitted']['name']['#title_display'] = "invisible";
			$form['submitted']['name']['#attributes']['id'][] = "name";
			$form['submitted']['name']['#attributes']['class'][] = "w-input";
			$form['submitted']['name']['#attributes']['class'][] = "text-field";
			$form['submitted']['name']['#attributes']['placeholder'] = "Name*";
			$form['submitted']['name']['#attributes']['data-name'] = "Name";
			$form['submitted']['name']['#attributes']['required'] = "required";
			$form['submitted']['name']['#prefix'] = '<div class="w-col w-col-4 field-name">';
			$form['submitted']['name']['#suffix'] = '</div>';

			//Phone
			$form['submitted']['phone']['#title_display'] = "invisible";
			$form['submitted']['phone']['#attributes']['id'][] = "phone";
			$form['submitted']['phone']['#attributes']['class'][] = "w-input";
			$form['submitted']['phone']['#attributes']['class'][] = "text-field";
			$form['submitted']['phone']['#attributes']['placeholder'] = "Phone";
			$form['submitted']['phone']['#attributes']['data-name'] = "Phone";
			$form['submitted']['phone']['#prefix'] = '<div class="w-col w-col-4 field-phone">';
			$form['submitted']['phone']['#suffix'] = '</div>';

			//Email
			$form['submitted']['email']['#title_display'] = "invisible";
			$form['submitted']['email']['#attributes']['id'][] = "email";
			$form['submitted']['email']['#attributes']['class'][] = "w-input";
			$form['submitted']['email']['#attributes']['class'][] = "text-field";
			$form['submitted']['email']['#attributes']['placeholder'] = "Email*";
			$form['submitted']['email']['#attributes']['data-name'] = "Email";
			$form['submitted']['email']['#attributes']['required'] = "required";
			$form['submitted']['email']['#prefix'] = '<div class="w-col w-col-4 field-email">';
			$form['submitted']['email']['#suffix'] = '</div>';

			//Message
			$form['submitted']['message']['#title_display'] = "invisible";
			$form['submitted']['message']['#attributes']['id'][] = "message";
			$form['submitted']['message']['#attributes']['class'][] = "w-input";
			$form['submitted']['message']['#attributes']['class'][] = "message";
			$form['submitted']['message']['#attributes']['placeholder'] = "Enter Message...";
			$form['submitted']['message']['#attributes']['data-name'] = "Message";
			$form['submitted']['message']['#attributes']['required'] = "required";

			//Submit
			$form['actions']['submit']['#attributes']['class'][] = "w-button";
			$form['actions']['submit']['#attributes']['class'][] = "button";
			$form['actions']['submit']['#attributes']['class'][] = "small-solid-btn";
			$form['actions']['submit']['#attributes']['class'][] = "green";
			$form['actions']['submit']['#attributes']['value'] = "Send Message";
			$form['actions']['submit']['#attributes']['data-wait'] = "Please Wait...";
		}

		//Alter Newsletter
		if ($form_id == "webform_client_form_45") {
			$form['#action'] = 'https://app.icontact.com/icp/signup.php';

			//Email
			$form['submitted']['email']['#title_display'] = "invisible";
			$form['submitted']['email']['#attributes']['id'][] = "email";
			$form['submitted']['email']['#attributes']['class'][] = "w-input";
			$form['submitted']['email']['#attributes']['class'][] = "text-field";
			$form['submitted']['email']['#attributes']['class'][] = "newsletter";
			$form['submitted']['email']['#attributes']['placeholder'] = "Email*";
			$form['submitted']['email']['#attributes']['data-name'] = "Email";
			$form['submitted']['email']['#attributes']['required'] = "required";

			//Submit
			$form['actions']['submit']['#attributes']['class'][] = "w-button";
			$form['actions']['submit']['#attributes']['class'][] = "submit-button";
			$form['actions']['submit']['#attributes']['value'][] = "Submit";
			$form['actions']['submit']['#attributes']['data-wait'] = "Please Wait...";
		}

		//Add new validation on Product Registration
		if ($form_id == "webform_client_form_46") {
			$form['#validate'][] = 'glyph_phone_number_validate';
			$form['#validate'][] = 'glyph_date_validate';
            $form['#submit'][] = 'glyph_product_registration';
		}

		// Alter User Login
		if ($form_id == "user_login") {
			//Name
			$form['name']['#attributes']['class'][] = "w-input";
			$form['name']['#attributes']['data-name'] = "Name";
			$form['name']['#attributes']['required'] = "required";

			//Password
			$form['pass']['#attributes']['class'][] = "w-input";
			$form['pass']['#attributes']['data-name'] = "Password";
			$form['pass']['#attributes']['required'] = "required";
			$form['pass']['#description'] = '<a style="color: #3cb373;" href="/user/password">Forgot password?</a>';

			//Submit
			$form['actions']['submit']['#attributes']['class'][] = "w-button button small-solid-btn green";
			$form['actions']['submit']['#attributes']['data-wait'] = "Please Wait...";
		}

		// Alter User Register
		if ($form_id == "user_register_form") {
			//Name
			$form['account']['name']['#attributes']['class'][] = "w-input";
			$form['account']['name']['#attributes']['data-name'] = "Name";
			$form['account']['name']['#attributes']['required'] = "required";

			//Password
			$form['account']['mail']['#attributes']['class'][] = "w-input";
			$form['account']['mail']['#attributes']['data-name'] = "Email";
			$form['account']['mail']['#attributes']['required'] = "required";

			//Submit
			$form['actions']['submit']['#attributes']['class'][] = "w-button button small-solid-btn green";
			$form['actions']['submit']['#attributes']['data-wait'] = "Please Wait...";
		}

		// Alter User Password
		if ($form_id == "user_pass") {
			//Name
			$form['name']['#attributes']['class'][] = "w-input";
			$form['name']['#attributes']['data-name'] = "Name";
			$form['name']['#attributes']['required'] = "required";

			//Submit
			$form['actions']['submit']['#attributes']['class'][] = "w-button button small-solid-btn green";
			$form['actions']['submit']['#attributes']['data-wait'] = "Please Wait...";
		}

        if ($form_id == 'page_node_form') {
            //print '<pre>'.print_r($form, true).'</pre>';
            //exit;
            $related_content = array();
            $result = db_query("select n.title, n.nid from {field_data_field_case_study_sub_content} s inner join {node} n on s.entity_id=n.nid where field_case_study_sub_content_nid=:nid", array(':nid' => $form['nid']['#value']));
            foreach ($result as $row) {
                $related_content[] = array('nid' => $row->nid, 'title' => $row->title);
            }
            $form['glyph_related_content'] = array(
                '#type' => 'fieldset',
                '#title' => 'Related content',
                '#group' => 'additional_settings',
                '#weight' => -1000
            );
            if (count($related_content) > 0) {
                $related_content_html = '';
                foreach ($related_content as $content) {
                    $related_content_html .= '<li><a href="/node/'.$content['nid'].'/edit">'.$content['title'].'</a></li>';
                }
                $form['glyph_related_content']['related'] = array(
                    '#markup' => "<strong>Related content:</strong><ul>$related_content_html</ul>"
                );
            } else {
                $form['glyph_related_content']['related'] = array(
                    '#markup' => 'This page is not referenced on any other content.'
                );
            }
        }

		//Alter Engage webform
		if ($form_id == "webform_client_form_423") {
			//Name
			$form['submitted']['name']['#title_display'] = "invisible";
			$form['submitted']['name']['#attributes']['class'][] = "w-input";
			$form['submitted']['name']['#attributes']['class'][] = "text-field";
			$form['submitted']['name']['#attributes']['placeholder'] = "Name*";
			$form['submitted']['name']['#attributes']['data-name'] = "Name";
			$form['submitted']['name']['#attributes']['required'] = "required";
			$form['submitted']['name']['#prefix'] = '<div class="w-row"><div class="w-col w-col-6 w-col-small-6">';
			$form['submitted']['name']['#suffix'] = '</div>';

			//Phone
			/*
			$form['submitted']['phone']['#title_display'] = "invisible";
			$form['submitted']['phone']['#attributes']['class'][] = "w-input";
			$form['submitted']['phone']['#attributes']['class'][] = "text-field";
			$form['submitted']['phone']['#attributes']['placeholder'] = "Phone";
			$form['submitted']['phone']['#attributes']['data-name'] = "Phone";
			$form['submitted']['phone']['#prefix'] = '<div class="w-col w-col-6 w-col-small-6">';
			$form['submitted']['phone']['#suffix'] = '</div>';
			*/
			//Email
			$form['submitted']['email']['#title_display'] = "invisible";
			$form['submitted']['email']['#attributes']['class'][] = "w-input";
			$form['submitted']['email']['#attributes']['class'][] = "text-field";
			$form['submitted']['email']['#attributes']['placeholder'] = "Email*";
			$form['submitted']['email']['#attributes']['data-name'] = "Email";
			$form['submitted']['email']['#attributes']['required'] = "required";
			$form['submitted']['email']['#prefix'] = '<div class="w-col w-col-6 w-col-small-6">';
			$form['submitted']['email']['#suffix'] = '</div></div>';

			//Submit
			$form['actions']['submit']['#attributes']['class'][] = "w-button";
			$form['actions']['submit']['#attributes']['class'][] = "button";
			$form['actions']['submit']['#attributes']['class'][] = "small-shell-btn";
			$form['actions']['submit']['#attributes']['class'][] = "white";
			$form['actions']['submit']['#attributes']['value'] = "Submit";
			$form['actions']['submit']['#attributes']['data-wait'] = "Please Wait...";
		}

		//Alter Application Form
		if ($form_id == "webform_client_form_491") {
			// print_r($form);
			global $base_url;

			$form['#theme'] = array('application_form');
			$form['#validate'][] = 'glyph_social_media_validate';
			$form['#attributes']['class'][] = 'application-form';
			$form['#action'] = 'application.html';

			//update name
			$form['submitted']['name']['#title_display'] = "invisible";
			$form['submitted']['name']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['name']['#attributes']['placeholder'] = "Enter your name*";
			$form['submitted']['name']['#attributes']['data-name'] = "Name";
			$form['submitted']['name']['#attributes']['required'] = "required";

			//update company
			$form['submitted']['company']['#title_display'] = "invisible";
			$form['submitted']['company']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['company']['#attributes']['placeholder'] = "Company";
			$form['submitted']['company']['#attributes']['data-name'] = "Company";

			//update profession
			$form['submitted']['profession']['#title_display'] = "invisible";
			$form['submitted']['profession']['#attributes']['class'][] = "w-select dropdown";
			$form['submitted']['profession']['#attributes']['required'] = "required";
			$form['submitted']['profession']['#attributes']['data-name'] = "Profession";
			$form['submitted']['profession']['#empty_option'] = t("Profession");

			//update email
			$form['submitted']['email']['#title_display'] = "invisible";
			$form['submitted']['email']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['email']['#attributes']['placeholder'] = "Email*";
			$form['submitted']['email']['#attributes']['data-name'] = "Email";
			$form['submitted']['email']['#attributes']['required'] = "required";

			//update phone
			$form['submitted']['phone']['#title_display'] = "invisible";
			$form['submitted']['phone']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['phone']['#attributes']['placeholder'] = "Phone*";
			$form['submitted']['phone']['#attributes']['data-name'] = "Phone";
			$form['submitted']['phone']['#attributes']['required'] = "required";

			//update website
			$form['submitted']['website']['#title_display'] = "invisible";
			$form['submitted']['website']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['website']['#attributes']['placeholder'] = "Website";
			$form['submitted']['website']['#attributes']['data-name'] = "Website";

			//update address
			$form['submitted']['address']['#title_display'] = "invisible";
			$form['submitted']['address']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['address']['#attributes']['placeholder'] = "Address";
			$form['submitted']['address']['#attributes']['data-name'] = "Address";

			//update use glyph
			$form['submitted']['use_glyph']['#title_display'] = "invisible";
			$form['submitted']['use_glyph']['#attributes']['class'][] = "form-invisible";

			//update drives
			$form['submitted']['drives']['#title_display'] = "invisible";
			$form['submitted']['drives']['#attributes']['class'][] = "form-invisible";

			//update favorite drive
			$form['submitted']['favorite_drive']['#title_display'] = "invisible";
			$form['submitted']['favorite_drive']['#attributes']['class'][] = "w-select dropdown";
			$form['submitted']['favorite_drive']['#attributes']['required'] = "required";
			$form['submitted']['favorite_drive']['#attributes']['data-name'] = "Favorite";
			$form['submitted']['favorite_drive']['#empty_option'] = t("Favorite");

			//update why glyph
			$form['submitted']['why_glyph']['#title_display'] = "invisible";
			$form['submitted']['why_glyph']['#attributes']['class'][] = "w-input message";
			$form['submitted']['why_glyph']['#attributes']['placeholder'] = "Why do you choose Glyph for your storage needs?*";
			$form['submitted']['why_glyph']['#attributes']['data-name'] = "Why Glyph";
			$form['submitted']['why_glyph']['#attributes']['required'] = "required";
			$form['submitted']['why_glyph']['#attributes']['maxlength'] = "5000";

			//update why join
			$form['submitted']['why_join']['#title_display'] = "invisible";
			$form['submitted']['why_join']['#attributes']['class'][] = "w-input message";
			$form['submitted']['why_join']['#attributes']['placeholder'] = "Why would you like to be on the Pro Team?*";
			$form['submitted']['why_join']['#attributes']['data-name'] = "Why Join";
			$form['submitted']['why_join']['#attributes']['required'] = "required";
			$form['submitted']['why_join']['#attributes']['maxlength'] = "5000";

			//update credentials
			$form['submitted']['credentials']['#title_display'] = "invisible";
			$form['submitted']['credentials']['#attributes']['class'][] = "w-input message";
			$form['submitted']['credentials']['#attributes']['placeholder'] = "What are your credentials?* (I.e elevator pitch)";
			$form['submitted']['credentials']['#attributes']['data-name'] = "Credientials";
			$form['submitted']['credentials']['#attributes']['required'] = "required";
			$form['submitted']['credentials']['#attributes']['maxlength'] = "5000";

			//update article1
			$form['submitted']['article1']['#title_display'] = "invisible";
			$form['submitted']['article1']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['article1']['#attributes']['placeholder'] = "1.";
			$form['submitted']['article1']['#attributes']['data-name'] = "Article 1";

			//update article2
			$form['submitted']['article2']['#title_display'] = "invisible";
			$form['submitted']['article2']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['article2']['#attributes']['placeholder'] = "2.";
			$form['submitted']['article2']['#attributes']['data-name'] = "Article 2";

			//update article3
			$form['submitted']['article3']['#title_display'] = "invisible";
			$form['submitted']['article3']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['article3']['#attributes']['placeholder'] = "3.";
			$form['submitted']['article3']['#attributes']['data-name'] = "Article 3";

			//update bio
			$form['submitted']['biography']['#title_display'] = "invisible";
			$form['submitted']['biography']['#attributes']['class'][] = "w-input message";
			$form['submitted']['biography']['#attributes']['placeholder'] = "Tell us your story / bio*";
			$form['submitted']['biography']['#attributes']['data-name'] = "Bio";
			$form['submitted']['biography']['#attributes']['required'] = "required";
			$form['submitted']['biography']['#attributes']['maxlength'] = "5000";

			//update facebook
			$form['submitted']['facebook']['#title_display'] = "invisible";
			$form['submitted']['facebook']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['facebook']['#attributes']['placeholder'] = "facebook.com/";
			$form['submitted']['facebook']['#attributes']['data-name'] = "Facebook";

			//update twitter
			$form['submitted']['twitter']['#title_display'] = "invisible";
			$form['submitted']['twitter']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['twitter']['#attributes']['placeholder'] = "twitter.com/";
			$form['submitted']['twitter']['#attributes']['data-name'] = "Twitter";

			//update instagram
			$form['submitted']['instagram']['#title_display'] = "invisible";
			$form['submitted']['instagram']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['instagram']['#attributes']['placeholder'] = "instagram.com/";
			$form['submitted']['instagram']['#attributes']['data-name'] = "Instagram";

			//update youtube
			$form['submitted']['youtube']['#title_display'] = "invisible";
			$form['submitted']['youtube']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['youtube']['#attributes']['placeholder'] = "youtube.com/";
			$form['submitted']['youtube']['#attributes']['data-name'] = "Youtube";

			//update google plus
			$form['submitted']['google_plus']['#title_display'] = "invisible";
			$form['submitted']['google_plus']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['google_plus']['#attributes']['placeholder'] = "plus.google.com/";
			$form['submitted']['google_plus']['#attributes']['data-name'] = "Google-Plus";

			//update other
			$form['submitted']['other']['#title_display'] = "invisible";
			$form['submitted']['other']['#attributes']['class'][] = "w-input text-field";
			$form['submitted']['other']['#attributes']['placeholder'] = "Other";
			$form['submitted']['other']['#attributes']['data-name'] = "Other";

			//update profile picture
			// $form['submitted']['profile_picture']['#title_display'] = "invisible";
			$form['submitted']['profile_picture']['#attributes']['class'][] = "glyph-upload-btn";

			//update cover photo
			// $form['submitted']['cover_photo']['#title_display'] = "invisible";
			$form['submitted']['cover_photo']['#attributes']['class'][] = "glyph-upload-btn";

			//update submit
			$form['actions']['submit']['#attributes']['class'][] = "w-button button small-solid-btn green";
			$form['actions']['submit']['#attributes']['data-wait'] = "Please Wait...";
			$form['actions']['submit']['#value'] = "Submit application";
			/*
			$form['actions']['submit']['#ajax'] = array(
				'callback' => 'glyph_scroll_to_top',
				'wrapper' => 'webform-ajax-wrapper-491',
				'progress' => array( 'message' => '', 'type' => 'throbber'),
                'event' => 'click'
			); */
		}

		//Alter Reseller Registration Form
		if ($form_id == "webform_client_form_764") {
			//Company
			$form['submitted']['company']['#attributes']['class'][] = "w-input";
			$form['submitted']['company']['#attributes']['placeholder'] = "Enter Company name";
			$form['submitted']['company']['#attributes']['data-name'] = "Company";
			$form['submitted']['company']['#attributes']['required'] = "required";

			//First Name
			$form['submitted']['first_name']['#attributes']['class'][] = "w-input";
			$form['submitted']['first_name']['#attributes']['placeholder'] = "Enter your First name";
			$form['submitted']['first_name']['#attributes']['data-name'] = "First Name";
			$form['submitted']['first_name']['#attributes']['required'] = "required";

			//Last Name
			$form['submitted']['last_name']['#attributes']['class'][] = "w-input";
			$form['submitted']['last_name']['#attributes']['placeholder'] = "Enter your Last name";
			$form['submitted']['last_name']['#attributes']['data-name'] = "Last Name";
			$form['submitted']['last_name']['#attributes']['required'] = "required";

			//Username
			$form['submitted']['name']['#attributes']['class'][] = "w-input";
			$form['submitted']['name']['#attributes']['placeholder'] = "Enter your desired username";
			$form['submitted']['name']['#attributes']['data-name'] = "Create username";
			$form['submitted']['name']['#attributes']['required'] = "required";

			//Email
			$form['submitted']['mail']['#attributes']['class'][] = "w-input";
			$form['submitted']['mail']['#attributes']['placeholder'] = "Enter your email";
			$form['submitted']['mail']['#attributes']['data-name'] = "Email";
			$form['submitted']['mail']['#attributes']['required'] = "required";

			//Password
			$form['submitted']['password']['#type'] = "password";
			$form['submitted']['password']['#attributes']['class'][] = "w-input";
			$form['submitted']['password']['#attributes']['placeholder'] = "Enter your password";
			$form['submitted']['password']['#attributes']['data-name'] = "Password";
			$form['submitted']['password']['#attributes']['required'] = "required";

			//Confirm Password
			$form['submitted']['confirm_password']['#type'] = "password";
			$form['submitted']['confirm_password']['#attributes']['class'][] = "w-input";
			$form['submitted']['confirm_password']['#attributes']['placeholder'] = "Confirm your password";
			$form['submitted']['confirm_password']['#attributes']['data-name'] = "Confirm Password";
			$form['submitted']['confirm_password']['#attributes']['required'] = "required";
			$form['submitted']['confirm_password']['#validate'][] = 'glyph_confirm_password_validate';

			//Submit btn
			$form['actions']['submit']['#attributes']['class'][] = "w-button button small-solid-btn green";
			$form['actions']['submit']['#attributes']['data-wait'] = "Please Wait...";

			$form['#validate'][] = 'glyphmod_reseller_register_validate';
			$form['#submit'][] = 'glyphmod_reseller_register_submit';
		}
	}
}

function glyph_scroll_to_top($form, &$form_state) {
	/* $commands[] = ajax_command_prepend('.section.form-section', '<script>jQuery(\'html, body\').animate({ scrollTop: jQuery(".section.form-section").offset().top }, 600);</script>');
	return array('#type' => 'ajax', '#commands' => $commands); */
	$output = array();
  // If user completed his submission, determine what to do.
  if (!empty($form_state ['webform_completed']) && empty($form_state ['save_draft'])) {
    $output = _webform_ajax_callback_completed($form, $form_state);
  }
  // Else, we're just switching page, or saving draft.
  else {
    $output = $form;
  }
  print_r($output);
  return $output;
}

function glyph_theme($existing, $type, $theme, $path) {
	$themepath = '/'.drupal_get_path('theme', 'glyph');

	return array(
		'application_form' => array(
		  'render element' => 'form',
		  'template' => 'form--application-form', //<-- enter template file name here
		  'path' => $themepath,
		),
	);
}

function glyph_social_media_validate($form, &$form_state) {
	//check if social media links is at least 3
	$values = $form_state['values']['submitted'];
	$ctr = 0;

	if ( isset($values['facebook']) && !empty($values['facebook']) ) {
		$ctr++;
	}
	if ( isset($values['twitter']) && !empty($values['twitter']) ) {
		$ctr++;
	}
	if ( isset($values['instagram']) && !empty($values['instagram']) ) {
		$ctr++;
	}
	if ( isset($values['youtube']) && !empty($values['youtube']) ) {
		$ctr++;
	}
	if ( isset($values['google_plus']) && !empty($values['google_plus']) ) {
		$ctr++;
	}
	if ( isset($values['other']) && !empty($values['other']) ) {
		$ctr++;
	}

	if ( $ctr < 3 ) {
		form_set_error('facebook', 'Social Media Links should at least be 3');
	}

}

function glyph_phone_number_validate($form, &$form_state) {
	if ( preg_match('/[a-zA-Z]/', $form_state['values']['submitted']['phone_number']) ) {
		form_set_error('phone_number', 'Please provide a valid phone number');
	}
}

function glyph_date_validate($form, &$form_state) {
	$date = $form_state['values']['submitted']['date_of_purchase'];
    if ( !(bool)strtotime($date) ) {
		form_set_error('date_of_purchase', 'Please provide a valid date');
	}
}

function glyph_confirm_password_validate($form, &$form_state) {
	$values = $form_state['values']['submitted'];
	if ( $values['password'] != $values['confirm_password'] ) {
		form_set_error('confirm_password', 'Password does not match');
	}
}

function glyph_user_login(&$edit, $account) {
	if (arg(0) == 'user' && arg(1) == 'reset') {
		return true;
	} else {
		if ( user_has_role(3, $account) ) {
			drupal_goto('/admin/content');
		} else if ( user_has_role(4, $account) ) {
			drupal_goto('/reseller-portal.html');
		} else {
			drupal_goto('/');
		}
	}
    exit;
}

function glyph_product_registration($form, &$form_state) {
    //print '<pre>';
    //print_r($form_state['values']['submitted_tree']);
    //print '</pre>';
    //exit;
    $values = $form_state['values']['submitted_tree'];

    /* submitted values
    [first_name] => mark
    [last_name] => Sly
    [email_address] => mark@digiance.com
    [phone_number] => 8016740506
    [address] => 5795 S RIDGE CREEK CIR
    [city] => MURRAY
    [state] => Utah
    [zip] => 84107
    [country] => United States
    [operating_system] => Array
        (
            [0] => Mac OS X
            [1] => Windows 8
        )

    [primary_use] => Video
    [where_to_use] => Professional Studio
    [yes_no] => Yes
    [date_of_purchase] => 5/12/2015
    [place_of_purchase] => BestBuy
    [serial_number_1] => 123testing
    [serial_number_2] => 213another
    [serial_number_3] => 586working
    [serial_number_4] =>
     */

    //create zendesk user
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://glyph.zendesk.com/api/v2/users.json');
    curl_setopt($ch, CURLOPT_USERPWD, "liran@glyphtech.com/token:thxZWve8oiTWRa4FGkriu53FtLr1HvOeLu43nJ5k");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
        'user' => array(
            'name' => "{$values['first_name']} {$values['last_name']}",
            'email' => $values['email_address'],
            'user_fields' => array(
                'billing_address' => "{$values['address']}, {$values['city']}, {$values['state']} {$values['zip']}",
                'phone_number' => formatPhoneNumber($values['phone_number']),
            ),
            'details' => "Place of Purchase: {$values['place_of_purchase']}\nPrimary Use: {$values['primary_use']}\nWhere to use: {$values['where_to_use']}\nDate of Purchase: {$values['date_of_purchase']}\nSerial #1: {$values['serial_number_1']}\nSerial #2: {$values['serial_number_2']}\nSerial #3: {$values['serial_number_3']}\nSerial #4: {$values['serial_number_4']}"
        ))
    ));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);


    //register with iContact
    require_once('sites/all/modules/glyph/iContactApi.php');

    // Give the API your information
    iContactApi::getInstance()->setConfig(array(
        'appId'       => '6qx6xwtwQ9PdPmrflA2MDjE4F2uPjN4n',
        'apiPassword' => 'F@k3riM0j0',
        'apiUsername' => 'jsularski@glyphtech.com'
    ));

    // Store the singleton
    $oiContact = iContactApi::getInstance();


    try {
        $contact = $oiContact->addContact($values['email_address'], null, null, $values['first_name'], $values['last_name'], null, $values['billing_address'], '', $values['city'], $values['state'], $values['zip'], $values['phone_number'], null, null);
        if (is_numeric($contact->contactId)) {
            $oiContact->subscribeContactToList($contact->contactId, 23494, 'normal');
        }
    } catch (Exception $oException) { // Catch any exceptions
    }

}

function glyph_can_product_add_cart($id) {
	$flag = false;

	if ( isset($id) && is_numeric($id) ) {
		$product = node_load($id);
		if ( isset($product->field_add_to_cart['und'][0]['value']) && $product->field_add_to_cart['und'][0]['value'] == 1 ) {
			$flag = true;
		}
	}

	return $flag;
}

function formatPhoneNumber($phoneNumber) {
    $phoneNumber = preg_replace('/[^0-9]/','',$phoneNumber);

    if(strlen($phoneNumber) > 10) {
        $countryCode = substr($phoneNumber, 0, strlen($phoneNumber)-10);
        $areaCode = substr($phoneNumber, -10, 3);
        $nextThree = substr($phoneNumber, -7, 3);
        $lastFour = substr($phoneNumber, -4, 4);

        $phoneNumber = '+'.$countryCode.' ('.$areaCode.') '.$nextThree.'-'.$lastFour;
    }
    else if(strlen($phoneNumber) == 10) {
        $areaCode = substr($phoneNumber, 0, 3);
        $nextThree = substr($phoneNumber, 3, 3);
        $lastFour = substr($phoneNumber, 6, 4);

        $phoneNumber = "$areaCode-".$nextThree.'-'.$lastFour;
    }
    else if(strlen($phoneNumber) == 7) {
        $nextThree = substr($phoneNumber, 0, 3);
        $lastFour = substr($phoneNumber, 3, 4);

        $phoneNumber = $nextThree.'-'.$lastFour;
    }

    return $phoneNumber;
}

function glyph_uc_order($op, $order, $arg2) {
    /*
    if ($op == 'save') {

        //build cti order object
        $cti_order = array(
            'gci' => 1081,
            'comment' => '',
            'order_number' => 'GLYPH'.$order->order_id.time()
        );
        $cti_order['bill_data'] = array(
            'last_name' => $order->billing_last_name,
            'street' => $order->billing_street1,
            'phone' => $order->billing_phone,
            'postal_code' => $order->billing_postal_code,
            'country_code' => 'US',
            'state_code' => uc_get_zone_code($order->billing_zone),
            'city' => $order->billing_city,
            'first_name' => $order->billing_first_name,
            'company_name' => $order->billing_company,
            'email' => $order->primary_email
        );
        $cti_order['delivery'] = array(
            'address' => array(
                'last_name' => $order->delivery_last_name,
                'street' => $order->delivery_street1,
                'phone' => $order->delivery_phone,
                'postal_code' => $order->delivery_postal_code,
                'country_code' => 'US',
                'state_code' => uc_get_zone_code($order->delivery_zone),
                'city' => $order->delivery_city,
                'first_name' => $order->delivery_first_name,
                'company_name' => $order->delivery_company,
                'email' => $order->primary_email
            )
        );

        $cti_order['customer'] = array(
            'phone' => $order->billing_phone,
            'last_name' => $order->billing_last_name,
            'first_name' => $order->billing_first_name,
            'email' => $order->primary_email,
            'company_name' => $order->billing_company,
            'address' => array(
                'city' => $order->billing_city,
                'state' => uc_get_zone_code($order->billing_zone),
                'street' => $order->billing_street1,
                'postal_code' => $order->billing_postal_code,
                'country_code' => 'US'
            )
        );
        $cti_order['products'] = array();
        foreach ($order->products as $product) {
            $cti_order['products'][] = array(
                'sku' => $product->model,
                'quantity' => $product->qty,
                'net_price' => $product->price,
                'part_number' => $product->model,
                'name' => $product->title
            );
        }

        if (count($order->data['coupons']) > 0) {
            foreach ($order->data['coupons'] as $coupon) {
                foreach ($coupon as  $key => $value) {
                    $cti_order['products'][] = array(
                        'sku' => 'discount',
                        'quantity' => 1,
                        'net_price' => $value->discount,
                        'part_number' => 'discount',
                        'name' => 'discount'
                    );
                }
            }
        }

        //get authorize.net transaction id
        $payment_data = db_query("select data from {uc_payment_receipts} where order_id=:id", array(':id' => $order->order_id))->fetchField();
        $payment_data = unserialize($payment_data);
        $cti_order['payment'] = array(
            'payment_name' => 'Credit Card',
            'id' => 3,
            'transaction_id' => $payment_data['txn_id'],
            'payment_status' => 'authorized'
        );

        if (isset($order->taxes)) {
            foreach ($order->taxes as $tax) {
                $cti_order['sales_tax'] = array(
                    'tax_amount' => round($tax->amount, 2),
                    'tax_code' => 'CA',
                    'tax_name' => 'Sales Tax'
                );
            }
        }

        //shipping method
        foreach ($order->line_items as $line) {
            if ($line['type'] == 'shipping') {
                if ($line['title'] == 'Overnight') {
                    $cti_order['delivery']['cost'] = $line['amount'];
                    $cti_order['delivery']['id'] = 12;
                    $cti_order['delivery']['name'] = 'FedEx Standard Overnight';
                } else {
                    $cti_order['delivery']['cost'] = $line['amount'];
                    $cti_order['delivery']['id'] = 1;
                    $cti_order['delivery']['name'] = 'FedEx Ground';
                }
            }
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://order.planetb2b.com/api/v.1.0/order/4b53df6d60a8f32c46327d0b487a57/e8127aae41/');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($cti_order));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-type: application/json'
        ));
        $response = curl_exec($curl);

        print_r($response);
    }
    */
}

function glyph_uc_checkout_complete($order, $account) {
    //see if order already transmitted
    $result = db_query("select cti_order_number from {uc_orders} where order_id=:id", array(':id' => $order->order_id))->fetchField();
    if (strlen($cti_order_number) < 3) {

        $order = uc_order_load($order->order_id);

        //build cti order object
        $cti_order = array(
            'gci' => 12345,
            'comment' => '',
            'origin' => 'GLYPH',
            'order_number' => 'GLYPH'.$order->order_id,
            'customer_group' => 'GLYPH',
        );
        $cti_order['bill_data'] = array(
            'last_name' => $order->billing_last_name,
            'street' => $order->billing_street1,
            'phone' => $order->billing_phone,
            'postal_code' => $order->billing_postal_code,
            'country_code' => 'US',
            'state_code' => uc_get_zone_code($order->billing_zone),
            'city' => $order->billing_city,
            'first_name' => $order->billing_first_name,
            'company_name' => $order->billing_company,
            'email' => $order->primary_email
        );
        $cti_order['delivery'] = array(
            'address' => array(
                'last_name' => $order->delivery_last_name,
                'street' => $order->delivery_street1,
                'phone' => $order->delivery_phone,
                'postal_code' => $order->delivery_postal_code,
                'country_code' => 'US',
                'state_code' => uc_get_zone_code($order->delivery_zone),
                'city' => $order->delivery_city,
                'first_name' => $order->delivery_first_name,
                'company_name' => $order->delivery_company,
                'email' => $order->primary_email
            )
        );

        $cti_order['customer'] = array(
            'phone' => $order->billing_phone,
            'last_name' => $order->billing_last_name,
            'first_name' => $order->billing_first_name,
            'email' => $order->primary_email,
            'company_name' => $order->billing_company,
            'customer_group' => 'GLYPH',
            'address' => array(
                'city' => $order->billing_city,
                'state' => uc_get_zone_code($order->billing_zone),
                'street' => $order->billing_street1,
                'postal_code' => $order->billing_postal_code,
                'country_code' => 'US'
            )
        );
        $cti_order['products'] = array();
        foreach ($order->products as $product) {
            $cti_order['products'][] = array(
                'sku' => $product->model,
                'quantity' => $product->qty,
                'net_price' => $product->price,
                'part_number' => $product->model,
                'name' => $product->title
            );
        }

        if (count($order->data['coupons']) > 0) {
            foreach ($order->data['coupons'] as $coupon_name => $coupon) {
                foreach ($coupon as  $key => $value) {
                    $cti_order['products'][] = array(
                        'sku' => 'discount',
                        'quantity' => 1,
                        'net_price' => ($value->discount*-1),
                        'part_number' => $coupon_name,
                        'name' => 'discount'
                    );
                }
            }
        }

        //get authorize.net transaction id
        $payment_data = db_query("select data from {uc_orders} where order_id=:id", array(':id' => $order->order_id))->fetchField();
        $payment_data = unserialize($payment_data);
        foreach ($payment_data['cc_txns']['authorizations'] as $key => $value) {
            $cti_order['payment'] = array(
                'payment_name' => 'Credit Card',
                'id' => 3,
                'transaction_id' => $key,
                'payment_status' => 'authorized'
            );
        }

        /*
        if (isset($order->taxes)) {
            foreach ($order->taxes as $tax) {
                $cti_order['sales_tax'] = array(
                    'tax_amount' => round($tax->amount, 2),
                    'tax_code' => 'CA',
                    'tax_name' => 'Sales Tax'
                );
            }
        }
        */

        foreach ($order->line_items as $line) {
            if ($line['type'] == 'tax') {
                $cti_order['sales_tax'] = array(
                    'tax_amount' => round($line['amount'], 2),
                    'tax_code' => uc_get_zone_code($order->billing_zone),
                    'tax_name' => $line['title']
                );
            }
        }

        //shipping method
        foreach ($order->line_items as $line) {
            if ($line['type'] == 'shipping') {
                if ($line['title'] == 'Overnight') {
                    $cti_order['delivery']['cost'] = $line['amount'];
                    $cti_order['delivery']['id'] = 12;
                    $cti_order['delivery']['name'] = 'Overnight';
                } else {
                    $cti_order['delivery']['cost'] = $line['amount'];
                    $cti_order['delivery']['id'] = 1;
                    $cti_order['delivery']['name'] = 'Ground';
                }
            }
        }

        $fp = fopen('/var/www/vhosts/glyphtech.com/debug.txt', 'w+');
        fwrite($fp, "**************************************\n");
        fwrite($fp, print_r($order, true));
        fwrite($fp, print_r($cti_order, true));
        fwrite($fp, print_r($payment_data, true));
        fclose($fp);

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'http://order.planetb2b.com/api/v.1.0/order/4b53df6d60a8f32c46327d0b487a57/e8127aae41/');
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($cti_order));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-type: application/json'
            ));
            $response = curl_exec($curl);

            $cti_response = json_decode($response);

            if (isset($cti_response->order_id)) {
                db_update('uc_orders')->fields(array(
                    'cti_order_id' => $cti_response->order_id,
                    'cti_order_number' => $cti_response->order_number,
                ))->condition('order_id', $order->order_id)->execute();
            }
        } catch (Exception $ex) {
            //error handling

        }
    }
}

function glyph_reseller_registration_form($form, &$form_state) {
	$form = array();

	// $form = drupal_get_form('user_register_form');
	$form = drupal_get_form('webform_client_form_660');
	// print_r($form);
	$form['reseller'] = array();

	//Reseller title
	$form['reseller']['title'] = array(
		'#type' => 'textfield',
		'#title' => 'Reseller Name',
		'#maxlength' => 60,
		'#required' => 1,
		'#attributes' => array(
			'class' => array('w-input'),
			'data-name' => 'Title',
			'required' => 'required'
		),
		'#title_display' => 'before',
		'#id' => 'title',
		'#name' => 'title'
	);

	//Reseller Logo
	$form['reseller']['logo'] = array(
		'#type' => 'file',
		'#title' => 'Reseller Logo',
		'#required' => 1,
		'#attributes' => array(
			'class' => array(),
			'data-name' => 'Logo',
			'required' => 'required'
		),
		'#title_display' => 'before',
		'#id' => 'logo',
		'#name' => 'logo'
	);

	// Reseller Link
	$form['reseller']['link'] = array(
		'#type' => 'textfield',
		'#title' => 'Reseller Link',
		'#maxlength' => 250,
		'#attributes' => array(
			'class' => array('w-input'),
			'data-name' => 'Link',
		),
		'#title_display' => 'before',
		'#id' => 'link',
		'#name' => 'link'
	);

	/* //Submit button
	$form['actions']['#type'] = 'actions';
	$form['actions']['submit']['#type'] = 'submit';
	$form['actions']['submit']['#value'] = 'Submit';
	$form['actions']['submit']['#attributes']['class'][] = "w-button button small-solid-btn green";
	$form['actions']['submit']['#attributes']['value'] = "Submit";
	$form['actions']['submit']['#attributes']['data-wait'] = "Please Wait..."; */

	//Add reseller submit form
	/* $form['#submit'] = array();
	$form['#submit'][] = 'glyph_reseller_register_submit'; */

	return $form;
}

function glyphmod_reseller_register_validate($form, &$form_state) {
	//check username
	$usr = user_load_by_name($form_state['values']['submitted']['name']);
	if($usr){
		form_set_error('name', 'Username already exists.');
	}

	//check email
	$usr = user_load_by_mail($form_state['values']['submitted']['mail']);
	if($usr){
		form_set_error('mail', 'Email already exists.');
	}

	//check if password match
	$pass = $form_state['values']['submitted']['password'];
	$pass2 = $form_state['values']['submitted']['confirm_password'];
	if ( $pass != $pass2 ) {
		form_set_error('confirm_password', 'Password do not match.');
	}
}

function glyphmod_reseller_register_submit($form, &$form_state) {
	global $base_url;
	$values = $form_state['values']['submitted_tree'];
	$file = file_load($values['logo']);
	// print_r($file); exit;

	if ( !user_load_by_mail($form_state['submitted'][2]) ) {
		// Create user.
		$new_user = array(
			'name' => $form_state['values']['submitted'][1],
			'pass' => $form_state['values']['submitted'][9],
			'mail' => $form_state['values']['submitted'][2],
			'status' => 0,
			'init' => $form_state['values']['submitted'][2],
			'roles' => array(4 => 'reseller'),
			'field_company' => array(LANGUAGE_NONE => array(array('value' => $form_state['values']['submitted'][6]))),
			'field_first_name' => array(LANGUAGE_NONE => array(array('value' => $form_state['values']['submitted'][7]))),
			'field_last_name' => array(LANGUAGE_NONE => array(array('value' => $form_state['values']['submitted'][8]))),
		);
		$account = user_save(NULL, $new_user);
		if ($account) {
			/* send email notification to admin */
			$to      = 'sales@glyphtech.com, ecardinale@glyphtech.com, marketing@glyphtech.com';
			$subject = 'Reseller Registration';
			$message = 'New reseller has registered and is pending approval. Click <a href="'.$base_url.'/admin/glyph/pending-resellers">HERE</a> to view registration.';
			$headers = "From: 'Glyphtech'<info@glyphtech.com>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

			mail($to, $subject, $message, $headers);

			/* _user_mail_notify('register_pending_approval', $account);
			drupal_set_message(t('Thank you for applying for an account. Your account is currently pending approval by the site administrator.<br />In the meantime, a welcome message with further instructions has been sent to your e-mail address.'));

			//Create reseller content type
			global $user;
			$node = new stdClass();
			$node->title = $values['title'];
			$node->type = 'retailer';
			node_object_prepare($node); // Sets some defaults. Invokes hook_prepare() and hook_node_prepare().
			$node->language = LANGUAGE_NONE; // Or e.g. 'en' if locale is enabled
			$node->uid = $account->uid;
			$node->status = 0;
			$node->promote = 0;
			$node->comment = 0;
			$node->field_retailer[$node->language][]['value'] = $values['title'];
			$node->field_display_title[$node->language][]['value'] = $values['title'];
			$fileArr = array(
				'fid' => $file->fid,
				'uid' => $account->uid,
				'filename' => $file->filename,
				'uri' => $file->uri,
				'filemime' => $file->filemime,
				'filesize' => $file->filesize,
				'status' => $file->status,
			);
			$node->field_retailer_logo[$node->language][] = $fileArr;
			$node->field_retailer_link[$node->language][]['value'] = $values['link'];
			$node = node_submit($node);
			if ( !node_save($node) ) {
				// drupal_set_message(t('Error saving reseller information.'), 'error');
			} */
		} else {
			drupal_set_message(t('Error saving user account.'), 'error');
		}
	}
}

function glyph_pending_resellers() {
    $output = '<h1>Pending Resellers</h1>';

	$sql = "select u.* from users u
			inner join users_roles r using(uid)
			where r.rid = 4 and u.status = 0
			order by u.created asc";
	$result = db_query($sql);

	foreach ($result as $row) {
		$userArr = user_load($row->uid);
        $rows[] = array(
            $userArr->mail,
			$userArr->field_first_name['und'][0]['value'].' '.$userArr->field_last_name['und'][0]['value'],
			$userArr->field_company['und'][0]['value'],
			date('m/d/Y g:i:s A', $userArr->created),
            l('edit','user/'.$userArr->uid.'/edit').' | '.l('approve', 'admin/reseller/'.$userArr->uid.'/approve'),
        );
    }

	if (count($rows) == 0) {
        $rows[] = array( array('data' => 'No pending resellers to review.', 'colspan' => 5, 'style' => 'text-align: center;' ) );
    }
    $header = array(t('Email'),t('Name'),t('Company'),t('Registered'),t('Operations'));
    $output .= theme_table(array('header' => $header, 'rows' => $rows, 'sticky' => true, 'attributes' => array('style' => 'width:768px')));

    return $output;
}

function glyphmod_approve_reseller($form, &$form_state, $uid) {
	if ( isset($uid) && is_numeric($uid) ) {
		$usr = user_load($uid);
		$field = array('status'=>1);
		if ( isset($usr->uid) ) {
			$usr = user_save($usr, $field);
			if ( $usr ) {
				// user_mail_notify('status_activated', $usr);
				drupal_set_message($usr->mail.' is successfully approved.');
			}
		}
	}

	drupal_goto('admin/glyph/pending-resellers');
}
?>
