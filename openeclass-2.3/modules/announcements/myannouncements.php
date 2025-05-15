<?php
$require_login = TRUE;
$ignore_module_ini = true;

include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';
include('../../include/phpmathpublisher/mathpublisher.php');

$nameTools = $langMyAnnouncements;
$tool_content = "";

// Query announcements
$result = db_query("SELECT annonces.id, annonces.title, annonces.contenu,
                        DATE_FORMAT(temps, '%e-%c-%Y') AS temps,
                        cours.fake_code,
                        annonces.ordre
                    FROM annonces, cours_user, cours
                    WHERE annonces.cours_id = cours_user.cours_id AND
                          cours_user.cours_id = cours.cours_id AND
                          cours_user.user_id = $uid
                    ORDER BY annonces.temps DESC", $mysqlMainDb);

// Heading table
$tool_content .= "
    <table width=\"99%\" class='FormData'>
    <thead>
    <tr>
        <th class=\"left\" width=\"220\">$langTitle</th>
        <td>&nbsp;</td>
    </tr>
    </thead>
    </table>";

$tool_content .= "<table width=\"99%\" align='left' class=\"announcements\"><tbody>";

// Found announcements?
if (mysql_num_rows($result) > 0) {
    while ($myrow = mysql_fetch_array($result)) {
        $title = htmlspecialchars($myrow['title'], ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars($myrow['temps'], ENT_QUOTES, 'UTF-8');

        // Get course info
        $row = mysql_fetch_array(db_query("SELECT intitule, titulaires FROM cours WHERE code='" . mysql_real_escape_string($myrow['fake_code']) . "'"));
        $course_title = htmlspecialchars($row['intitule'], ENT_QUOTES, 'UTF-8');
        $tutor_name = htmlspecialchars($row['titulaires'], ENT_QUOTES, 'UTF-8');

        // Process and sanitize announcement content
        $content = $myrow['contenu'];
        $content = make_clickable($content);
        $content = nl2br($content);
        $content = mathfilter($content, 12, "../../include/phpmathpublisher/img/");
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        $tool_content .= "
        <tr>
            <td width='3'>
                <img class=\"displayed\" src=\"../../template/classic/img/announcements_on.gif\" border=\"0\" title=\"$title\">
            </td>
            <td>
                " . htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8') . ": <b>$course_title</b><br />$content
            </td>
            <td align='right' width='300'>
                <small><i>($langAnn: $date)</i></small><br /><br />
                $langTutor: <b>$tutor_name</b>
            </td>
        </tr>";
    }

    $tool_content .= "</tbody></table>";
} else {
    $tool_content .= "<tr><td class='alert1'>" . htmlspecialchars($langNoAnnounce, ENT_QUOTES, 'UTF-8') . "</td></tr></tbody></table>";
}

draw($tool_content, 1, 'announcements');
?>
