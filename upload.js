var mouseDownCanvas = false;
var mouseDownRegion = false;
var resizeRegion = -1;
var startX = -1;
var startY = -1;
var pageNumber = -1;
var selectedRegionId = "";
var regionsCounter = 0;
var workerFieldSelected = false;

var pageHeight = 0;
var pageWidth = 0;

var domain = "localhost";

var uploadedFile = [];
var fields = [];

$(window).on("load", function()
{
    $("#upload").on("click", function() {
        $("#fileUpload").click();
    });

    $("#save").on("click", function()
    {
        if (fields.length == 0)
        {
            alert(`עליך ליעד שדות מבסיסי הנתונים ע"מ לבצע שמירה`);
            return;
        }

        for (let i = 0; i < $(".region").length; i++)
        {
            if (fields[i].length == 0) 
            {
                alert(`לא ניתן לבצע שמירה כל עוד קיימים תחומים שלא שובצו להם שדות מבסיסי הנתונים`);
                return;
            }
        }

        var filedsCordinates = [];
        for (let i = 0; i < $(".region").length; i++)
        {
            let top = parseInt($($(".region")[i]).css("top").split("px").join(""));
            let left = parseInt($($(".region")[i]).css("left").split("px").join(""));
            let pageIndex = $($(".region")[i]).parent().index(".canvas-w");
            filedsCordinates.push([pageIndex, top, left]);
        }

        $.ajax
        ({
            url         : "http://" + domain + "/pHandler",
            type        : "POST",                                                      
            data        : 
            {
                selectedFields: JSON.stringify(fields),
                coordinates: JSON.stringify(filedsCordinates),
                fileName: uploadedFile[1],
                pHeight: pageHeight,
                pWidth: pageWidth
            },
            success     : function(data)
            {
                alert(data); 
                location.reload();  
            }
        });
    });

    $("#fileUpload").on("change", function()
    {
        var uploader = document.getElementById("fileUpload");
        if (uploader.files.length > 0) 
        {
            if (uploader.files.length > 1 || uploadedFile.length > 0) 
            {
                alert("לא ניתן להעלות יותר מקובץ אחד בפעם אחת");
                return;
            }

            let file = uploader.files[0];
            var fileReader = new FileReader();
            fileReader.onload = function(event) 
            {
                if (file["type"] == "application/pdf")
                {
                    $.ajax
                    ({                        
                        url         : "http://" + domain + "/pHandler",
                        type        : "POST",                                                     
                        data        : 
                        {
                            fileData: event.target.result,
                            fileName: file.name
                        },
                        success     : function(fileUrl)
                        {
                            $(".file-name").text("שם הקובץ: " + file.name.split(".pdf").join(""));
                            const loadingDocTask = pdfjsLib.getDocument(fileUrl);
                            loadingDocTask.promise.then(doc => 
                            {
                                console.log("This file has " + doc._pdfInfo.numPages + " pages");
                                for (let i = 1; i <= doc._pdfInfo.numPages; i++)
                                {
                                    doc.getPage(i).then(page => 
                                    {
                                        var canvasElement = $(`<div class="canvas-w" id="pageWrapper` + i + `">
                                            <canvas id="pagePreview` + i + `"></canvas>
                                        </div>`);
                                        $(".file-preview").append(canvasElement);
                                        var canvas = document.getElementById("pagePreview" + i);
                                        var context = canvas.getContext("2d");
                                        var viewport = page.getViewport({ scale: 1.3, rotation: 0, dontFlip: false });
                                        canvas.width = viewport.width;
                                        canvas.height = viewport.height;
                                        pageHeight = viewport.height;
                                        pageWidth = viewport.width;
                                        $("#pageWrapper" + i).css("width", viewport.width + "px");
                                        $("#pageWrapper" + i).css("height", viewport.height + "px");
                                        
                                        page.render({
                                            canvasContext: context,
                                            viewport: viewport
                                        });
                                        addEvents();
                                    });
                                }
                            });
                            uploadedFile = [fileUrl, file.name];                            
                        }
                    });
                }
                else {
                    alert("זוהה קובץ לא תקין, בוטלה ההלעה של קובץ זה");
                }
            };
            fileReader.readAsDataURL(file);
        }
    });
});

