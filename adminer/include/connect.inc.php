<?php
function connect_error() {
	global $connection, $VERSION, $token, $error, $drivers;
	$databases = array();
	if (DB != "") {
		page_header(lang('Database') . ": " . h(DB), lang('Invalid database.'), true);
	} else {
		if ($_POST["db"] && !$error) {
			set_session("databases", null);
			queries_redirect(substr(ME, 0, -1), lang('Database has been dropped.'), drop_databases($_POST["db"]));
		}
		
		page_header(lang('Select database'), $error, false);
		echo "<p><a href='" . h(ME) . "database='>" . lang('Create new database') . "</a>\n";
		foreach (array(
			'privileges' => lang('Privileges'),
			'processlist' => lang('Process list'),
			'variables' => lang('Variables'),
			'status' => lang('Status'),
		) as $key => $val) {
			if (support($key)) {
				echo "<a href='" . h(ME) . "$key='>$val</a>\n";
			}
		}
		echo "<p>" . lang('%s version: %s through PHP extension %s', $drivers[DRIVER], "<b>$connection->server_info</b>", "<b>$connection->extension</b>") . "\n";
		echo "<p>" . lang('Logged as: %s', "<b>" . h(logged_user()) . "</b>") . "\n";
		$databases = get_databases();
		if ($databases) {
			$collations = collations();
			echo "<form action='' method='post'>\n";
			echo "<table cellspacing='0' onclick='tableClick(event);'>\n";
			echo "<thead><tr><td><input type='hidden' name='token' value='$token'>&nbsp;<th>" . lang('Database') . "<td>" . lang('Collation') . "<td>" . lang('Tables') . "</thead>\n";
			foreach ($databases as $db) {
				$root = h(ME) . "db=" . urlencode($db);
				echo "<tr" . odd() . "><td>" . checkbox("db[]", $db, in_array($db, (array) $_POST["db"]));
				echo "<th><a href='$root'>" . h($db) . "</a>";
				echo "<td><a href='$root&amp;database='>" . nbsp(db_collation($db, $collations)) . "</a>";
				echo "<td align='right'><a href='$root&amp;schema=' id='tables-" . h($db) . "'>?</a>";
				echo "\n";
			}
			echo "</table>\n";
			echo "<p><input type='submit' name='drop' value='" . lang('Drop') . "' onclick=\"return confirm('" . lang('Are you sure?') . " (' + formChecked(this, /db/) + ')');\">\n";
			echo "</form>\n";
		}
	}
	page_footer("db");
	echo "<script type='text/javascript'>\n";
	foreach (count_tables($databases) as $db => $val) {
		echo "setHtml('tables-" . addcslashes($db, "\\'/") . "', '$val');\n";
	}
	echo "</script>\n";
}

if (isset($_GET["status"])) {
	$_GET["variables"] = $_GET["status"];
}
if (!(DB != "" ? $connection->select_db(DB) : isset($_GET["sql"]) || isset($_GET["dump"]) || isset($_GET["database"]) || isset($_GET["processlist"]) || isset($_GET["privileges"]) || isset($_GET["user"]) || isset($_GET["variables"]))) {
	if (DB != "") {
		set_session("databases", null);
	}
	connect_error(); // separate function to catch SQLite error
	exit;
}
