<?php

$safe_self = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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

/*
 * Base Theme Component, eClass Core
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id: baseTheme.php,v 1.72 2009-11-11 10:03:07 jexi Exp $
 *
 * @abstract This component is the core of eclass. Each and every file that
 * requires output to the user's browser must include this file and use
 * the draw method to output the UI to the user's browser.
 *
 * An exception of this scenario is when the user uses the personalised
 * interface. In that case function drawPerso needs to be called.
 *
 */
include ('init.php');
if ($is_adminOfCourse and isset($currentCourseID)) {
	if (isset($_GET['hide']) and $_GET['hide'] == 0) {
		db_query("UPDATE accueil SET visible = 0 WHERE id='$eclass_module_id'", $currentCourseID);
	} else if (isset($_GET['hide']) and $_GET['hide'] == 1) {
		db_query("UPDATE accueil SET visible = 1 WHERE id='$eclass_module_id'", $currentCourseID);
	}
}
//template path for logged out + logged in (ex., when session expires)
$extraMessage = ""; //initialise var for security
if (isset($errorMessagePath)) {
	$relPath = $errorMessagePath;
}

if (isset($toolContent_ErrorExists)) {
	$toolContent = $toolContent_ErrorExists;

	$_SESSION['errMessage'] = $toolContent_ErrorExists;
	session_write_close ();
	header("Location:" . $urlServer . "index.php");
	exit();
}

if (isset($_SESSION['errMessage']) && strlen($_SESSION['errMessage']) > 0) {
	$extraMessage = $_SESSION['errMessage'];
	unset($_SESSION['errMessage']);
}

include ($relPath . "template/template.inc.php");
include ('tools.php');

/**
 * Function draw
 *
 * This method processes all data to render the display. It is executed by
 * each tool. Is in charge of generating the interface and parse it to the user's browser.
 *
 * @param mixed $toolContent html code
 * @param int $menuTypeID
 * @param string $tool_css (optional) catalog name where a "tool.css" file exists
 * @param string $head_content (optional) code to be added to the HEAD of the UI
 * @param string $body_action (optional) code to be added to the BODY tag
 */
