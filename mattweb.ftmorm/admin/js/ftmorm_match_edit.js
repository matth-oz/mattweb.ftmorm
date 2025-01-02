class matchEditForm{
    constructor(o){

        const MAX_ROWS_COUNT = 25;

        this.params = {            
            creationTimes: 0,
            rootElementId: o.rootElementId,
            addRowBtnSelector: o.addRowBtnSelector,
            detailsTableSelector: o.detailsTableSelector,
            playersRowsCount: o.playersRowsCount,
            MESS: o.MESSAGES,
        }

        this.counter = 2;

        if(this.params.playersRowsCount < MAX_ROWS_COUNT){
            this.params.creationTimes = MAX_ROWS_COUNT - this.params.playersRowsCount;
            // this.counter = this.params.playersRowsCount + 1;
        }

        this.init();
    }

    createRow(cn = 1){

        let mess = this.params.MESS;

        let rowTmpl = `<tr>
                            <td style="vertical-align: middle; padding: 2px 5px;">
                                <input type="text" name="PLAYER_ID[${cn}]" id="PLAYER_ID[${cn}]" value="" />
                                <input type="button" title="${mess.FTMORM_ADMIN_PLAYER_ENTITY_CHOOSE}" onclick="jsUtils.OpenWindow('/bitrix/admin/ftmorm_player_search.php?fid=PLAYER_ID[${cn}]&sid=PLAYER_NAME[${cn}]', 900, 700);" name="PLAYER_ID_${cn}_BTN" id="PLAYER_ID_${cn}_BTN" value="...">
                                <span id="PLAYER_NAME[${cn}]" style="display: block;"></span>
                            </td>
                            <td style="vertical-align: top; padding: 5px;">
                                <select name="START[${cn}]">
                                    <option value="0">--</option>
                                    <option value="B">${mess.BASE_PLAYER_TITLE}</option> <!--regular player-->
                                    <option value="S">${mess.SPARE_PLAYER_TITLE}</option> <!--spare player-->
                                </select>
                            </td>
                            <td style="vertical-align: top; padding: 5px;"><input type="text" size="5" name="TIME_IN[${cn}]" value="" /></td>
                            <td style="vertical-align: top; padding: 5px;"><input type="text" size="5" name="GOALS[${cn}]" value="" /></td>
                            <td style="vertical-align: top; padding: 5px;">
                                <select name="CARDS[n1]">
                                    <option value="">--</option>
                                    <option value="Y">${mess.CARD_Y_TITLE}</option>
                                    <option value="Y2">${mess.CARD_Y2_TITLE}</option>
                                    <option value="R">${mess.CARD_R_TITLE}</option>
                                </select>                        
                            </td>
                        </tr>`;
        return rowTmpl;
    }

    init(){
        const matchForm = document.getElementById(this.params.rootElementId);
        const detailsTable = document.querySelector(this.params.detailsTableSelector);
        const addRowBtn = document.querySelector(this.params.addRowBtnSelector);

        if(!addRowBtn) return;

        addRowBtn.addEventListener('click', (e) => {
            if(this.counter < this.params.creationTimes){
                let cNum = 'n' + this.counter;

            
                // добавляем новые строки с полями в таблицу формы
                let newRow = this.createRow(cNum);

                detailsTable.insertAdjacentHTML('beforeend', newRow);

                this.counter++;
            }
            console.log(this.counter);
            console.log(this.params.creationTimes);
        });
    }


}