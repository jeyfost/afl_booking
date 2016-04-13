function changeBlock(action, block, text) {
    if(action == 1) {
        document.getElementById(block).style.backgroundColor = '#33a0d7';
        document.getElementById(text).style.color = '#ffffff';
    } else {
        document.getElementById(block).style.backgroundColor = 'transparent';
        document.getElementById(text).style.color = '#33a0d7';
    }
}

$(document).ready(function() {
    $('#arr_icao').keyup(function() {
        var arr_icao = $('#arr_icao').val();
        var dep_icao = $('#dep_icao').val();

        $.ajax({
            type: 'POST',
            data: {"arr_icao": arr_icao, "dep_icao": dep_icao},
            url: 'scripts/ajaxArrivalICAO.php',
            success: function(response) {
                $('#scheduleTable').html(response);

                $.ajax({
                    type: 'POST',
                    data: {"dep_icao": dep_icao, "arr_icao": arr_icao},
                    url: 'scripts/ajaxPagesDepArr.php',
                    success: function(result) {
                        $('#pageNumbers').html(result);
                    }
                });
            }
        });
    });

    $('#dep_icao').keyup(function() {
        var icao = $('#dep_icao').val().toUpperCase();
        if($('#arr_icao').val() != "") {
            $('#arr_icao').val("");
        }
        $.ajax({
            type: 'POST',
            data: {"icao": icao},
            url: 'scripts/ajaxICAO.php',
            success: function(response) {
                $('#scheduleTable').html(response);

                if(icao != "") {
                    $.ajax({
                        type: 'POST',
                        data: {"icao": icao},
                        url: 'scripts/ajaxPages.php',
                        success: function(result) {
                            $('#pageNumbers').html(result);
                        }
                    });
                } else {
                    $('#pageNumbers').html("");
                }
            }
        });
    });

    $('#button').click(function() {
        var aircraft_type = $('input[name="aircraft_type"]:checked').val();
        var termianl = $('input[name="terminal"]:checked').val();
        var real_time = $('input[name="real_time"]:checked').val();

        $.ajax({
            type: 'POST',
            data: {"aircraft_type": aircraft_type, "terminal": termianl, "real_time": real_time},
            url: 'scripts/ajaxGenerateFlight.php',
            beforeSend: function() {
                $('#generationResult').html('&nbsp;&nbsp;<img src="img/preloader.gif" style="margin-left: 10px;" />')
            },
            success: function(response) {
                $('#generationResult').html(response);
            }
        });
    });

    $('#callsignTD').click(function() {
        var content = $('#callsignTD').html();
        if(content == "Позывной") {

        }
    });

});

function showWeather(icao) {
    $.ajax({
        type: 'POST',
        data: {"icao": icao},
        url: 'scripts/ajaxGetWeather.php',
        beforeSend: function() {
            $('#weatherBlock').html('<div style="position: relative; width: 640px; margin: 0 auto;"><br /><br />&nbsp;&nbsp;<img src="img/preloader_sun.gif" style="margin-left: 10px; position: relative; float: left;" /><img src="img/preloader_sun_cloud.gif" style="margin-left: 10px; position: relative; float: left;" /><img src="img/preloader_sun_cloud_rain.gif" style="margin-left: 10px; position: relative; float: left;" /><img src="img/preloader_cloud.gif" style="margin-left: 10px; position: relative; float: left;" /><img src="img/preloader_rain.gif" style="margin-left: 10px; position: relative; float: left;" /><img src="img/preloader_snow_rain.gif" style="margin-left: 10px; position: relative; float: left;" /><img src="img/preloader_snow.gif" style="margin-left: 10px; position: relative; float: left;" /><img src="img/preloader_thunderstorm.gif" style="margin-left: 10px; position: relative; float: left;" /><div style="clear: both;"></div><br /><br /><center>Идёт загрузка погоды...</center></div><div style="clear: both;"></div>')
        },
        success: function(response) {
            $('#weatherBlock').html(response);
        }
    });
}