function draw($toolContent, $menuTypeID, $tool_css = null, $head_content = null, $body_action = null, $hideLeftNav = null, $perso_tool_content = null) {
	global $langUser, $langUserHeader, $prenom, $nom, $langLogout, $siteName, $intitule, $nameTools, $langHelp, $langAnonUser, $langActivate, $langDeactivate;
	global $language, $helpTopic, $require_help, $langEclass, $langCopyrightFooter;
	global $relPath, $urlServer, $urlAppend, $toolContent_ErrorExists, $statut;
	global $page_name, $page_navi, $currentCourseID, $langHomePage, $siteName, $navigation;
	global $homePage, $courseHome, $uid, $webDir, $extraMessage;
	global $langChangeLang, $langUserBriefcase, $langPersonalisedBriefcase, $langAdmin, $switchLangURL;
	global $langSearch, $langAdvancedSearch;
	global $langMyPersoLessons, $langMyPersoDeadlines;
	global $langMyPersoAnnouncements, $langMyPersoDocs, $langMyPersoAgenda, $langMyPersoForum;
	global $langExtrasLeft, $langExtrasRight;
	global $require_current_course, $is_adminOfCourse;

	//get blocks content from $toolContent array
	if ($perso_tool_content) {
		$lesson_content = $perso_tool_content ['lessons_content'];
		$assigns_content = $perso_tool_content ['assigns_content'];
		$announce_content = $perso_tool_content ['announce_content'];
		$docs_content = $perso_tool_content ['docs_content'];
		$agenda_content = $perso_tool_content ['agenda_content'];
		$forum_content = $perso_tool_content ['forum_content'];
	}

	$messageBox = "";

	//if an error exists (ex., sessions is lost...)
	//show the error message above the normal tool content


	if (strlen ( $extraMessage ) > 0) {
		$messageBox = "<table width=\"99%\">
		<tbody><tr><td class=\"extraMessage\">
		$extraMessage</td></tr>
		</tbody></table><br/>";
	}

	//get the left side menu from tools.php
	$toolArr = getSideMenu ( $menuTypeID );
	$numOfToolGroups = count ( $toolArr );

	$t = new Template ( $relPath . "template/classic" );

	$t->set_file ( 'fh', "theme.html" );

	$t->set_block ( 'fh', 'mainBlock', 'main' );

	//	BEGIN constructing of left navigation
	//	----------------------------------------------------------------------
	$t->set_block ( 'mainBlock', 'leftNavBlock', 'leftNav' );
	$t->set_block ( 'leftNavBlock', 'leftNavCategoryBlock', 'leftNavCategory' );
	$t->set_block ( 'leftNavCategoryBlock', 'leftNavCategoryTitleBlock', 'leftNavCategoryTitle' );

	$t->set_block ( 'leftNavCategoryBlock', 'leftNavLinkBlock', 'leftNavLink' );

	if (is_array ( $toolArr )) {

		for($i = 0; $i < $numOfToolGroups; $i ++) {

			if ($toolArr [$i] [0] ['type'] == 'none') {
				$t->set_var ( 'ACTIVE_TOOLS', '&nbsp;' );
				$t->set_var ( 'NAV_CSS_CAT_CLASS', 'spacer' );
				$t->parse ( 'leftNavCategoryTitle', 'leftNavCategoryTitleBlock', false );
			} elseif ($toolArr [$i] [0] ['type'] == 'split') {
				$t->set_var ( 'ACTIVE_TOOLS', '&nbsp;' );
				$t->set_var ( 'NAV_CSS_CAT_CLASS', 'split' );
				$t->parse ( 'leftNavCategoryTitle', 'leftNavCategoryTitleBlock', false );

			} elseif ($toolArr [$i] [0] ['type'] == 'text') {
				$t->set_var ( 'ACTIVE_TOOLS', $toolArr [$i] [0] ['text'] );
				$t->set_var ( 'NAV_CSS_CAT_CLASS', 'category' );
				$t->parse ( 'leftNavCategoryTitle', 'leftNavCategoryTitleBlock', false );
			}

			$numOfTools = count ( $toolArr [$i] [1] );
			for($j = 0; $j < $numOfTools; $j ++) {

				$t->set_var ( 'TOOL_LINK', $toolArr [$i] [2] [$j] );
				$t->set_var ( 'TOOL_TEXT', $toolArr [$i] [1] [$j] );

				$t->set_var ( 'IMG_FILE', $toolArr [$i] [3] [$j] );
				$t->parse ( 'leftNavLink', 'leftNavLinkBlock', true );

			}

			$t->parse ( 'leftNavCategory', 'leftNavCategoryBlock', true );
			$t->clear_var ( 'leftNavLink' ); //clear inner block
		}
		$t->parse ( 'leftNav', 'leftNavBlock', true );

		if (isset ( $hideLeftNav )) {
			$t->clear_var ( 'leftNav' );
			$t->set_var ( 'CONTENT_MAIN_CSS', 'content_main_no_nav' );
		} else {
			$t->set_var ( 'CONTENT_MAIN_CSS', 'content_main' );
		}

		$t->set_var ( 'URL_PATH', $urlAppend.'/' );
		$t->set_var ( 'SITE_NAME', $siteName );

		//If there is a message to display, show it (ex. Session timeout)
		if (strlen ( $messageBox ) > 1) {
			$t->set_var ( 'EXTRA_MSG', $messageBox );
		}

		$t->set_var ( 'TOOL_CONTENT', $toolContent );

		// If we are on the login page we can define two optional variables 
		// in common.inc.php (to allow internationalizing messages)
		// for extra content on the left and right bar.
		
		if ($homePage  && !isset($_SESSION['uid'])) {
			$t->set_var ( 'ECLASS_HOME_EXTRAS_LEFT', $langExtrasLeft );
			$t->set_var ( 'ECLASS_HOME_EXTRAS_RIGHT', $langExtrasRight );
		}

		//show user's name and surname on the user bar
		if (isset($_SESSION['uid']) && strlen ($nom) > 0) {
			$t->set_var ( 'LANG_USER', $langUserHeader );
			$t->set_var ( 'USER_NAME', $prenom );
			$t->set_var ( 'USER_SURNAME', $nom . ", " );
		} else {
                        $t->set_var ( 'LANG_USER', '' );
			$t->set_var ( 'USER_NAME', '&nbsp;' );
                }

		//if user is logged in display the logout option
		if (isset($_SESSION['uid'])) {
			$t->set_var ('LANG_LOGOUT', $langLogout);
		}

		//set the text and icon on the third bar (header)
		if ($menuTypeID == 2) {
			$t->set_var ( 'THIRD_BAR_TEXT', q(ellipsize($intitule, 64)) );
			$t->set_var ( 'THIRDBAR_LEFT_ICON', 'lesson_icon' );
		} elseif (isset ( $langUserBriefcase ) && $menuTypeID > 0 && $menuTypeID < 3 && !isset($_SESSION['user_perso_active'])) {
			$t->set_var ( 'THIRD_BAR_TEXT', $langUserBriefcase );
			$t->set_var ( 'THIRDBAR_LEFT_ICON', 'briefcase_icon' );
		} elseif (isset ( $langPersonalisedBriefcase ) && $menuTypeID > 0 && isset($_SESSION['user_perso_active'])) {
			$t->set_var ( 'THIRD_BAR_TEXT', $langPersonalisedBriefcase );
			$t->set_var ( 'THIRDBAR_LEFT_ICON', 'briefcase_icon' );
		} elseif ($menuTypeID == 3) {
			$t->set_var ( 'THIRD_BAR_TEXT', $langAdmin );
			$t->set_var ( 'THIRDBAR_LEFT_ICON', 'admin_bar_icon' );
		} else {
			$t->set_var ( 'THIRD_BAR_TEXT', $langEclass );
			$t->set_var ( 'THIRDBAR_LEFT_ICON', 'logo_icon' );
		}

		//set the appropriate search action for the searchBox form
		if ($menuTypeID == 2) {
			$searchAction = "search_incourse.php";
			$searchAdvancedURL = $searchAction;
		} elseif ($menuTypeID == 1 || $menuTypeID == 3) {
			$searchAction = "search.php";
			$searchAdvancedURL = $searchAction;
		} else { //$menuType == 0
			$searchAction = "search.php";
			$searchAdvancedURL = $searchAction;
		}
		$mod_activation = '';
		if ($is_adminOfCourse and isset($currentCourseID)) {
			// link for activating / deactivating module
			if(file_exists($module_ini_dir = getcwd() . "/module.ini.php")) {
				include $module_ini_dir;
				if (display_activation_link($module_id)) {
					if (visible_module($module_id)) {
						$message = $langDeactivate;
						$mod_activation = "<a class='deactivate_module' href=' " . $safe_self ." ?eclass_module_id=$module_id&amp;hide=0'>($langDeactivate)</a>";
					} else {
						$message = $langActivate;
						$mod_activation = "<a class='activate_module' href=' ". $safe_self ."?eclass_module_id=$module_id&amp;hide=1'>($langActivate)</a>";
					}
				}
			}
		}

		$t->set_var ( 'SEARCH_ACTION', $searchAction );
		$t->set_var ( 'SEARCH_ADVANCED_URL', $searchAdvancedURL );
		$t->set_var ( 'SEARCH_TITLE', $langSearch );
		$t->set_var ( 'SEARCH_ADVANCED', $langAdvancedSearch );

		$t->set_var ( 'TOOL_NAME', $nameTools );

		if ($is_adminOfCourse) {
			$t->set_var ( 'ACTIVATE_MODULE', $mod_activation );
		}

		$t->set_var ( 'LOGOUT_LINK', $relPath );

		if ($menuTypeID != 2) {
			$t->set_var ( 'LANG_SELECT', lang_selections () );
		} else {
			$t->set_var ( 'LANG_SELECT', '' );
		}

		//START breadcrumb AND page title


		if (! $page_navi)
			$page_navi = $navigation;
		if (! $page_name)
			$page_name = $nameTools;

		$t->set_block ( 'mainBlock', 'breadCrumbHomeBlock', 'breadCrumbHome' );

		if ($statut != 10) {
			if (!isset($_SESSION['uid']))
				$t->set_var ( 'BREAD_TEXT', $langHomePage );
			elseif (isset($_SESSION['uid']) && isset($_SESSION['user_perso_active'])) {
				$t->set_var ( 'BREAD_TEXT', $langPersonalisedBriefcase );
			} elseif (isset($_SESSION['uid']) && !isset($_SESSION['user_perso_active'])) {
				$t->set_var ( 'BREAD_TEXT', $langUserBriefcase );
			}

			if (! $homePage) {
				$t->set_var ( 'BREAD_HREF_FRONT', '<a href="{BREAD_START_LINK}">' );
				$t->set_var ( 'BREAD_START_LINK', $urlServer );
				$t->set_var ( 'BREAD_HREF_END', '</a>' );
			}

			$t->parse ( 'breadCrumbHome', 'breadCrumbHomeBlock', false );
		}

		$pageTitle = $siteName;

		$breadIterator = 1;
		$t->set_block ( 'mainBlock', 'breadCrumbStartBlock', 'breadCrumbStart' );

		if (isset ( $currentCourseID ) && ! $courseHome) {
			$t->set_var ( 'BREAD_HREF_FRONT', '<a href="{BREAD_LINK}">' );
			$t->set_var ( 'BREAD_LINK', $urlServer . 'courses/' . $currentCourseID . '/index.php' );
			$t->set_var ( 'BREAD_TEXT', q($intitule) );
			if ($statut == 10)
				$t->set_var ( 'BREAD_ARROW', '' );
			$t->set_var ( 'BREAD_HREF_END', '</a>' );
			$t->parse ( 'breadCrumbStart', 'breadCrumbStartBlock', true );
			$breadIterator ++;
			if (isset ( $pageTitle )) {
				$pageTitle .= " | " . $intitule;
			} else {
				$pageTitle = $intitule;
			}

		} elseif (isset ( $currentCourseID ) && $courseHome) {
			$t->set_var ( 'BREAD_HREF_FRONT', '' );
			$t->set_var ( 'BREAD_LINK', '' );
			$t->set_var ( 'BREAD_TEXT', $intitule );
			$t->set_var ( 'BREAD_ARROW', '&#187;' );
			$t->set_var ( 'BREAD_HREF_END', '' );
			$t->parse ( 'breadCrumbStart', 'breadCrumbStartBlock', true );
			$breadIterator ++;
			$pageTitle .= " | " . $intitule;

		}

		if (isset ( $page_navi ) && is_array ( $page_navi ) && ! $homePage) {
			foreach ( $page_navi as $step ) {

				$t->set_var ( 'BREAD_HREF_FRONT', '<a href="{BREAD_LINK}">' );
				$t->set_var ( 'BREAD_LINK', $step ["url"] );
				$t->set_var ( 'BREAD_TEXT', $step ["name"] );
				$t->set_var ( 'BREAD_ARROW', '&#187;' );
				$t->set_var ( 'BREAD_HREF_END', '</a>' );
				$t->parse ( 'breadCrumbStart', 'breadCrumbStartBlock', true );

				$breadIterator ++;

				$pageTitle .= " | " . $step ["name"];
			}
		}

		if (isset ( $page_name ) && ! $homePage) {

			$t->set_var ( 'BREAD_HREF_FRONT', '' );
			$t->set_var ( 'BREAD_TEXT', $page_name );
			$t->set_var ( 'BREAD_ARROW', '&#187;' );
			$t->set_var ( 'BREAD_HREF_END', '' );

			$t->parse ( 'breadCrumbStart', 'breadCrumbStartBlock', true );
			$breadIterator ++;
			$pageTitle .= " | " . $page_name;

		}

		$t->set_block ( 'mainBlock', 'breadCrumbEndBlock', 'breadCrumbEnd' );

		for($breadIterator2 = 0; $breadIterator2 < $breadIterator; $breadIterator2 ++) {

			$t->parse ( 'breadCrumbEnd', 'breadCrumbEndBlock', true );
		}

		//END breadcrumb --------------------------------


		$t->set_var ( 'PAGE_TITLE', q($pageTitle) );

		//Add the optional tool-specific css of the tool, if it's set
		if (isset ( $tool_css )) {
			$t->set_var ( 'TOOL_CSS', "<link href=\"{TOOL_PATH}modules/$tool_css/tool.css\" rel=\"stylesheet\" type=\"text/css\" />" );
		}

		$t->set_var ( 'TOOL_PATH', $relPath );

		if (isset ( $head_content )) {
			$t->set_var ( 'HEAD_EXTRAS', $head_content );
		}

		if (isset ( $body_action )) {
			$t->set_var ( 'BODY_ACTION', $body_action );
		}

		//if $require_help is true (set by each tool) display the help link
		if ($require_help == true) {
			if ((isset($require_current_course) and !$is_adminOfCourse) or
			    (!isset($require_current_course) and !check_prof())) {
				$helpTopic .= '_student';
			}
			$help_link_icon = " <a  href=\"" . $relPath . "modules/help/help.php?topic=$helpTopic&amp;language=$language\"
        onClick=\"window.open('" . $relPath . "modules/help/help.php?topic=$helpTopic&amp;language=$language','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10');
        return false;\"><img class='HelpIcon' src='" . $relPath . "template/classic/img/help_icon.gif' width='12' height='12' alt=\"$langHelp\"/></a>";

			$t->set_var ( 'HELP_LINK_ICON', $help_link_icon );
			$t->set_var ( 'LANG_HELP', $langHelp );
		} else {
			$t->set_var ( '{HELP_LINK}', '' );
			$t->set_var ( 'LANG_HELP', '' );
		}
		if (defined('RSS')) {
			$t->set_var ('RSS_LINK_ICON', "<span class='feed'><a href='${urlServer}" . RSS . "'><img src='${urlServer}template/classic/img/feed.png' alt='RSS Feed' title='RSS Feed'></a></span>");
		}

		if ($perso_tool_content) {
			$t->set_var ( 'LANG_MY_PERSO_LESSONS', $langMyPersoLessons );
			$t->set_var ( 'LANG_MY_PERSO_DEADLINES', $langMyPersoDeadlines );
			$t->set_var ( 'LANG_MY_PERSO_ANNOUNCEMENTS', $langMyPersoAnnouncements );
			$t->set_var ( 'LANG_MY_PERSO_DOCS', $langMyPersoDocs );
			$t->set_var ( 'LANG_MY_PERSO_AGENDA', $langMyPersoAgenda );
			$t->set_var ( 'LANG_PERSO_FORUM', $langMyPersoForum );

			$t->set_var ( 'LESSON_CONTENT', $lesson_content );
			$t->set_var ( 'ASSIGN_CONTENT', $assigns_content );
			$t->set_var ( 'ANNOUNCE_CONTENT', $announce_content );
			$t->set_var ( 'DOCS_CONTENT', $docs_content );
			$t->set_var ( 'AGENDA_CONTENT', $agenda_content );
			$t->set_var ( 'FORUM_CONTENT', $forum_content );
			$t->set_var ( 'URL_PATH', $urlAppend.'/' );
			$t->set_var ( 'TOOL_PATH', $relPath );
		}

		$t->set_var ( 'LANG_COPYRIGHT_NOTICE', $langCopyrightFooter );

		//	At this point all variables are set and we are ready to send the final output
		//	back to the browser
		$t->parse ( 'main', 'mainBlock', false );
		$t->pparse ( 'Output', 'fh' );
	}
}


