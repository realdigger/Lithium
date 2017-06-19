<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2017 Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1 Beta 3
 */

/*	This template is, perhaps, the most important template in the theme. It
	contains the main template layer that displays the header and footer of
	the forum, namely with main_above and main_below. It also contains the
	menu sub template, which appropriately displays the menu; the init sub
	template, which is there to set the theme up; (init can be missing.) and
	the linktree sub template, which sorts out the link tree.

	The init sub template should load any data and set any hardcoded options.

	The main_above sub template is what is shown above the main content, and
	should contain anything that should be shown up there.

	The main_below sub template, conversely, is shown after the main content.
	It should probably contain the copyright statement and some other things.

	The linktree sub template should display the link tree, using the data
	in the $context['linktree'] variable.

	The menu sub template should display all the relevant buttons the user
	wants and or needs.

	For more information on the templating system, please see the site at:
	https://www.simplemachines.org/
*/

/**
 * Initialize the template... mainly little settings.
 */
function template_init()
{
	global $settings, $txt, $context, $user_info;

	/* $context, $options and $txt may be available for use, but may not be fully populated yet. */

	// The version this template/theme is for. This should probably be the version of SMF it was created for.
	$settings['theme_version'] = '2.1';

	// Use plain buttons - as opposed to text buttons?
	$settings['use_buttons'] = false;

	// Set the following variable to true if this theme requires the optional theme strings file to be loaded.
	$settings['require_theme_strings'] = true;

	// Set the following variable to true is this theme wants to display the avatar of the user that posted the last and the first post on the message index and recent pages.
	$settings['avatars_on_indexes'] = true;

	// Set the following variable to true is this theme wants to display the avatar of the user that posted the last post on the board index.
	$settings['avatars_on_boardIndex'] = true;

	// This defines the formatting for the page indexes used throughout the forum.
	$settings['page_index'] = array(
		'extra_before' => '',
		'previous_page' => '<span class="icon-reply"></span>',
		'current_page' => '<span class="current_page">%1$d</span> ',
		'page' => '<a class="navPages" href="{URL}">%2$s</a> ',
		'expand_pages' => '<span class="expand_pages" onclick="expandPages(this, {LINK}, {FIRST_PAGE}, {LAST_PAGE}, {PER_PAGE});"> ... </span>',
		'next_page' => '<span class="icon-forward"></span>',
		'extra_after' => '',
	);

	// Allow css/js files to be disable for this specific theme.
	// Add the identifier as an array key. IE array('smf_script'); Some external files might not add identifiers, on those cases SMF uses its filename as reference.
	if (!isset($settings['disable_files']))
		$settings['disable_files'] = array();

	$settings['f_personal_menu'] = array();
	$settings['f_mobile_menu'] = array('template_menu_mobile', 'template_menu_personal');
}

/**
 * The main sub template above the content.
 */