function addRegionEvents()
{
    $(".region").off("mousedown");
    $(".region").on("mousedown", function(event)
    {
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();

        mouseDownRegion = true;
        startX = event.pageX - $(this).offset().left;
        startY = event.pageY - $(this).offset().top;

        $(".resize").css("display", "none");
        $(this).find(".resize").css("display", "block");
        selectedRegionId = $(this).attr("id"); 
        
        $(".select-fields-menu").css("display", "none");
        $(".select-fields-w").css("border-bottom-left-radius", "3px");
        $(".select-fields-w").css("border-bottom-right-radius", "3px");
    });

    $(".region").off("click");
    $(".region").on("click", function(event)
    {
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();

        $(".resize").css("display", "none");
        $(this).find(".resize").css("display", "block");
        selectedRegionId = $(this).attr("id");
    });

    $(".resize").off("mousedown");
    $(".resize").on("mousedown", function(event)
    {
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();

        resizeRegion = $(this).index();
        selectedRegionId = $(this).parent().attr("id");

        startX = event.pageX;
        startY = event.pageY;

        $(".select-fields-menu").css("display", "none");
        $(".select-fields-w").css("border-bottom-left-radius", "3px");
        $(".select-fields-w").css("border-bottom-right-radius", "3px");
    });

    $(".remove-field").off("click");
    $(".remove-field").on("click", function()
    {
        let index = $(this).parent().index(".field-select-w");
        $($(".region")[index]).remove();
        $(this).parent().remove();
        fields.splice(index, 1);
        $(this).parent().remove();
        if ($(".field-select-w").length > 0) {
            $($(".field-select-w")[0]).css("margin-top", "0");
        }        
    });
    
    $(".select-fields-w").off("click");
    $(".select-fields-w").on("click", function(event)
    {
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();

        $(".resize").css("display", "none");
        selectedRegionId = "";

        let display = $(this).parent().find(".select-fields-menu").css("display");
        $(".select-fields-menu").css("display", "none");
        $(".select-fields-w").css("border-bottom-left-radius", "3px");
        $(".select-fields-w").css("border-bottom-right-radius", "3px");
        if (display == "none") 
        {
            $(this).parent().find(".select-fields-menu").css("display", "block");
            $(this).parent().find(".select-fields-w").css("border-bottom-left-radius", "0");
            $(this).parent().find(".select-fields-w").css("border-bottom-right-radius", "0");
        }
    });

    $(".select-fields-menu").off("click");
    $(".select-fields-menu").on("click", function(event)
    {
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();
    });

    $(".field-selection-itm-w").off("click");
    $(".field-selection-itm-w").on("click", function(event)
    {
        if (event.stopPropagation) event.stopPropagation();
        if (event.preventDefault) event.preventDefault();

        let fieldType = $(this).attr("fieldType");
        let checked = $(this).attr("field-selected");
        let regionNumber = parseInt($(this).attr("region"));
        let selectedField = "Select value";

        if (fieldType == "role") 
        {
            $("[fieldType=role][region=" + regionNumber + "]").find(".checkmark").css("background", "lightblue");
            $("[fieldType=role][region=" + regionNumber + "]").attr("field-selected", "f");
            $("[fieldType=association][region=" + regionNumber + "]").find(".checkmark").css("background", "lightblue");
            $("[fieldType=association][region=" + regionNumber + "]").attr("field-selected", "f");

            if (checked == "f") 
            {
                $(this).attr("field-selected", "t");
                $(this).find(".checkmark").css("background", "#1b4c7a");

                let index = $(this).index("[fieldType=role][region=" + regionNumber + "]");
                let role = roles[index];

                if (workerFieldSelected == true) { // 0: role, 1: worker
                    fields[regionNumber] = [['Role', role], fields[regionNumber][0]];
                }
                else {
                    fields[regionNumber] = [['Role', role], null];
                }  
            }
            else if (checked == "t") 
            {
                if (workerFieldSelected == true) {
                    fields[regionNumber].splice(0, 1);
                } 
                else {
                    fields[regionNumber] = [];
                }               
            }
        }  
        else if (fieldType == "association")
        {
            $("[fieldType=role][region=" + regionNumber + "]").find(".checkmark").css("background", "lightblue");
            $("[fieldType=role][region=" + regionNumber + "]").attr("field-selected", "f");
            $("[fieldType=association][region=" + regionNumber + "]").find(".checkmark").css("background", "lightblue");
            $("[fieldType=association][region=" + regionNumber + "]").attr("field-selected", "f");
            $("[fieldType=worker][region=" + regionNumber + "]").find(".checkmark").css("background", "lightblue");
            $("[fieldType=worker][region=" + regionNumber + "]").attr("field-selected", "f");

            if (checked == "f") 
            {
                $(this).attr("field-selected", "t");
                $(this).find(".checkmark").css("background", "#1b4c7a");

                let index = $(this).index("[fieldType=association][region=" + regionNumber + "]");
                let association = associationsFields[index];

                fields[regionNumber] = [['Association', association]];                
            }
            else if (checked == "t") {
                fields[regionNumber] = [];
            }
        }         
        else if (fieldType == "worker")
        {
            $("[fieldType=association][region=" + regionNumber + "]").find(".checkmark").css("background", "lightblue");
            $("[fieldType=association][region=" + regionNumber + "]").attr("field-selected", "f");
            $("[fieldType=worker][region=" + regionNumber + "]").find(".checkmark").css("background", "lightblue");
            $("[fieldType=worker][region=" + regionNumber + "]").attr("field-selected", "f");

            if (checked == "f") 
            {
                $(this).attr("field-selected", "t");
                $(this).find(".checkmark").css("background", "#1b4c7a");

                let index = $(this).index("[fieldType=worker][region=" + regionNumber + "]");
                let worker = workersFields[index];

                if (fields[regionNumber].length == 2) { // 0: role, 1: worker
                    fields[regionNumber][1] = ['Worker', worker];
                }
                else {
                    fields[regionNumber] = [['Worker', worker]];
                }
                workerFieldSelected = true;
            }
            else if (checked == "t") 
            {
                if (fields[regionNumber].length == 2) {
                    fields[regionNumber].splice(1, 1);
                }
                else {
                    fields[regionNumber] = [];
                }
                workerFieldSelected = false;
            }
        }

        if (fields[regionNumber].length > 0) { selectedField = ""; }
        for (let i = 0; i < fields[regionNumber].length; i++)
        {
            if (fields[regionNumber][i] == null) continue;

            let delimiter = i > 0 ? ", " : "";
            selectedField += delimiter + fields[regionNumber][i][0] + ": " + fields[regionNumber][i][1];
        }
        $($(".select-fields-w")[regionNumber]).find(".field-selected-value").text(selectedField);
    });
}

