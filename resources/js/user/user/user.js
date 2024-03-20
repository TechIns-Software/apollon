import $ from "jquery"
import {checkFieldHavingSameValue, formEnable, toogleEyePasword} from "@techins/jsutils/utils";
import {addInputErrorMsg} from "@techins/jsutils/input-error";


window.toogleEyePasword=toogleEyePasword;

function checkIfPasswordSame(e){

    const passwordInput = e.target

    const passwordRepeat = passwordInput.name !== 'password'? document.querySelector(`input[name='password']`):document.querySelector(`input[name='password_confirmation']`);
    const form = passwordInput.form;

    if(!checkFieldHavingSameValue(passwordInput,passwordRepeat)){
        formEnable(form,false)
        addInputErrorMsg(passwordInput,"O κωδικός δεν είναι ο ίδιος")
    } else {
        formCheck(e);
    }
}

function formCheck(){
    const form = document.getElementById('userForm')

    let valid = form.checkValidity();

    if(!valid){
        event.preventDefault()
        event.stopPropagation()
    }

    formEnable(form,valid)

    form.classList.add('was-validated');
    return valid;
}

$(document).ready(function (){
    const passwordInput = document.querySelector(`input[name='password']`);
    const passwordRepeat = document.querySelector(`input[name='password_confirmation']`)

    passwordInput.addEventListener('change',checkIfPasswordSame);
    passwordRepeat.addEventListener('change',checkIfPasswordSame);

    document.getElementById("userForm").addEventListener('submit',formCheck)

    const form = document.getElementById('userForm')
    form.querySelectorAll('input:not([type="password"]):not([type="hidden"])').forEach(input=>{
       input.addEventListener('change',formCheck);
    });
})