function template_html_above()
{
	global $context, $settings, $scripturl, $txt, $modSettings, $mbname;

	// Show right to left, the language code, and the character set for ease of translating.
	echo '<!DOCTYPE html>
	<html', $context['right_to_left'] ? ' dir="rtl"' : '', !empty($txt['lang_locale']) ? ' lang="' . str_replace("_", "-", substr($txt['lang_locale'], 0, strcspn($txt['lang_locale'], "."))) . '"' : '', '>
<head>
	<meta charset="', $context['character_set'], '">';

	// You don't need to manually load index.css, this will be set up for you. You can, of course, add
	// any other files you want, after template_css() has been run. Note that RTL will also be loaded for you.

	// The most efficient way of writing multi themes is to use a master index.css plus variant.css files.
	// If you've set them up properly (through $settings['theme_variants'], loadCSSFile will load the variant files for you.

	// load in any css from mods or themes so they can overwrite if wanted
	template_css();

	// load in any javascript files from mods and themes
	template_javascript();

	echo '
	<title>', $context['page_title_html_safe'], '</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">';

	// Content related meta tags, like description, keywords, Open Graph stuff, etc...
	foreach ($context['meta_tags'] as $meta_tag)
	{
		echo '
	<meta';

		foreach ($meta_tag as $meta_key => $meta_value)
			echo ' ', $meta_key, '="', $meta_value, '"';

		echo '>';
	}

	/* What is your Lollipop's color?
	Theme Authors you can change here to make sure your theme's main color got visible on tab */
	echo '
	<meta name="theme-color" content="#102b42">';

	// Please don't index these Mr Robot.
	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex">';

	// Present a canonical url for search engines to prevent duplicate content in their indices.
	if (!empty($context['canonical_url']))
		echo '
	<link rel="canonical" href="', $context['canonical_url'], '">';

	// Show all the relative links, such as help, search, contents, and the like.
	echo '
	<link rel="help" href="', $scripturl, '?action=help">
	<link rel="contents" href="', $scripturl, '">', ($context['allow_search'] ? '
	<link rel="search" href="' . $scripturl . '?action=search">' : '');

	// If RSS feeds are enabled, advertise the presence of one.
	if (!empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']))
		echo '
	<link rel="alternate feed" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['rss'], '" href="', $scripturl, '?action=.xml;type=rss2', !empty($context['current_board']) ? ';board=' . $context['current_board'] : '', '">
	<link rel="alternate feed" type="application/atom+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['atom'], '" href="', $scripturl, '?action=.xml;type=atom', !empty($context['current_board']) ? ';board=' . $context['current_board'] : '', '">';

	// If we're viewing a topic, these should be the previous and next topics, respectively.
	if (!empty($context['links']['next']))
	{
		echo '
	<link rel="next" href="', $context['links']['next'], '">';
	}

	if (!empty($context['links']['prev']))
	{
		echo '
	<link rel="prev" href="', $context['links']['prev'], '">';
	}

	// If we're in a board, or a topic for that matter, the index will be the board's index.
	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="', $scripturl, '?board=', $context['current_board'], '.0">';

	// Output any remaining HTML headers. (from mods, maybe?)
	echo $context['html_headers'];

	echo '
	<link href="https://fonts.googleapis.com/css?family=Questrial" rel="stylesheet">
</head>
<body id="', $context['browser_body_id'], '" class="action_', !empty($context['current_action']) ? $context['current_action'] : (!empty($context['current_board']) ?
		'messageindex' : (!empty($context['current_topic']) ? 'display' : 'home')), !empty($context['current_board']) ? ' board_' . $context['current_board'] : '', '">';
}

/**
 * The upper part of the main template layer. This is the stuff that shows above the main forum content.
 */
