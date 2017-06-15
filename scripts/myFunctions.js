// The functions we will use to enhance our web pageX

// Get a clock in text form
function startTime(){
    var today = new Date();
    var h = today.getHours();
    var m = today.getMinutes();
    var s = today.getSeconds();
    m = checkTime(m);
    s = checkTime(s);
	
	// Define what ID we should update
    var clockDiv = document.getElementById('Clock');
	
	// Set new text (time)
	clockDiv.innerHTML = h + ":" + m + ":" + s;
	
	// Update every 0.5s
    var t = setTimeout(startTime, 500);
}

// add zero in front of numbers < 10 for our clock
function checkTime(i){
    if (i < 10){
		i = "0" + i
	};  
    return i;
}
