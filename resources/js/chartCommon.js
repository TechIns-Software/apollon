import Chart from 'chart.js/auto';
import {stringToDomHtml} from "@techins/jsutils/utils";

function monthLabels(data){
    const monthNames = [
        'Ιανουάριος', 'Φεβρουάριος', 'Μάρτιος', 'Απρίλιος', 'Μάιος', 'Ιούνιος',
        'Ιούλιος', 'Αύγουστος', 'Σεπτέμβριος', 'Οκτώβριος', 'Νοέμβριος', 'Δεκέμβριος'
    ];

    // Generate labels using the defined month names
    return Object.keys(data).map(key => monthNames[key - 1]);
}

function drawChart(data,ctx){

    // Generate labels using the defined month names
    const labels = monthLabels(data)

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

function formSubmitChartAjax(form,chart){



}

function initializeChartJsForYearMonthlyStats(ctx){
    // Generate labels using the defined month names
    const labels = monthLabels(data)

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets:[]
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

function bootstrapYearMonthChart(form,canvasWrapper){

    canvasWrapper = stringToDomHtml(canvasWrapper)

    const canvas = document.createElement("canvas");
    canvasWrapper.appendChild(canvas)
    const ctx = canvas.getContext("2d");
    const chart = initializeChartJsForYearMonthlyStats(ctx)

    formSubmitChartAjax(form,canvasWrapper);


}

export {
    drawChart,
    bootstrapYearMonthChart
}
