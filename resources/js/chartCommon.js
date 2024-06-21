import Chart from 'chart.js/auto';
import {stringToDomHtml, submitFormAjax} from "@techins/jsutils/utils";

const monthNames = [
    'Ιανουάριος', 'Φεβρουάριος', 'Μάρτιος', 'Απρίλιος', 'Μάιος', 'Ιούνιος',
    'Ιούλιος', 'Αύγουστος', 'Σεπτέμβριος', 'Οκτώβριος', 'Νοέμβριος', 'Δεκέμβριος'
];

let ajaxCall=null;

function monthLabels(data){
    // Generate labels using the defined month names
    return Object.keys(data).map(key => monthNames[key - 1]);
}

function initializeChartJsForYearMonthStats(canvasWrapper){
    // Generate labels using the defined month names

    canvasWrapper = stringToDomHtml(canvasWrapper)

    const canvas = document.createElement("canvas");
    canvasWrapper.appendChild(canvas)
    const ctx = canvas.getContext("2d");

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthNames,
            datasets:[
                // Dummy Initial Dataset
                {
                    label: "2024",
                    data: [0,0,0,0,0,0,0,0,0,0,0,0]
                },
            ]
        },
        options: {}
    });
}


/**
 * Retrieves ajax data and updates the chart provided from chart
 * @param {HTMLElement} form
 * @param {Chart} chart
 */
function updateChartFromAjax(form,chart, successCallback)
{
    ajaxCall=submitFormAjax(form,(data)=>{
        const finalDataset = []
        const years = []
        Object.keys(data).forEach((year)=>{
                finalDataset.push({
                    label: ""+year,
                    data: Object.values(data[year])
                })

            years.push();
        });
        console.log(finalDataset);
        chart.data.datasets = finalDataset;
        chart.update();
        if(typeof successCallback == 'function'){
            successCallback();
        }
    },()=>{},null,ajaxCall)
}

function createLiForYear(year,form,chart,createBtn=true)
{
    const li = document.createElement('li');
    li.innerText=year;

    if(createBtn){
        const deleteButton = document.createElement('button')
        deleteButton.classList.add('btn','btn-link','text-danger')
        deleteButton.innerHTML="<i class='fa fa-trash'></i>"
        li.append(deleteButton);

        deleteButton.addEventListener('click',function (e){
            e.preventDefault();
            e.stopPropagation();
            li.remove();
            updateChartFromAjax(form,chart)
        })
    }

    const input = document.createElement('input')
    input.type = 'hidden';
    input.name = "year[]";
    input.value = year;
    li.append(input);



    form.querySelector('.form-years').append(li)
}

/**
 * Initialize ChartJS
 *
 * @param {String | HTMLElement} form
 * @param {String | HTMLElement} canvasWrapper
 *
 * @return {Chart}
 */
function bootstrapYearMonthChart(form,canvasWrapper){
    form = stringToDomHtml(form);
    const chart = initializeChartJsForYearMonthStats(canvasWrapper)
    ajaxCall = updateChartFromAjax(form,chart)
    createLiForYear(new Date().getFullYear(),form,chart,false);

    form.addEventListener('submit',(event)=>{
        event.preventDefault();
        if (form.checkValidity() == false) {
            return false;
        }
        const input = form.querySelector(".yearInput")
        createLiForYear(input.value,form,chart);
        updateChartFromAjax(form,chart,()=>{
            input.value=""
        });
    })
}

export {
    bootstrapYearMonthChart
}
