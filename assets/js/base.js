$(function () {
    $("form#general-form").submit(function (event) {
        event.preventDefault();

        var is_micro = is_micromessenger();
        if(is_micro){
            alert("open in browser to get ticket");

            return false;
        }

        var f = $(this);
        submitForm(f);

    });

    var is_micro = is_micromessenger();
    if(is_micro){
        alert("open in browser to get ticket");
    }

});


var myinterval = "";

function submitForm(f) {

    
    //reset progress
    resetProgress();

    // clear errors
    $("#notifications").html("");

    // close progress
    $(".progress").show("slow");

    //hide modal
    $("#id-downloadticket-modal").modal("hide");

    $.ajax({
        url: "./generate_ticket.php",
        type: "POST",
        data: f.serialize(),
        success: function (res) {
            console.log("success", res);

            try {
                var res = JSON.parse(res);
                if (res.type == "success") {
                    $("#studnum-placeholder").attr("href", res.value);
                    $("#studnum-placeholder-img").attr("src", res.value);
                    $("#id-downloadticket-modal").modal("show");

                    //set progress bar to 100%
                    clearInterval(myinterval);
                    $("#progress").css("width", "100%");
                    $("#progress").html("100%");

                } else if (res.type == "error") {
                    var errors = res.value;
                    errors.forEach(element => {
                        notify(element, "danger", 0);
                    });

                    //reset progress
                    resetProgress();

                }
            } catch (e) {
                console.log("exception", res);

                notify("An error occurred, try again after a few minutes", "danger", 0);

                //reset progress
                resetProgress();
            }
        },
        error: function (res) {
            console.log("error", res);

            notify("An error occurred, try again after a few minutes", "danger", 0);

            //reset progress
            resetProgress();

        },
        complete: function () {

        }
    });

    //hide modal
    $("#id-downloadticket-modal").modal("hide");

    var width = 10;
    myinterval = setInterval(function () { // set interval to show progress
        width += 10;
        $("#progress").css("width", width + "%");
        $("#progress").html(width + "%");

        if (width == 90) {
            clearInterval(myinterval);
        }
    }, 1000)

}

function is_micromessenger() {
    var ua = navigator.userAgent.toLowerCase();
    alert(ua); //Browser mozilla/5.0 (windows nt 6.1) applewebkit/537.36(khtml,like gecko) chrome/41.0.2272.12 safari/53736
                //WeChat mozilla/5.0 (linux;u;android 4.4.2;zh-cn;coolpad 8675 build/kot49h) applewebkit/533.1 (khtml,like gecko)version/4.0 mqqbrowser/5.4 tbs/025440 mobile safari/533.1 micromessenger/6.2. 4.53_r843fb8e.600 nettype/wifi language/zh_cn
   
    if (ua.match(/MicroMessenger/i) == "micromessenger") {
        return true;
    } else {
        return false;
    }
}


// reset progress
function resetProgress() {

    if(myinterval)
    {
        clearInterval(myinterval);
    }
 

    //reset progress
    $("#progress").css("width", "10%");
    $("#progress").html("10%");
    $(".progress").hide("slow"); // hide the progress bar
}

function resetgetTicket() {
    //reset progress
    resetProgress();

    // clear errors
    $("#notifications").html("");

    //clear input
    $("#student-num").val("");

}