function resize()
{
    for (let i = 0; i < $(".field-w").length; i++)
    {
        let fullWidth = $($(".field-w")[i]).width();
        let nameWidth = $($(".field-w")[i]).find(".field-name").width();
        let inputWidth = fullWidth - 10 - nameWidth;
        $($(".field-w")[i]).find(".field-input").css("width", inputWidth + "px");
    }
}

$(window).on("load", function()
{
    resize();
});