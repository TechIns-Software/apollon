import $ from "jquery";
import {toogleEyePasword, checkFieldHavingSameValue, formEnable, resetFormFeedback} from "@techins/jsutils/utils";
import {addInputErrorMsg} from "@techins/jsutils/input-error";

window.toogleEyePasword=toogleEyePasword;

function checkIfPasswordSame(e){

    const passwordInput = e.target

    const passwordRepeat = passwordInput.name !== 'password'? document.querySelector(`input[name='password']`):document.querySelector(`input[name='password_confirmation']`);
    const form = passwordInput.form;
    resetFormFeedback(form)
    if(!checkFieldHavingSameValue(passwordInput,passwordRepeat)){
        formEnable(form,false)
        addInputErrorMsg(passwordInput,"O κωδικός δεν είναι ο ίδιος")
    } else {
        formEnable(form,true)
    }
}
 $(document).ready(function(){
     const passwordInput = document.querySelector(`input[name='password']`);
     const passwordRepeat = document.querySelector(`input[name='password_confirmation']`)

     passwordInput.addEventListener('change',checkIfPasswordSame);
     passwordRepeat.addEventListener('change',checkIfPasswordSame);
 });