function template_body_above()
{
	global $context, $settings, $scripturl, $txt, $modSettings, $maintenance, $user_info;

	// If the user is logged in, display some things that might be useful.
	if ($context['user']['is_logged'])
	{
		$settings['f_personal_menu']['personal'] = array(
				'title' => $txt['welmsg_welcome'].' '. $context['user']['name'],
				'href' => 'action=profile',
				'body' => '
			<a href="' . $scripturl . '?action=profile;u=' . $context['user']['id'] . '"><img src="' . $context['user']['avatar']['href'] . '" alt="" class="avatar_80 floatleft" style="margin-right: 4rem;" /></a>
			<div class="floatleft">
				<a href="' . $scripturl . '?action=profile;area=showposts;u=' . $context['user']['id'] . '"><span class="icon-browser icon-size80"></span>&nbsp;' . $txt['posts'] . '</a> <br> 
				<a href="' . $scripturl . '?action=profile;area=forumprofile;u=' . $context['user']['id'] . '"><span class="icon-user icon-size80"></span>&nbsp;' . $txt['forumprofile'] . '</a> <br> 
				<a href="' . $scripturl . '?action=profile;area=notification;u=' . $context['user']['id'] . '"><span class="icon-bell icon-size80"></span>&nbsp;' . $txt['notification'] . '</a>  
				<br>
				<a href="' . $scripturl . '?action=profile;u=' . $context['user']['id'] . '"><span class="icon-clock3 icon-size80"></span>&nbsp;' . (timeformat($user_info['last_login'])) . '</a>
			</div>
			',
		);
		// Secondly, PMs if we're doing them
		if ($context['allow_pm'])
			$settings['f_personal_menu']['pm'] = array(
				'title' => $txt['pm_short'] . (!empty($context['user']['unread_messages']) ? ' <span class="amt">' . $context['user']['unread_messages'] . '</span>' : ''), 
				'href' => 'action=pm',
				'body' => '
			<a href="' . $scripturl . '?action=pm;u=' . $context['user']['id'] . '"><img src="' . $settings['images_url'] . '/f_pm.png" alt="" class="avatar_80 floatleft" style="margin-right: 4rem;" /></a>
			<div class="floatleft">
				<a href="' . $scripturl . '?action=pm;sa=send"><span class="icon-bubble icon-size80"></span>&nbsp;' . $txt['send_message'] . '</a> <br> 
				<a href="' . $scripturl . '?action=pm;sa=settings"><span class="icon-cog icon-size80"></span>&nbsp;' . $txt['settings'] . '</a>  
				<br>
				<a href="' . $scripturl . '?action=pm">' . $user_info['messages'] . ' '. $txt['personal_messages'] . ' , <b>' . sprintf($txt['msg_alert_many_new'], $user_info['unread_messages']) . '</b></a>
			</div>
		',
			);

		$settings['f_personal_menu']['alert'] = array(
				'title' => $txt['alerts'] . (!empty($context['user']['alerts']) ? ' <span class="amt">' . $context['user']['alerts'] . '</span>' : ''),
				'href' => 'action=profile;area=showalerts;u='. $context['user']['id'],
				'body' => '
			<a href="' . $scripturl . '?action=profile;area=showalerts"><img src="' . $settings['images_url'] . '/f_alert.png" alt="" class="avatar_80 floatleft" style="margin-right: 4rem;" /></a>
			<div class="floatleft">
				<a href="' . $scripturl . '?action=profile;area=showalerts"><span class="icon-tag icon-size80"></span>&nbsp;' . $txt['alerts'] . '</a><br>
				<a href="' . $scripturl . '?action=profile;area=notification;sa=alerts"><span class="icon-cog icon-size80"></span>&nbsp;' . $txt['settings'] . '</a>  
				<br>
			</div>
	',
			);
	}	

	echo '
<form id="search_form3" class="mob" action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
	<input type="search" name="search" id="search_form3_input" value="" class="input_text">
	<input type="hidden" name="advanced" value="0">
</form>

<div id="frame">
<div class="box_top mob" id="side_menu">
	' , template_show_menu() , '
</div>
<div id="side_main">
	<div id="fhead">
		<div id="fhead_menu" class="section_shadow">
			<div class="fwidth" style="position: relative;">';

	// If the user is logged in, display some things that might be useful.
	if ($context['user']['is_logged'])
	{
		foreach($settings['f_personal_menu'] as $p => $data)
			echo '
				<div id="' . $p . '_fpop" class="f_pop_body des" style="display: none;">
					<div class="f_pop_body_padding">' , $data['body'], '</div>
				</div>';
	}

	if ($context['allow_search'])
	{
		echo '
				<div id="qsearch_fpop" class="f_pop_body des" style="display: none;">
					<div class="f_pop_body_padding" >
						<form id="search_form" class="floatright" action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
							<input type="search" name="search" id="search1" value="" class="input_text">&nbsp;';

		// Using the quick search dropdown?
		$selected = !empty($context['current_topic']) ? 'current_topic' : (!empty($context['current_board']) ? 'current_board' : 'all');

		echo '
							<select name="search_selection">
								<option value="all"', ($selected == 'all' ? ' selected' : ''), '>', $txt['search_entireforum'], ' </option>';

		// Can't limit it to a specific topic if we are not in one
		if (!empty($context['current_topic']))
			echo '
								<option value="topic"', ($selected == 'current_topic' ? ' selected' : ''), '>', $txt['search_thistopic'], '</option>';

		// Can't limit it to a specific board if we are not in one
		if (!empty($context['current_board']))
			echo '
								<option value="board"', ($selected == 'current_board' ? ' selected' : ''), '>', $txt['search_thisbrd'], '</option>';

		// Can't search for members if we can't see the memberlist
		if (!empty($context['allow_memberlist']))
			echo '
								<option value="members"', ($selected == 'members' ? ' selected' : ''), '>', $txt['search_members'], ' </option>';

		echo '
							</select>';

		// Search within current topic?
		if (!empty($context['current_topic']))
			echo '
							<input type="hidden" name="sd_topic" value="', $context['current_topic'], '">';
		// If we're on a certain board, limit it to this board ;).
		elseif (!empty($context['current_board']))
			echo '
							<input type="hidden" name="sd_brd" value="', $context['current_board'], '">';

		echo '
							<input type="submit" name="search2" value="', $txt['search'], '" class="button_submit">
							<input type="hidden" name="advanced" value="0">
						</form>
					</div>
				</div>';	
	}
	
	echo '
				<div class="bwgrid" id="f_pop">
					<div class="bwcell11">
						<div class="floatright themepadding mob" id="f_hamburger_menu">
							<a href="#side_menu"><span class="icon-menu icon-size150"></span></a>&nbsp;&nbsp;
							<a href="#search_form3"><span class="icon-search icon-size150"></span></a>
						</div>
					';
					
	// If the user is logged in, display some things that might be useful.
	if ($context['user']['is_logged'])
	{
		echo '
						<div class="themepadding" id="f_pop_main">
							<a href="' , $scripturl , '?action=profile" id="personal_mob_poplink" class="mob">', $txt['welmsg_welcome'].' '. $context['user']['name'] , '</a>';

		foreach($settings['f_personal_menu'] as $p => $data)
			echo '		<a href="#'. $p . '_fpop" id="' . $p . '_poplink" class="des f_pop_link" onclick="fPop_slide(\'#'.$p.'_fpop\',\'#iconpop'.$p.'\'); return false;" style="position: relative;">', $data['title'], '&nbsp;<span id="iconpop'.$p.'" class="" style="position: absolute; top: 4px;"></span></a>';

		echo '
						</div>
';
	}
	// Otherwise they're a guest. Ask them to either register or login.
	else
		if (empty($maintenance))
			echo '
						<div class="themepadding" id="personal_menu">
							<span class="f_pop_link">', $txt['login_or_register'], '</span>
						</div>';
		else
			//In maintenance mode, only login is allowed and don't show OverlayDiv
			echo '
						<div class="bwgrid" id="personal_menu">
							<div class="bwcell16">', $txt['forum_in_maintenance'],'</div>
						</div>';
					
	echo '
					</div>
					<div class="bwcell5" id="qsearch_bg">
						<div class="bwfloatright">';

	if ($context['allow_search'])
	{
		echo '
							<form id="search_form2"  action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
								<input type="search" name="search" id="search_form2_input" value="" class="input_text">
								<a href="#qsearch_fpop" onclick="fPop_slide(\'#qsearch_fpop\'); fPop_copy(\'#search_form2_input\',\'#search1\'); return false;"><span class="icon-cog icon-size150 des"></span></a>
								<input type="hidden" name="advanced" value="0">
							</form>';
	}
	echo '
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="fhead_logo">
			<div class="fwidth">
				<div class="themepadding">
					<h1 id="forumtitle" class="floatleft">
						<a id="top" href="', $scripturl, '">', empty($context['header_logo_url_html_safe']) ? $context['forum_name_html_safe'] : '<img src="' . $context['header_logo_url_html_safe'] . '" alt="' . $context['forum_name_html_safe'] . '">', '</a>
					</h1>';

	if(!empty($settings['site_slogan']))
		echo '
					<div class="floatleft" id="site_slogan">' , $settings['site_slogan'] , '</div>';

	echo '<br class="clear">
				</div>
			</div>
		</div>
	</div>';
	
	if (!empty($settings['enable_news']) && !empty($context['random_news_line']))
		echo '
	<div id="fhead_news">
		<div class="fwidth">
			<div class="themepadding">', $context['random_news_line'], '</div>
		</div>
	</div>';

	echo '
	<div id="fhead_breadcrumb">
		<div class="fwidth">
			<div class="themepadding">
				' , theme_linktree() , '
			</div>
		</div>
	</div>
	<div id="fbody">
		<div class="fwidth">
			<div id="fcontent" class="bwgrid_less">
				<div class="bwcell12">
					<div class="themepadding bwoverflow" id="fcontent_main">';

}

