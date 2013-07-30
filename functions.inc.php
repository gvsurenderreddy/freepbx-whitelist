<?php /* $Id */
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

//    This is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    This is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with FreePBX.  If not, see <http://www.gnu.org/licenses/>.
//



function whitelist_get_config($engine) {
	global $ext;
	global $version;
	global $astman;

	switch($engine) {
		case "asterisk":

			$id = "app-whitelist";
			$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

			$id = "app-whitelist-check";
			$c = "s";

// rename from blocked to allowed
			$ext->add($id, $c, '', new ext_gotoif('$["${CALLERID(number)}" = "Unknown"]','check-blocked'));
			$ext->add($id, $c, '', new ext_gotoif('$["${CALLERID(number)}" = "Unavailable"]','check-blocked'));
			$ext->add($id, $c, '', new ext_gotoif('$["foo${CALLERID(number)}" = "foo"]','check-blocked','check'));
			$ext->add($id, $c, 'check-blocked', new ext_gotoif('$["${DB(whitelist/blocked)}" = "1"]','whitelisted'));

			
			if (version_compare($version, "1.6", "ge")) {
				$ext->add($id, $c, 'check', new ext_gotoif('$["${BLACKLIST()}"="1"]', 'whitelisted'));
			} else {
				$ext->add($id, $c, 'check', new ext_lookupwhitelist(''));
				$ext->add($id, $c, '', new ext_gotoif('$["${LOOKUPBLSTATUS}"="FOUND"]', 'whitelisted'));
			}
    			$ext->add($id, $c, '', new ext_setvar('CALLED_BLACKLIST','1'));
			$ext->add($id, $c, '', new ext_return(''));
			$ext->add($id, $c, 'whitelisted', new ext_answer(''));
			$ext->add($id, $c, '', new ext_wait(1));
			$ext->add($id, $c, '', new ext_zapateller(''));
			$ext->add($id, $c, '', new ext_playback('ss-noservice'));
			$ext->add($id, $c, '', new ext_hangup(''));

			$modulename = 'whitelist';

			if (is_array($featurelist = featurecodes_getModuleFeatures($modulename))) {
				foreach($featurelist as $item) {
					$featurename = $item['featurename'];
					$fname = $modulename.'_'.$featurename;
					if (function_exists($fname)) {
						$fcc = new featurecode($modulename, $featurename);
						$fc = $fcc->getCodeActive();
						unset($fcc);

						if ($fc != '') {
							$fname($fc);
						}
					} else {
						$ext->add('from-internal-additional', 'debug', '', new ext_noop($modulename.": No func $fname"));
						var_dump($item);
					}
				}
			}

			break;
	}
}

function whitelist_whitelist_add($fc) {
	global $ext;

	$ext->add('app-whitelist', $fc, '', new ext_goto('1', 's', 'app-whitelist-add'));

	$id = "app-whitelist-add";
	$c = "s";
	$ext->add($id, $c, '', new ext_answer);
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_set('NumLoops', 0));
	$ext->add($id, $c, 'start', new ext_playback('enter-num-whitelist'));
	$ext->add($id, $c, '', new ext_digittimeout(5));
	$ext->add($id, $c, '', new ext_responsetimeout(60));
	$ext->add($id, $c, '', new ext_read('blacknr', 'then-press-pound'));
	$ext->add($id, $c, '', new ext_saydigits('${blacknr}'));
	$ext->add($id, $c, '', new ext_playback('if-correct-press&digits/1'));
	$ext->add($id, $c, '', new ext_noop('Waiting for input'));
	$ext->add($id, $c, 'end', new ext_waitexten(60));
	$ext->add($id, $c, '', new ext_playback('sorry-youre-having-problems&goodbye'));
	$c = "1";
	$ext->add($id, $c, '', new ext_gotoif('$[ "${blacknr}" != ""]','','app-whitelist-add-invalid,s,1'));
	$ext->add($id, $c, '', new ext_set('DB(whitelist/${blacknr})', 1));
	$ext->add($id, $c, '', new ext_playback('num-was-successfully&added'));
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_hangup);

	$id = "app-whitelist-add-invalid";
	$c = "s";
	$ext->add($id, $c, '', new ext_set('NumLoops','$[${NumLoops} + 1]'));
	$ext->add($id, $c, '', new ext_playback('pm-invalid-option'));
	$ext->add($id, $c, '', new ext_gotoif('$[${NumLoops} < 3]','app-whitelist-add,s,start'));
	$ext->add($id, $c, '', new ext_playback('goodbye'));
	$ext->add($id, $c, '', new ext_hangup);

}

function whitelist_whitelist_remove($fc) {
	global $ext;

	$ext->add('app-whitelist', $fc, '', new ext_goto('1', 's', 'app-whitelist-remove'));

	$id = "app-whitelist-remove";
	$c = "s";
	$ext->add($id, $c, '', new ext_answer);
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_playback('entr-num-rmv-blklist'));
	$ext->add($id, $c, '', new ext_digittimeout(5));
	$ext->add($id, $c, '', new ext_responsetimeout(60));
	$ext->add($id, $c, '', new ext_read('blacknr', 'then-press-pound'));
	$ext->add($id, $c, '', new ext_saydigits('${blacknr}'));
	$ext->add($id, $c, '', new ext_playback('if-correct-press&digits/1'));
	$ext->add($id, $c, '', new ext_noop('Waiting for input'));
	$ext->add($id, $c, 'end', new ext_waitexten(60));
	$ext->add($id, $c, '', new ext_playback('sorry-youre-having-problems&goodbye'));
	$c = "1";
 	$ext->add($id, $c, '', new ext_dbdel('whitelist/${blacknr}'));
 	$ext->add($id, $c, '', new ext_playback('num-was-successfully&removed'));
 	$ext->add($id, $c, '', new ext_wait(1));
 	$ext->add($id, $c, '', new ext_hangup);
}

