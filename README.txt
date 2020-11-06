/***
* WICHTIG! Fontawesome MUSS vorhanden sein
*
***/

// bewerber_header
<br /><table width="500px" style="margin:auto">
	<tr><td class="thead" colspan="2"><h1>Bewerberchecklist</h1></td></tr>
<tr><td class="trow2"  width="10%" align="center">{$avatarstatus}</td><td class="trow1"><div class="checklist">Wurde ein Avatar hochgeladen?</div></td></tr>
<tr><td class="trow2"  width="10%" align="center">{$steckistatus}</td><td class="trow1"><div class="checklist">Wurde ein Steckbrief erstellt?</div></td></tr>
	{$bewerberchecklist_birthday}
{$bewerberchecklist_bit}
	</table>
  
  // bewerber_header_birthday
  <tr><td class="trow2"  width="10%" align="center">{$birthdaystatus}</td><td class="trow1"><div class="checklist">Wurder der <b>Charaktergeburtstag</b> angegeben?</div></td></tr>
  
  // bewerber_header_bit
  <tr><td class="trow2"  width="10%" align="center">{$factsstatus}</td><td class="trow1"><div class="checklist">Wurde <b>{$pf_name}</b> ausgefüllt?</div></td></tr>
