window.MatchList.params.pagerLinkSelector = '.j-bx-pag a';

window.addEventListener('load', function(){

    const trWrap = document.querySelector(window.MatchList.params.dataTbodySelector);
    const navWrap = document.querySelector(window.MatchList.params.navWrapSelector); 
    
    document.addEventListener('click', (e)=>{
        console.log(e.target);   
        
        let cl = e.target.classList;       

        if(cl.contains('j-navlink')){

            console.log(cl.contains('j-navlink'));

            let resObj = window.MatchList.getPageNav(e.target);

            resObj.then(
                response => {                    
                    trWrap.innerHTML = '';
                    trWrap.innerHTML = response.MATCHES_HTML;
                    navWrap.innerHTML = response.NAV_STRING;
                },
                error =>{
                    console.log(error);
                }
            );

            e.preventDefault();
        }
    });
});

//window.onload = function(){}