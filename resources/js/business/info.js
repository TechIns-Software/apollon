import $ from "jquery";
import 'jscroll';

import { Modal } from "bootstrap";
import {submitFormAjax,boolInputUponCheckboxCheckedStatus,enableTabs,debounce} from "@techins/jsutils/utils";
import {addInputErrorMsg} from "@techins/jsutils/input-error";
import {toggleVisibilityBetween2Elements} from "@techins/jsutils/visibility";

import AirDatepicker from "air-datepicker";
import el from 'air-datepicker/locale/el';
import 'air-datepicker/air-datepicker.css';
import tab from "bootstrap/js/src/tab.js";

function createAlert(msg,success=true){
    const alert = document.createElement("div")
    alert.className = success?"alert alert-success":"alert alert-danger"
    alert.innerText = msg
    const msgContainer = document.getElementById("msg");
    msgContainer.innerHTML=alert.outerHTML
}
function formSubmitSuccess(data){
    createAlert("Επιτυχής αποθήκευση")
}

function formSubmitFail(xhr){
    const responseJson = JSON.parse(xhr.responseText)['msg']

    if(xhr.status == 400){
        Object.keys(responseJson).forEach((key)=>{
            const msg = responseJson[key].join("<br>")
            addInputErrorMsg(document.querySelector(`input[name=${key}]`),msg)
        })
        createAlert("Προέκυψε σφάλμα",false)
        return
    }

    createAlert(responseJson??"Αδυναμία Αποθήκευσης",false)
}

function resetProductAddModal() {

    const modal = Modal.getInstance(document.getElementById('createProduct'))
    modal.hide()

    const inputElement = document.querySelector("#createProductForm").querySelector(" input[name='name']");
    inputElement.value="";
}

let prevAjax=null

function handleSearch(){
    const searchForm = document.getElementById("productSearchform");

    prevAjax=submitFormAjax(searchForm, (data) => {
        const table = document.getElementById("productListTable").querySelector("tbody")
        table.innerHTML=data;
    }, (xhr)=>{

    },null,prevAjax);
}

$(document).ready(function () {

    enableTabs(document.getElementById("myTab"),"#home-tab-pane")

    boolInputUponCheckboxCheckedStatus('active');
    new AirDatepicker("#expiration_date",{
        locale: el,
        dateFormat: "yyyy-MM-dd"
    });

    $("#infoForm").on('submit',function (e){
        e.preventDefault();
        const form = e.target
        submitFormAjax(form,formSubmitSuccess,formSubmitFail)
    })

    $(".toggle-visibility").on('click',function (e){
        e.preventDefault();
        const target = this;

        toggleVisibilityBetween2Elements(target.hash,target.dataset.hide);
    })

    $(".productEditForm").on('submit',function (e){
        e.preventDefault();
        e.stopPropagation();

        const form=this;

        submitFormAjax(form,(data)=>{
            const elementToPlaceNewValue = document.getElementById(form.dataset.success);
            // Frontend sends only 1 items to edit, despite endpoint supports mass editing.
            // Thus only One item will be returned as response and always will be the first one.
            elementToPlaceNewValue.innerHTML = data[0].name
            toggleVisibilityBetween2Elements(this,form.dataset.success);
            createAlert("Επιτυχής αποθήκευση")
        },formSubmitFail);
    });


    $("#createProductForm").on('submit',function (e) {
        e.preventDefault();

        const form = this;
        submitFormAjax(form, (data) => {
            const tableBody=document.getElementById("productListTable").querySelector("tbody");
            const element = document.createElement("template")
            element.innerHTML=data

            tableBody.prepend(element.content.firstChild)
            createAlert("Επιτυχής αποθήκευση")

            resetProductAddModal()
        }, (xhr)=>{
            formSubmitFail(xhr)
            resetProductAddModal()
        });
    })

    $("#productScroll").jscroll( {
        loadingHtml: '<tr>' +
            '<td colspan="2" class="text-center"><i class="fa-solid fa-circle-notch fa-spin"></i></td>'+
            '</tr>',
        nextSelector: 'a.jscroll-next:last',
        contentSelector: '#productScroll.tbody',}
    );

    const searchForm = document.getElementById("productSearchform");

    $("#productSearchform").on('submit',function (e){
        e.preventDefault();
        e.stopPropagation();

        handleSearch();
    });

    $("#inputSearchField").on('change',debounce(()=>{
        handleSearch();
    }));
})
