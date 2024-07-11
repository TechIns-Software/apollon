import {errorResponseHandler} from "@techins/jsutils/input-error";
import {stringToDomHtml} from "@techins/jsutils/utils";

import AirDatepicker from "air-datepicker";
import el from 'air-datepicker/locale/el';
import 'air-datepicker/air-datepicker.css';

/**
 * A unique form submission error handler. In order for this to work the response must be a JSon containing this format:
 * {
 *    msg: string|object
 * }
 *
 * If error is 400 then msg must be an object containing:
 *
 * ^column_name^: ^message^
 *
 *
 * Upon failure is display a message
 *
 * @param xhr Ajax response
 * @param msgContainer message container
 * @param msgFor400 If true display message for error 400
 */
function errorFormHandle(xhr,msgContainer,msgFor400=true){
    errorResponseHandler(xhr,(is400,message)=>{
        if(is400){
            if(msgFor400){
                createAlert(msgContainer,"Προέκυψε σφάλμα",false)
            }
        }
        createAlert(msgContainer,message??"Αδυναμία Αποθήκευσης",false)
    })
}

function createAlert(msgContainer, msg,success=true){
    const alert = document.createElement("div")
    alert.className = success?"alert alert-success":"alert alert-danger"
    alert.innerText = msg

    msgContainer = stringToDomHtml(msgContainer);
    msgContainer.innerHTML=alert.outerHTML
}

function initDatePicker()
{
    return new AirDatepicker("#expiration_date",{
        locale: el,
        dateFormat: "yyyy-MM-dd",
        container: "#datePicker"
    });
}

export {
    errorFormHandle,
    createAlert,
    initDatePicker,
}
