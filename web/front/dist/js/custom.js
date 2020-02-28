function showMenu() {
    document.getElementsByClassName('menu-hamb')[0].classList.toggle('close-hamb');
    document.getElementById('menuMobileItems').classList.toggle('translate');
}

function showAgregar(e) {
    e.parentNode.parentElement.classList.add('hover');
}

function hideAgregar(e) {
    e.parentNode.parentElement.classList.remove("hover");
}

function toggleMenuNav(){
    document.getElementById('closeMenuNav').classList.toggle('rotate-180');
    document.getElementById('temas').classList.toggle('showMenuNav');
}

function showInt() {
    document.getElementById('interactivo').classList.add('showInt');
    document.body.style.overflowY = "hidden";
    document.getElementsByClassName('ctrl-header')[0].classList.add('show');
    document.getElementsByClassName('ctrl-header')[1].classList.add('show');

}
function hideInt() {
    document.getElementsByClassName('ctrl-header')[0].classList.remove('show');
    document.getElementsByClassName('ctrl-header')[1].classList.remove('show');
    document.getElementById('interactivo').classList.remove('showInt');
    document.body.style.overflowY = "auto";
}

let i = 2
let int = document.getElementById('intFrame')
let countInt = document.getElementById('countInt')

function nextInt() {
    i++
    let loc = `https://recursos2puntocero.com/recursos/EA/INT/L1U3/recurso0${i}/`
    int.setAttribute('src', loc);
    countInt.textContent=`${i-1}/5`;

}

function prevInt() {
    i--
    let loc = `https://recursos2puntocero.com/recursos/EA/INT/L1U3/recurso0${i}/`
    int.setAttribute('src', loc);
    countInt.textContent=`${i-1}/5`;
}

function showPass(e) {

    let $input = document.getElementById(e.dataset.tipo)
    let eb = window.location.origin + '/img/eye-blocked.svg'

    if (e.src === eb){
        e.src="img/eye.svg";
        $input.type="password"
    } else {
        e.src="img/eye-blocked.svg";
        $input.type="text"
    }

   
}

function passDis() {
 
    document.getElementById("passLoginA").classList.toggle("input-disabled")
    document.getElementById("passLoginB").classList.toggle("input-disabled")
    document.getElementById("imgPassA").classList.toggle("img-disabled")
    document.getElementById("imgPassB").classList.toggle("img-disabled")
}

let showing = false;

function helper () {
    showing = true;
    document.getElementById('help').classList.toggle('showgif')
    setTimeout(function() {
        document.getElementById('iconHelper').classList.toggle('hiden')
    }, 100);
    setTimeout(function() {
        document.getElementById('helpergif').classList.toggle('shown')
    }, 100);
    setTimeout(function() {
        document.getElementById('helperclose').classList.toggle('shown')
    }, 100);
    
 if (showing) {

     setTimeout(function(){
         document.getElementById('help').classList.remove('showgif')
         setTimeout(function() {
             document.getElementById('iconHelper').classList.remove('hiden')
            }, 100);
            setTimeout(function() {
                document.getElementById('helpergif').classList.remove('shown')
            }, 100);
            setTimeout(function() {
                document.getElementById('helperclose').classList.remove('shown')
            }, 100);
        },10000)

        showing = false;
      
    }
}

function isHover(e){
    if (e == 1){
        document.getElementById('iconAlumno').classList.add('bright')
    } 
    else {
        document.getElementById('iconProfe').classList.add('bright')
    }
}

function isNotHover(e){
    if (e == 1){
        document.getElementById('iconAlumno').classList.remove('bright')
    } 
    else {
        document.getElementById('iconProfe').classList.remove('bright')
    }
}

    

    