function addEvents()
{
    if ($(".canvas-w").length)
    {
        $(".canvas-w").off("mousedown");
        $(".canvas-w").on("mousedown", function(event)
        {
            if (event.stopPropagation) event.stopPropagation();
            if (event.preventDefault) event.preventDefault();  
            
            if (selectedRegionId != "")
            {
                $(".resize").css("display", "none");
                selectedRegionId = "";
                return;
            }

            mouseDownCanvas = true;
            mouseDownRegion = false;
            startX = event.pageX - $(this).offset().left;
            startY = event.pageY - $(this).offset().top;
            pageNumber = $(this).index(".canvas-w");
            selectedRegionId = "region" + regionsCounter;
            $(this).append(`<div id="` + selectedRegionId + `" class="region" style="top: ` + startY + `px; left: ` + startX + `px;">` + (regionsCounter + 1) + `</div>`);
            regionsCounter++;
            fields.push([]);
        });

        $("html").off("click");
        $("html").on("click", function()
        {
            if ($(".region").length > 0)
            {     
                $(".resize").css("display", "none");
                selectedRegionId = "";
                
                $(".select-fields-menu").css("display", "none");
                $(".select-fields-w").css("border-bottom-left-radius", "3px");
                $(".select-fields-w").css("border-bottom-right-radius", "3px");
            }            
        });

        $("body").off("mouseup");
        $("body").on("mouseup", function() 
        {
            if (mouseDownCanvas == true)
            {
                let fieldsSelectionMenu =
                `<div class="field-select-w">
                    <div class="select-field-name">יעוד שדות עבור תחום ` + regionsCounter + `:</div>
                    <img class="remove-field" src="http://` + domain + `/utils/close.png"/>
                    <div class="select-fields-w noselect">
                        <div class="field-selected-value">Select value</div>
                    </div>
                    <div class="select-fields-menu-threshold">
                        <div class="select-fields-menu">
                            <div class="field-selection-title noselect">Roles</div>`;
                            for (let i = 0; i < roles.length; i++)
                            {
                                let margin = i == 0 ? ` style="margin-top: 10px;"` : ``;
                                fieldsSelectionMenu +=
                                `<div fieldType="role" field-selected="f" region="` + $(".inner-selection-w").find(".field-select-w").length + `" class="field-selection-itm-w noselect"` + margin + `>
                                    <div class="checkmark"></div>
                                    <div class="field-selection-itm-txt">` + roles[i] + `</div>
                                </div>`;
                            }
                            fieldsSelectionMenu +=
                            `<div class="field-selection-title noselect">associations_table</div>`;
                            for (let i = 0; i < associationsFields.length; i++)
                            {
                                let margin = i == 0 ? ` style="margin-top: 10px;"` : ``;
                                fieldsSelectionMenu +=
                                `<div fieldType="association" field-selected="f" region="` + $(".inner-selection-w").find(".field-select-w").length + `" class="field-selection-itm-w noselect"` + margin + `>
                                    <div class="checkmark"></div>
                                    <div class="field-selection-itm-txt">` + associationsFields[i] + `</div>
                                </div>`;
                            }
                            fieldsSelectionMenu +=
                            `<div class="field-selection-title noselect">workers_table</div>`;
                            for (let i = 0; i < workersFields.length; i++)
                            {
                                let margin = i == 0 ? ` style="margin-top: 10px;"` : ``;
                                if (i == workersFields.length - 1) margin = ` style="margin-bottom: 10px;"`;
                                fieldsSelectionMenu +=
                                `<div fieldType="worker" field-selected="f" region="` + $(".inner-selection-w").find(".field-select-w").length + `" class="field-selection-itm-w noselect"` + margin + `>
                                    <div class="checkmark"></div>
                                    <div class="field-selection-itm-txt">` + workersFields[i] + `</div>
                                </div>`;
                            }
                            fieldsSelectionMenu +=
                        `</div>
                    </div>
                </div>`;
                $(".inner-selection-w").append(fieldsSelectionMenu);

                $($(".field-select-w")[0]).css("margin-top", "0");

                let w = $("#" + selectedRegionId).width();
                let h = $("#" + selectedRegionId).height();

                if (h < 15) {
                    $("#" + selectedRegionId).css("height", "15px");
                }
                if (w < 15) {
                    $("#" + selectedRegionId).css("width", "15px");
                }

                $("#" + selectedRegionId).append(`<div class="resize" style="display: block; left: 0; top: 0; transform: translate(-50%, -50%); cursor: nwse-resize;"></div>
                <div class="resize" style="display: block; right: 0; top: 0; transform: translate(50%, -50%); cursor: nesw-resize;"></div>
                <div class="resize" style="display: block; left: 0; bottom: 0; transform: translate(-50%, 50%); cursor: nesw-resize;"></div>
                <div class="resize" style="display: block; right: 0; bottom: 0; transform: translate(50%, 50%); cursor: nwse-resize;"></div>`);
                selectedRegionId = "";
                addRegionEvents();
            }

            mouseDownCanvas = false;
            mouseDownRegion = false;
            resizeRegion = -1;
            startX = startY = -1;
        });

        $("body").off("mousemove");
        $("body").on("mousemove", function(event)
        {
            if (event.stopPropagation) event.stopPropagation();
            if (event.preventDefault) event.preventDefault();        
            
            if (mouseDownRegion == true)
            {
                let currentX = event.pageX - $("#" + selectedRegionId).offset().left;
                let currnetY = event.pageY - $("#" + selectedRegionId).offset().top;
                let movX = currentX - startX;
                let movY = currnetY - startY;

                let left = parseInt($("#" + selectedRegionId).css("left").split("px").join(""));
                let top = parseInt($("#" + selectedRegionId).css("top").split("px").join("")); 
                                
                if (top + movY >= 0 && top + movY <= ($("#" + selectedRegionId).parent().height() - $("#" + selectedRegionId).height())) { 
                    $("#" + selectedRegionId).css("top", (top + movY) + "px");
                }   
                if (left + movX >= 0 && left + movX <= ($("#" + selectedRegionId).parent().width() - $("#" + selectedRegionId).width())) {
                    $("#" + selectedRegionId).css("left", (left + movX) + "px");
                }         
            }
            else if (resizeRegion != -1)
            {
                let diffX = event.pageX - startX;
                let diffY = event.pageY - startY;
                
                let top = parseInt($("#" + selectedRegionId).css("top").split("px").join(""));
                let left = parseInt($("#" + selectedRegionId).css("left").split("px").join(""));
                let width = $("#" + selectedRegionId).width();
                let height = $("#" + selectedRegionId).height();

                let resize = true;

                if (resizeRegion == 0)
                {
                    let newTop = top + diffY, 
                        newLeft = left + diffX,
                        newHeight = height - diffY,
                        newWidth = width - diffX;

                    if (newTop < 0 || newTop + newHeight > $("#" + selectedRegionId).parent().height() || newHeight < 15) {
                        resize = false;
                    }
                    if (newLeft < 0 || newLeft + newWidth > $("#" + selectedRegionId).parent().width() || newWidth < 15) {
                        resize = false;
                    }

                    if (resize == true)
                    {
                        $("#" + selectedRegionId).css("top", newTop + "px");
                        $("#" + selectedRegionId).css("left", newLeft + "px");
                        $("#" + selectedRegionId).css("height", newHeight + "px");
                        $("#" + selectedRegionId).css("width", newWidth + "px");
                    }                
                }
                if (resizeRegion == 1)
                {
                    let newTop = top + diffY, 
                        newHeight = height - diffY,
                        newWidth = width + diffX;

                    if (newTop < 0 || newTop + newHeight > $("#" + selectedRegionId).parent().height() || newHeight < 15) {
                        resize = false;
                    }
                    if (left + newWidth > $("#" + selectedRegionId).parent().width() || newWidth < 15) {
                        resize = false;
                    }

                    if (resize == true)
                    {
                        $("#" + selectedRegionId).css("top", newTop + "px");
                        $("#" + selectedRegionId).css("height", newHeight + "px");
                        $("#" + selectedRegionId).css("width", newWidth + "px");
                    }       
                }
                if (resizeRegion == 2)
                {
                    let newLeft = left + diffX,
                        newHeight = height + diffY,
                        newWidth = width - diffX;

                    if (top + newHeight > $("#" + selectedRegionId).parent().height() || newHeight < 15) {
                        resize = false;
                    }
                    if (newLeft < 0 || newLeft + newWidth > $("#" + selectedRegionId).parent().width() || newWidth < 15) {
                        resize = false;
                    }

                    if (resize == true)
                    {
                        $("#" + selectedRegionId).css("left", newLeft + "px");
                        $("#" + selectedRegionId).css("height", newHeight + "px");
                        $("#" + selectedRegionId).css("width", newWidth + "px");
                    }                
                }
                if (resizeRegion == 3)
                {
                    let newHeight = height + diffY,
                        newWidth = width + diffX;

                    if (top + newHeight > $("#" + selectedRegionId).parent().height() || newHeight < 15) {
                        resize = false;
                    }
                    if (left + newWidth > $("#" + selectedRegionId).parent().width() || newWidth < 15) {
                        resize = false;
                    }

                    if (resize == true)
                    {
                        $("#" + selectedRegionId).css("height", newHeight + "px");
                        $("#" + selectedRegionId).css("width", newWidth + "px");
                    }     
                }

                startX = event.pageX;
                startY = event.pageY;
            }
            else if (mouseDownCanvas == true)
            {
                let currentX = event.pageX - $("#" + selectedRegionId).parent().offset().left;
                let currnetY = event.pageY - $("#" + selectedRegionId).parent().offset().top;
                let movX = currentX - startX;
                let movY = currnetY - startY;

                let w = $("#" + selectedRegionId).width();
                let h = $("#" + selectedRegionId).height();
                let left = parseInt($("#" + selectedRegionId).css("left").split("px").join(""));
                let top = parseInt($("#" + selectedRegionId).css("top").split("px").join(""));

                if (left + w + movX <= $("#" + selectedRegionId).parent().width()) {
                    $("#" + selectedRegionId).css("width", (w + movX) + "px");
                }
                if (top + h + movY <= $("#" + selectedRegionId).parent().height()) {
                    $("#" + selectedRegionId).css("height", (h + movY) + "px");
                }

                startX = event.pageX - $("#" + selectedRegionId).parent().offset().left;
                startY = event.pageY - $("#" + selectedRegionId).parent().offset().top;
            }  
        });
    }
}