window.addEventListener('load', ()=>{

    var term = document.querySelector('input[name=\"term\"]');
    if (!term) return;
    
    term.addEventListener('keyup', ()=>{

    document.querySelectorAll('table tbody tr').forEach((tr,idx)=>{
    if (term.value.trim().length == 0){
        tr.style.display = 'table-row'
    } else {
    let val = tr.innerText.toLowerCase();
    if (val.indexOf(term.value.toLowerCase()) > -1){
        tr.style.display = 'table-row'
    } else {
        tr.style.display = 'none'
    }
    }

    });
    }, false);

},false);