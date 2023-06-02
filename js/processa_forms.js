var objeto_valida_especial = [];
var funcao_valida_especial = [];
var flag_foi_alterado = [];
var em_salvamento = false;
var flag_mostrando_caixa = false;
var id;
var id_unidade;
var id_validador;
var mes;
var ano;
var caixa_dados_salvos;

function carrega_pagina() {
    let img = document.getElementById("img_imagem_consumo");
    let imagem_consumo = document.getElementById("imagem_consumo"); //Input com o caminho da imagem
    if (objeto_existe(img)) {
        if (imagem_consumo.value != "") {
            magnify("img_imagem_consumo", 3);
        }
    }

    id = document.getElementById("id");
    mes = document.getElementById("mes");
    ano = document.getElementById("ano");
    id_unidade = document.getElementById("id_unidade");
    id_validador = document.getElementById("id_validador");
    caixa_dados_salvos = document.getElementById("caixa_dados_salvos");
    if (objeto_existe(caixa_dados_salvos)) { 
        caixa_dados_salvos.addEventListener("transitionend", (event) => { remove_caixa_mensagem(event,caixa_dados_salvos); });
    }
}

function carrega_lgpd() {
    let aceite_lgpd = document.getElementById("aceite_lgpd");

    aceite_lgpd.addEventListener("click", (event) => {
        event.preventDefault();
        return aceita_lgpd();
    });
}

