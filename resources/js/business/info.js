import $ from "jquery";
import {submitFormAjax,boolInputUponCheckboxCheckedStatus} from "@techins/jsutils/utils";

function formSubmitSuccess(data){
    console.log(data)
}

function formSubmitFail(){
    alert("FAIL");
}
$(document).ready(function () {
    boolInputUponCheckboxCheckedStatus('active');

    $("#infoForm").on('submit',function (e){
        e.preventDefault();
        const form = e.target
        submitFormAjax(form,formSubmitSuccess,formSubmitFail)
    })
})
