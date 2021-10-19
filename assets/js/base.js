$(document).ready(function () {
    $("form#general-form").submit(function (event) {
        event.preventDefault();
        var f = $(this);
        submitForm(f);

    });
});



function submitForm(f) {

    $(".progress").show();

    $.ajax({
        url: "./generate_ticket.php",
        type: "POST",
        data: f.serialize(),
        success:function(res){
            console.log(res)
        },
        error:function(res){
            console.log(res)
        }
    });

    var width = 5;
    var myinterval = setInterval(function () {
        width += 5;
        $("#progress").css("width", width+"%");
        $("#progress").html(width+"%");

        if(width == 90)
        {
           clearInterval(myinterval); 
        }
    }, 100)


}