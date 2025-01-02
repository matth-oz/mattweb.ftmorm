// settings.js
function OnOrmClassSFieldsTypesEdit(arParams){

    window.ormFilterHtmlEditor = new FilterFiedsTypesEditor(arParams);

    console.log('arParams = ');
    console.log(arParams);


    BX.loadCSS([
        '/local/components/ftmorm/ftmorm.filter/settings/settings.css',
    ]);
}

function FilterFiedsTypesEditor(arParams){
    let that = this;

    this.arParams = arParams;

    let arJsOptionsData = JSON.parse(arParams.propertyParams.DEFAULT);

    if (arJsOptionsData === null || this.arParams.data === ''){
        this.arParams.oInput.value = '';

        // нет данных для создания формы
        let errBlockHtml = `<div class="err-block">Поля ORM-класса не выбраны</div>`;
        this.arParams.oCont.innerHTML = errBlockHtml;
    }
    else{


        this.jsOptions = this.arParams.data.split('||');
        
        this.jsDataDefault = arJsOptionsData[0];
        this.ormFieldsTitles = arJsOptionsData[1];

        console.log(typeof(JSON.parse(this.arParams.propertyParams.CUR_VALUES)));
        this.ormFieldsCurVals = JSON.parse(this.arParams.propertyParams.CUR_VALUES);


        let paramsForm = document.createElement('form');
        paramsForm.id = 'orm_fields_form';
        
        let errBlock = document.createElement('div');
        errBlock.id = 'err_list';
        paramsForm.append(errBlock);

        this.jsOptions.forEach(function(el){
            let curFrmItem = that.createFrmItem(el);

            paramsForm.append(curFrmItem);
        });

        let submButton = document.createElement('input');
        submButton.name = 'saveParams';
        submButton.type = 'submit';
        submButton.value = 'Сохранить поля';

        paramsForm.append(submButton);

        this.arParams.oCont.append(paramsForm);

        paramsForm.onsubmit = BX.delegate(this.prmsFrmSubmit, this);

        let selects = document.querySelectorAll('#orm_fields_form select');
        selects.forEach(sel => {
            sel.addEventListener('change', (e) => {
                if(sel.value !== 0){
                    let errBlockEl = document.querySelector('#err_list');
                    errBlockEl.innerHTML = '';
                }
            });

        });
    }

}

FilterFiedsTypesEditor.prototype.createFrmItem = function(itemKey){
    let itemWrap = document.createElement('label');
    itemWrap.className = 'frm-item';
    let itemHtml = '<span class="title">' + this.ormFieldsTitles[itemKey] + ':</span>';
    
    itemHtml += '<select name="' + itemKey + '">';
    itemHtml += '<option value="0">Выберите тип</option>';
    
    let curItem = this.jsDataDefault[itemKey];

    Object.keys(curItem).forEach(key => {
        itemHtml += '<option value="' + key + '"';

        if(this.ormFieldsCurVals !== null && itemKey in this.ormFieldsCurVals){
            savedVal = this.ormFieldsCurVals[itemKey];
            if(savedVal == key) itemHtml += ' selected="selected" ';
        }
        
        itemHtml += '>' + curItem[key].TITLE + '</option>';
    });

    itemHtml += '</select>';
    
    itemWrap.innerHTML = itemHtml;

    return itemWrap;
}

FilterFiedsTypesEditor.prototype.prmsFrmSubmit = function(){
    let ormFieldsForm = document.querySelector('#orm_fields_form');
    let errBlockEl = document.querySelector('#err_list');

    let frmData = new FormData(ormFieldsForm);
    let isValid = true;

    let curParamsData = {};

    for (const [key, value] of frmData) {

        if(value == 0){
            isValid = false;
            errBlockEl.innerHTML = `Все поля должны быть заполены. Не заполнено поле <b>${this.ormFieldsTitles[key]}</b>`;
            break;
        }

        curParamsData[key] = value;

    }

    console.log(curParamsData);

    let strData = JSON.stringify(curParamsData);

    console.log(strData);

    this.arParams.oInput.value = strData;

    return false;
}