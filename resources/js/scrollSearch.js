import $ from "jquery";
import {stringToDomHtml} from "@techins/jsutils/utils";
import th from "air-datepicker/locale/th";

class ScrollTable {
    constructor(scrollWrapper,scrollAjaxErrorCallback) {

        this.__scrollWrapper = stringToDomHtml(scrollWrapper)
        this.__dataContainer = this.__scrollWrapper.querySelector("tbody");
        this.__triggerElement = 'tr:last-child';



        if(typeof scrollAjaxErrorCallback === "function"){
            this.scrollAjaxErrorCallback=scrollAjaxErrorCallback;
        } else {
            this.scrollAjaxErrorCallback=()=>{}
        }

        this.__observer =new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                console.log(entry)
                if (entry.isIntersecting) {
                    this.__ajaxUpdateData(); // Your function to append more data
                }
            });
        });

        this.__observe()
    }

    __observe()
    {
        this.__observer.observe(this.__dataContainer.querySelector(this.__triggerElement));
    }

    __ajaxUpdateData(){
        console.log("Here")
        const url =this.__scrollWrapper.getAttribute("data-url")
        console.log(url);
        if(!url){
            return;
        }

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data, textStatus, jqXHR){
                const url = jqXHR.getResponseHeader('X-NextUrl');
                const hasMore = jqXHR.getResponseHeader('X-HasMore');
                this.populateData(data,url,hasMore)
            }.bind(this),
            error:this.scrollAjaxErrorCallback
        });
    }


    populateData(data,url,hasMore){
        if(!url){
            throw new Error("Url has not Been provided")
        }

        if(hasMore){
            this.__scrollWrapper.setAttribute('data-url',url)
        } else {
            this.__scrollWrapper.removeAttribute('data-url');
        }

        this.__dataContainer.innerHTML = this.__dataContainer.innerHTML+data;
        this.__observe();
    }

    overWriteData(data,url){
        this.__dataContainer.innerHTML = data
        this.__observe();
    }
}


export {ScrollTable}
