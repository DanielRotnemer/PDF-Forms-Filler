var selectedAssociation = "";
var selectedFile = "";
var domain = "localhost";

function addEvents()
{
    $(".field-value-w").on("click", function(event)
    {
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();  

        let index = $(this).index(".field-value-w");
        let display = $($(".select-fields-menu")[index]).css("display");
        $(".select-fields-menu").css("display", "none");
        $(".field-value-w").css("border-bottom-left-radius", "3px");
        $(".field-value-w").css("border-bottom-right-radius", "3px");

        if (display == "none")
        {
            $($(".select-fields-menu")[index]).css("display", "block");
            $($(".field-value-w")[index]).css("border-bottom-left-radius", "0");
            $($(".field-value-w")[index]).css("border-bottom-right-radius", "0");
        }
    });

    $(".menu-item-w").on("click", function()
    {
        if ($(this).attr("itmType") == "file")
        {
            $("#selectedFile").text($(this).find(".menu-item-text").text());
            selectedFile = $(this).attr("fileName");
        }
        else if ($(this).attr("itmType") == "association")
        {
            $("#selectedAssociation").text($(this).find(".menu-item-text").text());
            selectedAssociation = $(this).attr("associationId");
        }
    });

    $("#export").on("click", function()
    {
        if (selectedFile == "" || selectedAssociation == "") { return; }

        $.ajax
        ({
            url         : "http://" + domain + "/pHandler",
            type        : "POST",                                                      
            data        : 
            {
                fileName: selectedFile,
                associationId: selectedAssociation
            },
            success     : function(data)
            {
                alert(data);
            }
        });
    });

    $("html").on("click", function()
    {
        $(".select-fields-menu").css("display", "none");
        $(".field-value-w").css("border-bottom-left-radius", "3px");
        $(".field-value-w").css("border-bottom-right-radius", "3px");
    });
}

$(window).on("load", function()
{
    addEvents();
});