function template_menu_personal()
{
	global $context, $settings, $scripturl;

	// If the user is logged in, display some things that might be useful.
	if ($context['user']['is_logged'])
	{
		echo '
		<ul>';

		foreach($settings['f_personal_menu'] as $p => $data)
			echo '		
			<li><a href="'. $scripturl . '?' . $data['href'] . '">', $data['title'], '</a></li>';

		echo '
		</ul>
';
	}
	// Otherwise they're a guest. Ask them to either register or login.
	else
		if (empty($maintenance))
			echo '
						<div class="bwgrid" id="personal_menu">
							<div class="bwcell16">', sprintf($txt[$context['can_register'] ? 'welcome_guest_register' : 'welcome_guest'], $txt['guest_title'], $context['forum_name_html_safe'], $scripturl . '?action=login', 'return reqOverlayDiv(this.href, ' . JavaScriptEscape($txt['login']) . ');', $scripturl . '?action=signup'), '</div>
						</div>';
		else
			//In maintenance mode, only login is allowed and don't show OverlayDiv
			echo '
						<div class="bwgrid" id="personal_menu">
							<div class="bwcell16">', sprintf($txt['welcome_guest'], $txt['guest_title'], '', $scripturl. '?action=login', 'return true;'), '</div>
						</div>';
}
/**
 * The stuff shown immediately below the main content, including the footer
 */
