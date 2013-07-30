<?php
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


// module configuration column names
//$cols['id'] = "INTEGER NOT NULL PRIMARY KEY $autoincrement";
$cols['description'] = "varchar(50) default NULL";
$cols['blocked'] = "INT default '0'";

// create the tables
$sql = "CREATE TABLE IF NOT EXISTS `whitelist` (
	id INTEGER NOT NULL PRIMARY KEY $autoincrement);";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not create `whitelist` table: " . $check->getMessage() .  "\n");
}

//check to see that the proper columns are in the table.
$curret_cols = array();
$sql = "DESC whitelist";
$res = $db->query($sql);
while($row = $res->fetchRow())
{
	if(array_key_exists($row[0],$cols))
	{
		$curret_cols[] = $row[0];
		//make sure it has the latest definition
		$sql = "ALTER TABLE whitelist MODIFY ".$row[0]." ".$cols[$row[0]];
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_freepbx( "Can not update column ".$row[0].": " . $check->getMessage() .  "<br>");
		}
	}
	
}

//add missing columns
foreach($cols as $key=>$val)
{
	if(!in_array($key,$curret_cols))
	{
		$sql = "ALTER TABLE whitelist ADD ".$key." ".$val;
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_freepbx( "Can not add column ".$key.": " . $check->getMessage() .  "<br>");
		}
		else
		{
			print 'Added column '.$key.' to whitelist table.<br>';
		}
	}
}

$fcc = new featurecode('whitelist', 'whitelist_add');
$fcc->setDescription('Whitelist a number');
$fcc->setDefault('*330');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$fcc = new featurecode('whitelist', 'whitelist_remove');
$fcc->setDescription('Remove a number from the Whitelist');
$fcc->setDefault('*331');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$fcc = new featurecode('whitelist', 'whitelist_last');
$fcc->setDescription('Whitelist the last caller');
$fcc->setDefault('*332');
$fcc->update();
unset($fcc);
?>
