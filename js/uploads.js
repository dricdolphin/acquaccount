function preventDefaults (e) {
    e.preventDefault();
    e.stopPropagation();
  }
  
  function highlight(e) {
    dropArea.classList.add("highlight");
  }
  
  function unhighlight(e) {
    dropArea.classList.remove("highlight");
  } 

  function handleDrop(e) {
    let dt = e.dataTransfer;
    let files = dt.files;
  
    handleFiles(files);
  }

  function handleFiles(files) {
    files = [...files]
    files.forEach(uploadFile)
    files.forEach(previewFile)
  }

  function uploadFile(file, i) {
    let url = "processa_arquivo.php";
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    let img = document.getElementById("img_imagem_consumo");
    let imagem_consumo = document.getElementById("imagem_consumo"); //Input com o caminho da imagem
    xhr.open("POST", url, true);
  
    xhr.upload.addEventListener("progress", function(e) {
      updateProgress(i, (e.loaded * 100.0 / e.total) || 100);
    });
  
    xhr.addEventListener("readystatechange", function(e) {
      if (xhr.readyState == 4 && xhr.status == 200) {
        console.log(xhr.responseText);
        let obj = JSON.parse(xhr.responseText);
        if (objeto_existe(img)) {
            img.src = "./upload_files/" + obj.arquivo;
            img.setAttribute("data_medicao_realizada","true");
            imagem_consumo.value = img.src;
            imagem_consumo.dispatchEvent(new Event("change"));
            magnify("img_imagem_consumo", 3);
         }
      }
      else if (xhr.readyState == 4 && xhr.status != 200) {
        alert("Ocorreu um erro ao realizar o upload!");
        console.log(xhr.responseText);
        progressBar.value = 0;
        if (objeto_existe(img)) {
            img.src = "";
        }
      }
    });
    
    if (objeto_existe(img)) {
        if (img.getAttribute("data_medicao_realizada") != null) {
            formData.append("replace", img.src);
        }
    }
    
    formData.append("uploadBtn", "Upload");
    formData.append("file", file);
    xhr.send(formData);
  }

  function previewFile(file) {
    let reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onloadend = function() {
      let img = document.getElementById("img_imagem_consumo");
      img.id = "img_imagem_consumo";
      img.src = reader.result;
    }
  }

  function initializeProgress(numFiles) {
    progressBar.value = 0;
    uploadProgress = [];
  
    for(let i = numFiles; i > 0; i--) {
      uploadProgress.push(0);
    }
  }
  
  function updateProgress(fileNumber, percent) {
    uploadProgress[fileNumber] = percent;
    let total = uploadProgress.reduce((tot, curr) => tot + curr, 0) / uploadProgress.length;
    progressBar.value = total;
  }

  function progressDone() {
    filesDone++;
    progressBar.value = filesDone / filesToDo * 100;
  }