function carrega_valida_form(form) { 
    let email = document.getElementById("email");
    let CPF = document.getElementById("CPF");
    let CEP = document.getElementById("CEP");
    let select_condominio = document.getElementById("id_condominio");
    let select_bloco = document.getElementById("id_bloco");
    let select_mes_ano = document.getElementById("mes_ano");
    let checkbox_validado = document.getElementById("validado");
    let valor_m3 = document.getElementById("valor_m3");
    
    let ids_unidade = document.getElementsByName("ids_unidade");
    let ids_condominio = document.getElementsByName("ids_condominio");

    let inputs = form_dados.getElementsByTagName("input");
    let valor_input = [];
    let selects = form_dados.getElementsByTagName("select");
    let anchors = document.getElementsByTagName("a");
    


    Array.from(inputs).forEach(
        (elemento) => {
            elemento.addEventListener("click",
            (event) => {
                if (em_salvamento) {
                    event.preventDefault();
                }
            });
            
            if (elemento.type == "checkbox" && elemento.id != "validado") {
                elemento.addEventListener("change", 
                    debounce(
                        (event) => 
                        {
                            if (!em_salvamento) { salva_objeto(event, elemento); }
                        }
                    ,1500)     
                );           
            }
                
            if (elemento.id == "imagem_consumo") {
                elemento.addEventListener("change", 
                    debounce(
                        (event) => 
                        {
                            if (!em_salvamento) { salva_objeto(event, elemento); }
                        }
                    ,1500)     
                );           
            }

            if ((elemento.type == "hidden" || elemento.type == "checkbox" || elemento.type == "file") 
            && !flag_foi_alterado.includes(elemento)) { 
                flag_foi_alterado.push(elemento); 
                return true;
            }

            elemento.addEventListener("keyup", 
                debounce(
                    (event) => 
                    {
                        if (!flag_foi_alterado.includes(elemento)) { flag_foi_alterado.push(elemento); }
                        if (!em_salvamento) { salva_objeto(event, elemento); }
                    }
                ,1500)
            );

            if (elemento.pattern != "") {
                elemento.addEventListener("paste", 
                (event) => {
                    event.preventDefault();
                    
                    let pasted = '';
                    if (window.clipboardData && window.clipboardData.getData) { // IE
                        pasted = window.clipboardData.getData('Text');
                    } else if (event.clipboardData && event.clipboardData.getData) {
                        pasted = event.clipboardData.getData('text/plain');
                    }
                    
                    let padrao_regex = elemento.pattern;
                    let array_pasted = [...pasted.matchAll(padrao_regex)];
                    elemento.value = array_pasted.join('');
                    if (elemento.maxLength != "") {
                        elemento.value = elemento.value.substring(0, elemento.maxLength);
                    }
                });
            }
            
            valor_input[elemento.id] = elemento.value;
            elemento.addEventListener("input", 
                (event) => {
                    if(elemento.checkValidity()){
                        valor_input[elemento.id] = elemento.value;
                    } else {
                        elemento.value = valor_input[elemento.id];
                    }
                });
        }
    );

    Array.from(selects).forEach(
        (elemento) => {
            elemento.addEventListener("mousedown",
            (event) => {
                if (em_salvamento) {
                    event.preventDefault();
                    return true;
                }
            }
            );

            elemento.addEventListener("touchstart",
            (event) => {
                if (em_salvamento) {
                    event.preventDefault();
                    return true;
                }
            }
            );
            
            elemento.addEventListener("change", 
                debounce(
                    (event) => 
                    {
                        if (!flag_foi_alterado.includes(elemento)) { flag_foi_alterado.push(elemento); }
                        if (!em_salvamento) { salva_objeto(event, elemento); }
                    }
                ,1500)
            );
        }
    );

    Array.from(anchors).forEach(
        (elemento) => {
            if (elemento.id.includes("link_tabela_consumo_condominio_")) {  
                elemento.addEventListener("click",
                    (event) => {
                        let nome_tabela = elemento.id.substring(5);
                        let tabela = document.getElementById(nome_tabela);
                        let mes = document.getElementById('mes').value;
                        let ano = document.getElementById('ano').value;
                        let nome_condominio = tabela.getAttribute('data_nome_condominio').replaceAll(' ','_').toLowerCase();
                        let filename = 'consumo_condominio_'+nome_condominio+'_'+ano+mes+'.csv';
                        event.preventDefault();
                        htmlToCSV(nome_tabela, filename);
                    }
                );
                
                return;
            }

            if (elemento.id.includes("link_imprimir_tabela_consumo_condominio_")) {  
                elemento.addEventListener("click",
                    (event) => {
                        let nome_tabela = elemento.id.substring(14);
                        let tabela = document.getElementById(nome_tabela);
                        let div_tabela = document.getElementById("div_" + nome_tabela);
                        let mes = document.getElementById('mes').value;
                        let ano = document.getElementById('ano').value;
                        let nome_condominio = tabela.getAttribute('data_nome_condominio').replaceAll(' ','_').toLowerCase();
                        let filename = 'consumo_condominio_'+nome_condominio+'_'+ano+mes+'.pdf';
                        event.preventDefault();
                        imprime_pdf(div_tabela, filename);
                    }
                );
                
                return;
            }
        }
    );

    //Validadores especiais
    if (objeto_existe(email)) {
        objeto_valida_especial.push(email);
        funcao_valida_especial.push((event, elemento) => { return valida_email(event, elemento); });
    }

    if (objeto_existe(CPF)) {
            objeto_valida_especial.push(CPF);
            funcao_valida_especial.push((event, elemento) => { return valida_CPF(event, elemento); });
    }
    
    if (objeto_existe(CEP)) {
        objeto_valida_especial.push(CEP);
        funcao_valida_especial.push((event, elemento) => { return valida_CEP(event, elemento); });
    }

    if (objeto_existe(imagem_consumo)) {
        objeto_valida_especial.push(imagem_consumo);
        funcao_valida_especial.push((event, elemento) => { return valida_imagem_consumo(event, elemento); });
    }

    if (objeto_existe(select_mes_ano) && objeto_existe(mes) && objeto_existe(ano)) {
        select_mes_ano.addEventListener("change", 
            (event) => 
            {
                return muda_mes_ano(select_mes_ano.selectedOptions[0].value);
            }
        );
    }

    if (objeto_existe(select_condominio) && objeto_existe(select_bloco)) {
        select_condominio.addEventListener("change", 
            (event) => 
            {
                muda_lista_bloco(event, select_condominio, select_bloco);
            }

        );
    }

    if (objeto_existe(ids_condominio) && objeto_existe(ids_unidade)) {
        ids_condominio.forEach((checkbox_condominio) => {
            checkbox_condominio.addEventListener("change", 
                (event) => {
                    let id_condominio = checkbox_condominio.value;
                    let div_unidades_condominio = document.getElementById("div_unidades_condominio_" + id_condominio);
                    
                    muda_div_unidades(event, checkbox_condominio, div_unidades_condominio);
                }
            );
        }
        );
    }

    if (objeto_existe(checkbox_validado)) {
        checkbox_validado.addEventListener("change", 
            (event) => {
                if (objeto_existe(imagem_consumo) && objeto_existe(valor_m3)) {
                    if (valor_m3.value != "" && imagem_consumo.value != "") {
                        let url = "pega_validador.php";
                        let dados = {'id_validador': id_validador.value,
                        'objeto': 'consumo_unidade',
                        'id_unidade': id_unidade.value,
                        'mes': mes.value,
                        'ano': ano.value
                        };
                        if (!em_salvamento) { envia_dados(url, (json) => { muda_validador(event, json); }, event.target, dados); }
                        return true;
                    }
                }
                
                checkbox_validado.checked = false;
                mostra_caixa_mensagem("SÓ É POSSÍVEL VALIDAR UMA LEITURA COM VOLUME E IMAGEM DEFINIDA!","w3-red");
                return false;
            }
        );
    }
}

