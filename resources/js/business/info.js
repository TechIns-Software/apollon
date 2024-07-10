import $ from "jquery";
import 'jscroll';

import { Modal } from "bootstrap";

import {submitFormAjax,boolInputUponCheckboxCheckedStatus,enableTabs,prependHtmlRowIntoATable} from "@techins/jsutils/utils";
import {toggleVisibilityBetween2Elements} from "@techins/jsutils/visibility";
import {clearInputErrorMessage, errorResponseHandler,} from "@techins/jsutils/input-error";
import SearchForm from "@techins/jsutils/searchForm";

import {bootstrapYearMonthChart} from '../chartCommon.js';
import {errorFormHandle, createAlert as mkAlert, initDatePicker} from "./common.js";

function createAlert(msg,success){
    const msgContainer = document.getElementById("msg");
    mkAlert(msgContainer,msg,success);
}

function formSubmitFail(xhr){
    const msgContainer = document.getElementById("msg");
    errorFormHandle(xhr,msgContainer)
}

function resetProductAddModal() {

    const modal = Modal.getInstance(document.getElementById('createProduct'))
    modal.hide()

    const inputElement = document.querySelector("#createProductForm").querySelector(" input[name='name']");
    inputElement.value="";
}

function resetUserModal(){
    const modalElem = document.getElementById('createUser');
    const modal = Modal.getInstance(modalElem)
    modal.hide()

    const form = modalElem.querySelector('form');
    resetUserForm(form);
}

function resetUserForm(form){
    form.reset();
    form.querySelectorAll('input').forEach((item)=>{
        console.log("Before Send",item)
        clearInputErrorMessage(item)
    });
}

$(document).ready(function () {

    enableTabs(document.getElementById("myTab"),"#home-tab-pane")

    boolInputUponCheckboxCheckedStatus('active');
    initDatePicker();

    $("#infoForm").on('submit',function (e){
        e.preventDefault();
        const form = e.target
        submitFormAjax(form,(data)=>{
            createAlert("Επιτυχής αποθήκευση")
        },formSubmitFail)
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

            prependHtmlRowIntoATable("productListTable",data)
            createAlert("Επιτυχής αποθήκευση")

            resetProductAddModal()
        }, (xhr)=>{
            formSubmitFail(xhr)
            resetProductAddModal()
        });
    })

    const createUserModal = document.getElementById("createUser");
    const newUserform = createUserModal.querySelector('form');

    createUserModal.addEventListener('shown.bs.modal', () => {
        resetUserForm(newUserform)
        createUserModal.focus()
    });

    newUserform.querySelector('form').addEventListener('submit',function (e){
        e.preventDefault();
        const form = this;
        submitFormAjax(form, (data) => {

            prependHtmlRowIntoATable("userListTable",data)
            createAlert("Επιτυχής αποθήκευση χρήστη")

            resetUserModal()
        }, (xhr)=>{
            errorResponseHandler(xhr,(is400,msg)=>{
                if(!is400){
                    formSubmitFail(xhr)
                    resetProductAddModal()
                }
            })
        },()=>{
            form.querySelectorAll('input').forEach((item)=>{
                console.log("Before Send",item)
                clearInputErrorMessage(item)
            });
        });
    },);



    $("#productScroll").jscroll( {
        loadingHtml: '<tr>' +
            '<td colspan="2" class="text-center"><i class="fa-solid fa-circle-notch fa-spin"></i></td>'+
            '</tr>',
        nextSelector: 'a.jscroll-next:last',
        contentSelector: '#productScroll.tbody',}
    );

    $("#userScroll").jscroll({
        loadingHtml: '<tr>' +
            '<td colspan="2" class="text-center"><i class="fa-solid fa-circle-notch fa-spin"></i></td>'+
            '</tr>',
        nextSelector: 'a.jscroll-next:last',
        contentSelector: '#userScroll.tbody'
    });

    new SearchForm("productSearchform","productListTable",()=>{})
    new SearchForm("userSearchForm","userListTable",()=>{})

    bootstrapYearMonthChart("statsForm","orderStatsWrapper");
})
