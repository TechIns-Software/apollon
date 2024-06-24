import $ from "jquery";
import {bootstrapYearMonthChart}  from "../chartCommon.js";
import {debounce, enableTabs, submitFormAjax} from "@techins/jsutils/utils";
import {updateQueryParam} from "@techins/jsutils/url";
import {Modal} from "bootstrap";
import {errorFormHandle, initDatePicker} from "./common.js";

import AirDatepicker from "air-datepicker";
import el from 'air-datepicker/locale/el';
import 'air-datepicker/air-datepicker.css';

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

    const errorWrapper = document.getElementById("addBussinessErrorWrapper");

    const modalWrapper = document.getElementById('createBusiness');
    let datepicker = null;

    const form = document.getElementById("addBusinessForm")

    modalWrapper.addEventListener('shown.bs.modal',()=>{
        datepicker = initDatePicker();
        form.reset()
    })

    modalWrapper.addEventListener("hidden.bs.modal",()=>{
        console.log("Closing Modal")
        if(datepicker!=null){
            datepicker.destroy();
            datepicker=null;
        }

        form.reset()
    })


   form.addEventListener('submit',function (e){
        e.preventDefault()
        const form = e.target

        submitFormAjax(form,(data)=>{
            const wrapper = document.createElement('template')
            wrapper.innerHTML=data

            document.getElementById("businessTable").querySelector('tbody').prepend(wrapper.content)
            closeAddBusinessModal()
        },(xhr)=>{
            errorFormHandle(xhr,errorWrapper)
        })
    })
});