function template_body_below()
{
	global $context, $txt, $scripturl, $modSettings;

	echo '
					</div>
				</div>
				<div class="bwcell4">
					<div id="move_sidebar">
						<div class="box_top des" id="main_menu">
							' , template_menu() , '
						</div>';
	
	if(function_exists('template_put_me_aside'))
		template_put_me_aside();

	echo '
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="fbottom">
		<div class="fwidth"><div class="themepadding">
			<div class="floatleft">
				', theme_copyright(), '
			</div>
			<div class="bwfloatright">
				<a href="', $scripturl, '?action=help">', $txt['help'], '</a> ', (!empty($modSettings['requireAgreement'])) ? '| <a href="' . $scripturl . '?action=help;sa=rules">' . $txt['terms_and_rules'] . '</a>' : '', ' | <a href="#fhead"><span class="icon-up"></span>&nbsp;', $txt['go_up'], '</a>
			</div>
		';

	// Show the load time?
	if ($context['show_load_time'])
		echo '
			<div class="clear bwcentertext">', sprintf($txt['page_created_full'], $context['load_time'], $context['load_queries']),'</div>';

	echo '
		</div></div>
	</div>
</div>
</div>';
}

/**
 * This shows any deferred JavaScript and closes out the HTML
 */
function template_html_below()
{
	// load in any javascipt that could be deferred to the end of the page
	template_javascript(true);

	echo '
</body>
</html>';
}

/**
 * Show a linktree. This is that thing that shows "My Community | General Category | General Discussion"..
 *
 * @param bool $force_show Whether to force showing it even if settings say otherwise
 */
function theme_linktree($force_show = false)
{
	global $context, $shown_linktree, $scripturl, $txt;

	// If linktree is empty, just return - also allow an override.
	if (empty($context['linktree']) || (!empty($context['dont_default_linktree']) && !$force_show))
		return;

	echo '
	<ul class="breadcrumb">';

	foreach ($context['linktree'] as $link_num => $tree)
	{
		echo '
		<li>';

		// Show something before the link?
		if (isset($tree['extra_before']))
			echo $tree['extra_before'], ' ';

		// Show the link, including a URL if it should have one.
		if (isset($tree['url']))
			echo '
			<a href="' . $tree['url'] . '"><span>' . $tree['name'] . '</span></a>';
		else
			echo '
			<span>' . $tree['name'] . '</span>';

		// Show something after the link...?
		if (isset($tree['extra_after']))
			echo ' ', $tree['extra_after'];

		echo '
		</li>';
	}

	echo '
	</ul>';

	$shown_linktree = true;
}

function template_show_menu()
{
	global $context, $settings;

	foreach($settings['f_mobile_menu'] as $m)
		echo '
	<div class="menus_box">', call_user_func($m) , '</div>';
}
/**
 * Show the menu up top. Something like [home] [help] [profile] [logout]...
 */
function template_menu_mobile()
{
	global $context;

	echo '
	<a href="#frame" class="mob"><span id="hamburger_back" class="icon-arrow-left floatright themepadding icon-size150"></span></a>

	<ul>
		<li class="mob"><a href="#side_menu">', empty($context['header_logo_url_html_safe']) ? '<b>'.$context['forum_name_html_safe'].'</b>' : '<img src="' . $context['header_logo_url_html_safe'] . '" alt="' . $context['forum_name_html_safe'] . '" style="max-width: 100%;" />', '</a></li>';

	// Note: Menu markup has been cleaned up to remove unnecessary spans and classes.
	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
		<li class="button_', $act, '', !empty($button['sub_buttons']) ? ' subsections"' : '"', '>
			<a', $button['active_button'] ? ' class="active"' : '', ' href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', '>
				<span class="textmenu">', $button['title'], '</span>
			</a>', !empty($button['sub_buttons']) ? ' <span onclick="fPop_slide(\'#msub_'.$act.'\'); return false;" class="icon-plus-square icon-less icon-show-pointer floatright" style="margin: -2rem 2rem 0 0;"></span>' : '';

		if (!empty($button['sub_buttons']))
		{
			echo '
			<ul class="subsection less" id="msub_' . $act .'" style="display: none;">';

			foreach ($button['sub_buttons'] as $childbutton)
			{
				echo '
				<li>
					<a href="', $childbutton['href'], '"', isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', '>
						', $childbutton['title'], '
					</a>
				</li>';
			}
				echo '
			</ul>';
		}
		echo '
		</li>';
	}

	echo '
	</ul>';
}

