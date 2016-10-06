<?php

	##############################################################################
	# Calendar 3.1 Template Database libraries
	# J.Kenney 2016 - 20160915

	##############################################################################
	# Note: This library expects, at a minimum the following constants
	#       to be defined prior to loading the library, or included
	#       in the default/local config.inc.php as seen below
	#
	# define('DATABASE_DB_HOST', 'XXXX.academy.usna.edu');
  # define('DATABASE_USER', 'database username');
  # define('DATABASE_PW', 'users password');
  # define('DATABASE_DB_NAME', 'database/schema name');

	##############################################################################
	# Load in setting variables, if none provided
	if (!defined('DATABASE_DB_HOST')) {
	  require_once("config.inc.php");
	}

	##############################################################################
	# Connection information to connect to production server
	class myConnectDB extends mysqli{
		public function __construct($hostname=DATABASE_DB_HOST,
				$user=DATABASE_USER,
				$password=DATABASE_PW,
				$dbname=DATABASE_DB_NAME){
			parent::__construct($hostname, $user, $password, $dbname);
		}
	}

	##############################################################################
	# Only configure Develeopment Host if data provided!
	if (defined('DATABASE_DEV_HOSTNAME')) {
		# Connection information to connect to development server
		class myConnectDBDEV extends mysqli{
			public function __construct($hostname=DATABASE_DEV_DB_HOST,
					$user=DATABASE_DEV_USER,
					$password=DATABASE_DEV_PW,
					$dbname=DATABASE_DEV_DB_NAME){
				parent::__construct($hostname, $user, $password, $dbname);
			}
		}
	}

	##############################################################################
	# Connect to, and Create required database object (detect dev server by hostname)
	if (defined('DATABASE_DEV_HOSTNAME') && gethostname() == DATABASE_DEV_HOSTNAME) {
		$db = new myConnectDBDEV();		// Use Development Database
	} else {
		$db = new myConnectDB();			// Use Production Database
	}

	# Provide Failure data if unsuccessful
	if (mysqli_connect_errno()) {
		echo "<hr><h1><font color=red>Database Connection Failure:</font></h1>";
		echo "<pre>ERROR: " . mysqli_connect_errno() . "</pre><pre>" . mysqli_connect_error() . "</pre><hr>";
	}

	##############################################################################
	# Let there be UTF-8
	$db->query("set character_set_client='utf8'");
	$db->query("set character_set_results='utf8'");
	$db->query("set collation_connection='utf8_general_ci'");

	##############################################################################
	# Database Specific Functions

	##############################################################################
	# Dynamically Build a MySQL Prepared Query...
	# Returns an executed $stmt, that can then be stepped though or processed
	#
	# Example Usage:
	#  $query = "SELECT * FROM auth_user WHERE user=? AND last=?";
	#  $bind_fields = array("jpjones", "Jones");
	#  $stmt = build_query($db, $query, $bind_fields);
	# Example Usage:
	#  $stmt = build_query($db, "SELECT * FROM auth_user");
	function build_query($db, $query, $bind_fields=array(), $bind_types=array(), $reveal_prepare=true, $hide_errors=false) {

		# Mark if errors found.
		$errors_detected = false;

		# Determine the bind types of variables, and build the string
		$bind_string = '';
		foreach ($bind_fields as $bf) {
			if (isset($bind_types[$bf])) {
				$bind_string .= $bind_types[$bf];
			} else {
				$bind_string .= 's';
			}
		}

		# Build the parameter array
		$mysql_bind_string = array();
		$mysql_bind_string[] = & $bind_string;
		for ($i = 0; $i < count($bind_fields); $i++) {
			$mysql_bind_string[] = & $bind_fields[$i];
		}

		# Initialize a new DB query
		$stmt = $db->stmt_init();

		# Initial Error Checking
		if($stmt === false && !$hide_errors) {
			if (!$errors_detected) {
				$errors_detected = true;
				echo "<div class=container><div class=jumbotron><h1><font color=red>Database Error (Step 1):</font></h1>";
				echo "<p>The following query was provided to the <b>build_query()</b> function:</p>";
				echo "<pre><code class=sql>$query</code></pre>";
				if ($reveal_prepare) {
					echo "<p>This query would have been interpreted, via <b>PREPARE</b> procedures, as:</p>";
					$expand_query = $query;
					foreach ($bind_fields as $bf) {
						$expand_query = implode("<font color=red>{</font><font color=blue>".$bf."</font><font color=red>}</font>", explode("?", $expand_query, 2));
					}
					echo "<pre><code class=sql>$expand_query</code></pre>";
					echo "<h4>Location of DB Function Call:</h4><p>";
					array_walk( debug_backtrace(), create_function( '$a,$b', 'print "<b>". basename( $a[\'file\'] ). "</b> &nbsp; <font color=\"red\">{$a[\'line\']}</font> &nbsp; <font color=\"green\">{$a[\'function\']} ()</font> &nbsp; -- ". dirname( $a[\'file\'] ). "/";' ) );
				}
				echo "</p>";
				echo "<h4>Additional Error Data:</h4>";
				echo "<p>The following debugging data is provided by the SQL library functions:	</p>";
			}
			echo "<pre>Connect Error: " . $db->errno . " - " . $db->error . "</pre>";
		}

		# Build MySQL PREPARED statement
		$stmt->prepare($query);

		# Prepared Error Checking
		if ($db->errno && !$hide_errors) {
			if (!$errors_detected) {
				$errors_detected = true;
				echo "<div class=container><div class=jumbotron><h1><font color=red>Database Error (Step 2):</font></h1>";
				echo "<p>The following query was provided to the <b>build_query()</b> function:</p>";
				echo "<pre><code class=sql>$query</code></pre>";
				if ($reveal_prepare) {
					echo "<p>This query would have been interpreted, via <b>PREPARE</b> procedures, as:</p>";
					$expand_query = $query;
					foreach ($bind_fields as $bf) {
						$expand_query = implode("<font color=red>{</font><font color=blue>".$bf."</font><font color=red>}</font>", explode("?", $expand_query, 2));
					}
					echo "<pre><code class=sql>$expand_query</code></pre>";
					echo "<h4>Location of DB Function Call:</h4><p>";
					array_walk( debug_backtrace(), create_function( '$a,$b', 'print "<b>". basename( $a[\'file\'] ). "</b> &nbsp; <font color=\"red\">{$a[\'line\']}</font> &nbsp; <font color=\"green\">{$a[\'function\']} ()</font> &nbsp; -- ". dirname( $a[\'file\'] ). "/";' ) );
				}
				echo "</p>";
				echo "<h4>Additional Error Data:</h4>";
				echo "<p>The following debugging data is provided by the SQL library functions:	</p>";
			}
			echo "<pre>Error: " . $db->errno . " - " . $db->error . "</pre>";
		}

		# Bind the fields to the query
		#$stmt->bind_param($bind_string, $token);
		if ($bind_string != '') {
			$result = call_user_func_array(array($stmt, 'bind_param'), $mysql_bind_string);
			# Count the number of ? in the prepared query statement
			if (count($bind_fields) != substr_count($query, '?') && !$hide_errors) {
				if (!$errors_detected) {
					$errors_detected = true;
					echo "<div class=container><div class=jumbotron><h1><font color=red>Database Error (Step 3):</font></h1>";
					echo "<p>The following query was provided to the <b>build_query()</b> function:</p>";
					echo "<pre><code class=sql>$query</code></pre>";
					if ($reveal_prepare) {
						echo "<p>This query would have been interpreted, via <b>PREPARE</b> procedures, as:</p>";
						$expand_query = $query;
						foreach ($bind_fields as $bf) {
							$expand_query = implode("<font color=red>{</font><font color=blue>".$bf."</font><font color=red>}</font>", explode("?", $expand_query, 2));
						}
						echo "<pre><code class=sql>$expand_query</code></pre>";
						echo "<h4>Location of DB Function Call:</h4><p>";
						array_walk( debug_backtrace(), create_function( '$a,$b', 'print "<b>". basename( $a[\'file\'] ). "</b> &nbsp; <font color=\"red\">{$a[\'line\']}</font> &nbsp; <font color=\"green\">{$a[\'function\']} ()</font> &nbsp; -- ". dirname( $a[\'file\'] ). "/";' ) );
					}
					echo "</p>";
					echo "<h4>Additional Error Data:</h4>";
					echo "<p>The following debugging data is provided by the SQL library functions:	</p>";
				}
				echo "<pre>Expected Bind Error: Number of ? marks (".substr_count($query, '?').") != provided elements (".count($bind_fields).") </pre>";
			}
			# Check for binding errors
			if (!$result && !$hide_errors) {
				$last_error = error_get_last();
				if ($last_error) {
					if (!$errors_detected) {
						$errors_detected = true;
						echo "<div class=container><div class=jumbotron><h1><font color=red>Database Error (Step 4):</font></h1>";
						echo "<p>The following query was provided to the <b>build_query()</b> function:</p>";
						echo "<pre><code class=sql>$query</code></pre>";
						if ($reveal_prepare) {
							echo "<p>This query would have been interpreted, via <b>PREPARE</b> procedures, as:</p>";
							$expand_query = $query;
							foreach ($bind_fields as $bf) {
								$expand_query = implode("<font color=red>{</font><font color=blue>".$bf."</font><font color=red>}</font>", explode("?", $expand_query, 2));
							}
							echo "<pre><code class=sql>$expand_query</code></pre>";
							echo "<h4>Location of DB Function Call:</h4><p>";
							array_walk( debug_backtrace(), create_function( '$a,$b', 'print "<b>". basename( $a[\'file\'] ). "</b> &nbsp; <font color=\"red\">{$a[\'line\']}</font> &nbsp; <font color=\"green\">{$a[\'function\']} ()</font> &nbsp; -- ". dirname( $a[\'file\'] ). "/";' ) );
						}
						echo "</p>";
						echo "<h4>Additional Error Data:</h4>";
						echo "<p>The following debugging data is provided by the SQL library functions:	</p>";
					}
					echo "<pre>PHP Warning: ".PHP_EOL;
					echo "Line: " . $last_error['line'] . PHP_EOL;
					echo "Message: " . $last_error['message'] . PHP_EOL;
					echo "</pre>";
				}
			}
		}

		# Execute MySQL PREPARE statement
		$result = $stmt->execute();

		# Verify logon information
		#if (!$result || $db->affected_rows == 0) {
		if (!$result && !$hide_errors) {
			if (!$errors_detected) {
				$errors_detected = true;
				echo "<div class=container><div class=jumbotron><h1><font color=red>Database Error (Step 5):</font></h1>";
				echo "<p>The following query was provided to the <b>build_query()</b> function:</p>";
				echo "<pre><code class=sql>$query</code></pre>";
				if ($reveal_prepare) {
					echo "<p>This query would have been interpreted, via <b>PREPARE</b> procedures, as:</p>";
					$expand_query = $query;
					foreach ($bind_fields as $bf) {
						$expand_query = implode("<font color=red>{</font><font color=blue>".$bf."</font><font color=red>}</font>", explode("?", $expand_query, 2));
					}
					echo "<pre><code class=sql>$expand_query</code></pre>";
					echo "<h4>Location of DB Function Call:</h4><p>";
					array_walk( debug_backtrace(), create_function( '$a,$b', 'print "<b>". basename( $a[\'file\'] ). "</b> &nbsp; <font color=\"red\">{$a[\'line\']}</font> &nbsp; <font color=\"green\">{$a[\'function\']} ()</font> &nbsp; -- ". dirname( $a[\'file\'] ). "/";' ) );
				}
				echo "</p>";
				echo "<h4>Additional Error Data:</h4>";
				echo "<p>The following debugging data is provided by the SQL library functions:	</p>";
			}
			echo "<pre>". $db->error . "</pre>";
		}

		# Close out error reporting
		if ($errors_detected && !$hide_errors) {
			echo "<h4><font color=red>Please contact the database or web administrator to report this error.</font></h4>";
			echo "</div></div>";
		}

		# Return the SQL $stmt object.
		return $stmt;
	}

	# Shortcut function to hide prepared values in debugging
	function build_query_hide($db, $query, $bind_fields=array(), $bind_types=array(), $reveal_prepare=false) {
		return build_query($db, $query, $bind_fields, $bind_types, $reveal_prepare);
	}

	##############################################################################
	// Return an associative array of the MySQLi results, given a returned $stmt object
	//
	//  Results will be returned in the following format
	// 	Array
	// (
	//     [0] => Array
	//         (
	//             [user] => jpjones
	//             [displayname] => Professor John Paul Jones
	//             [first] => John Paul
	//             [last] => Jones
	//             [department] => Computer Science
	//             [session] => user|s:6:"kenney";
	//             [id] => fknib7t97m2gq6ehk4a1rpaki7
	//             [valid] => 1472867262
	//             [login] => 1472861282
	//         )
	//
	// )
	function stmt_to_assoc_array($stmt) {
		# Retrieve associated metadata
	  $meta = $stmt->result_metadata();

		# Determine column Names
	  while ($field = $meta->fetch_field()) {
	    $params[] = &$row[$field->name];
	  }

		# Build variables into which data will be placed
	  call_user_func_array(array($stmt, 'bind_result'), $params);

		# Retrieve a row of data
	  while ($stmt->fetch()) {
			# Retrieve a single column->value pair
	    foreach($row as $key => $val) {
	      $c[$key] = $val;
	    }
	    $results[] = $c;
	  }

		# If there were no results, then just return an empty array
	  if (!isset($results)) {
	    return array();
	  }

		# Return results
	  return $results;
	}

	##############################################################################
	# return a single row (from results assuming that there was only one row)
	function array_2d_to_1d($results) {
	  if (isset($results[0])) {
	    return $results[0];
	  } else {
	    return array();
	  }
	}

	##############################################################################
	# Take in a string like: "org, address1, address2, city, state, zip, formal,
	#                         signed_by, signed_data, held_by, agree_type, comments"
	# And convert it to a populated array of values (from $ARRAY) which have these as keys
	# And return a correct size string of ?'s for a PREPARED statement Insert or Update
	function build_array($ARRAY, $fields, $labeled=true) {
	  $bind_fields = array();
	  $bind_quests = '';
	  $empty = '';
	  $insert_quests = '';
		$counter = 0;
	  foreach (explode(",", $fields) as $field) {
	    $field = trim($field);
	    if ($bind_quests != '') {
	      $bind_quests .= ', ';
	      $insert_quests .= ', ';
	    }
	    $bind_quests .= '?';
	    $insert_quests .= "$field=?";
			if (!$labeled) {
				$bind_fields[] = $ARRAY[$counter];
	    } elseif (isset($ARRAY[$field])) {
	      $bind_fields[] = trim($ARRAY[$field]);
	    } else {
	      $bind_fields[] = $empty;
	    }
			$counter++;
	  }
	  return array($bind_fields, $bind_quests, $insert_quests);
	}

	##############################################################################
	# Print out an array of associative array
	#
	# Results will be in the following format:
	# ROW: 0 {<b>user</b> => jpjones, <b>displayname</b> => Professor John Paul Jones, <b>first</b> => John Paul, <b>last</b> => Jones, <b>department</b> => Computer Science, <b>session</b> => user|s:6:"kenney";, <b>id</b> => fknib7t97m2gq6ehk4a1rpaki7, <b>valid</b> => 1472867387, <b>login</b> => 1472861282, }<br>
	function print_array($results) {
		if (!empty($results)) {
		  foreach($results as $key => $row) {
		    echo "ROW: $key {";
		    #asort($row);
		    foreach($row as $key => $val) {
		      echo "<b>$key</b> => $val, ";
		    }
		    echo "}<br>";
		  }
		}
	}

	##############################################################################
	# HTML Table an array of associative arrays
	function html_array($results, $table_class="", $link="", $link_key="", $link_real_key="", $replace_with="") {
		if (!empty($results)) {
			if ($table_class != "") {
				$table_class = 'class="'.$table_class.'"';
			}
		  echo PHP_EOL."<table $table_class>".PHP_EOL;
			echo "  <thead>".PHP_EOL;
			echo "    <tr>".PHP_EOL;
		  $header_count = count($results[0]);
		  foreach($results[0] as $key => $val) {
		    echo "      <th>$key</th>".PHP_EOL;
		  }
		  echo "    </tr>".PHP_EOL;
			echo "  </thead>".PHP_EOL;
			echo "  <tbody>".PHP_EOL;
		  foreach($results as $key => $row) {
		  	echo "    <tr>".PHP_EOL;
		    foreach($row as $key => $val) {
					$prev_val = $val;
					if ($link_key != "" && $key == $link_key && $link_real_key == "") {
						if ($replace_with != "") {
							$val = $replace_with;
						}
						echo "      <td><a href='$link?$link_key=$prev_val'>$val</a></td>".PHP_EOL;
					} elseif ($link_real_key != "" && $key == $link_key) {
						if ($replace_with != "") {
							$val = $replace_with;
						}
						echo "      <td><a href='$link?$link_real_key=$prev_val'>$val</a></td>".PHP_EOL;
					} else {
						echo "      <td>$val</td>".PHP_EOL;
					}
		    }
		  	echo "    </tr>".PHP_EOL;
		  }
		  echo "  </tbody>".PHP_EOL;
			echo "</table>".PHP_EOL;
		}
	}

	##############################################################################
	# show_x functions to assist with filling in web pages and forms.

	# Function to remove the tags from string so that they can be printed
	# correctly in textboxes
	function html_remove_tags($results) {
		$results = implode("&lt;", explode("<", $results));
		$results = implode("&gt;", explode(">", $results));
		return $results;
	}

	# Provide the item from an array if it exists, otherwise
	# provide the default value.
	function show_value($array, $key, $default='') {
	  if (isset($array[$key])) {
	    echo html_remove_tags($array[$key]);
	  } else {
	    echo html_remove_tags($default);
	  }
	}

	function show_checked($array, $key, $value, $default=false) {
	  if (isset($array[$key]) && $array[$key] == $value) {
	    echo " checked ";
	  } elseif ($default) {
	    echo " checked ";
	  }
	}

	function show_selected($array, $key, $value, $default=false) {
	  if (isset($array[$key]) && $array[$key] == $value) {
	    echo " selected ";
	  } elseif ($default) {
	    echo " selected ";
	  }
	}

?>
