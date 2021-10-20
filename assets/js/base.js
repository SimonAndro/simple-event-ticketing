$(function () {
    $("form#general-form").submit(function (event) {
        event.preventDefault();
        var f = $(this);
        submitForm(f);

    });
})();


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