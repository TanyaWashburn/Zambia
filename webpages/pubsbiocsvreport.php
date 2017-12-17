<?php
//	Copyright (c) 2011-2017 Peter Olszowka. All rights reserved. See copyright document for more details.
require_once('db_functions.php');
require_once('StaffCommonCode.php'); //reset connection to db and check if logged in
$query=<<<EOD
SELECT
        P.badgeid, CD.lastname, CD.firstname, CD.badgename, P.pubsname, P.bio
    FROM
             Participants P
        JOIN CongoDump CD USING (badgeid)
    WHERE
            EXISTS ( SELECT *
                FROM
                    UserHasPermissionRole UHPR
                WHERE
                        UHPR.badgeid = P.badgeid
                    AND UHPR.permroleid IN (3, 6) /* Program Participant, Event Organizer */
                )
EOD;
// order by: if lastname is part of pubsname, order by it, otherwise, order by last word/token in pubsname
if (!$result=mysql_query($query,$link)) {
	require_once('StaffHeader.php');
	require_once('StaffFooter.php');
	$title="Send CSV file of Biography Report for Publications";
	staff_header($title);
	$message=$query."<BR>Error querying database. Unable to continue.<BR>";
    echo "<P class\"errmsg\">".$message."\n";
    staff_footer();
    exit();
    }
if (mysql_num_rows($result)==0) {
	require_once('StaffHeader.php');
	require_once('StaffFooter.php');
	$title="Send CSV file of Biography Report for Publications";
	staff_header($title);
	$message="Report returned no records.";
    echo "<P>".$message."\n";
    staff_footer();
    exit(); 
	}
header('Content-disposition: attachment; filename=PubBio.csv');
header('Content-type: text/csv');
echo "badgeid,lastname,firstname,badgename,pubsname,bio\n";
while ($row= mysql_fetch_array($result, MYSQL_NUM)) {
	$betweenValues=false;
	foreach ($row as $value) {
		if ($betweenValues) echo ",";
		if (strpos($value,"\"")!==false) {
				$value=str_replace("\"","\"\"",$value);
				echo "\"$value\""; 
				}
			elseif (strpos($value,",")!==false or strpos($value,"\n")!==false) {
				echo "\"$value\"";
				}
			else {
				echo $value;
				}
		$betweenValues=true;
		}
	echo "\n";
	}
exit();
?>
