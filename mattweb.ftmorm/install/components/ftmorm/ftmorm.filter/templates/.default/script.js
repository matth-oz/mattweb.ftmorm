function TextFieldTooltip(params){
    let _this = this;

    this.params = {
        'AJAX_HANDLER': params.AJAX_HANDLER,
        'FLT_TEXT_FIELD_SELECTOR': params.FLT_TEXT_FIELD_SELECTOR,
        'TOOLTIPS_WRAP_SELECTOR': params.TOOLTIPS_WRAP_SELECTOR,
        'TOOLTIP_RES_CSS_CLASS': params.TOOLTIP_RES_CSS_CLASS,
        'TOOLTIP_RES_SELECTOR': params.TOOLTIP_RES_SELECTOR,
        'MIN_QUERY_LENGTH': parseInt(params.MIN_QUERY_LENGTH),
        'USE_ORM_ENTITY_ALIAS': params.USE_ORM_ENTITY_ALIAS,
        'HIDDEN_CSS_CLASS': params.HIDDEN_CSS_CLASS,
    };

    this.params.CUR_TEXT_FIELD = null;
    this.tooltipWrapIsActive = false;

    /**
     * Функция для отправки запроса серверному скрипту
     * @param {object} params
     * @return {JSON}
     */
    this.sendRequest = async function(params){        
        let response = await fetch(_this.params.AJAX_HANDLER, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify(params),
        });

        return await response.json();
    }

    this._getTooltipsWrap = function(textFld, toolTipsWrapSel){
        const textFieldParent = textFld.parentElement;                                    
        const toolTipsWrap = textFieldParent.querySelector(toolTipsWrapSel);

        return toolTipsWrap;
    }

    this._createTooltipsHtml = function(tooltipsList, textFieldId){
        let toolTipsHtml = ``;

        tooltipsList.forEach((tooltipItem)=>{
            toolTipsHtml += `<span class="tooltip-res j-tooltip-res" data-ftarget="${textFieldId}">${tooltipItem}</span>`;
        });

        return toolTipsHtml;
    }

    this._clearToolTipsWrap = function(textFld, toolTipsWrapSel, hide = false){
        
        const textFieldParent = textFld.parentElement;
        const toolTipsWrap = textFieldParent.querySelector(toolTipsWrapSel);

        toolTipsWrap.textContent = '';

        if(hide === true){
            toolTipsWrap.classList.add(_this.params.HIDDEN_CSS_CLASS);
        }

        _this.tooltipWrapIsActive = false;
    }

    this.fillTooltipWrap = function(textField){
        /** TODO:
         * Добавить возможность кеширования результатов
         * чтобы избежать лишних обращений к серверному скрипту
         */
        let paramsToSend = {
            field_value: textField.value,
            model_name: textField.dataset.model,
            model_field_name: textField.dataset.field,
            model_alias: _this.params.USE_ORM_ENTITY_ALIAS,
        }

        // Promise
        const resObj = _this.sendRequest(paramsToSend);
        resObj.then(
            success = (robj) =>{
                //console.log(robj);
                if(robj.result === 'success'){
                    let resElems = robj.elements;

                    if(resElems.length > 0){ 

                        _this._clearToolTipsWrap(textField, _this.params.TOOLTIPS_WRAP_SELECTOR);

                        const toolTipsWrap = _this._getTooltipsWrap(textField, _this.params.TOOLTIPS_WRAP_SELECTOR);

                        let toolTipsHtml = _this._createTooltipsHtml(resElems, textField.id);

                        toolTipsWrap.insertAdjacentHTML('afterbegin', toolTipsHtml);

                        if(toolTipsWrap.classList.contains(_this.params.HIDDEN_CSS_CLASS))
                            toolTipsWrap.classList.remove(_this.params.HIDDEN_CSS_CLASS);

                        _this.tooltipWrapIsActive = true;
                    }
                }
            },
            error => new Error('Response error'),
        );
    }

    this.init = function(){

        const textFields = document.querySelectorAll(_this.params.FLT_TEXT_FIELD_SELECTOR);

        textFields.forEach((textField) => {

            textField.addEventListener('focus', (e)=>{
                _this.params.CUR_TEXT_FIELD = '#' + textField.id;


                console.log(_this.tooltipWrapIsActive);
                console.log(textField.value.length);
                
                if(!_this.tooltipWrapIsActive && textField.value.length > 0){
                    _this.fillTooltipWrap(textField);
                }
            });

            textField.addEventListener('blur', (e)=>{
                console.log('blur fired');
                _this._clearToolTipsWrap(textField, _this.params.TOOLTIPS_WRAP_SELECTOR, true);
                _this.params.CUR_TEXT_FIELD = null;
            });

            textField.addEventListener('input', (e)=>{
                let curValue = textField.value;

                if(curValue.length >= _this.params.MIN_QUERY_LENGTH){
                    _this.fillTooltipWrap(textField); 
                }
                else{
                    _this._clearToolTipsWrap(textField, _this.params.TOOLTIPS_WRAP_SELECTOR, true); 
                }                
            });
        });

        /* используем mousedown, потому что click конфликтует с blur и не срабатывает вообще*/
        document.addEventListener('mousedown', (e)=>{
            let clickTarget = e.target;
            
            console.log(clickTarget);
            //console.dir(_this.params);

            if(clickTarget.classList.contains(_this.params.TOOLTIP_RES_CSS_CLASS)){

                const parentTextFieldId = clickTarget.dataset.ftarget;
                const parentTextField = document.getElementById(parentTextFieldId);
                const currentValue = clickTarget.textContent;
                parentTextField.value = currentValue;

                _this._clearToolTipsWrap(parentTextField, _this.params.TOOLTIPS_WRAP_SELECTOR, true);

                console.log(parentTextFieldId);
                //console.dir(e);
            }
        });


        
    }

    this.init();

}