function template_menu()
{
	global $context;

	echo '
	<ul>';

	// Note: Menu markup has been cleaned up to remove unnecessary spans and classes.
	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
		<li class="button_', $act, '', !empty($button['sub_buttons']) ? ' subsections"' : '"', '>
			<a', $button['active_button'] ? ' class="active"' : '', ' href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', '>
				<span class="textmenu">', $button['title'], '</span>
			</a>', !empty($button['sub_buttons']) ? ' <span onclick="fPop_slide(\'#subb_'.$act.'\'); return false;" class="icon-plus-square icon-less icon-show-pointer"></span>' : '';

		if (!empty($button['sub_buttons']))
		{
			echo '
			<ul class="subsection less" id="subb_' . $act .'" style="display: none;">';

			foreach ($button['sub_buttons'] as $childbutton)
			{
				echo '
				<li>
					<a href="', $childbutton['href'], '"', isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', '>
						', $childbutton['title'], '
					</a>
				</li>';
			}
				echo '
			</ul>';
		}
		echo '
		</li>';
	}

	echo '
	</ul>';
}
/**
 * Generate a strip of buttons.
 *
 * @param array $button_strip An array with info for displaying the strip
 * @param string $direction The direction
 * @param array $strip_options Options for the button strip
 */
function template_button_strip($button_strip, $direction = '', $strip_options = array())
{
	global $context, $txt;

	if (!is_array($strip_options))
		$strip_options = array();

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		// As of 2.1, the 'test' for each button happens while the array is being generated. The extra 'test' check here is deprecated but kept for backward compatibility (update your mods, folks!)
		if (!isset($value['test']) || !empty($context[$value['test']]))
		{
			if (!isset($value['id']))
				$value['id'] = $key;

			$button = '
				<a class="button button_strip_' . $key . (!empty($value['active']) ? ' active' : '') . (isset($value['class']) ? ' ' . $value['class'] : '') . '" ' . (!empty($value['url']) ? 'href="' . $value['url'] . '"' : '') . ' ' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '>' . $txt[$value['text']] . '</a>';

			if (!empty($value['sub_buttons']))
			{
				$button .= '
					<div class="top_menu dropmenu ' . $key . '_dropdown">
						<div class="viewport">
							<div class="overview">';
				foreach ($value['sub_buttons'] as $element)
				{
					if (isset($element['test']) && empty($context[$element['test']]))
						continue;

					$button .= '
								<a href="' . $element['url'] . '"><strong>' . $txt[$element['text']] . '</strong>';
					if (isset($txt[$element['text'] . '_desc']))
						$button .= '<br /><span>' . $txt[$element['text'] . '_desc'] . '</span>';
					$button .= '</a>';
				}
				$button .= '
							</div>
						</div>
					</div>';
			}

			$buttons[] = $button;
		}
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	echo '
		<div class="buttonlist', !empty($direction) ? ' float' . $direction : '', '"', (empty($buttons) ? ' style="display: none;"' : ''), (!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"' : ''), '>
			',implode('', $buttons), '
		</div>';
}

/**
 * The upper part of the maintenance warning box
 */
function template_maint_warning_above()
{
	global $txt, $context, $scripturl;

	echo '
	<div class="errorbox" id="errors">
		<dl>
			<dt>
				<strong id="error_serious">', $txt['forum_in_maintenance'], '</strong>
			</dt>
			<dd class="error" id="error_list">
				', sprintf($txt['maintenance_page'], $scripturl . '?action=admin;area=serversettings;' . $context['session_var'] . '=' . $context['session_id']), '
			</dd>
		</dl>
	</div>';
}

/**
 * The lower part of the maintenance warning box.
 */
function template_maint_warning_below()
{

}

?>