function carrega_valida_form_contato(form_contato) {
    let inputs = document.getElementsByTagName("input");
    Array.from(inputs).forEach(
        (elemento) => {
            elemento.addEventListener("keyup",
            debounce((event) => {
                valida_form_contato(event, elemento);
            }, 500)
            );
        }
    );
}


function muda_validador(evento, dados) {
    let checkbox_validado = document.getElementById("validado");
    let id_validador = document.getElementById("id_validador");
    let input_data_validado = document.getElementById("data_validado");

    if (dados.erro == "true") {
        checkbox_validado.checked = false;
        id_validador.value = "";
        input_data_validado.value = "";
        mostra_caixa_mensagem(dados.mensagem_erro,"w3-red");
        return false;
    }
    
    let today = new Date();
    let yyyy = today.getFullYear();
    let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    let dd = String(today.getDate()).padStart(2, '0'); //.getDay pega o dia da semana, e não o DIA... getDate pega o dia
    let hh = String(today.getHours()).padStart(2, '0');
    let min = String(today.getMinutes()).padStart(2, '0');
    let ss = String(today.getSeconds()).padStart(2, '0');
    let data_validado = yyyy+"-"+mm+"-"+dd+" "+hh+":"+min;

    if (!em_salvamento) { 
        id_validador.value = dados.id_validador;
        input_data_validado.value = data_validado+":"+ss;
        
        salva_objeto(evento, checkbox_validado, 
            () => {
            let checkbox_validado = document.getElementById("validado");
            let label_validado = document.getElementById("label_validado");

            let fileElem = document.getElementById("fileElem");
            let valor_m3 = document.getElementById("valor_m3");
            let label_button = document.getElementById("label_button");

            label_validado.innerHTML = " Leitura validada por "+dados.nome_validador+" em "+data_validado;
            checkbox_validado.checked = true;

            checkbox_validado.disabled = true;
            fileElem.disabled = true;
            valor_m3.disabled = true;
            label_button.classList.add("disabled");
        }); 
    }

    return true;
}

