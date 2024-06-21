import $ from "jquery";
import {bootstrapYearMonthChart}  from "../chartCommon.js";
import {enableTabs} from "@techins/jsutils/utils";

$(document).ready(function (){

    // const chartUrl = document.head.querySelector('meta[name="chart_url"]').content;
    // $.ajax({
    //     // I prefer using meta instead of hardcoding the url. I can use the laravel's blade fuintionalities.
    //     url: chartUrl,
    //     method: "GET",
    //     success: function(data) {
    //         console.log(data);
    //
    //         const canvas = document.createElement("canvas");
    //         document.getElementById("statsContainer").appendChild(canvas)
    //         const ctx = canvas.getContext("2d");
    //
    //         drawChart(data,ctx)
    //     }
    // })
    bootstrapYearMonthChart("statsForm","statsContainer")
    enableTabs(document.getElementById("myTab"),"#home-tab-pane")

    $('#business_container').jscroll({
        loadingHtml: '<tr>' +
            '<td colspan="2"><i class="fa-solid fa-circle-notch fa-spin"></i></td>'+
            '</tr>',
        nextSelector: 'a.jscroll-next:last',
        contentSelector: '#business_container.tbody',
    });
});

