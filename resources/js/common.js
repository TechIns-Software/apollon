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

export {
    drawChart
}
