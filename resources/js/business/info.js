import $ from "jquery";
import {submitFormAjax,boolInputUponCheckboxCheckedStatus,enableTabs} from "@techins/jsutils/utils";
import {addInputErrorMsg} from "@techins/jsutils/input-error";

import AirDatepicker from "air-datepicker";
import el from 'air-datepicker/locale/el';
import 'air-datepicker/air-datepicker.css';

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
    console.log("Hello");
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
})
