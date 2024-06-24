import $ from "jquery";
import {bootstrapYearMonthChart}  from "../chartCommon.js";
import {debounce, enableTabs, submitFormAjax} from "@techins/jsutils/utils";
import {updateQueryParam} from "@techins/jsutils/url";
import {addInputErrorMsg} from "@techins/jsutils/input-error";
import {Modal} from "bootstrap";

// Variable that contains list Ajax.
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

function closeAddBusinessModal()
{
    const modal = Modal.getInstance(document.getElementById('createBusiness'))
    modal.hide()
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

    $("#infoForm").on('submit',function (e){
        e.preventDefault()
        const form = e.target

        submitFormAjax(form,(data)=>{
            const wrapper = document.createElement('template')
            wrapper.innerHTML=data

            document.getElementById("businessTable").querySelector('tbody').prepend(wrapper.content)
            closeAddBusinessModal()
            form.reset()
        },(xhr)=>{
            const response = JSON.parse(xhr.responseText)['msg'];
            if(xhr.status == 400){
                Object.keys(response).forEach((key)=>{
                    const inputElem = form.querySelector(`input[name="${key}"]`)
                    response[key].forEach((msg)=>addInputErrorMsg(inputElem,msg))
                })
                return;
            }
        })
    })
});

