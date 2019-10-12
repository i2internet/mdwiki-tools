<?php

// ------------------
// ---  Wiki DIR  ---
// ------------------

/* -- MDwiki-tools v0.7-0 --
	wikidir.php Created by the people at i2 Internet, www.i2internet.com
	See https://github.com/i2internet/mdwiki-tools for notes & latest source code.
	(C) 2018 by i2 Internet Incorporated and contributors.
	This software is licensed under the terms of the GNU GPLv3.
	Originally created for use with MDwiki, see www.mdwiki.info */

if (isset($_GET['p']))
	$path = $_GET['p'];
elseif (isset($_GET['path']))
	$path = $_GET['path'];
else
	$path = '/';

$path = str_replace('*and*', '&', $path);  // ensure '&' utilized, at folder

$dir = getcwd().$path;

$mdpath = $path.'/';
if ($mdpath == '//')
	$mdpath = '/';

if (!is_dir($dir)) {
	echo '#Invalid Directory'.chr(10);
	echo "The path supplied to 'wikidir.php':".chr(10);
	echo '<pre>/#!/wikidir.php?path=</pre>';
	echo '... is not a directory:'.chr(10);
	echo '<pre>'.$dir.'</pre>';
	echo 'Please make any necessary changes, thank you.'.chr(10);
	echo '<br />'.chr(10);
	echo '###Directories Only - No Markdown'.chr(10);
	echo '*Note that the path above references a markdown file.'.chr(10);
	echo "'wikidir.php' accommodates directories only.*".chr(10);
	exit;
}
else {
	$files = scandir($dir);  // no 2nd param or 0 = ascending
	if (isset($files[2]) && substr($files[2], 0, 2) == '20')  // use [2] since '.' and '..' are at [0] and [1]
		$files = scandir($dir, 1);  // 1 = descending, show dated items (which start with year) descending instead of ascending
	else
		natcasesort($files);
	$files = array_diff($files, array('..', '.'));  // rid '.' and '..'
}

if ($mdpath == '/') {
	echo '#[**Wiki**](/) / ';
	echo 'Notes';
}
elseif ($mdpath != '/') {

	echo '#[**Wiki**](/) / ';
	echo '[**Notes**](/#!wikidir.php)';
	//echo '#[**Notes**](/#!wikidir.php)';

	$a = explode('/', ltrim($mdpath, '/'));

	foreach($a as $index => $element) {
		if ($element != '') {
			if ($index != (count($a) - 2)) {
				$dirpath = implode('/', array_slice($a, 0, $index + 1));
				echo ' / [**'.$element.'**](#!/wikidir.php?path=/'.$dirpath.')';
			}
			else
				echo ' / '.$element;
		}
	}
}
else
	echo chr(10).'---'.chr(10);

echo chr(10);

function boldFileExt($s) {
	$p = strrpos($s, '.');
	if ($p !== false)
		return substr($s, 0, $p).'**'.substr($s, $p).'**';
	else
		return $s;
}

foreach($files as $file) {

	// -- extension --
	$p = strrpos($file, '.');
	if ($p !== false)
		$ext = strtolower(substr($file, $p));  // '.txt' for example (and '.TXT' becomes '.txt')
	else
		$ext = '';

	if (substr($file, 0, 1) != '.'
		&& $ext != '.php'
		&& $ext != '.html'
		&& substr($file, -7) != '_UNUSED'
		&& $file != 'index.md' && $file != 'navigation.md'
		&& $file != 'serverid'
	) {

		// -- closing parenthesis --
		// ensure that closing parenthesis are replaced with HTML entity '&$41;' at markdown links since markdown utilizes ')' as the marker for the end of the URL:
		$url = str_replace(')', '&#41;', $file);

		// -- spaces to pluses --
		// MDwiki wants actual spaces (whereas 'wikidir.php' accommodates pluses), so we comment this out:
		//$mdpath = str_replace(' ', '+', $mdpath);


		// -- markdown --
		if ($ext == '.md') {
			echo '['.rtrim($file, '.md');
			echo '](#!'.$mdpath.$url.')'.chr(10);
		}

		// -- image --
		elseif ($ext == '.jpg'
			|| $ext == '.gif'
			|| $ext == '.png'
			|| $ext == '.svg'
			|| $ext == '.ai'
		) {
			echo '['.boldFileExt($file);
			echo ']('.$mdpath.$url.')'.chr(10);
		}

		// -- text, json --
		elseif ($ext == '.txt' || $ext == '.json') {
			echo '['.boldFileExt($file);
			echo ']('.$mdpath.$url.')'.chr(10);
		}

		// -- other files --
		elseif ($ext == '.rtf' || $ext == '.rtfd' || $ext == '.pages'
			|| $ext == '.numbers' || $ext == '.xlsx'
		) {
			echo '['.boldFileExt($file);
			echo ']('.$mdpath.$url.')'.chr(10);
		}

		// -- folder --
		else {
			echo '[**'.$file.' »**';
			# $file = str_replace(array('&'), array('&#38;'), $file);
			$file = str_replace(array('&'), array('*and*'), $file);  // ensure '&' passed through
			echo '](#!/wikidir.php?path='.str_replace(' ', '+', $mdpath).urlencode($file).')'.chr(10);  // str_replace: 'wikidir.php' accommodates pluses
		}
	}
}

?>
