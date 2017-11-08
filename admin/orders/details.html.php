<!-- This is the HTML form used for EDITING Order information-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Order Details</title>
		<style>
			label {
				width: 220px;
			}
			.checkboxlabel{
				display: inline-block;
				float: left;
				clear: none;
				width: auto;
			}
		</style>
		<script>
			var alternativeID = 0;
			var alternativesAdded = 0;
			var newAlternativesCreated = 0;
			var itemsRemovedFromOrder = 0;
			var addAlternativeExtra = false;
			var createNewAlternativeExtra = false;
			var availableExtrasArray = <?php echo json_encode($availableExtra); ?>;
			var extrasOrdered = <?php echo json_encode($extraOrdered); ?>;

			function addTableRow(){
				// Get table we want to manipulate
				var table = document.getElementById("addAlternative");
				var tableRows = table.rows.length;
				var rowNumber = tableRows - 2;

				// Add new row at the "end" i.e. between the old extra and the button
				var row = table.insertRow(rowNumber);

				// Insert td element for "Name" column
				var columnName = row.insertCell(0);
				var columnDescription = row.insertCell(1);
				var columnDescriptionID = "addAlternativeDescriptionSelected" + alternativeID;
				columnDescription.setAttribute("id", columnDescriptionID);
				var columnPrice = row.insertCell(2);
				var columnPriceID = "addAlternativePriceSelected" + alternativeID;
				columnPrice.setAttribute("id", columnPriceID);
				var columnAmount = row.insertCell(3);
				var columnConfirmAndRemoveButton = row.insertCell(4);

				// Create the remove alternative extra (remove table row) button
				var removeAlternativeExtraButton = document.createElement("input");
				removeAlternativeExtraButton.setAttribute("type", "button");
				removeAlternativeExtraButton.innerHTML = "✖";
				removeAlternativeExtraButton.value = "✖";
				removeAlternativeExtraButton.style.color = "red";
				var removeAlternativeExtraButtonIDNumber = alternativeID;

				// Create the confirm chosen extra button
				var confirmAddedExtraButton = document.createElement("input");
				var confirmAddedExtraButtonName = "confirmButton-" + alternativeID;
				confirmAddedExtraButton.setAttribute("id", confirmAddedExtraButtonName)
				confirmAddedExtraButton.setAttribute("type", "button");
				confirmAddedExtraButton.innerHTML = "✔";
				confirmAddedExtraButton.value = "✔";
				confirmAddedExtraButton.style.color = "green";
				var confirmAddedExtraButtonIDNumber = alternativeID;

				// Create the input number for amount
				var inputExtraAmount = document.createElement("input");
				var inputExtraAmountAttributeName = "AmountSelected-" + alternativeID;
				inputExtraAmount.setAttribute("type", "number");
				inputExtraAmount.setAttribute("id", inputExtraAmountAttributeName);
				inputExtraAmount.setAttribute("name", inputExtraAmountAttributeName);
				inputExtraAmount.setAttribute("value", "1");
				inputExtraAmount.setAttribute("min", "0");
				inputExtraAmount.style.width = "45px";
				inputExtraAmount.onchange = function onChangeAmount(){changeAmountNewAlternative(this);}

				// Create the hidden input for accepted extra
				var inputExtraAccepted = document.createElement("input");
				var inputExtraAcceptedAttributeName = "extraIDAccepted" + alternativeID;
				inputExtraAccepted.setAttribute("type", "hidden");
				inputExtraAccepted.setAttribute("id", inputExtraAcceptedAttributeName);
				inputExtraAccepted.setAttribute("name", inputExtraAcceptedAttributeName);
				inputExtraAccepted.setAttribute("value", "");

				if(addAlternativeExtra){
					removeAlternativeExtraButton.onclick = function onClick(){removeAddedExtra(this, removeAlternativeExtraButtonIDNumber);}
					confirmAddedExtraButton.onclick = function onClick(){confirmAddedExtra(this, confirmAddedExtraButtonIDNumber);}
					
					// Get available extras
					var availableExtrasNumber = <?php echo $availableExtrasNumber; ?>;

					if(alternativesAdded == availableExtrasNumber){
						// cancel the function, since we have nothing else to add
						table.deleteRow(rowNumber);
						return;
					}

					// Create the select box we want to be able to choose from
					var selectExtraName = document.createElement("select");
					var selectExtraNameID = "addAlternativeSelected-" + alternativeID;
					selectExtraName.setAttribute("id", selectExtraNameID);
					selectExtraName.setAttribute("name", selectExtraNameID);
					selectExtraName.onchange = function onChange(){changeAlternativeText(this);}

					// Add the available extra names as options
					// exclude already confirmed items
					var firstIndexInSelectBox = 0;
					var firstIndexAdded = false;
					var acceptedExtraIDArray = [];

					// Get the extraIDs that have already been accepted
					for(var j = 0; j < alternativeID; j++){
						var extraIDAcceptedID = "extraIDAccepted" + j;
						var extraIDAccepted = document.getElementById(extraIDAcceptedID);

						if(extraIDAccepted !== null){
							acceptedExtraIDArray.push(extraIDAccepted.value);
						}
					}

					// Exclude already accepted extras from being displayed in select box
					for(var i = 0; i < availableExtrasArray.length; i++){
						var extraAlreadyAdded = false;

						for(var j = 0; j < acceptedExtraIDArray.length; j++){
							if(availableExtrasArray[i]['ExtraID'] == acceptedExtraIDArray[j]){
								extraAlreadyAdded = true;
							}
						}

						if(extraAlreadyAdded === false){
							var option = document.createElement("option");
							option.value = availableExtrasArray[i]['ExtraID'];
							option.text = availableExtrasArray[i]['ExtraName'];
							selectExtraName.appendChild(option);

							// Make sure we have the appropriate description and price for the extra name
							if(firstIndexAdded === false){
								firstIndexInSelectBox = i;
								firstIndexAdded = true;
							}
						}
					}

					alternativesAdded += 1;

					if(alternativesAdded == availableExtrasNumber){
						// disable the add alternative button
						var addAlternativeExtraButton = document.getElementById("addAlternativeExtraButton");
						addAlternativeExtraButton.style.display = 'none';
					}

					// Add items/values to columns
					columnName.appendChild(selectExtraName);
					columnName.appendChild(inputExtraAccepted);
					columnDescription.innerHTML = availableExtrasArray[firstIndexInSelectBox]['ExtraDescription'];
					columnPrice.innerHTML = availableExtrasArray[firstIndexInSelectBox]['ExtraPrice'];

					// update the input to check how many alternatives we have submitted
					var inputAlternativesAdded = document.getElementById("AlternativesAdded");
					inputAlternativesAdded.value = alternativesAdded;
				} else if(createNewAlternativeExtra){
					removeAlternativeExtraButton.onclick = function onClick(){removeNewAlternativeExtra(this);}
					confirmAddedExtraButton.onclick = function onClick(){confirmNewAlternativeExtra(this, confirmAddedExtraButtonIDNumber);}

					// add an input for name, description and price for the alternative choice
					var inputExtraName = document.createElement("input");
					var inputExtraNameAttributeName = "AlternativeName" + alternativeID;
					inputExtraName.setAttribute("type", "text");
					inputExtraName.setAttribute("name", inputExtraNameAttributeName);
					inputExtraName.setAttribute("id", inputExtraNameAttributeName);
					inputExtraName.setAttribute("placeholder", "Enter A Name");

					var inputExtraPrice = document.createElement("input");
					var inputExtraPriceAttributeName = "AlternativePrice" + alternativeID;
					inputExtraPrice.setAttribute("type", "number");
					inputExtraPrice.setAttribute("value", "0");
					inputExtraPrice.setAttribute("min", "0");
					inputExtraPrice.setAttribute("name", inputExtraPriceAttributeName);
					inputExtraPrice.setAttribute("id", inputExtraPriceAttributeName);
					inputExtraPrice.style.width = "60px";
					inputExtraPrice.onchange = function onChangePrice(){changePriceNewAlternative(this);}

					var inputExtraPriceConfirmed = document.createElement("input");
					var inputExtraPriceConfirmedAttributeName = "AlternativePriceConfirmed" + alternativeID;
					inputExtraPriceConfirmed.setAttribute("type", "hidden");
					inputExtraPriceConfirmed.setAttribute("value", "0");
					inputExtraPriceConfirmed.setAttribute("min", "0");
					inputExtraPriceConfirmed.setAttribute("id", inputExtraPriceConfirmedAttributeName);

					var inputExtraDescription = document.createElement("textarea");
					var inputExtraDescriptionAttributeName = "AlternativeDescription" + alternativeID;
					inputExtraDescription.setAttribute("name", inputExtraDescriptionAttributeName);
					inputExtraDescription.setAttribute("id", inputExtraDescriptionAttributeName);
					inputExtraDescription.setAttribute("placeholder", "Enter A Description");

					newAlternativesCreated += 1;

					// Add items/values to columns
					columnName.appendChild(inputExtraName);
					columnDescription.appendChild(inputExtraDescription);
					columnPrice.appendChild(inputExtraPrice);
					columnPrice.appendChild(inputExtraPriceConfirmed);

					// update the input to check how many alternatives we have submitted
					var inputNewAlternativesCreated = document.getElementById("NewAlternativesCreated");
					inputNewAlternativesCreated.value = newAlternativesCreated;
				}

				columnAmount.appendChild(inputExtraAmount);
				columnConfirmAndRemoveButton.appendChild(confirmAddedExtraButton);
				columnConfirmAndRemoveButton.appendChild(removeAlternativeExtraButton);

				// update the input to keep track of the last ID value on the submitted alternative
				var inputLastAlternativeIDValue = document.getElementById("LastAlternativeID");
				inputLastAlternativeIDValue.value = alternativeID;

				alternativeID += 1;

				// Make sure we don't trigger multiple buttons (e.g. remove alternative)
				disableEventPropagation(event);
			}

			function addAlternativeExtraRow(){
				addAlternativeExtra = true;
				addTableRow();
				addAlternativeExtra = false;
			}

			function createNewAlternativeExtraRow(){
				createNewAlternativeExtra = true;
				addTableRow();
				createNewAlternativeExtra = false;
			}

			function changeAlternativeText(selectBox){
				var selectBoxID = selectBox.id;
				var splitID = selectBoxID.split("-");
				var attributeID = splitID[1];
				var descriptionTextID = "addAlternativeDescriptionSelected" + attributeID;
				var	descriptionText = document.getElementById(descriptionTextID);
				var priceTextID = "addAlternativePriceSelected" + attributeID;
				var priceText = document.getElementById(priceTextID);
				var amountValueID = "AmountSelected-" + attributeID;
				var amountValue = document.getElementById(amountValueID);

				// get the extra ID for reference
				var extraIDSelected = selectBox.options[selectBox.selectedIndex].value;

				// Add the available extra names as options
				for(var i = 0; i < availableExtrasArray.length; i++){
					if(extraIDSelected == availableExtrasArray[i]['ExtraID']){
						descriptionText.innerHTML = availableExtrasArray[i]['ExtraDescription'];
						priceText.innerHTML = availableExtrasArray[i]['ExtraPrice'];
						amountValue.value = 1;
						break;
					}
				}
			}

			function changeTotalPrice(){
				var totalPrice = 0;

				var displayTotalPrice = document.getElementById("DisplayTotalPricePlacement");
				var saveTotalPrice = document.getElementById("SaveTotalPrice");

				for(var i = 0; i < extrasOrdered.length; i++){
					var extraID = extrasOrdered[i]['ExtraID'];
					var extraAmountSelected = document.getElementById("extraAmountSelected-" + extraID);
					if(extraAmountSelected !== null){
						var amountSelected = extraAmountSelected.value;
						var pricePerAmount = extrasOrdered[i]['ExtraPrice']
						var finalPrice = amountSelected * pricePerAmount;
						totalPrice += finalPrice;
					}
				}

				// Add price from added items
				if(alternativesAdded > 0){
					for(var i = 0; i < alternativeID; i++){
						var acceptedExtraID = "extraIDAccepted" + i;
						var acceptedExtra = document.getElementById(acceptedExtraID);
						if(acceptedExtra !== null && acceptedExtra.value != ""){
							var amountValueID = "AmountSelected-" + i;
							var amountValue = document.getElementById(amountValueID);
							var priceTextID = "addAlternativePriceSelected" + i;
							var priceText = document.getElementById(priceTextID);

							var amountSelected = amountValue.value;
							var pricePerAmount = priceText.innerHTML;
							var finalPrice = pricePerAmount * amountSelected;

							totalPrice += finalPrice;
						}
					}
				}

				// Add price from created items
				if(newAlternativesCreated > 0){
					for(var i = 0; i < alternativeID; i++){
						var alternativePriceConfirmedID = "AlternativePriceConfirmed" + i;
						var alternativePriceConfirmed = document.getElementById(alternativePriceConfirmedID);
						if(alternativePriceConfirmed !== null && alternativePriceConfirmed.value != ""){
							var amountValueID = "AmountSelected-" + i;
							var amountValue = document.getElementById(amountValueID);

							var amountSelected = amountValue.value;
							var pricePerAmount = alternativePriceConfirmed.value;
							var finalPrice = pricePerAmount * amountSelected;

							totalPrice += finalPrice;
						}
					}
				}

				displayTotalPrice.innerHTML = "<span>Total Price: " + totalPrice + "</span>";
				saveTotalPrice.value = totalPrice;
			}

			function confirmNewAlternativeExtra(confirmButton){
				var confirmButtonID = confirmButton.id;
				var splitID = confirmButtonID.split("-");
				var attributeID = splitID[1];
				var inputNameID = "AlternativeName" + attributeID;
				var inputName = document.getElementById(inputNameID);
				var inputDescriptionID = "AlternativeDescription" + attributeID;
				var inputDescription = document.getElementById(inputDescriptionID);
				var inputPriceID = "AlternativePrice" + attributeID;
				var inputPrice = document.getElementById(inputPriceID);
				var inputPriceConfirmedID = "AlternativePriceConfirmed" + attributeID;
				var inputPriceConfirmed = document.getElementById(inputPriceConfirmedID);
				var inputAmountID = "AmountSelected-" + attributeID;
				var inputAmount = document.getElementById(inputAmountID);

				// Validate name
				var inputNameText = inputName.value;
				if(inputNameText == ""){
					// Name not filled out
					inputName.setAttribute("class", "fillOut");
				} else if(inputNameText.match(/^[A-Za-z\u00AA\u00B5\u00BA\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u037F\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u052F\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0-\u08B4\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0980\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0AF9\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C39\u0C3D\u0C58-\u0C5A\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D5F-\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F5\u13F8-\u13FD\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16F1-\u16F8\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191E\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19B0-\u19C9\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2183\u2184\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005\u3006\u3031-\u3035\u303B\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FD5\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA69D\uA6A0-\uA6E5\uA717-\uA71F\uA722-\uA788\uA78B-\uA7AD\uA7B0-\uA7B7\uA7F7-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA8FD\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uA9E0-\uA9E4\uA9E6-\uA9EF\uA9FA-\uA9FE\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA7E-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uAB30-\uAB5A\uAB5C-\uAB65\uAB70-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC '-]*$/) === null){
					// Did not match the entire string i.e. Found illegal characters
					inputName.setAttribute("class", "fillOut");
				} else if(inputNameText.length > 255){
					// Name too long
					inputName.setAttribute("class", "fillOut");;
				} else if(extraNameExists(inputNameText, attributeID)){
					// Name already exists
					inputName.setAttribute("class", "fillOut");
				} else {
					// All good
					inputName.removeAttribute("class", "fillOut");
				}

				// Validate description
				var inputDescriptionText = inputDescription.value;
				if(inputDescriptionText == ""){
					// Description not filled out
					inputDescription.setAttribute("class", "fillOut");
				} else if(inputDescriptionText.match(/^[+=\sA-Za-z\u00AA\u00B5\u00BA\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u037F\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u052F\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0-\u08B4\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0980\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0AF9\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C39\u0C3D\u0C58-\u0C5A\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D5F-\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F5\u13F8-\u13FD\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16F1-\u16F8\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191E\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19B0-\u19C9\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2183\u2184\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005\u3006\u3031-\u3035\u303B\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FD5\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA69D\uA6A0-\uA6E5\uA717-\uA71F\uA722-\uA788\uA78B-\uA7AD\uA7B0-\uA7B7\uA7F7-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA8FD\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uA9E0-\uA9E4\uA9E6-\uA9EF\uA9FA-\uA9FE\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA7E-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uAB30-\uAB5A\uAB5C-\uAB65\uAB70-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC0-9\u0660-\u0669\u06F0-\u06F9\u07C0-\u07C9\u0966-\u096F\u09E6-\u09EF\u0A66-\u0A6F\u0AE6-\u0AEF\u0B66-\u0B6F\u0BE6-\u0BEF\u0C66-\u0C6F\u0CE6-\u0CEF\u0D66-\u0D6F\u0DE6-\u0DEF\u0E50-\u0E59\u0ED0-\u0ED9\u0F20-\u0F29\u1040-\u1049\u1090-\u1099\u17E0-\u17E9\u1810-\u1819\u1946-\u194F\u19D0-\u19D9\u1A80-\u1A89\u1A90-\u1A99\u1B50-\u1B59\u1BB0-\u1BB9\u1C40-\u1C49\u1C50-\u1C59\uA620-\uA629\uA8D0-\uA8D9\uA900-\uA909\uA9D0-\uA9D9\uA9F0-\uA9F9\uAA50-\uAA59\uABF0-\uABF9\uFF10-\uFF19!-#%-*,-\/:;?@\[-\]_\{\}\u00A1\u00A7\u00AB\u00B6\u00B7\u00BB\u00BF\u037E\u0387\u055A-\u055F\u0589\u058A\u05BE\u05C0\u05C3\u05C6\u05F3\u05F4\u0609\u060A\u060C\u060D\u061B\u061E\u061F\u066A-\u066D\u06D4\u0700-\u070D\u07F7-\u07F9\u0830-\u083E\u085E\u0964\u0965\u0970\u0AF0\u0DF4\u0E4F\u0E5A\u0E5B\u0F04-\u0F12\u0F14\u0F3A-\u0F3D\u0F85\u0FD0-\u0FD4\u0FD9\u0FDA\u104A-\u104F\u10FB\u1360-\u1368\u1400\u166D\u166E\u169B\u169C\u16EB-\u16ED\u1735\u1736\u17D4-\u17D6\u17D8-\u17DA\u1800-\u180A\u1944\u1945\u1A1E\u1A1F\u1AA0-\u1AA6\u1AA8-\u1AAD\u1B5A-\u1B60\u1BFC-\u1BFF\u1C3B-\u1C3F\u1C7E\u1C7F\u1CC0-\u1CC7\u1CD3\u2010-\u2027\u2030-\u2043\u2045-\u2051\u2053-\u205E\u207D\u207E\u208D\u208E\u2308-\u230B\u2329\u232A\u2768-\u2775\u27C5\u27C6\u27E6-\u27EF\u2983-\u2998\u29D8-\u29DB\u29FC\u29FD\u2CF9-\u2CFC\u2CFE\u2CFF\u2D70\u2E00-\u2E2E\u2E30-\u2E42\u3001-\u3003\u3008-\u3011\u3014-\u301F\u3030\u303D\u30A0\u30FB\uA4FE\uA4FF\uA60D-\uA60F\uA673\uA67E\uA6F2-\uA6F7\uA874-\uA877\uA8CE\uA8CF\uA8F8-\uA8FA\uA8FC\uA92E\uA92F\uA95F\uA9C1-\uA9CD\uA9DE\uA9DF\uAA5C-\uAA5F\uAADE\uAADF\uAAF0\uAAF1\uABEB\uFD3E\uFD3F\uFE10-\uFE19\uFE30-\uFE52\uFE54-\uFE61\uFE63\uFE68\uFE6A\uFE6B\uFF01-\uFF03\uFF05-\uFF0A\uFF0C-\uFF0F\uFF1A\uFF1B\uFF1F\uFF20\uFF3B-\uFF3D\uFF3F\uFF5B\uFF5D\uFF5F-\uFF65]*$/) === null){
					// Did not match the entire string i.e. Found illegal characters
					inputDescription.setAttribute("class", "fillOut");
				} else if(inputDescriptionText.length > 500){
					// Description too long
					inputDescription.setAttribute("class", "fillOut");
				} else {
					// All good
					inputDescription.removeAttribute("class", "fillOut");
				}

				// Validate price
				var selectedPrice = inputPrice.value;
				if(selectedPrice == ""){
					inputPrice.setAttribute("class", "fillOut");
					alert("The item price needs to be filled out and a valid number.");
					return;
				} else if(selectedPrice.match(/^[0-9]*$/) === null){
					inputPrice.setAttribute("class", "fillOut");
					alert("The item price needs to be filled out and a valid number.");
					return;
				} else if(selectedPrice < 0 || selectedPrice > 65635){
					inputPrice.setAttribute("class", "fillOut");
					alert("The item price needs to be filled out and a valid number between 0 and 65635.");
					return;
				} else {
					inputPrice.removeAttribute("class", "fillOut");
				}

				// Validate amount
				var selectedAmount = inputAmount.value;
				if(selectedAmount == "" || selectedAmount == 0){
					inputAmount.setAttribute("class", "fillOut");
					alert("The order amount needs to be filled out and a valid positive number.");
					return;
				} else if(selectedAmount.match(/^[0-9]*$/) === null){
					inputAmount.setAttribute("class", "fillOut");
					alert("The order amount needs to be filled out and a valid positive number.");
					return;
				} else if(selectedAmount < 0 || selectedAmount > 255){
					inputAmount.setAttribute("class", "fillOut");
					alert("The order amount needs to be filled out and a valid number between 1 and 255.");
					return;
				} else {
					inputAmount.removeAttribute("class", "fillOut");
				}

				// Disable editing the newly created extra on confirm
				inputName.readOnly = true;
				inputDescription.readOnly = true;
				inputAmount.readOnly = true;
				inputPrice.readOnly = true;
				confirmButton.parentNode.removeChild(confirmButton);

				// Update the hidden price used for total price calculation
				inputPriceConfirmed.value = selectedPrice;

				// Update total price displayed
				changeTotalPrice();
			}

			function removeNewAlternativeExtra(removeButton){
				var tableRow = removeButton.parentNode.parentNode;
				tableRow.parentNode.removeChild(tableRow);

				newAlternativesCreated -= 1;

				// update the input to check how many alternatives we have submitted
				var inputNewAlternativesCreated = document.getElementById("NewAlternativesCreated");
				inputNewAlternativesCreated.value = newAlternativesCreated;

				// Update total price displayed
				changeTotalPrice();
			}

			function confirmAddedExtra(confirmButton){
				var selectBoxID = confirmButton.id;
				var splitID = selectBoxID.split("-");
				var selectBoxIDNumber = splitID[1];
				var selectBoxID = "addAlternativeSelected-" + selectBoxIDNumber;
				var selectBox = document.getElementById(selectBoxID);
				var extraIDSelected = selectBox.options[selectBox.selectedIndex].value;
				var extraIDName = document.createTextNode(selectBox.options[selectBox.selectedIndex].text);
				var inputExtraAcceptedID = "extraIDAccepted" + selectBoxIDNumber;
				var inputExtraAccepted = document.getElementById(inputExtraAcceptedID);
				var inputAmountID = "AmountSelected-" + selectBoxIDNumber;
				var inputAmount = document.getElementById(inputAmountID);

				// Check if the amount selected is a valid amount
				if(inputAmount !== null){
					var selectedAmount = inputAmount.value;
					if(selectedAmount == "" || selectedAmount == 0){
						inputAmount.setAttribute("class", "fillOut");
						alert("The order amount needs to be filled out and a valid number.");
						return;
					} else if(selectedAmount.match(/^[0-9]*$/) === null){
						inputAmount.setAttribute("class", "fillOut");
						alert("The order amount needs to be filled out and a valid number.");
						return;
					} else if(selectedAmount < 0 || selectedAmount > 255){
						inputAmount.setAttribute("class", "fillOut");
						alert("The order amount needs to be filled out and a valid number between 1 and 255.");
						return;
					} else {
						inputAmount.removeAttribute("class", "fillOut");
					}
				}

				// Remove selected extra ID from other open options
				for(var j = 0; j < alternativeID; j++){
					if(j != selectBoxIDNumber){
						var newSelectBoxID = "addAlternativeSelected-" + j;
						var newSelectBox = document.getElementById(newSelectBoxID);
						if(newSelectBox !== null){
							// Remove the option
							for (var i = 0; i < newSelectBox.options.length; i++){
								if(newSelectBox.options[i].value === extraIDSelected){
									newSelectBox.removeChild(newSelectBox.options[i]);
									break;
								}
							}

							// Update text values connected to the now selected index in the select box
							changeAlternativeText(newSelectBox);
						}
					}
				}

				// Add extra name selected to table cell etc.
				selectBox.parentNode.appendChild(extraIDName);
				inputExtraAccepted.setAttribute("value", selectBox.options[selectBox.selectedIndex].value);
				inputAmount.readOnly = true;
				selectBox.parentNode.removeChild(selectBox);
				confirmButton.parentNode.removeChild(confirmButton);

				// Update total price displayed
				changeTotalPrice();
			}

			function removeAddedExtra(removeButton, attributeID){
				// get the extra ID that had already been accepted that was removed
				var extraIDRemovedID = "extraIDAccepted" + attributeID;
				var extraIDRemoved = document.getElementById(extraIDRemovedID);
				if(extraIDRemoved !== null && extraIDRemoved.value != ""){
					// Go through the open select boxes
					for(var j = 0; j < alternativeID; j++){
						var selectBoxToAddOptionID = "addAlternativeSelected-" + j;
						var selectBoxToAddOption = document.getElementById(selectBoxToAddOptionID);
						if(selectBoxToAddOption !== null){
							// Add the option that is no longer accepted
							for(var i = 0; i < availableExtrasArray.length; i++){
								if(availableExtrasArray[i]['ExtraID'] === extraIDRemoved.value){
									var option = document.createElement("option");
									option.value = availableExtrasArray[i]['ExtraID'];
									option.text = availableExtrasArray[i]['ExtraName'];
									selectBoxToAddOption.appendChild(option);
									break;
								}
							}
						}
					}
				}

				var tableRow = removeButton.parentNode.parentNode;
				tableRow.parentNode.removeChild(tableRow);

				alternativesAdded -= 1;

				// update the input to check how many alternatives we have submitted
				var inputAlternativesAdded = document.getElementById("AlternativesAdded");
				inputAlternativesAdded.value = alternativesAdded;

				var addAlternativeExtraButton = document.getElementById("addAlternativeExtraButton");
					// Enable button again if it was disabled
				addAlternativeExtraButton.removeAttribute("disabled");
					// Display add button again if it wasn't before
				addAlternativeExtraButton.style.display = 'inline-block';

				// Update total price displayed
				changeTotalPrice();
			}

			function confirmNewAmount(confirmButton, extraID){
				// Get new amount 
				var inputExtraAmount = document.getElementById("extraAmount-" + extraID);
				var newExtraAmountValue = inputExtraAmount.value;

				// Check if the amount selected is a valid amount first
				if(inputExtraAmount !== null){
					if(newExtraAmountValue == ""){
						inputExtraAmount.setAttribute("class", "fillOut");
						alert("The order amount needs to be filled out and a valid number.");
						return;
					} else if(newExtraAmountValue.match(/^[0-9]*$/) === null){
						inputExtraAmount.setAttribute("class", "fillOut");
						alert("The order amount needs to be filled out and a valid number.");
						return;
					} else if(newExtraAmountValue < 0 || newExtraAmountValue > 255){
						inputExtraAmount.setAttribute("class", "fillOut");
						alert("The order amount needs to be filled out and a valid number between 0 and 255.");
						return;
					} else {
						inputExtraAmount.removeAttribute("class", "fillOut");
					}
				}

				// Check if the user wants to remove the item (amount is 0)
				if(newExtraAmountValue == 0){
					var removeItem = confirm("You've set the amount of this item to 0. This will remove the item from the order. Is this what you want?");
					if(removeItem){
						// Remove table row
						var tableCell = confirmButton.parentNode;
						var tableRow = tableCell.parentNode;
						tableRow.parentNode.removeChild(tableRow);

						// Add that we've removed an item from the order
						itemsRemovedFromOrder++;
						var inputItemsRemoved = document.getElementById("ItemsRemovedFromOrder");
						inputItemsRemoved.value = itemsRemovedFromOrder;
					}
				} else {
					// Set new amount 
					var extraAmountSelected = document.getElementById("extraAmountSelected-" + extraID);
					extraAmountSelected.value = newExtraAmountValue;

					// Remove confirm/reset buttons
					var tableCell = confirmButton.parentNode;
					var resetAmountButtonName = "resetAmountButton-" + extraID;
					var resetAmountButton = document.getElementById(resetAmountButtonName);
					tableCell.removeChild(confirmButton);
					tableCell.removeChild(resetAmountButton);
				}

				// Update total price
				changeTotalPrice();
			}

			function resetAmount(resetAmountButton, extraID){
				// Get original amount
				var originalExtraAmountSelected = document.getElementById("extraAmountSelected-" + extraID);
				var originalExtraAmount = originalExtraAmountSelected.value;

				// Set back to original amount
				var inputExtraAmount = document.getElementById("extraAmount-" + extraID);
				inputExtraAmount.value = originalExtraAmount;
				inputExtraAmount.removeAttribute("class", "fillOut");

				// Remove confirm/reset buttons
				var tableCell = resetAmountButton.parentNode;
				var confirmNewAmountButtonName = "confirmAmountButton-" + extraID;
				var confirmButton = document.getElementById(confirmNewAmountButtonName);
				tableCell.removeChild(confirmButton);
				tableCell.removeChild(resetAmountButton);

				changeTotalPrice();
			}

			function extraNameExists(name, attributeID){
				// Check if we're creating the same name as an existing extra (already in the order)
				var usedExtraArray = <?php echo json_encode($extraOrderedOnlyNames); ?>;
				for(var j = 0; j < usedExtraArray.length; j++){
					if(name.toLowerCase() == usedExtraArray[j].toLowerCase()){
						alert("The name " + name + " is already taken");
						return true;
					}
				}

				// Check if we're creating the same name as an existing extra (not yet selected)
				for(var j = 0; j < availableExtrasArray.length; j++){
					if(name.toLowerCase() == availableExtrasArray[j]['ExtraName'].toLowerCase()){
						alert("The name " + name + " is already taken");
						return true;
					}
				}

				// Check if we're giving the same name multiple times on creating an alternative
				if(newAlternativesCreated > 1){
					for(var j = 0; j < alternativeID; j++){
						if(j != attributeID){
							var inputNameID = "AlternativeName" + j;
							var inputName = document.getElementById(inputNameID);
							if(inputName !== null){
								var inputNameText = inputName.value;
								if(inputNameText.toLowerCase() == name.toLowerCase()){
									alert("You have already added another alternative with the name " + name);
									return true;
								}
							}
						}
					}
				}
				// Name not used before
				return false;
			}

			function changeAmount(inputAmount){
				var splitID = inputAmount.id.split("-");
				var extraID = splitID[1];
				var inputCurrentValue = inputAmount.value;
				var tableCell = inputAmount.parentNode;

				// First make sure we only allow numbers to be entered
				inputAmount.value = inputCurrentValue.replace(/[^0-9]/g, '');

				// Get original amount value
				var alreadySelectedValueSelected = document.getElementById("extraAmountSelected-" + extraID);
				var alreadySelectedValue = alreadySelectedValueSelected.value;

				// Add a checkmark/cross to the same tablecell, if they're not already added
				var confirmNewAmountButtonName = "confirmAmountButton-" + extraID;
				var resetAmountButtonName = "resetAmountButton-" + extraID;
				var confirmNewAmountButton = document.getElementById(confirmNewAmountButtonName);
				var resetAmountButton = document.getElementById(resetAmountButtonName);

				if(inputCurrentValue == alreadySelectedValue){
					inputAmount.removeAttribute("class", "fillOut");
				} else if(inputCurrentValue < 0){
					inputAmount.value = 0;
				} else if(inputCurrentValue > 255){
					inputAmount.value = 255;
				}

				if(confirmNewAmountButton === null && inputCurrentValue != alreadySelectedValue){
					var confirmNewAmountButton = document.createElement("input");
					confirmNewAmountButton.setAttribute("id", confirmNewAmountButtonName)
					confirmNewAmountButton.setAttribute("type", "button");
					confirmNewAmountButton.innerHTML = "✔";
					confirmNewAmountButton.value = "✔";
					confirmNewAmountButton.style.color = "green";
					var confirmNewAmountButtonIDNumber = extraID;
					confirmNewAmountButton.onclick = function onClick(){confirmNewAmount(this, confirmNewAmountButtonIDNumber);}
					tableCell.appendChild(confirmNewAmountButton);
				} else if(confirmNewAmountButton !== null && inputCurrentValue == alreadySelectedValue){
					tableCell.removeChild(confirmNewAmountButton);
				}

				if(resetAmountButton === null && inputCurrentValue != alreadySelectedValue){
					var resetAmountButton = document.createElement("input");
					resetAmountButton.setAttribute("id", resetAmountButtonName)
					resetAmountButton.setAttribute("type", "button");
					resetAmountButton.innerHTML = "✖";
					resetAmountButton.value = "✖";
					resetAmountButton.style.color = "red";
					var resetAmountButtonIDNumber = extraID;
					resetAmountButton.onclick = function onClick(){resetAmount(this, resetAmountButtonIDNumber);}
					tableCell.appendChild(resetAmountButton);
				} else if(resetAmountButton !== null && inputCurrentValue == alreadySelectedValue){
					tableCell.removeChild(resetAmountButton);
				}
			}

			function changeAmountNewAlternative(inputAmount){
				var inputCurrentValue = inputAmount.value;

				// First make sure we only allow numbers to be entered
				inputAmount.value = inputCurrentValue.replace(/[^0-9]/g, '');

				if(inputCurrentValue < 1){
					inputAmount.value = 1;
				} else if(inputCurrentValue > 255){
					inputAmount.value = 255;
				}
			}

			function changePriceNewAlternative(inputPrice){
				var inputCurrentValue = inputPrice.value;

				// First make sure we only allow numbers to be entered
				inputPrice.value = inputCurrentValue.replace(/[^0-9]/g, '');
				
				if(inputCurrentValue < 0){
					inputPrice.value = 0;
				} else if(inputCurrentValue > 65535){
					inputPrice.value = 65535;
				}
			}

			function validateUserInputs(){

				// Check if any amount has been changed, and if so if they've been confirmed
				var amountsNotConfirmed = 0;
				var amountsChangedFromOriginal = 0;
				for(var i = 0; i < extrasOrdered.length; i++){
					var extraID = extrasOrdered[i]['ExtraID'];
					var confirmNewAmountButtonName = "confirmAmountButton-" + extraID;
					var confirmNewAmountButton = document.getElementById(confirmNewAmountButtonName);
					var inputExtraAmount = document.getElementById("extraAmount-" + extraID);
					// Check if item still is in the order and hasn't been removed
					if(inputExtraAmount !== null){
						if(confirmNewAmountButton !== null){
							inputExtraAmount.setAttribute("class", "fillOut");
							amountsNotConfirmed++;
						} else {
							inputExtraAmount.removeAttribute("class", "fillOut");
						}

						// Check if any amounts have changed
						var originalAmount = extrasOrdered[i]['ExtraAmount'];
						var submittedAmount = inputExtraAmount.value;
						if(submittedAmount != originalAmount){
							amountsChangedFromOriginal++;
						}
					}
				}

				if(amountsNotConfirmed > 0){
					alert("You have made some changes to an item's amount. You have to confirm the change (✔) or reset it (✖) before submitting the changes.");
					return false;
				}

				// Validate staff message to user
				var inputUserMessage = document.getElementById("OrderCommunicationToUser");
				var userMessageSubmitted = false;
				if(inputUserMessage !== null){
					var userMessageSubmitted = inputUserMessage.value;

					var illegalCharacterFound = userMessageSubmitted.match(/^[+=\sA-Za-z\u00AA\u00B5\u00BA\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u037F\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u052F\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0-\u08B4\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0980\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0AF9\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C39\u0C3D\u0C58-\u0C5A\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D5F-\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F5\u13F8-\u13FD\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16F1-\u16F8\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191E\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19B0-\u19C9\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2183\u2184\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005\u3006\u3031-\u3035\u303B\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FD5\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA69D\uA6A0-\uA6E5\uA717-\uA71F\uA722-\uA788\uA78B-\uA7AD\uA7B0-\uA7B7\uA7F7-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA8FD\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uA9E0-\uA9E4\uA9E6-\uA9EF\uA9FA-\uA9FE\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA7E-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uAB30-\uAB5A\uAB5C-\uAB65\uAB70-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC0-9\u0660-\u0669\u06F0-\u06F9\u07C0-\u07C9\u0966-\u096F\u09E6-\u09EF\u0A66-\u0A6F\u0AE6-\u0AEF\u0B66-\u0B6F\u0BE6-\u0BEF\u0C66-\u0C6F\u0CE6-\u0CEF\u0D66-\u0D6F\u0DE6-\u0DEF\u0E50-\u0E59\u0ED0-\u0ED9\u0F20-\u0F29\u1040-\u1049\u1090-\u1099\u17E0-\u17E9\u1810-\u1819\u1946-\u194F\u19D0-\u19D9\u1A80-\u1A89\u1A90-\u1A99\u1B50-\u1B59\u1BB0-\u1BB9\u1C40-\u1C49\u1C50-\u1C59\uA620-\uA629\uA8D0-\uA8D9\uA900-\uA909\uA9D0-\uA9D9\uA9F0-\uA9F9\uAA50-\uAA59\uABF0-\uABF9\uFF10-\uFF19!-#%-*,-\/:;?@\[-\]_\{\}\u00A1\u00A7\u00AB\u00B6\u00B7\u00BB\u00BF\u037E\u0387\u055A-\u055F\u0589\u058A\u05BE\u05C0\u05C3\u05C6\u05F3\u05F4\u0609\u060A\u060C\u060D\u061B\u061E\u061F\u066A-\u066D\u06D4\u0700-\u070D\u07F7-\u07F9\u0830-\u083E\u085E\u0964\u0965\u0970\u0AF0\u0DF4\u0E4F\u0E5A\u0E5B\u0F04-\u0F12\u0F14\u0F3A-\u0F3D\u0F85\u0FD0-\u0FD4\u0FD9\u0FDA\u104A-\u104F\u10FB\u1360-\u1368\u1400\u166D\u166E\u169B\u169C\u16EB-\u16ED\u1735\u1736\u17D4-\u17D6\u17D8-\u17DA\u1800-\u180A\u1944\u1945\u1A1E\u1A1F\u1AA0-\u1AA6\u1AA8-\u1AAD\u1B5A-\u1B60\u1BFC-\u1BFF\u1C3B-\u1C3F\u1C7E\u1C7F\u1CC0-\u1CC7\u1CD3\u2010-\u2027\u2030-\u2043\u2045-\u2051\u2053-\u205E\u207D\u207E\u208D\u208E\u2308-\u230B\u2329\u232A\u2768-\u2775\u27C5\u27C6\u27E6-\u27EF\u2983-\u2998\u29D8-\u29DB\u29FC\u29FD\u2CF9-\u2CFC\u2CFE\u2CFF\u2D70\u2E00-\u2E2E\u2E30-\u2E42\u3001-\u3003\u3008-\u3011\u3014-\u301F\u3030\u303D\u30A0\u30FB\uA4FE\uA4FF\uA60D-\uA60F\uA673\uA67E\uA6F2-\uA6F7\uA874-\uA877\uA8CE\uA8CF\uA8F8-\uA8FA\uA8FC\uA92E\uA92F\uA95F\uA9C1-\uA9CD\uA9DE\uA9DF\uAA5C-\uAA5F\uAADE\uAADF\uAAF0\uAAF1\uABEB\uFD3E\uFD3F\uFE10-\uFE19\uFE30-\uFE52\uFE54-\uFE61\uFE63\uFE68\uFE6A\uFE6B\uFF01-\uFF03\uFF05-\uFF0A\uFF0C-\uFF0F\uFF1A\uFF1B\uFF1F\uFF20\uFF3B-\uFF3D\uFF3F\uFF5B\uFF5D\uFF5F-\uFF65]*$/);
					if(illegalCharacterFound === null){
						// Did not match the entire string i.e. Found illegal characters
						inputUserMessage.setAttribute("class", "fillOut");
						alert("The message to the user you submitted contain illegal characters.");
						return false;
					} else if(userMessageSubmitted.length > 500){
						// Text submitted is too long
						inputUserMessage.setAttribute("class", "fillOut");
						alert("The message to the user you submitted are too long.");
						return false;
					} else {
						// All good
						inputUserMessage.removeAttribute("class", "fillOut");
					}

					if(userMessageSubmitted.length > 0){
						userMessageSubmitted = true;
					}
				}

				// Validate admin note
				var inputAdminText = document.getElementById("AdminNote");
				if(inputAdminText !== null){
					var adminTextSubmitted = inputAdminText.value;

					var illegalCharacterFound = adminTextSubmitted.match(/^[+=\sA-Za-z\u00AA\u00B5\u00BA\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u037F\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u052F\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0-\u08B4\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0980\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0AF9\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C39\u0C3D\u0C58-\u0C5A\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D5F-\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F5\u13F8-\u13FD\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16F1-\u16F8\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191E\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19B0-\u19C9\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2183\u2184\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005\u3006\u3031-\u3035\u303B\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FD5\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA69D\uA6A0-\uA6E5\uA717-\uA71F\uA722-\uA788\uA78B-\uA7AD\uA7B0-\uA7B7\uA7F7-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA8FD\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uA9E0-\uA9E4\uA9E6-\uA9EF\uA9FA-\uA9FE\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA7E-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uAB30-\uAB5A\uAB5C-\uAB65\uAB70-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC0-9\u0660-\u0669\u06F0-\u06F9\u07C0-\u07C9\u0966-\u096F\u09E6-\u09EF\u0A66-\u0A6F\u0AE6-\u0AEF\u0B66-\u0B6F\u0BE6-\u0BEF\u0C66-\u0C6F\u0CE6-\u0CEF\u0D66-\u0D6F\u0DE6-\u0DEF\u0E50-\u0E59\u0ED0-\u0ED9\u0F20-\u0F29\u1040-\u1049\u1090-\u1099\u17E0-\u17E9\u1810-\u1819\u1946-\u194F\u19D0-\u19D9\u1A80-\u1A89\u1A90-\u1A99\u1B50-\u1B59\u1BB0-\u1BB9\u1C40-\u1C49\u1C50-\u1C59\uA620-\uA629\uA8D0-\uA8D9\uA900-\uA909\uA9D0-\uA9D9\uA9F0-\uA9F9\uAA50-\uAA59\uABF0-\uABF9\uFF10-\uFF19!-#%-*,-\/:;?@\[-\]_\{\}\u00A1\u00A7\u00AB\u00B6\u00B7\u00BB\u00BF\u037E\u0387\u055A-\u055F\u0589\u058A\u05BE\u05C0\u05C3\u05C6\u05F3\u05F4\u0609\u060A\u060C\u060D\u061B\u061E\u061F\u066A-\u066D\u06D4\u0700-\u070D\u07F7-\u07F9\u0830-\u083E\u085E\u0964\u0965\u0970\u0AF0\u0DF4\u0E4F\u0E5A\u0E5B\u0F04-\u0F12\u0F14\u0F3A-\u0F3D\u0F85\u0FD0-\u0FD4\u0FD9\u0FDA\u104A-\u104F\u10FB\u1360-\u1368\u1400\u166D\u166E\u169B\u169C\u16EB-\u16ED\u1735\u1736\u17D4-\u17D6\u17D8-\u17DA\u1800-\u180A\u1944\u1945\u1A1E\u1A1F\u1AA0-\u1AA6\u1AA8-\u1AAD\u1B5A-\u1B60\u1BFC-\u1BFF\u1C3B-\u1C3F\u1C7E\u1C7F\u1CC0-\u1CC7\u1CD3\u2010-\u2027\u2030-\u2043\u2045-\u2051\u2053-\u205E\u207D\u207E\u208D\u208E\u2308-\u230B\u2329\u232A\u2768-\u2775\u27C5\u27C6\u27E6-\u27EF\u2983-\u2998\u29D8-\u29DB\u29FC\u29FD\u2CF9-\u2CFC\u2CFE\u2CFF\u2D70\u2E00-\u2E2E\u2E30-\u2E42\u3001-\u3003\u3008-\u3011\u3014-\u301F\u3030\u303D\u30A0\u30FB\uA4FE\uA4FF\uA60D-\uA60F\uA673\uA67E\uA6F2-\uA6F7\uA874-\uA877\uA8CE\uA8CF\uA8F8-\uA8FA\uA8FC\uA92E\uA92F\uA95F\uA9C1-\uA9CD\uA9DE\uA9DF\uAA5C-\uAA5F\uAADE\uAADF\uAAF0\uAAF1\uABEB\uFD3E\uFD3F\uFE10-\uFE19\uFE30-\uFE52\uFE54-\uFE61\uFE63\uFE68\uFE6A\uFE6B\uFF01-\uFF03\uFF05-\uFF0A\uFF0C-\uFF0F\uFF1A\uFF1B\uFF1F\uFF20\uFF3B-\uFF3D\uFF3F\uFF5B\uFF5D\uFF5F-\uFF65]*$/);
					if(illegalCharacterFound === null){
						// Did not match the entire string i.e. Found illegal characters
						inputAdminText.setAttribute("class", "fillOut");
						alert("The admin notes you submitted contain illegal characters.");
						return false;
					} else if(adminTextSubmitted.length > 500){
						// Text submitted is too long
						inputAdminText.setAttribute("class", "fillOut");
						alert("The admin notes you submitted are too long.");
						return false;
					} else {
						// All good
						inputAdminText.removeAttribute("class", "fillOut");
					}
				}

				if(newAlternativesCreated > 0){
					var invalidInputs = 0;
					var takenName = 0;
					// Check if text fields are filled out
					for(var i = 0; i < alternativeID; i++){
						// validate name
						var inputNameID = "AlternativeName" + i;
						var inputName = document.getElementById(inputNameID);
						if(inputName !== null){
							var inputNameText = inputName.value;
							if(inputNameText == ""){
								// Name not filled out
								inputName.setAttribute("class", "fillOut");
								invalidInputs++;
							} else if(inputNameText.match(/^[A-Za-z\u00AA\u00B5\u00BA\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u037F\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u052F\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0-\u08B4\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0980\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0AF9\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C39\u0C3D\u0C58-\u0C5A\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D5F-\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F5\u13F8-\u13FD\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16F1-\u16F8\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191E\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19B0-\u19C9\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2183\u2184\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005\u3006\u3031-\u3035\u303B\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FD5\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA69D\uA6A0-\uA6E5\uA717-\uA71F\uA722-\uA788\uA78B-\uA7AD\uA7B0-\uA7B7\uA7F7-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA8FD\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uA9E0-\uA9E4\uA9E6-\uA9EF\uA9FA-\uA9FE\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA7E-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uAB30-\uAB5A\uAB5C-\uAB65\uAB70-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC '-]*$/) === null){
								// Did not match the entire string i.e. Found illegal characters
								inputName.setAttribute("class", "fillOut");
								invalidInputs++;
							} else if(inputNameText.length > 255){
								// Name too long
								inputName.setAttribute("class", "fillOut");
								invalidInputs++;
							} else if(extraNameExists(inputNameText, i)){
								// Name already exists
								inputName.setAttribute("class", "fillOut");
								takenName++;
							} else {
								// All good
								inputName.removeAttribute("class", "fillOut");
							}
						}

						// validate description
						var inputDescriptionID = "AlternativeDescription" + i;
						var inputDescription = document.getElementById(inputDescriptionID);
						if(inputDescription !== null){
							var inputDescriptionText = inputDescription.value;
							if(inputDescriptionText == ""){
								// Description not filled out
								inputDescription.setAttribute("class", "fillOut");
								invalidInputs++;
							} else if(inputDescriptionText.match(/^[+=\sA-Za-z\u00AA\u00B5\u00BA\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u037F\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u052F\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0-\u08B4\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0980\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0AF9\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C39\u0C3D\u0C58-\u0C5A\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D5F-\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F5\u13F8-\u13FD\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u16F1-\u16F8\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191E\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19B0-\u19C9\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2183\u2184\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005\u3006\u3031-\u3035\u303B\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FD5\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA69D\uA6A0-\uA6E5\uA717-\uA71F\uA722-\uA788\uA78B-\uA7AD\uA7B0-\uA7B7\uA7F7-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA8FD\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uA9E0-\uA9E4\uA9E6-\uA9EF\uA9FA-\uA9FE\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA7E-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uAB30-\uAB5A\uAB5C-\uAB65\uAB70-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC0-9\u0660-\u0669\u06F0-\u06F9\u07C0-\u07C9\u0966-\u096F\u09E6-\u09EF\u0A66-\u0A6F\u0AE6-\u0AEF\u0B66-\u0B6F\u0BE6-\u0BEF\u0C66-\u0C6F\u0CE6-\u0CEF\u0D66-\u0D6F\u0DE6-\u0DEF\u0E50-\u0E59\u0ED0-\u0ED9\u0F20-\u0F29\u1040-\u1049\u1090-\u1099\u17E0-\u17E9\u1810-\u1819\u1946-\u194F\u19D0-\u19D9\u1A80-\u1A89\u1A90-\u1A99\u1B50-\u1B59\u1BB0-\u1BB9\u1C40-\u1C49\u1C50-\u1C59\uA620-\uA629\uA8D0-\uA8D9\uA900-\uA909\uA9D0-\uA9D9\uA9F0-\uA9F9\uAA50-\uAA59\uABF0-\uABF9\uFF10-\uFF19!-#%-*,-\/:;?@\[-\]_\{\}\u00A1\u00A7\u00AB\u00B6\u00B7\u00BB\u00BF\u037E\u0387\u055A-\u055F\u0589\u058A\u05BE\u05C0\u05C3\u05C6\u05F3\u05F4\u0609\u060A\u060C\u060D\u061B\u061E\u061F\u066A-\u066D\u06D4\u0700-\u070D\u07F7-\u07F9\u0830-\u083E\u085E\u0964\u0965\u0970\u0AF0\u0DF4\u0E4F\u0E5A\u0E5B\u0F04-\u0F12\u0F14\u0F3A-\u0F3D\u0F85\u0FD0-\u0FD4\u0FD9\u0FDA\u104A-\u104F\u10FB\u1360-\u1368\u1400\u166D\u166E\u169B\u169C\u16EB-\u16ED\u1735\u1736\u17D4-\u17D6\u17D8-\u17DA\u1800-\u180A\u1944\u1945\u1A1E\u1A1F\u1AA0-\u1AA6\u1AA8-\u1AAD\u1B5A-\u1B60\u1BFC-\u1BFF\u1C3B-\u1C3F\u1C7E\u1C7F\u1CC0-\u1CC7\u1CD3\u2010-\u2027\u2030-\u2043\u2045-\u2051\u2053-\u205E\u207D\u207E\u208D\u208E\u2308-\u230B\u2329\u232A\u2768-\u2775\u27C5\u27C6\u27E6-\u27EF\u2983-\u2998\u29D8-\u29DB\u29FC\u29FD\u2CF9-\u2CFC\u2CFE\u2CFF\u2D70\u2E00-\u2E2E\u2E30-\u2E42\u3001-\u3003\u3008-\u3011\u3014-\u301F\u3030\u303D\u30A0\u30FB\uA4FE\uA4FF\uA60D-\uA60F\uA673\uA67E\uA6F2-\uA6F7\uA874-\uA877\uA8CE\uA8CF\uA8F8-\uA8FA\uA8FC\uA92E\uA92F\uA95F\uA9C1-\uA9CD\uA9DE\uA9DF\uAA5C-\uAA5F\uAADE\uAADF\uAAF0\uAAF1\uABEB\uFD3E\uFD3F\uFE10-\uFE19\uFE30-\uFE52\uFE54-\uFE61\uFE63\uFE68\uFE6A\uFE6B\uFF01-\uFF03\uFF05-\uFF0A\uFF0C-\uFF0F\uFF1A\uFF1B\uFF1F\uFF20\uFF3B-\uFF3D\uFF3F\uFF5B\uFF5D\uFF5F-\uFF65]*$/) === null){
								// Did not match the entire string i.e. Found illegal characters
								inputDescription.setAttribute("class", "fillOut");
								invalidInputs++;
							} else if(inputDescriptionText.length > 500){
								// Description too long
								inputDescription.setAttribute("class", "fillOut");
								invalidInputs++;
							} else {
								// All good
								inputDescription.removeAttribute("class", "fillOut");
							}
						}
					}

					if(takenName > 0){
						return false;
					} else if(invalidInputs > 0){
						alert("One of your inputs are missing, too long or contain illegal characters.");
						return false;
					}
				}

				// Check if admin has changed the order approval
				var originalAdminApproval = document.getElementById("originalIsApproved");
				var originalAdminApprovalValue = originalAdminApproval.value;
				var adminApprovalCheckbox = document.getElementById("isApproved");
				var orderApprovalChanged = false;

				if(adminApprovalCheckbox.checked){
					var adminApprovalCheckboxValue = adminApprovalCheckbox.value;
				} else {
					var adminApprovalCheckboxValue = 0;
				}

				if(adminApprovalCheckboxValue != originalAdminApprovalValue){
					orderApprovalChanged = true;
				}

				// Check if staff has changed any item's being marked as approved or purchased
				var itemsApprovedChanged = 0;
				var itemsPurchasedChanged = 0;
				for(var i = 0; i < extrasOrdered.length; i++){
					var extraID = extrasOrdered[i]['ExtraID'];

					var originalIsApprovedForPurchaseID = "originalIsApprovedForPurchase" + extraID;
					var originalIsApprovedForPurchase = document.getElementById(originalIsApprovedForPurchaseID);
					var originalIsApprovedForPurchaseValue = originalIsApprovedForPurchase.value;
					var checkboxIsApprovedForPurchaseID = "isApprovedForPurchase" + extraID;
					var checkboxIsApprovedForPurchase = document.getElementById(checkboxIsApprovedForPurchaseID);

					if(checkboxIsApprovedForPurchase.checked){
						var checkboxIsApprovedForPurchaseValue = 1;
					} else {
						var checkboxIsApprovedForPurchaseValue = 0;
					}

					if(checkboxIsApprovedForPurchaseValue != originalIsApprovedForPurchaseValue){
						itemsApprovedChanged++;
					}

					var originalIsPurchasedID = "originalIsPurchased" + extraID;
					var originalIsPurchased = document.getElementById(originalIsPurchasedID);
					var originalIsPurchasedValue = originalIsPurchased.value;
					var checkboxIsPurchasedID = "isPurchased" + extraID;
					var checkboxIsPurchased = document.getElementById(checkboxIsPurchasedID);

					if(checkboxIsPurchased.checked){
						var checkboxIsPurchasedValue = 1;
					} else {
						var checkboxIsPurchasedValue = 0;
					}

					if(checkboxIsPurchasedValue != originalIsPurchasedValue){
						itemsPurchasedChanged++;
					}
				}

				// Check if new added items/created have been confirmed
				if(alternativesAdded > 0 || newAlternativesCreated > 0){
					for(var i = 0; i < alternativeID; i++){
						var confirmButtonID = "confirmButton-" + i;
						var confirmButton = document.getElementById(confirmButtonID);
						if(confirmButton !== null){
							alert("All new items ordered need to be confirmed (✔) before you can update the order.");
							return false;
						}
					}

					// Submit message on adding new items
					var totalPrice = document.getElementById("SaveTotalPrice").value;
					var submitConfirmed = confirm("The total cost of this order, with the new items, will be " + totalPrice + " NOK. Are you sure you want to submit these updates to the order?");
					return submitConfirmed;
				} else if(itemsRemovedFromOrder > 0){
					var totalPrice = document.getElementById("SaveTotalPrice").value;
					var submitConfirmed = confirm("The total cost of this order, after removing " + itemsRemovedFromOrder + " item(s), will be " + totalPrice + " NOK. Are you sure you want to submit these updates to the order?");
					return submitConfirmed;
				} else if(amountsChangedFromOriginal > 0){
					// Submit message on changing item amounts
					var totalPrice = document.getElementById("SaveTotalPrice").value;
					var submitConfirmed = confirm("The total cost of this order, with the updated amount, will be " + totalPrice + " NOK. Are you sure you want to submit these updates to the order?");
					return submitConfirmed;
				} else if(userMessageSubmitted){
					// Submit message on sending staff a message
					var submitConfirmed = confirm("Are you sure you want to send the new message to user?");
					return submitConfirmed;
				} else if(orderApprovalChanged){
					// User approved staff changes
					var submitConfirmed = confirm("Are you sure you want to change the order approval?");
					return submitConfirmed;
				} else if(itemsApprovedChanged > 0){
					// User approved staff changes
					var submitConfirmed = confirm("Are you sure you want to change if the " + itemsApprovedChanged + " item(s) is marked as approved?");
					return submitConfirmed;
				} else if(itemsPurchasedChanged > 0){
					// User approved staff changes
					var submitConfirmed = confirm("Are you sure you want to change if the " + itemsPurchasedChanged +  " item(s) is marked as purchased?");
					return submitConfirmed;
				} else {
					// No change detected
					var submitConfirmed = confirm("No changes have been detected. Are you sure you want to exit the update process?");
					return submitConfirmed;
				}
			}
		</script>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/admintopnav.html.php'; ?>

		<form action="" method="post">
			<div class="left">
				<fieldset><legend>Order Details - Status: <?php htmlout($orderStatus); ?></legend>
					<div>
						<?php if(isSet($_SESSION['AddOrderError'])) :?>
							<span><b class="feedback"><?php htmlout($_SESSION['AddOrderError']); ?></b></span>
							<?php unset($_SESSION['AddOrderError']); ?>
						<?php endif; ?>
					</div>

					<div>
						<label>Date Created: </label>
						<span><b><?php htmlout($originalOrderCreated); ?></b></span>
					</div>

					<div>
						<label>Date Of Meeting: </label>
						<span><b><?php htmlout($originalMeetingStartDate); ?></b></span>
					</div>

					<div>
						<label>Days Left User Can Alter Order: </label>
						<span><b><?php htmlout($displayDaysLeftMessage); ?></b></span>
					</div>

					<div>
						<label>Last Update (Staff): </label>
						<?php if(empty($originalOrderUpdatedByStaff)) : ?>
							<span><b><i><?php htmlout("Staff has made no changes to this order yet."); ?></i></b></span>
						<?php else : ?>
							<span><b><?php htmlout($originalOrderUpdatedByStaff); ?></b></span>
						<?php endif; ?>
					</div>

					<div>
						<label>Last Update (User): </label>
						<?php if(empty($originalOrderUpdatedByUser)) : ?>
							<span><b><i><?php htmlout("User has made no changes to this order yet."); ?></i></b></span>
						<?php else : ?>
							<span><b><?php htmlout($originalOrderUpdatedByUser); ?></b></span>
						<?php endif; ?>
					</div>

					<div>
						<label>User Notes: </label>
						<?php if(empty($originalOrderUserNotes)) : ?>
							<span><b><i><?php htmlout("User did not submit any additional information."); ?></i></b></span>
						<?php else : ?>
							<span style="white-space: pre-wrap;"><b><?php htmlout($originalOrderUserNotes); ?></b></span>
						<?php endif; ?>
					</div>

					<div>
						<label>Messages: </label>
						<?php if(empty($orderMessages)) : ?>
							<span><b><i><?php htmlout("No messages have been sent so far."); ?></i></b></span>
						<?php else : ?>
							<span style="white-space: pre-wrap;"><b><?php htmlout($orderMessages); ?></b></span>
						<?php endif; ?>
					</div>

					<div>
						<label class="description">Send New Message To User: </label>
						<textarea rows="4" cols="50" id="OrderCommunicationToUser" name="OrderCommunicationToUser" placeholder="Enter New Message To User"><?php htmlout($orderCommunicationToUser); ?></textarea>
					</div>

					<div>
						<label>Original Admin Note: </label>
						<?php if(empty($originalOrderAdminNote)) : ?>
							<span><b><i><?php htmlout("No admin note has been set."); ?></i></b></span>
						<?php else : ?>
							<span style="white-space: pre-wrap;"><b><?php htmlout($originalOrderAdminNote); ?></b></span>
						<?php endif; ?>
					</div>

					<div>
						<label class="description">Set New Admin Note: </label>
							<textarea rows="4" cols="50" id="AdminNote" name="AdminNote" placeholder="Enter Admin Note"><?php htmlout($orderAdminNote); ?></textarea>
					</div>

					<div>
						<label>Original Order Approval: </label>
						<?php if($originalOrderIsApproved == 1) : ?>
							<span><b><?php htmlout("Order Approved"); ?></b></span>
						<?php else : ?>
							<span><b><?php htmlout("Order Not Approved"); ?></b></span>
						<?php endif; ?>
					</div>

					<div>
						<?php if($disableEdit == 0) : ?>
							<label>Change Order Approval: </label>
							<?php if($orderIsApproved == 1) : ?>
								<label class="checkboxlabel"><input type="checkbox" id="isApproved" name="isApproved" value="1" checked="checked">Set As Approved</label>
							<?php else : ?>
								<label class="checkboxlabel"><input type="checkbox" id="isApproved" name="isApproved" value="1">Set As Approved</label>
							<?php endif; ?>
						<?php else : ?>
							<input type="hidden" name="isApproved" value="<?php htmlout($orderIsApproved); ?>">
						<?php endif; ?>
						<input type="hidden" id="originalIsApproved" name="originalIsApproved" value="<?php htmlout($originalOrderIsApproved); ?>">
					</div>

					<?php if(isSet($originalDateTimeCancelled)) : ?>
						<div>
							<label>Date Cancelled: </label>
							<span><b><?php htmlout($originalDateTimeCancelled); ?></b></span>
						</div>
						<div>
							<label>Reason For Cancelling: </label>
							<span style="white-space: pre-wrap;"><b><?php htmlout($originalCancelMessage); ?></b></span>
						</div>
					<?php endif; ?>
				</fieldset>
			</div>

			<div class="left">
				<table id="addAlternative">
					<caption>Items Ordered</caption>
					<tr>
						<th colspan="4">Item</th>
						<th colspan="3">Approved For Purchase</th>
						<th colspan="3">Set As Purchased</th>
					</tr>
					<tr>
						<th>Name</th>
						<th>Description</th>
						<th>Price (1 Amount)</th>
						<th>Amount</th>
						<th>Approved?</th>
						<th>By Staff</th>
						<th>At Date</th>
						<th>Purchased?</th>
						<th>By Staff</th>
						<th>At Date</th>
					</tr>
					<?php if(isSet($extraOrdered) AND sizeOf($extraOrdered) > 0) : ?>
						<?php foreach($extraOrdered AS $row): ?>
							<tr>
								<td><?php htmlout($row['ExtraName']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['ExtraDescription']); ?></td>
								<td><?php htmlout($row['ExtraPrice']); ?></td>
								<td>
									<?php if($disableEdit == 0) : ?>
										<input style="width: 45px;" type="number" id="extraAmount-<?php htmlout($row['ExtraID']); ?>" name="extraAmount-<?php htmlout($row['ExtraID']); ?>" min="0" onchange="changeAmount(this)" value="<?php htmlout($row['ExtraAmount']); ?>">
										<input type="hidden" id="extraAmountSelected-<?php htmlout($row['ExtraID']); ?>" name="extraAmountSelected-<?php htmlout($row['ExtraID']); ?>" value="<?php htmlout($row['ExtraAmount']); ?>">
									<?php else : ?>
										<span><b><?php htmlout($row['ExtraAmount']); ?></b></span>
										<input type="hidden" id="extraAmountSelected-<?php htmlout($row['ExtraID']); ?>" name="extraAmountSelected-<?php htmlout($row['ExtraID']); ?>" value="<?php htmlout($row['ExtraAmount']); ?>">										
									<?php endif; ?>
								</td>
								<td>
									<?php if($disableEdit == 0) : ?>
										<?php if($row['ExtraBooleanApprovedForPurchase'] == 1) : ?>
											<label style="width: auto;"><input type="checkbox" id="isApprovedForPurchase<?php htmlout($row['ExtraID']); ?>" name="isApprovedForPurchase[]" value="<?php htmlout($row['ExtraID']); ?>" checked="checked">Approved</label>
										<?php else : ?>
											<label style="width: auto;"><input type="checkbox" id="isApprovedForPurchase<?php htmlout($row['ExtraID']); ?>" name="isApprovedForPurchase[]" value="<?php htmlout($row['ExtraID']); ?>">Approved</label>
										<?php endif; ?>
										<input type="hidden" id="originalIsApprovedForPurchase<?php htmlout($row['ExtraID']); ?>" value="<?php htmlout($row['ExtraBooleanApprovedForPurchase']); ?>">
									<?php else : ?>
										<?php if($row['ExtraBooleanApprovedForPurchase'] == 1) : ?>
											<label style="width: auto;"><input type="checkbox" name="disabled" disabled="disabled" value="<?php htmlout($row['ExtraID']); ?>" checked="checked">Approved</label>
											<input type="hidden" name="isApprovedForPurchase[]" value="<?php htmlout($row['ExtraID']); ?>">
										<?php else : ?>
											<label style="width: auto;"><input type="checkbox" name="disabled" disabled="disabled" value="<?php htmlout($row['ExtraID']); ?>">Approved</label>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<td><?php htmlout($row['ExtraApprovedForPurchaseByUser']); ?></td>
								<td><?php htmlout($row['ExtraDateTimeApprovedForPurchase']); ?></td>
								<td>
									<?php if($disableEdit == 0) : ?>
										<?php if($row['ExtraBooleanPurchased'] == 1) : ?>
											<label style="width: auto;"><input type="checkbox" id="isPurchased<?php htmlout($row['ExtraID']); ?>" name="isPurchased[]" value="<?php htmlout($row['ExtraID']); ?>" checked="checked">Purchased</label>
										<?php else : ?>
											<label style="width: auto;"><input type="checkbox" id="isPurchased<?php htmlout($row['ExtraID']); ?>" name="isPurchased[]" value="<?php htmlout($row['ExtraID']); ?>">Purchased</label>
										<?php endif; ?>
										<input type="hidden" id="originalIsPurchased<?php htmlout($row['ExtraID']); ?>" value="<?php htmlout($row['ExtraBooleanApprovedForPurchase']); ?>">
									<?php else : ?>
										<?php if($row['ExtraBooleanPurchased'] == 1) : ?>
											<label style="width: auto;"><input type="checkbox" name="disabled" disabled="disabled" value="<?php htmlout($row['ExtraID']); ?>" checked="checked">Purchased</label>
											<input type="hidden" name="isPurchased[]" value="<?php htmlout($row['ExtraID']); ?>">
										<?php else : ?>
											<label style="width: auto;"><input type="checkbox" name="disabled" disabled="disabled" value="<?php htmlout($row['ExtraID']); ?>">Purchased</label>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<td><?php htmlout($row['ExtraPurchasedByUser']); ?></td>
								<td><?php htmlout($row['ExtraDateTimePurchased']); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="10"><b>This order has nothing in it.</b></td></tr>
					<?php endif; ?>

					<?php if($disableEdit == 0) : ?>
						<?php if($availableExtrasNumber > 0) : ?>
							<tr><td colspan="10"><button type="button" onclick="createNewAlternativeExtraRow()">Create New Alternative</button><button type="button" id="addAlternativeExtraButton" onclick="addAlternativeExtraRow()">Add Alternative</button></td></tr>
						<?php else : ?>
							<tr><td colspan="10"><button type="button" onclick="createNewAlternativeExtraRow()">Create New Alternative</button><button type="button" id="addAlternativeExtraButton" onclick="addAlternativeExtraRow()" disabled="disabled">Add Alternative</button></td></tr>
						<?php endif; ?>
					<?php endif; ?>
						<tr><th colspan="10"></th></tr>
				</table>
			</div>
			<div id="DisplayTotalPricePlacement" class="left">
				<span>Total Price: <?php htmlout($originalTotalPrice); ?></span>
			</div>

			<div class="left">
				<input type="hidden" name="OrderID" value="<?php htmlout($orderID); ?>">
				<input type="hidden" id="SaveTotalPrice" name="SaveTotalPrice" value="">
				<input type="hidden" id="LastAlternativeID" name="LastAlternativeID" value="">
				<input type="hidden" id="AlternativesAdded" name="AlternativesAdded" value="0">
				<input type="hidden" id="NewAlternativesCreated" name="NewAlternativesCreated" value="0">
				<input type="hidden" id="ItemsRemovedFromOrder" name="ItemsRemovedFromOrder" value="0">
				<input type="submit" name="action" value="Submit Changes" onclick="return validateUserInputs()">
				<input type="submit" name="action" value="Go Back">
				<input type="submit" name="action" value="Reset">
			</div>
		</form>
	</body>
</html>