/**
 * Function dumpArray
 *
 * Used for debugging purposes. Dumps array to browser
 * window.
 *
 * @param array $arr
 */
function dumpArray($arr) {
	echo "<pre>";
	print_r ( $arr );
	echo "</pre>";
}

/**
 * Function print_a
 *
 * Used for debugging purposes. Dumps array to browser
 * window. Better organisation of arrays than dumpArray
 *
 * @param array $arr
 */
function print_a($TheArray) {
	echo "<table border=1>n";

	$Keys = array_keys ( $TheArray );
	foreach ( $Keys as $OneKey ) {
		echo "<tr>n";
		echo "<td bgcolor='yellow'>";
		echo "<b>" . $OneKey . "</b>";
		echo "</td>n";
		echo "<td bgcolor='#C4C2A6'>";
		if (is_array ( $TheArray [$OneKey] ))
			print_a ( $TheArray [$OneKey] );
		else
			echo $TheArray [$OneKey];
		echo "</td>n";

		echo "</tr>n";
	}
	echo "</table>n";
}

/*
 * Function lang_selections
 *
 * Returns the HTML code for a language selection tool form
 *
 */
function lang_selections() {
	$html = '<form name="langform" action="' . $_SERVER ['PHP_SELF'] . '" method="get" >';
	$html .= lang_select_options('localize', 'onChange="document.langform.submit();"');
	$html .= '</form>';
	return $html;
}

/*
 * Function lang_select_option
 *
 * Returns the HTML code for the <select> element of the language selection tool
 *
 */
function lang_select_options($name, $onchange_js = '', $default_langcode = false) {
	global $language, $native_language_names;

        if ($default_langcode === false) {
                $default_langcode = langname_to_code($language);
        }
	return selection($native_language_names, $name, $default_langcode, $onchange_js);
}

