<?php /* $Id: locales.php 939 2010-01-23 06:11:13Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/locales/de/locales.php $ */
//$locale_char_set = 'iso-8859-15';
$locale_char_set = 'utf-8'; // must be lower-case! because dP doesn't check case-insensitively against this!
// 0 = sunday, 1 = monday
define('LOCALE_FIRST_DAY', 0);
define('LOCALE_TIME_FORMAT', '%I:%M %p');