function envia_dados(url, funcao_retorno, elemento, dados='') {
    let fetch_options =         
    {
        'mode': 'cors'
    };
    
    if (dados != '') {
        let form_data = new FormData();
        Object.entries(dados).forEach(
            ([chave, valor]) => {
                if (Array.isArray(valor)) {
                    chave_temp = chave + "[]";
                    valor_temp = valor.filter((e) => { return (e === 0 || e); });
                    form_data.append(chave_temp, JSON.stringify(valor_temp));
                } else {
                    form_data.append(chave, valor);
                }    
            }
        );

        fetch_options = { 
        'method': 'POST',
        'body': form_data
        }
    }


    return fetch(url, fetch_options)
    .then(
        (response) => 
        { 
            if (response.status == "200") {
                let resposta_json = "";
                let resposta_text = response.text()
                .then((resposta) => {
                    try {
                        resposta_json = JSON.parse(resposta);
                    } catch {
                        resposta_json = {};
                        resposta_json.erro = true;
                        resposta_json.mensagem_erro = "ERRO DO SERVIDOR!";
                        console.log(resposta); 
                    }
    
                    return resposta_json;
                });
            
                return resposta_text;
            }
        }
    )  
    .then(
        (json) => {
            if (!json || json == undefined) {
                mostra_caixa_mensagem("ERRO AO PROCESSAR OS DADOS!","w3-red");
                if (elemento.type == "checkbox") { elemento.checked = !elemento.checked; }
                return Promise.reject(json);
            } else if (json.erro) {
                if (json.mensagem_erro == undefined) { return funcao_retorno(json); }
                mostra_caixa_mensagem(json.mensagem_erro,"w3-red");
                if (elemento.type == "checkbox") { elemento.checked = !elemento.checked; }
                return Promise.reject(json);
            }
            
            return funcao_retorno(json);
        }
    )    
    .catch(
        (err) => {
            console.log('ERRO NA RESPOSTA!', err);
            return Promise.reject(false);
        }
    ); 
}

function muda_cor_input(input, cor) {
    if (cor == "erro") {
        input.style.borderColor = "red";
        input.style.outlineColor = "red";
    } else {
        input.style.borderColor = "#ccc";
        input.style.outlineColor = "black";
    }

}

function altera_endereco(json) {
    if (json.erro) {
        let CEP = document.getElementById("CEP");
        if (objeto_existe(CEP)) { muda_cor_input(CEP,"erro"); }
        mostra_caixa_mensagem("CEP INVÁLIDO!","w3-red");
        return false;
    }

    let endereco = document.getElementById("endereco");
    let bairro = document.getElementById("bairro");
    let cidade = document.getElementById("cidade");
    let estado = document.getElementById("estado");

    if (objeto_existe(endereco)) { 
        if (json.logradouro != "") { endereco.value = json.logradouro;  }
        if (!flag_foi_alterado.includes(endereco)) { flag_foi_alterado.push(endereco); } 
    }
    if (objeto_existe(bairro)) { 
        if (json.bairro != "") { bairro.value = json.bairro;  }
        if (!flag_foi_alterado.includes(bairro)) { flag_foi_alterado.push(bairro); } 
    }
    if (objeto_existe(cidade)) { 
        cidade.value = json.localidade; 
        if (!flag_foi_alterado.includes(cidade)) { flag_foi_alterado.push(cidade); } 
    }
    if (objeto_existe(estado)) { 
        estado.value = json.uf; 
        if (!flag_foi_alterado.includes(estado)) { flag_foi_alterado.push(estado); } 
    }

    return true;
}

function muda_mes_ano(mes_ano) {
    let url_atual = location.href;
    let url_nova = url_atual
    let mes = mes_ano.substring(0,2);
    let ano = mes_ano.substring(2);
    let today = new Date();
    let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    let yyyy = today.getFullYear();
    let mes_ano_atual = mm + "" + yyyy;

    if (url_atual.includes("#")) {
        let posicao_id = url_atual.indexOf("#");
        url_atual = url_atual.substring(0, posicao_id);
        url_nova = url_atual;
    }

    if (url_atual.includes("&mes_ano=")) { 
        url_nova = url_atual.substring(0, url_atual.length-15);
    } 
    
    if (mes_ano != mes_ano_atual) {
        url_nova =  url_nova+"&mes_ano="+mes+""+ano;
    }
    console.log(url_nova);
    location.replace(url_nova);
}

function muda_lista_bloco(evento, select_condominio, select_bloco) {
    let id_select_condominio = select_condominio.selectedOptions[0].value;
    let url = 'select_bloco.php';
    let dados = 
    {
        'id_condominio': id_select_condominio
    };
    envia_dados(url, (json) => { select_bloco.innerHTML = json.html; }, select_condominio, dados);

    return true;
}

