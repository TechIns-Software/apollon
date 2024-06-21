import $ from "jquery";
import {bootstrapYearMonthChart}  from "../chartCommon.js";
import {debounce, enableTabs, submitFormAjax} from "@techins/jsutils/utils";
import {updateQueryParam} from "@techins/jsutils/url";

let prevAjax;
function handleSearch()
{
    const searchForm = document.getElementById("businessSearchForm");
    const searchInput = searchForm.querySelector("#inputSearchField");
    prevAjax=submitFormAjax(searchForm, (data) => {
        const table = document.getElementById("businessTable").querySelector("tbody")
        table.innerHTML=data;

        updateQueryParam('name',searchInput.value);
    }, (xhr)=>{

    },null,prevAjax);
}

$(document).ready(function (){

    bootstrapYearMonthChart("statsForm","statsContainer")
    enableTabs(document.getElementById("myTab"),"#home-tab-pane")

    $('#business_container').jscroll({
        loadingHtml: '<tr>' +
            '<td colspan="2"><i class="fa-solid fa-circle-notch fa-spin"></i></td>'+
            '</tr>',
        nextSelector: 'a.jscroll-next:last',
        contentSelector: '#business_container.tbody',
    });

    $("#businessSearchForm").on('submit',function (e){
        e.preventDefault();
        e.stopPropagation();

        handleSearch();
    });

    $("#inputSearchField").on('change',debounce(()=>{
        handleSearch();
    }));

    $("#cleanSearch").on('click',debounce(()=>{
        document.getElementById("inputSearchField").value="";
        handleSearch();
    }))
});

