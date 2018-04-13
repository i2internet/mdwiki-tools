<?php

// ------------------
// ---  Wiki GIT  ---
// ------------------

/* -- MDwiki-tools v0.7-0 --
	wikigit.php Created by the people at i2 Internet, www.i2internet.com
	See https://github.com/i2internet/mdwiki-tools for notes & latest source code.
	(C) 2018 by i2 Internet Incorporated and contributors.
	This software is licensed under the terms of the GNU GPLv3.
	Originally created for use with MDwiki, see www.mdwiki.info */

// --- Session ---

session_start();

if (isset($_GET['name'])) {
	$_SESSION['name'] = $_GET['name'];
	setcookie('name', $_GET['name']);
}
elseif (isset($_COOKIE['name']))
	$_SESSION['name'] = $_COOKIE['name'];

if (isset($_GET['email'])) {
	$_SESSION['email'] = $_GET['email'];
	setcookie('email', $_GET['email']);
}
elseif (isset($_COOKIE['email']))
	$_SESSION['email'] = $_COOKIE['email'];

if (isset($_GET['desc'])) {
	$_SESSION['desc'] = $_GET['desc'];
	setcookie('desc', $_GET['desc']);
}
elseif (isset($_COOKIE['desc']))
	$_SESSION['desc'] = $_COOKIE['desc'];


// --- Heading ---

echo '#Git'.chr(10);


// ---------------------------
// --- git_output Function ---
// ---------------------------

$div = false;

function git_output($s) {

	global $what, $div;
	$what = '';

	$a = explode(chr(10), $s);

	foreach($a as $s) {

		// -- what ---
		// determine section, ex: 'commit', 'add'
		if ($s == '# Changes not staged for commit:'
			|| $s == '# Changes to be committed:'
		) {	$what = 'commit'; }
		elseif ($s == '# Untracked files:')
			$what = 'add';
		elseif ($s == 'nothing to commit, working directory clean')
			$what = 'clean';


		// -- commit all tracked files --
		if ($what == 'commit'
			&& ($s == '#   (use "git add <file>..." to update what will be committed)'
				|| $s == '# Changes to be committed:'
			)
		) {	echo ' &nbsp;&nbsp;<a href="/#!/wikigit.php?action=commitall">Commit All Tracked Files</a>'.chr(10); }

		// -- add all untracked files --
		elseif ($what == 'add' && $s == '#   (use "git add <file>..." to include in what will be committed)')
			echo ' &nbsp;&nbsp;<a href="/#!/wikigit.php?action=addall">Add All Untracked Files</a>'.chr(10);

		// -- clean, git pull --
		//elseif ($what == 'clean' && $s == 'nothing to commit, working directory clean')
		//	echo ' &nbsp;&nbsp;<a href="/#!/wikigit.php?action=gitpull">Do a Git Pull</a>'.chr(10);


		// -- strip leading '#' ---
		if (isset($s[0]) && $s[0] == '#')
			$s = substr($s, 1);

		$s = htmlentities($s);

		// -- indents --
		// replace with non-breaking spaces, unless tab
		if (isset($s[0])) {
			if ($s[0] != chr(9))
				$s = str_replace('  ', '&nbsp;&nbsp;', $s);
		}

		echo $s.chr(10);
	}
	if (count($a) > 0 && $s != '') {
		echo chr(10).'---'.chr(10);
		$div = true;
	}
}


// --------------
// --- Action ---
// --------------

if (isset($_GET['action'])) {

	echo '**'.$_GET['action'].'**'.chr(10);


	// --- Commit All ---

	if ($_GET['action'] == 'commitall') {

		// -- action links --
		if (!isset($_GET['doit'])) {
			if (isset($_SESSION['name']) && isset($_SESSION['email']) && isset($_SESSION['desc'])) {
				echo '
<form>
	Name <input type="text" name="name" value="'.$_SESSION['name'].'">
	Email <input type="text" name="email" value="'.$_SESSION['email'].'">
	Description <input type="text" name="desc" value="'.$_SESSION['desc'].'" size="120">
	<input type="submit" value="Step 1 - Update Values" onclick="location.href=\'/#!/wikigit.php?action='.$_GET['action'].'&name=\'+document.getElementsByName(\'name\')[0].value+\'&email=\'+document.getElementsByName(\'email\')[0].value+\'&desc=\'+document.getElementsByName(\'desc\')[0].value">
	<input type="submit" value="Step 2 - Commit All" onclick="location.href=\'/#!/wikigit.php?action='.$_GET['action'].'&doit\'">
</form>
';
			}
			elseif (!isset($_SESSION['name']) || !isset($_SESSION['email']) || !isset($_SESSION['desc'])) {
				echo '
<form>
	Name <input type="text" name="name" value="">
	Email <input type="text" name="email" value="">
	Description <input type="text" name="desc" value="" size="120">
	<input type="submit" value="Submit Values" onclick="location.href=\'/#!/wikigit.php?action='.$_GET['action'].'&name=\'+document.getElementsByName(\'name\')[0].value+\'&email=\'+document.getElementsByName(\'email\')[0].value+\'&desc=\'+document.getElementsByName(\'desc\')[0].value">
</form>
';
			}
		}

		// -- do commit all --
		else {  // isset($_GET['doit'])
			if (isset($_SESSION['name']) && isset($_SESSION['email']) && isset($_SESSION['desc'])) {

				// -- commit all --
				$s = shell_exec('git commit -am "'.$_SESSION['desc'].'"').chr(10);

				// -- author --
				// currently not showing output:
				shell_exec('git commit --amend --author "'.$_SESSION['name'].' <'.$_SESSION['email'].'>" --no-edit');

				// -- push --
				$s .= '---'.chr(10).shell_exec('git push -f');

				git_output($s);
			}
		}
	}


	// --- Add All ---

	elseif ($_GET['action'] == 'addall') {

		$s = shell_exec('git add -A');

		git_output($s);
	}

	// --- Git Pull ---

	elseif ($_GET['action'] == 'gitpull') {

		$s = shell_exec('git pull');

		git_output($s);
	}
}


// --------------
// --- Status ---
// --------------

$s = shell_exec('git status');

git_output($s);


// ----------------
// --- Git Pull ---
// ----------------

if (!$div)
	echo chr(10).'---'.chr(10);

echo '<a href="/#!/wikigit.php?action=gitpull&t='.time().'">Do a Git Pull</a>'.chr(10);  // time included so that the link can be repeatedly clicked to load the page

?>
