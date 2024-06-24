import {addInputErrorMsg} from "@techins/jsutils/input-error";
import {stringToDomHtml} from "@techins/jsutils/utils";

function errorFormHandle(xhr,msgContainer){
    const responseJson = JSON.parse(xhr.responseText)['msg']

    if(xhr.status == 400){
        Object.keys(responseJson).forEach((key)=>{
            const msg = responseJson[key].join("<br>")
            addInputErrorMsg(document.querySelector(`input[name=${key}]`),msg)
        })
        createAlert(msgContainer,"Προέκυψε σφάλμα",false)
        return
    }
    createAlert(msgContainer,responseJson??"Αδυναμία Αποθήκευσης",false)
}

function createAlert(msgContainer, msg,success=true){
    const alert = document.createElement("div")
    alert.className = success?"alert alert-success":"alert alert-danger"
    alert.innerText = msg

    msgContainer = stringToDomHtml(msgContainer);
    msgContainer.innerHTML=alert.outerHTML
}

export {
    errorFormHandle,
    createAlert
}
