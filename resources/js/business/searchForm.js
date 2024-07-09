import {stringToDomHtml, submitFormAjax,debounce} from "@techins/jsutils/utils";

class SearchForm
{
    constructor(form_element,table_element,submitErrorCallback) {
        this.form = stringToDomHtml(form_element)
        this.table = stringToDomHtml(table_element)
        console.log(this.table)
        this.prevAjax=null
        this.submitErrorCallback=submitErrorCallback

        this.appendDataElementToTable = this.appendDataElementToTable.bind(this);
        this.handleSearch = this.handleSearch.bind(this);

        this.form.addEventListener('submit',(e)=>{
            e.preventDefault();
            e.stopPropagation();
            this.handleSearch()
        })

        const inputSearchField = this.form.querySelector('.inputSearchField')
        inputSearchField.addEventListener('change',debounce(()=>{
            this.handleSearch()
        }))

        this.form.querySelector(".cleanSearch").addEventListener('click',debounce(()=>{
            inputSearchField.value=""
            this.handleSearch()
        }))
    }

    appendDataElementToTable(data) {
        const tbody = this.table.querySelector('tbody')
        tbody.innerHTML=data
    }

    handleSearch(){
        this.prevAjax=submitFormAjax(this.form,this.appendDataElementToTable,this.submitErrorCallback,null,this.prevAjax)
    }

}

export default SearchForm
