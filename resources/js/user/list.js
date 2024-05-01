import {onChangeSubmitForm,replaceCurrentUrlQueryWithFormInput} from "@techins/jsutils/utils";
import $ from "jquery";
import * as bootstrap from 'bootstrap';


const spinner=`
<div id="spinner" style="z-index:999999;" class="w-100 d-flex flex-column justify-content-center text-center">
            <h4>Φόρτωση Δεδομένων</h4>
            <div class="w-100 d-flex justify-content-center">
                <i class="fa-solid fa-circle-notch fa-spin"></i>
            </div>
        </div>
`

let table=null

function ajaxSuccess(data){

    const container = document.getElementById("userTableContainer");
    container.innerHTML=data;
    table=data;

    const form = document.getElementById("searchform");
    replaceCurrentUrlQueryWithFormInput(form);
}

function ajaxFail(){

    if(table){
        const container = document.getElementById("userTableContainer");
        container.innerHTML=table.outerHTML
    }

    $(container).prepend("<div class=\"alert alert-danger\" role=\"alert\">An error has occured</div>")
}

function beforeSend() {
    const container = document.getElementById("userTableContainer");
    table = container.querySelector('table');
    container.innerHTML=spinner;

    hideReset();
}

function hideReset()
{
    const form = document.getElementById("searchform");
    const search = form.querySelector(`input[name="search"]`).value.trim()
    if(search == ''){
        $("#resetForm").hide()
    } else {
        $("#resetForm").show()
    }
}

$(document).ready(function (){
    onChangeSubmitForm(document.getElementById("searchform"),ajaxSuccess,ajaxFail,beforeSend)

    $(`.dropdown-menu input[type='radio']`).on('change',function (e){
        const target = e.target;
        const parentString = target.parentElement.innerText.trim()
        const button = target.closest('.dropdown-menu')
            .parentElement
            .querySelector('button');

        button.innerText = parentString;
    })

    hideReset()
})