function muda_div_unidades(evento, checkbox_condominio, div_unidades_condominio) {
    let div_unidades = document.getElementById("div_unidades");
    
    if (!checkbox_condominio.checked && objeto_existe(div_unidades_condominio)) {
        let unidades_selecionadas = div_unidades_condominio.querySelectorAll("input[type=checkbox]:checked");
        if (unidades_selecionadas.length == 0) {
            div_unidades_condominio.remove();
            return true;
        } else {
            mostra_caixa_mensagem("NÃO É POSSÍVEL REMOVER UM CONDOMÍNIO SE HOUVER UNIDADES SELECIONADAS!","w3-red");
            checkbox_condominio.checked = true;
            return false;
        }
    }
    
     let id_condominio = checkbox_condominio.value;
    
    div_unidades_condominio = document.createElement("div");
    div_unidades_condominio.id = "div_unidades_condominio_" + id_condominio;
    let url = 'checkbox_unidade.php';
    let dados = 
    {
        'id_condominio': id_condominio,
        'id_user': id.value
    };
    envia_dados(url, (json) => { 
            div_unidades_condominio.innerHTML = json.html;
            if (objeto_existe(div_unidades)) {
                div_unidades.appendChild(div_unidades_condominio);
                
                let inputs = div_unidades_condominio.getElementsByTagName("input");
                Array.from(inputs).forEach(
                    (elemento) => {
                        if (elemento.type == "checkbox") {
                            elemento.addEventListener("click",
                            (event) => {
                                if (em_salvamento) {
                                    event.preventDefault();
                                    return true;
                                }
                            }
                            );
                            
                            elemento.addEventListener("change", 
                            debounce(
                                (event) => 
                                {
                                    if (!em_salvamento) { salva_objeto(event, elemento); }
                                }
                            ,1500)
                        );                
                        }
                    }
                );
            }
        }, checkbox_condominio, dados);
     
    return true;
}

function remove_caixa_mensagem(event, caixa_dados_salvos = "") {
    if (!flag_mostrando_caixa) { return false; }
    if (caixa_dados_salvos == "") {
        caixa_dados_salvos = event.target;
    }
    caixa_dados_salvos.classList.forEach(
        (classe) => {
            if (classe != "hidden") {
                caixa_dados_salvos.classList.remove(classe);
            }
        }
    );
    caixa_dados_salvos.classList.add("w3-green");
    caixa_dados_salvos.innerHTML = "&nbsp;";
    flag_mostrando_caixa = false;
}

function mostra_caixa_mensagem(mensagem, cor='w3-green') {
    if (flag_mostrando_caixa) { return false;}
  
    if (objeto_existe(caixa_dados_salvos)) {
        caixa_dados_salvos.classList.remove("w3-green");
        caixa_dados_salvos.classList.remove(cor);
        caixa_dados_salvos.classList.add(cor);

        caixa_dados_salvos.innerHTML = mensagem;
        caixa_dados_salvos.classList.toggle("hidden");
        flag_mostrando_caixa = true;
        setTimeout(
            () => { 
                caixa_dados_salvos.classList.toggle("hidden");
            }, 1000);
    }
}

function deleta_objeto(objeto_id, evento, elemento) {
    evento.preventDefault();

    if(confirm("Tem certeza que deseja deletar esse item?")) {
        let objeto = objeto_id.split('&')[0];
        let id = objeto_id.split('&')[1].replace("id=","");
        let url = 'processa_objeto.php?deletar=true';
        let form_dados = 
            {
                'id': id,
                'objeto': objeto
            };

        envia_dados(url, (json) => { location.reload(); }, elemento, form_dados)
        .catch((err) => { console.log('ERRO AO DELETAR OBJETO!'); });
    } else {
        return false;
    }
}

