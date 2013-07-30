<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//This file is part of FreePBX.
//
//    FreePBX is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    FreePBX is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with FreePBX.  If not, see <http://www.gnu.org/licenses/>.
//
//  Copyright (C) 2006 Magnus Ullberg (magnus@ullberg.us)
//


$fcc = new featurecode('whitelist', 'whitelist_add');
$fcc->setDescription('Blacklist a number');
$fcc->setDefault('*30');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$fcc = new featurecode('whitelist', 'whitelist_remove');
$fcc->setDescription('Remove a number from the whitelist');
$fcc->setDefault('*31');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$fcc = new featurecode('whitelist', 'whitelist_last');
$fcc->setDescription('Blacklist the last caller');
$fcc->setDefault('*32');
$fcc->update();
unset($fcc);
?>
