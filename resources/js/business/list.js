import $ from "jquery";
import Chart from 'chart.js/auto';

function drawChart(data,ctx){

    const monthNames = [
        'Ιανουάριος', 'Φεβρουάριος', 'Μάρτιος', 'Απρίλιος', 'Μάιος', 'Ιούνιος',
        'Ιούλιος', 'Αύγουστος', 'Σεπτέμβριος', 'Οκτώβριος', 'Νοέμβριος', 'Δεκέμβριος'
    ];

    // Generate labels using the defined month names
    const labels = Object.keys(data).map(key => monthNames[key - 1]);

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets:[
                {
                    label: 'Πλήθος Επιχειρήσεων ανα μήνα',
                    data: Object.values(data)
                }
            ]
        },
        options: {
            scales: {
                xAxes: [{
                    type: 'time',
                    position: 'bottom',
                    time: {
                        unit: 'month'
                    }
                }],
            }
        }
    });
}

$(document).ready(function (){

    const chartUrl = document.head.querySelector('meta[name="chart_url"]').content;
    $.ajax({
        // I prefer using meta instead of hardcoding the url. I can use the laravel's blade fuintionalities.
        url: chartUrl,
        method: "GET",
        success: function(data) {
            console.log(data);

            const canvas = document.createElement("canvas");
            document.getElementById("statsContainer").appendChild(canvas)
            const ctx = canvas.getContext("2d");

            drawChart(data,ctx)
        }
    })

    $('#business_container').jscroll({
        loadingHtml: '<tr>' +
            '<td colspan="2"><i class="fa-solid fa-circle-notch fa-spin"></i></td>'+
            '</tr>',
        nextSelector: 'a.jscroll-next:last',
        contentSelector: '#business_container.tbody',
    });
});

