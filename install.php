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
