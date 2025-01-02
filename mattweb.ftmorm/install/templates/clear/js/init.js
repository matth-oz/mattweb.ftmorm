window.MatchList = window.MatchList || {};

/* параметры */
let params = {
    dataTbodySelector: '.j-main-tbl tbody',
    navWrapSelector: '.j-nav-wrap',

    formSelector: '.j-filter-form',
    citySelSelector: 'match_city',
    gameDstartSelector: 'game_dstart',
    gameDfinishSelector: 'game_dfinish',

    sortBtnSelector: '.j-sort-btn',
}

/* получение результатов постраничной навигации */
async function getPageNav(pnLink){
    let linkValNew = pnLink.getAttribute('href');
    let arLink = linkValNew.split('?');

    let linkValAjax = arLink[0] + 'ajax.php';

    if(typeof arLink[1] !== 'undefined'){
        linkValAjax += '?' + arLink[1];
    }
    
    let response = await fetch(linkValAjax);
    window.MatchList.updateURL(linkValNew);

    return await response.json();        
}

/* устанавливаем значение сортировки в COOKIE */
function setSortCookie(sortParams){
      
    let sort = sortParams.sort;
    let ord = sortParams.ord;

    if(typeof sortParams.clear === 'undefined'){
        let date = new Date(Date.now() + 2592000e3);
        date = date.toUTCString();        
        document.cookie = sortParams.sortCookieParam + '=' + sort + '; path=/; domain='+ window.location.href.split('/')[2] +'; expires=' + date;
        document.cookie = sortParams.ordCookieParam + '=' + ord + '; path=/; domain='+ window.location.href.split('/')[2] +'; expires=' + date;
    }
    else{
        document.cookie = sortParams.sortCookieParam + '=' + sort + '; path=/; domain='+ window.location.href.split('/')[2] +'; max-age=0';
        document.cookie = sortParams.ordCookieParam + '=' + ord + '; path=/; domain='+ window.location.href.split('/')[2] +'; max-age=0';
    }    
}

/* получаем значение сортировки из COOKIE по ключу */
function getSortCookie(name){
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
      ));
 
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

/* получаем информацию об URL в виде объекта */
function getURLInfo(){

    let urlObj = {};
    urlObj.lp = location.protocol;
    urlObj.lh = location.hostname;
    urlObj.lpn = location.pathname;
    urlObj.ls = location.search;
    
    return urlObj;
}

/* изменяет параметры URL после получения данных от сервера */
function updateURL(linkVal) {
    if (history.pushState) {
        var baseUrl = window.location.protocol + "//" + window.location.host;
        console.warn(linkVal);

        var newUrl = baseUrl + linkVal;
        history.pushState(null, null, newUrl);
    }
    else {
        console.warn('History API не поддерживает ваш браузер');
    }
}

/* запрос отфильтрованных данных */
async function applyFilter(form){

    // https://learn.javascript.ru/url
    // https://itchief.ru/javascript/window-location
    // https://xn----7sbbaqhlkm9ah9aiq.net/news-new/kak-izmenit-adresnuyu-stroku-brauzera-bez-perezagruzki.html

    const urlInfo = getURLInfo();

    let linkParams = '';

    let formData = new FormData(form);

    let cityVal = formData.get('match_city');
    let dateStart = formData.get('game_dstart');
    let dateFinish = formData.get('game_dfinish');

    if(cityVal !== ''){
        linkParams += '?match_city=' + cityVal;
    }

    if(dateStart !== ''){

        if(linkParams.indexOf('?') !== -1){
            linkParams += '&game_dstart=' + dateStart;
        }else{
            linkParams += '?game_dstart=' + dateStart;
        }        
    }

    if(dateFinish !== ''){
        if(linkParams.indexOf('?') !== -1){
            linkParams += '&game_dfinish=' + dateFinish;
        }else{
            linkParams += '?game_dfinish=' + dateFinish;
        }       
    }

    if(linkParams.length > 0){
        linkParams += '&filter=Filter';
    }
    
    let linkValNew = urlInfo.lpn + linkParams;
    let linkValAjax = urlInfo.lp + '//' + urlInfo.lh + '/' + urlInfo.lpn + 'ajax.php' + linkParams;

    let response = await fetch(linkValAjax);
    window.MatchList.updateURL(linkValNew);

    return await response.json();
}

/* получение отсотрированых данных */
async function applySort(params){

    const urlInfo = getURLInfo();
    let linkParamsTmp = '';
    let linkParams = '';
   
    let cookiePrefix = 'BITRIX_SM_';
    params.sortCookieParam = cookiePrefix + params.sortCookieParam;
    params.ordCookieParam = cookiePrefix + params.ordCookieParam;
    
    if(params.sort == 'clear'){
        linkParamsTmp += 'sort=' + params.sort;
        params.sort = getSortCookie(params.sortCookieParam); 
        params.ord = getSortCookie(params.ordCookieParam);
        params.clear = true; 
    }
    else{
        linkParamsTmp += 'sort=' + params.sort + '&ord=' + params.ord;
    }
    
    // устанавливаем (перезаписываем) или удаляем cookie
    setSortCookie(params);   

    //console.log(urlInfo.ls);

    if(urlInfo.ls === ''){
        // если нет строки параметров - просто добавляем
        linkParams = '?' + linkParamsTmp;
    }
    else{
        // ищем в строке sort и/или sort и ord
        // если нет - добавляем
        if(urlInfo.ls.indexOf('sort') == -1){
            linkParams = urlInfo.ls + '&' + linkParamsTmp;
        }
        else{
            // если есть - заменяем значения
            let pattern = /sort=(game_date|gm_city|tm_name)&ord=(asc|desc)/i;
            let res = pattern.exec(urlInfo.ls);
          
            if(res !== null){
                linkParams = urlInfo.ls;
                linkParams = linkParams.replace(res[0], linkParamsTmp);
            }
        }
    }

    // отправляем на сервер
    //?sort=game_date&ord=asc
    let linkValNew = urlInfo.lpn + linkParams;
    let linkValAjax = urlInfo.lp + '//' + urlInfo.lh + '/' + urlInfo.lpn + 'ajax.php' + linkParams;

    let response = await fetch(linkValAjax);

    window.MatchList.updateURL(linkValNew);

    return await response.json();
}

window.MatchList.params = params;
window.MatchList.updateURL = updateURL;
window.MatchList.getPageNav = getPageNav;
window.MatchList.applyFilter = applyFilter;
window.MatchList.applySort = applySort;

