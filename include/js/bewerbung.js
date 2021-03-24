
function checkNotEmpty(ids)
{
	var errors = [];

	for(var i in ids) {

		var input = $('#' + ids[i]);

		if(!$.trim(input.val())) {
			errors.push(ids[i]);
			input.closest('div.form-group').removeClass('has-success').addClass('has-error');
		} else {
			input.closest('div.form-group').removeClass('has-error').addClass('has-success');
		}
	}

	return errors;
}

function checkKontakt()
{
	var errors;

	errors = checkNotEmpty([
			'telefonnummer',
			'email',
			'strasse',
			'plz',
			'ort'
		]);

	if(errors.length) {
		return false;
	}

	return true;
}

function checkPerson()
{
	var errors;

	errors = checkNotEmpty([
		'nachname',
		'vorname',
		'staatsbuergerschaft'
	]);

	if ($("#gebdatum").val() !== '')
	{
		var patt1 = new RegExp("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})");
		if (!patt1.test($("#gebdatum").val()))
		{
			$('#gebdatum').closest('div.form-group').removeClass('has-success').addClass('has-error');
			errors.push('gebdatum');
		}
		else
		{
			$('#gebdatum').closest('div.form-group').removeClass('has-error').addClass('has-success');
		}
	}

	// Berechnung der Sozialversicherungsnummer wenn AT
	if ($("#staatsbuergerschaft").val() === 'A')
	{
		var soz_nr = $.trim($("#svnr").val());

		if (!/^\d{10}$/.test(soz_nr))
		{
			$('#svnr').closest('div.form-group').removeClass('has-success').addClass('has-error');
			errors.push('svnr');
		}

		var checksum = 0;

		checksum = (3 * soz_nr[0]) + (7 * soz_nr[1]) + (9 * soz_nr[2]) + (5 * soz_nr[4]) + (8 * soz_nr[5]) + (4 * soz_nr[6]) + (2 * soz_nr[7]) + (1 * soz_nr[8]) + (6 * soz_nr[9]);
		checksum = checksum % 11;

		if (checksum !== parseInt(soz_nr[3], 10))
		{
			$('#svnr').closest('div.form-group').removeClass('has-success').addClass('has-error');
			errors.push('svnr');
		}
		else
		{
			$('#svnr').closest('div.form-group').removeClass('has-error').addClass('has-success');
		}
	}

	if(errors.length) {
		return false;
	}

	return true;
}

function FensterOeffnen(adresse, width, height)
{
	width = (typeof width !== 'undefined') ?  width : 700;
	height = (typeof height !== 'undefined') ?  height : 400;
	MeinFenster = window.open(adresse, "Info", "width="+width+",height="+height+", resizable=yes, scrollbars=yes, titlebar=yes");
	MeinFenster.focus();
}

function toggleDiv(div)
{
	var colspan = document.getElementById('document_table').rows[0].cells.length;
	$('#nachgereicht_'+div).toggle();
	$('#leerSymbol_'+div).toggle();
	$('#row_'+div).find('td').toggle();
	$('#anmerkung_row_'+div).toggle();
	$('#anmerkung_row_'+div).attr('colspan',colspan);
}

function toggleNachreichdaten(id)
{
	$('#nachreichdaten_'+id).toggle();
	$('#panelbody_'+id).toggle();
}

$(function()
{
	if(activeTab) {
		$('#bewerber-navigation a[href="#' + activeTab + '"]').tab('show');
	}

	$('.btn-nav').on('click', function() {
		var tabname = $(this).attr('data-jump-tab');
		$('#bewerber-navigation a[href="#' + tabname + '"]').tab('show');
	});

	$('#bewerber-navigation a').on('click', function() {

		if($('div.navbar-header button:visible').length)
		{
			$(this).closest('.collapse').collapse('hide');
		}
	});

	$('.fileselect').on('change', function()
	{
		if ($(this).val() != '')
		{
			// Enable Upload-Button
			$(this).closest('form').find(':submit').prop('disabled', false);
			// Change Class of Upload-Button
			$(this).closest('form').find(':submit').removeClass('btn-default').addClass('btn-primary');

			// Set Value of Textfield to Filename of selected file
			var label = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
			$(this).closest('form').find('.selectedFile').val(label);
		}
	});

	// Croppie für Bildzuschnitt initialisieren
	/*$uploadCrop = $(".croppie-container").croppie(
	{
		enableExif: true,
		enforceBoundary: true,
		viewport: {
			width: 240,
			height: 320
		},
		boundary: {
			width: 400,
			height: 400
		}
	});*/

	$(".imageselect").on("change", function ()
	{
		readURL(this);

		/*var reader = new FileReader();

		reader.onload = function (e)
		{
			alert (e.target.result);
			checkImageProperties($(this), function(result)
			{
				if (result)
				{
					$(".croppie-container").addClass("ready");
					$uploadCrop.croppie("bind",
						{
							url: e.target.result
						}).then(function ()
					{
						console.log("jQuery bind complete");
					});
					$("#zuschnittLichtbildModal").modal();
				}
			});

		};*/

	});
});

