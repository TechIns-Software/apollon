import $ from "jquery";
import {debounce, stringToDomHtml, submitFormAjax} from "@techins/jsutils/utils";


class ScrollTable {
    constructor(scrollWrapper,scrollAjaxErrorCallback) {

        this.__scrollWrapper = stringToDomHtml(scrollWrapper)
        this.__dataContainer = this.__scrollWrapper.querySelector("tbody");
        this.__triggerElement = 'tr:last-child';

        this.__initialUrl = this.__scrollWrapper.getAttribute("data-url")
        this.currentAjax = null;

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

    abortRefresh(){
        if(this.currentAjax){
            this.currentAjax.abort();
        }
    }

    __observe() {
        const trigger = this.__dataContainer.querySelector(this.__triggerElement);
        if(trigger){
            this.__observer.observe(this.__dataContainer.querySelector(this.__triggerElement));
        }
    }

    __ajaxUpdateData(){
        console.log("Here")
        const url =this.__scrollWrapper.getAttribute("data-url")
        console.log(url);
        if(!url){
            return;
        }

        this.currentAjax = $.ajax({
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

        if(hasMore){
            if(!url){
                throw new Error("Url has not Been provided")
            }
            this.__scrollWrapper.setAttribute('data-url',url)
        } else {
            this.__scrollWrapper.removeAttribute('data-url');
        }

        this.__dataContainer.innerHTML = this.__dataContainer.innerHTML+data;
        this.__observe();
    }

    overWriteData(data,url){

        if(!url){
            this.__scrollWrapper.removeAttribute('data-url')
        } else {
            this.__scrollWrapper.setAttribute('data-url',url)
        }

        this.__dataContainer.innerHTML = data
        this.__observe();
    }

    resetOriginalData(){
        console.log("RESET")
        this.__scrollWrapper.setAttribute('data-url',this.__initialUrl)
        this.__dataContainer.innerHTML = ""
        this.__ajaxUpdateData();
    }
}


class GenericSearchForm
{
    constructor(form_element,successCallback,submitErrorCallback,clearValueOnInit){
        this.form = stringToDomHtml(form_element)
        this.prevAjax=null

        if(typeof submitErrorCallback === "function"){
            this.submitErrorCallback=submitErrorCallback.bind(this)
        } else {
            this.submitErrorCallback=()=>{}
        }

        if(typeof successCallback !== "function"){
            throw new Error("Success Search function Not Submitted")
        }

        this.successCallback = successCallback.bind(this)

        this.__init(clearValueOnInit)
    }

    __init(clearValueOnInit) {
        const inputSearchField = this.form.querySelector('.inputSearchField')

        // Upon refresh form stays with its old value.
        // We can configure to be removed
        if(clearValueOnInit){
            inputSearchField.value="";
        }

        this.manualSearch=()=>{
            this.__handleSearch(inputSearchField)
        }

        this.reset=()=>{
            this.__reset(inputSearchField)
        }

        this.form.addEventListener('submit',(e)=>{
            e.preventDefault();
            e.stopPropagation();
            this.__handleSearch(inputSearchField)
        })

        inputSearchField.addEventListener('change',debounce(()=>{
            this.__handleSearch(inputSearchField)
        }))

        this.form.querySelector(".cleanSearch").addEventListener('click',debounce(()=>{
           this.__reset(inputSearchField);
        }))
    }

    __reset(inputSearchField) {
        inputSearchField.value=""
        this.handleSearch()
    }

    abortSearch(){
        this.prevAjax.abortSearch();
    }

    /**
     * Handle the search action, submit the form via AJAX, and manage the results.
     */
    __handleSearch(inputSearchField){
        this.prevAjax=submitFormAjax(this.form,this.successCallback,this.submitErrorCallback,null,this.prevAjax)
    }
}

/**
 * SearchForm that populates a ScrollTable. It is a combination of a ScrollTable with SearchForm.
 */
class ScrollTableSearchForm extends GenericSearchForm
{
    constructor(form,scrollWrapper,clearFormValueOnInit,searchErrorCallback,scrollAjaxErrorCallback){
        super(form,(data,textStatus, jqXHR)=>{
            const url = jqXHR.getResponseHeader('X-NextUrl');
            console.log("SEARCH",url);
            this.scrollTable.overWriteData(data,url)
        },searchErrorCallback,clearFormValueOnInit);
        this.scrollTable = new ScrollTable(scrollWrapper,scrollAjaxErrorCallback)
    }

    handleSearch(inputSearchField) {
        this.scrollTable.abortRefresh();

        const value = inputSearchField.value
        if(value===''){
            this.abortSearch()
            return this.__reset(inputSearchField)
        }

        super.handleSearch();
    }

    __reset(inputSearchField){
        inputSearchField.value=""
        this.scrollTable.resetOriginalData();
    }
}


export {
    ScrollTable,
    ScrollTableSearchForm
}
