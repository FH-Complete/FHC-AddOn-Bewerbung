<?php
/*
 * Copyright (C) 2015 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Authors: Robert Hofer <robert.hofer@technikum-wien.at>
 * 			Manfred Kindl <manfred.kindl@technikum-wien.at>
 */

if(!isset($person_id))
{
	die($p->t('bewerbung/ungueltigerZugriff'));
}
/*echo '<style>
#massagesIframe #page-wrapper
{
	margin: auto; !important
	
}

</style>';*/
echo '<div role="tabpanel" class="tab-pane" id="messages">
	<h2>'.$p->t('bewerbung/menuMessages').'</h2>';

echo '<p>'.$p->t('bewerbung/erklaerungMessages').'</p>';

/*if($save_error_zugangscode===false)
{
	echo '	<div class="alert alert-success" id="success-alert_abschicken">
				<button type="button" class="close" data-dismiss="alert">x</button>
					<strong>'.$message.'</strong>
				</div>';
}
elseif($save_error_zugangscode===true)
{
	echo '	<div class="alert alert-danger" id="danger-alert">
			<button type="button" class="close" data-dismiss="alert">x</button>
				<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
			</div>';
}*/

echo '<!-- 4:3 aspect ratio -->

		<div id="responsiveDiv" class="embed-responsive" style="padding-bottom: 100%; border: 1px solid lightgrey">
		  <iframe   id="massagesIframe"
		            class="embed-responsive-item"
		            style="padding-bottom: 50%; border: 1px solid lightgrey"
		            src="../../../index.ci.php/system/messages/MessageClient/read" 
		            <!--onload="changeIframeSize()"-->
		            ></iframe>
		</div>';

//https://c3p0.kindlm.technikum-wien.at/fhcomplete/index.ci.php/system/messages/MessageClient/write


/*echo '	<form method="POST" action="'.$_SERVER['PHP_SELF'].'?active=sicherheit">
		<button class="btn btn-primary" type="submit" name="btn_new_accesscode">
			'.$p->t('bewerbung/buttonNeuerZugangscode').'
		</button>
	</form><br>';

*/

?>
<script type="text/javascript">
$('#massagesIframe').load(function()
{
	$('#responsiveDiv').css('height','70%');
	//$('#responsiveDiv').height(this.contentWindow.document.body.offsetHeight + 'px');


	//this.style.height =
	//this.contentWindow.document.body.offsetHeight + 'px';
});

$(document).ready(function () {
    
    // color message frame white
    $('#massagesIframe').css('background-color', 'white');
});

/*function changeIframeSize()
{
	$('#massagesIframe').height =

	/*var iFrameID = document.getElementById('massagesIframe');
	var responsiveDiv = document.getElementById('responsiveDiv');
	if(iFrameID)
	{
		// here you can make the height, I delete it first, then I make it again
		iFrameID.height = "";
		alert(iFrameID.contentWindow.document.body.scrollHeight);
		iFrameID.height = iFrameID.contentWindow.document.body.scrollHeight + "px";
		responsiveDiv.height = iFrameID.contentWindow.document.body.scrollHeight + "px";
	}
}*/
</script>