function whitelist_whitelist_last($fc) {
	global $ext;

	$ext->add('app-whitelist', $fc, '', new ext_goto('1', 's', 'app-whitelist-last'));

	$id = "app-whitelist-last";
	$c = "s";
	$ext->add($id, $c, '', new ext_answer);
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_setvar('lastcaller', '${DB(CALLTRACE/${CALLERID(number)})}'));
	$ext->add($id, $c, '', new ext_gotoif('$[ $[ "${lastcaller}" = "" ] | $[ "${lastcaller}" = "unknown" ] ]', 'noinfo'));
 	$ext->add($id, $c, '', new ext_playback('privacy-to-whitelist-last-caller&telephone-number'));
	$ext->add($id, $c, '', new ext_saydigits('${lastcaller}'));
	$ext->add($id, $c, '', new ext_setvar('TIMEOUT(digit)', '3'));
	$ext->add($id, $c, '', new ext_setvar('TIMEOUT(response)', '7'));
	$ext->add($id, $c, '', new ext_playback('if-correct-press&digits/1'));
	$ext->add($id, $c, '', new ext_goto('end'));
	$ext->add($id, $c, 'noinfo', new ext_playback('unidentified-no-callback'));
	$ext->add($id, $c, '', new ext_hangup);
	$ext->add($id, $c, '', new ext_noop('Waiting for input'));
	$ext->add($id, $c, 'end', new ext_waitexten(60));
	$ext->add($id, $c, '', new ext_playback('sorry-youre-having-problems&goodbye'));
	$c = "1";
	$ext->add($id, $c, '', new ext_set('DB(whitelist/${lastcaller})', 1));
	$ext->add($id, $c, '', new ext_playback('num-was-successfully'));
	$ext->add($id, $c, '', new ext_playback('added'));
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_hangup);
}

function whitelist_hookGet_config($engine) {
	global $ext;
	switch($engine) {
		case "asterisk":
			// Code from modules/core/functions.inc.php core_get_config inbound routes
			$didlist = core_did_list();
			if (is_array($didlist)) {
				foreach ($didlist as $item) {

					$exten = trim($item['extension']);
					$cidnum = trim($item['cidnum']);
						
					if ($cidnum != '' && $exten == '') {
						$exten = 's';
						$pricid = ($item['pricid']) ? true:false;
					} else if (($cidnum != '' && $exten != '') || ($cidnum == '' && $exten == '')) {
						$pricid = true;
					} else {
						$pricid = false;
					}
					$context = ($pricid) ? "ext-did-0001":"ext-did-0002";

					$exten = (empty($exten)?"s":$exten);
					$exten = $exten.(empty($cidnum)?"":"/".$cidnum); //if a CID num is defined, add it

					$ext->splice($context, $exten, 1, new ext_gosub('1', 's', 'app-whitelist-check'));
				}
			} // else no DID's defined. Not even a catchall.
			break;
	}
}

function whitelist_list() {
	global $amp_conf;
	global $astman;

$ast_ge_16 =  version_compare($amp_conf['ASTVERSION'], "1.6", "ge");
        if ($astman) {
		$list = $astman->database_show('cidname');
		if($ast_ge_16) {
		    foreach ($list as $k => $v) {
			$numbers = substr($k, 9);
			$whitelisted[] = array('number' => $numbers, 'description' => $v);
			}
		    if (isset($whitelisted) && is_array($whitelisted))
			// Why this sorting? When used it does not yield the result I want
			//    natsort($whitelisted);
		    return isset($whitelisted)?$whitelisted:null;
		} else {
		    foreach ($list as $k => $v) {
			$numbers[substr($k, 11)] = substr($k, 11);
			}
			if (isset($numbers) && is_array($numbers))
			    natcasesort($numbers);
			return isset($numbers)?$numbers:null;
			}
        } else {
                fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
        }
}

function whitelist_del($number){
	global $amp_conf;
	global $astman;
	if ($astman) {
		$astman->database_del("cidname",$number);
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}

function whitelist_add($post){
	global $amp_conf;
	global $astman;

$ast_ge_16 =  version_compare($amp_conf['ASTVERSION'], "1.6", "ge");

	if(!whitelist_chk($post))
		return false;

	extract($post);
	if ($astman) {
		if ($ast_ge_16) {
		$post['description']==""?$post['description'] = '1':$post['description'];
		$astman->database_put("cidname",$post['number'], '"'.$post['description'].'"');
		    } else {
		    	    $astman->database_put("cidname",$number, '1');
		    	    }
		
		// Add it back if it's checked
		if($post['blocked'] == "1")  {

// need to write this value to module table		
			needreload();
		}
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}


// ensures post vars is valid
function whitelist_chk($post){
	return true;
}

?>
