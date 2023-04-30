//Variáveis Globais
var mySidebar;
var overlayBg;
var dropArea;
var filesDone = 0;
var filesToDo = 0;
var progressBar;
var uploadProgress = [];
var imagem_consumo;
var fileElem;
var label_button;
var form;
var div_graficos = [];

function objeto_existe(objeto) {
  if (objeto != 'undefined' && objeto != null) {
      return true;
  }
  return false;
}

function onload_w3c() {
    mySidebar = document.getElementById("mySidebar");
    overlayBg = document.getElementById("myOverlay");
    dropArea = document.getElementById("drop-area");
    imagem_consumo = document.getElementById("imagem_consumo");
    fileElem = document.getElementById("fileElem");
    label_button = document.getElementById("label_button");
    progressBar = document.getElementById("progress-bar");
    form = document.getElementById("form_dados");
    div_graficos = document.getElementsByName("div_graficos");

    carrega_pagina();

    let signout_button = document.getElementById("signout_button");
    if (objeto_existe(signout_button)) {
      signout_button.onclick = () => {
        google.accounts.id.disableAutoSelect();
      }
  }
 
  if (objeto_existe(form)) {
    carrega_valida_form(form);
  }

  if (objeto_existe(div_graficos[0])) {
    google.charts.load("current", {packages:["corechart"]});
    Array.from(div_graficos).forEach(
      (elemento) => {
          window.addEventListener("resize",
            debounce(
              (event) => 
              {
                draw_chart(elemento);
              }
          ,300));
          pega_dados_charts(elemento);
      }); 
  }
}

function w3_open() {
  if (mySidebar.style.display === 'block') {
    mySidebar.style.display = 'none';
    overlayBg.style.display = "none";
  } else {
    mySidebar.style.display = 'block';
    overlayBg.style.display = "block";
  }
}

function w3_close() {
  mySidebar.style.display = "none";
  overlayBg.style.display = "none";
}

function magnify(imgID, zoom) {
  var img, glass, w, h, bw;
  img = document.getElementById(imgID);
  glass = document.getElementById("magnifier_glass");
  /*create magnifier glass:*/
  if (objeto_existe(glass) ) {
    glass.remove();
  }
  glass = document.createElement("DIV");
  glass.id = "magnifier_glass";
  glass.setAttribute("class", "img-magnifier-glass");
  /*insert magnifier glass:*/
  img.parentElement.insertBefore(glass, img);
  /*set background properties for the magnifier glass:*/
  glass.style.backgroundImage = "url('" + img.src + "')";
  glass.style.backgroundRepeat = "no-repeat";
  glass.style.backgroundSize = (img.width * zoom) + "px " + (img.height * zoom) + "px";
  bw = 3;
  w = glass.offsetWidth / 2;
  h = glass.offsetHeight / 2;
  /*execute a function when someone moves the magnifier glass over the image:*/
  glass.addEventListener("mousemove", moveMagnifier);
  img.addEventListener("mousemove", moveMagnifier);
  /*and also for touch screens:*/
  glass.addEventListener("touchmove", moveMagnifier);
  img.addEventListener("touchmove", moveMagnifier);
  
  function moveMagnifier(e) {
    var pos, x, y;
    /*prevent any other actions that may occur when moving over the image*/
    e.preventDefault();
    /*get the cursor's x and y positions:*/
    pos = getCursorPos(e);
    x = pos.x;
    y = pos.y;
    glass.style.visibility = 'visible';
    /*prevent the magnifier glass from being positioned outside the image:*/
    if (x > img.width - (w / zoom)) {x = img.width - (w / zoom); glass.style.visibility = 'hidden';}
    if (x < w / zoom) {x = w / zoom; glass.style.visibility = 'hidden';}
    if (y > img.height - (h / zoom)) {y = img.height - (h / zoom); glass.style.visibility = 'hidden';}
    if (y < h / zoom) {y = h / zoom; glass.style.visibility = 'hidden';}
    /*set the position of the magnifier glass:*/
    glass.style.left = (x - w) + "px";
    glass.style.top = (y - h) + "px";
    /*display what the magnifier glass "sees":*/
    glass.style.backgroundPosition = "-" + ((x * zoom) - w + bw) + "px -" + ((y * zoom) - h + bw) + "px";
    
  }
  
  function getCursorPos(e) {
    var a, x = 0, y = 0;
    e = e || window.event;
    /*get the x and y positions of the image:*/
    a = img.getBoundingClientRect();
    /*calculate the cursor's x and y coordinates, relative to the image:*/
    x = e.pageX - a.left;
    y = e.pageY - a.top;
    /*consider any page scrolling:*/
    x = x - window.pageXOffset;
    y = y - window.pageYOffset;
    return {x : x, y : y};
  }
}

function debounce(func, timeout = 1000){
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => { func.apply(this, args); }, timeout);
  };
}