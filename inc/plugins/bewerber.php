<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

//Hooks
$plugins->add_hook('global_start', 'bewerber_header');

function bewerber_info()
{
    return array(
        "name"			=> "Bewerbercheckliste",
        "description"	=> "Hier können Bewerber sehen, ob sie alles erledigt haben.",
        "website"		=> "",
        "author"		=> "Alex",
        "authorsite"	=> "",
        "version"		=> "1.0",
        "guid" 			=> "",
        "codename"		=> "",
        "compatibility" => "*"
    );
}

function bewerber_install()
{

    //Settings
    global $db, $mybb;

    $setting_group = array(
        'name' => 'Bewerberchecklist',
        'title' => 'Bewerberchecklist',
        'description' => 'Hier finden sich alle Einstellungen für den Plugin.',
        'disporder' => 5, // The order your setting group will display
        'isdefault' => 0
    );



    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        // Kategorie Bewerberungen
        'bewerber_forumid' => array(
            'title' => 'Bewerberforum ID',
            'description' => 'Was ist die ID des Forums, worin die Bewerbungen landen:',
            'optionscode' => 'forumselectsingle',
            'value' => '23', // Default
            'disporder' => 1
        ),

        // Profilfelder
        'bewerber_profilfields' => array(
            'title' => 'Gebrauchte Profilfelder:',
            'description' => 'Gebe hier die Profilfelder an, die du braucht',
            'optionscode' => 'text',
            'value' => '2,3, 4',
            'disporder' => 2
        ),
        // Geburtstagsfeld
        'bewerber_birthday' => array(
            'title' => 'Wird das Geburtstagsfeld gebraucht?:',
            'description' => 'Wird die Geburtstagsfunktion genutzt?',
            'optionscode' => 'yesno',
            'value' => 1,
            'disporder' => 3
        ),
    );

    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

// Don't forget this!
    rebuild_settings();

//Templates
    $insert_array = array(
        'title' => 'bewerber_header',
        'template' => $db->escape_string('<br /><table width="500px" style="margin:auto">
	<tr><td class="thead" colspan="2"><h1>Bewerberchecklist</h1></td></tr>
<tr><td class="trow2"  width="10%" align="center">{$avatarstatus}</td><td class="trow1"><div class="checklist">Wurde ein <b>Avatar</b> hochgeladen?</div></td></tr>
<tr><td class="trow2"  width="10%" align="center">{$steckistatus}</td><td class="trow1"><div class="checklist">Wurde ein <b>fertiger Steckbrief</b> erstellt?</div></td></tr>
{$bewerberchecklist_bit}
	</table>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'bewerber_header_bit',
        'template' => $db->escape_string('<tr><td class="trow2"  width="10%" align="center">{$factsstatus}</td><td class="trow1"><div class="checklist">Wurde <b>{$pf_name}</b> ausgefüllt?</div></td></tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);
    $insert_array = array(
        'title' => 'bewerber_header_birthday',
        'template' => $db->escape_string('<tr><td class="trow2"  width="10%" align="center">{$birthdaystatus}</td><td class="trow1"><div class="checklist">Wurder der <b>Charaktergeburtstag</b> angegeben?</div></td></tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);
}

function bewerber_is_installed()
{

    global $mybb;
    if(isset($mybb->settings['bewerber_forumid']))
    {
        return true;
    }

    return false;

}

function bewerber_uninstall()
{

    global $db, $mybb;

    $db->delete_query('settings', "name IN ('bewerber_forumid','bewerber_profilfields', 'bewerber_birthday')");
    $db->delete_query('settinggroups', "name = 'Bewerberchecklist'");

    $db->delete_query("templates", "title IN ('bewerber_header', 'bewerber_header_bit', 'bewerber_header_birthday')");
// Don't forget this
    rebuild_settings();

}

function bewerber_activate()
{
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('<navigation>')."#i", '<navigation>{$bewerberchecklist}');
}

function bewerber_deactivate()
{
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('{$bewerberchecklist}')."#i", '', 0);
}

function bewerber_header(){
    global $db, $mybb, $templates, $avatarstatus, $bewerberchecklist,  $bewerberchecklist_birthday, $steckistatus,$factsstatus,$pf_name;

    $bforum = $mybb->settings['bewerber_forumid'];
    $profilfields = $mybb->settings['bewerber_profilfields'];
    $profilfields = explode(",", $profilfields);
    $user = $mybb->user['uid'];
    $birthday = $mybb->settings['bewerber_birthday'];
  

    if($mybb->user['usergroup'] == 2){

        //Ist ein Avatar hochgeladen
        if(!empty($mybb->user['avatar'])){
            $avatarstatus ="<div style='font-size: 30px'><i class=\"far fa-check-square\"></i></div>";
        } else{
            $avatarstatus = "<div style='font-size: 30px'><i class=\"far fa-square\"></i></div>";
        }


        //Ist ein Steckbrief vorhanden
        $select = $db->query("SELECT *
   FROM ".TABLE_PREFIX."forums f
   LEFT JOIN ".TABLE_PREFIX."threads t
   ON f.fid = t.fid
   WHERE f.parentlist LIKE '%,".$bforum."'
   AND t.uid = '".$user."'
   ");

        $bewerberf = $db->fetch_array($select);

        if(!empty($bewerberf)){
            $steckistatus ="<div style='font-size: 30px'><i class=\"far fa-check-square\"></i></div>";
        } else{
            $steckistatus = "<div style='font-size: 30px'><i class=\"far fa-square\"></i></div>";
        }

        foreach ($profilfields as $fid){

            $factsstatus = "";

            $pf_query =  $db->query("SELECT name
                FROM ".TABLE_PREFIX."profilefields pf
                WHERE fid = '".$fid."'
                ");


            $pf = $db->fetch_array($pf_query);
            $pf_name = $pf['name'];
            //Wurden die Shortfacts ausgefüllt
            $pfid = "fid".$fid;
            if (!empty($mybb->user[$pfid])) {

                $factsstatus = "<div style='font-size: 30px'><i class=\"far fa-check-square\"></i></div>";
            } else {
                $factsstatus = "<div style='font-size: 30px'><i class=\"far fa-square\"></i> </div>";
            }

            eval("\$bewerberchecklist_bit .= \"" . $templates->get("bewerber_header_bit") . "\";");

        }


        if($birthday == 1) {
            //Wurde die Geburtstagsfunktion ausgefüllt
            if (!empty($mybb->user['birthday'])) {
                $birthdaystatus = "<div style='font-size: 30px'><i class=\"far fa-check-square\"></i></div>";
            } else {
                $birthdaystatus = "<div style='font-size: 30px'><i class=\"far fa-square\"></i></div>";
            }
            eval("\$bewerberchecklist_birthday = \"" . $templates->get("bewerber_header_birthday") . "\";");
        }

        eval("\$bewerberchecklist = \"" . $templates->get("bewerber_header") . "\";");


    }

}