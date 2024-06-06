import $ from "jquery";
import {submitFormAjax,boolInputUponCheckboxCheckedStatus} from "@techins/jsutils/utils";
import {addInputErrorMsg} from "@techins/jsutils/input-error";

function createAlert(msg,success=true){
    const alert = document.createElement("div")
    alert.className = success?"alert alert-success":"alert alert-danger"
    alert.innerText = msg

    $("#msg").append(alert)
}
function formSubmitSuccess(data){
    console.log(data)
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
    boolInputUponCheckboxCheckedStatus('active');

    $("#infoForm").on('submit',function (e){
        e.preventDefault();
        const form = e.target
        submitFormAjax(form,formSubmitSuccess,formSubmitFail)
    })
})