function salva_objeto(evento, elemento, funcao_retorno = "") {
    if (em_salvamento) { 
        evento.preventDefault(); 
        return false;
    }

    if (!objeto_existe(form_dados)) { return false; }
    if (document.body.innerHTML.includes("Data das Medições:")) { return false; }
    
    em_salvamento = true;
    let promise_valida_form = valida_form(evento,elemento);

    if (promise_valida_form === true) {
        envia_dados_para_salvar(evento, elemento)
        .then(() => { 
            em_salvamento = false; 
            if (funcao_retorno != "") {
                funcao_retorno();
            }
        })
        .catch((err) => { em_salvamento = false; });
    } else if (!promise_valida_form) {
        em_salvamento = false;
    } else {
        promise_valida_form
        .then(() => {        
            envia_dados_para_salvar(evento, elemento)
            .then(() => { 
                em_salvamento = false;
                if (funcao_retorno != "") {
                    funcao_retorno();
                }
            })
            .catch((err) => { em_salvamento = false; });
        })
        .catch((err) => { em_salvamento = false; });
    } 
}

function processa_resposta_salvamento(json, elemento) {
    if (json.erro) {  
        mostra_caixa_mensagem(json.mensagem_erro),'w3-red';
        if (elemento.type == "checkbox") { elemento.checked = !elemento.checked; }
    } else {
        mostra_caixa_mensagem("DADOS SALVOS COM SUCESSO!");
        if (id.value == '' || id.value == 0) {
            id.value = json.dados.id;
        }
    }
}

function envia_dados_para_salvar(evento, elemento, deletar = false) {
    let url = "processa_objeto.php";
    let dados_form = {};
    let inputs = form_dados.getElementsByTagName("input");
    let selects = form_dados.getElementsByTagName("select");
    
    Array.from(inputs).forEach((elemento) => { 
        let valor_elemento = elemento.value;
        let id_elemento = elemento.id;
        if (elemento.type == "checkbox" && !elemento.checked) {
            valor_elemento = "";
        }
        
        if (id_elemento.includes("[]")) {
            id_elemento = id_elemento.substring(0,(id_elemento.length - 2));
            if (!(id_elemento in dados_form)) {
                dados_form[id_elemento] = [];
            }
        }
        
        if (Array.isArray(dados_form[id_elemento])) {
            dados_form[id_elemento].push(valor_elemento);
        } else {
            dados_form[id_elemento] = valor_elemento;
        }  
    });
    
    Array.from(selects).forEach((elemento) => { 
        let valor_elemento = elemento.selectedOptions[0].value;
        let id_elemento = elemento.id;
        dados_form[id_elemento] = valor_elemento; 
    });

    return envia_dados(url, (json) => { return processa_resposta_salvamento(json, elemento); }, elemento, dados_form);
}

function envia_form_contato(event, form_contato) {
    event.preventDefault();
    let cookie_enviou_contato = getCookie("enviou_form_contato");
    if (cookie_enviou_contato != "") {
        let json = {};
        json.erro = true;
        json.mensagem_erro = "VOCÊ SÓ PODE ENVIAR UMA MENSAGEM POR DIA!";
        processa_resposta_form_contato(json, form_contato);
        return;
    }

    let url = "processa_contato.php";
    let dados_form = {};
    let inputs = form_contato.getElementsByTagName("input");
    let botao_form_submit = document.getElementById("form_submit");
    botao_form_submit.disabled = true;

    Array.from(inputs).forEach((elemento) => { 
        let valor_elemento = elemento.value;
        let id_elemento = elemento.name;
        dados_form[id_elemento] = valor_elemento;
     });
    
     return envia_dados(url, (json) => { return processa_resposta_form_contato(json, form_contato); }, form_contato, dados_form);
}

function processa_resposta_form_contato (json, elemento) {
    if (json.erro) {  
        mostra_caixa_mensagem(json.mensagem_erro,'w3-red');
    } else {
        mostra_caixa_mensagem("DADOS ENVIADOS COM SUCESSO PARA O ADMINISTRADOR DO SISTEMA!");
        setCookie("enviou_form_contato","true",1);
    }
    
    setTimeout(
        () => { 
            document.location.href="/";
        }, 2000);
    
    return;
}

function aceita_lgpd() {
    let url = "processa_lgpd.php";
    let dados_form = {};
    let id_user = document.getElementById("id_user");
    dados_form['id']= id_user.value;
    
    envia_dados(url, (json) => {
        if (json.erro) {
            console.log(json);
        }
        setTimeout(
            () => { 
                document.location.href="/";
            }, 1000);
    }, aceite_lgpd, dados_form);
}