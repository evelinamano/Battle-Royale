<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

$require_login = TRUE;
include '../../include/baseTheme.php';

$nameTools = $langUnregCours;

$local_style = 'h3 { font-size: 10pt;} li { font-size: 10pt;} ';

$tool_content = "";

if (isset($_GET['cid']))
  $_SESSION['cid_tmp']=$cid;
if(!isset($_GET['cid']))
  $cid=$_SESSION['cid_tmp'];

if (!isset($doit) or $doit != "yes") {

         $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
         $unregToken = $_SESSION['token'];

  $tool_content .= "
    <table width='40%'>
    <tbody>
    <tr>
      <td class='caution_NoBorder' height='60' colspan='2'>
      	<p>$langConfirmUnregCours:</p><p> <em>".course_code_to_title($cid)."</em>&nbsp;? </p>
	<ul class='listBullet'>
	<li>$langYes: 
	<a href='$_SERVER[PHP_SELF]?u=$uid&amp;cid=$cid&amp;doit=yes' class=mainpage>$langUnregCours</a>
	</li>
	<li>$langNo: <a href='../../index.php' class=mainpage>$langBack</a>
	</li></ul>
      </td>
    </tr>
    </tbody>
    </table>";

} else {

        /*if (empty($_GET['token']) || $_SESSION['token'] !== $_GET['token'] ) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
                exit();
        }
        unset($_SESSION['token']);

        if (isset($uid) and $uid==$_SESSION['uid']) {
        $mysqli = new mysqli($GLOBALS['mysqlServer'], $GLOBALS['mysqlUser'], $GLOBALS['mysqlPassword'], $mysqlMainDb);
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }
        if (!$mysqli->set_charset("utf8")) {
            printf("Error loading character set utf8: %s\n", $mysqli->error);
            exit();
        }
        $stmt = $mysqli->prepare("DELETE from cours_user WHERE cours_id = (SELECT cours_id FROM cours WHERE code = ?) AND user_id= ? ");
        $stmt->bind_param("si", $cid,$uid);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
                $tool_content .= "<p class='success_small'>$langCoursDelSuccess</p>";
        } else {
                $tool_content .= "<p class='caution_small'>$langCoursError</p>";
        }
        $stmt->close();
     }
    $tool_content .= "<br><br><div align=right><a href='../../index.php' class=mainpage>$langBack</a></div>";
}*/

if (isset($uid) and $uid==$_SESSION['uid']) {
            db_query("DELETE from cours_user WHERE cours_id = (SELECT cours_id FROM cours WHERE code = " . quote($cid) . ") AND user_id='$uid'");
                if (mysql_affected_rows() > 0) {
                        $tool_content .= "<p class='success_small'>$langCoursDelSuccess</p>";
                } else {
                        $tool_content .= "<p class='caution_small'>$langCoursError</p>";
                }
         }
        $tool_content .= "<br><br><div align=right><a href='../../index.php' class=mainpage>$langBack</a></div>";
}



if (isset($_SESSION['uid'])) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}
?>