function readURL(input)
{
	if (input.files && input.files[0])
	{
		var reader = new FileReader();
		reader.onload = function(e)
		{
			$('#zuschnittLichtbildModal').modal();
			$('#croppie-container').attr('src', e.target.result);
			//setTimeout($('.croppie-container').attr('src', e.target.result), 2000);
			var resize = new Croppie($('#croppie-container')[0],
			{
				enableExif: true,
				enforceBoundary: true,
				viewport: {
					width: 240,
					height: 320
				},
				boundary: {
					width: 400,
					height: 400
				}
			});
			$('#submitimage').on('click', function()
			{
				// Do something
				/*resize.result('base64').then(function(dataImg)
				{
					var data = [{ image: dataImg }, { name: 'myimgage.jpg' }];
					// use ajax to send data to php
					$('#result').attr('src', dataImg);
				})*/
			})
		}
		reader.readAsDataURL(input.files[0]);
	}
}

function deleteAkte(akte_id, dokument_kurzbz, maxDokumente)
{
	data = {
		akte_id: akte_id,
		dokument_kurzbz: dokument_kurzbz,
		deleteAkte: true
	};

	$.ajax({
		url: '../cis/bewerbung.php',
		data: data,
		type: 'POST',
		dataType: "json",
		success: function(data)
		{
			if(data.status!='ok')
			{
				$("#dokumente_message_div").attr("class","alert alert-danger");
				$("#dokumente_message_div").html(data["msg"]+"<button type=\"button\" class=\"close\" data-dismiss=\"alert\">x</button>");
			}
			else
			{
				// Entferne alle Listeneinträge vom gleichen Dokumenttyp
				$(".listItem_"+akte_id).remove();
				// Reload Dokumentenupload
				//location.reload(true);
				window.location.href = 'bewerbung.php?active=dokumente';
				// Zeige den Upload wieder an
				/*anzahlDokumente = $(".list_"+dokument_kurzbz+" li").length;
				if (anzahlDokumente < maxDokumente)
				{
					$(".dokumentUploadDiv_" + dokument_kurzbz).removeClass("hidden");
					$(".aktenliste_" + dokument_kurzbz).removeClass("col-sm-offset-3");
				}
				$("#dokumente_message_div").attr("class","alert alert-success");

				$("#dokumente_message_div").html(data["msg"]);
				$("#dokumente_message_div").html(data["msg"]).delay(2000).fadeOut();*/
			}
		},
		error: function(data)
		{
			$("#dokumente_message_div").attr("class","alert alert-danger");
			$("#dokumente_message_div").html(data["msg"]);
			$("#dokumente_message_div").html(data["msg"]+"<button type=\"button\" class=\"close\" data-dismiss=\"alert\">x</button>").delay(2000).fadeOut();
		}
	});
}

function checkImageProperties(input, callback)
{
	// get the file name, possibly with path (depends on browser)
	var filename = input.val();

	// Use a regular expression to trim everything before final dot
	var extension = filename.replace(/^.*\./, '');

	// Iff there is no dot anywhere in filename, we would have extension == filename,
	// so we account for this possibility now
	if (extension == filename)
	{
		extension = '';
	}
	else
	{
		// if there is an extension, we convert to lower case
		// (N.B. this conversion will not effect the value of the extension
		// on the file upload.)
		extension = extension.toLowerCase();
	}

	if (extension != 'jpeg' && extension != 'jpg')
	{
		alert("Das Bild muss von Typ .jpg sein.\n\nThe image has to be of type .jpg");
		return false;
	}

	// Check auf Bildgroeße
	var _URL = window.URL || window.webkitURL;
	var file = input[0].files && input[0].files[0];

	var image = new Image();
	image.src = _URL.createObjectURL(file);

	image.onload = function ()
	{
		var imgwidth = image.width;
		var imgheight = image.height;

		if(imgwidth <= 240 || imgheight <= 320)
		{
			alert("Das Bild muss mindestens die Auflösung 240x320 Pixel haben.\nBitte wählen Sie ein größeres Bild.\n\nThe minimum solution of the image has to be 240x320 pixels.\nPlease choose a larger image");
			callback(false);
		}
		else
		{
			callback(true);
		}
	};
};

/**
 * Prueft, ob es sich um ein gültiges Datum im korrekten Format (dd.mm.yyyy bzw. yyyy-mm-jj) handelt
 * @return true wenn korrekt und gültig, false wenn nicht korrekt und gültig (zum Beispiel 30.2.2020)
 */
function checkValidDate(datum)
{
	var output;
	datum.toString() =='Invalid Date' ? output = false : output = true;
	console.log(output);

	return output;

}

function checkFormat(datum)
{
	var regex1 = new RegExp("([0-9]{2}).([0-9]{2}).([0-9]{4})$");
	var regex2 = new RegExp("([0-9]{4})-([0-9]{2})-([0-9]{2})$");


	if (regex1.test(datum))
	{
		var day = datum.substr(0,2);
		var month = datum.substr(3,2);
		var year = datum.substr(6,4);
		var d = new Date (year + '-' + month + '-'+ day);

		return checkValidDate(d);
	
		
	}
	else if (regex2.test(datum))
	{
		return checkValidDate(new Date (datum));
	}
	else
	{
		return false;
	}
}
