function valida_form(event, input) {
    let form = document.getElementById("form_dados");

    let inputs = form.querySelectorAll("input:not([disabled])");
    let selects = form.querySelectorAll("select:not([disabled])");
    let flag_todos_inputs_alterados = Array.from(inputs).every((elemento) => { flag_foi_alterado.includes(elemento); });

    let form_validou = true;
    Array.from(inputs).forEach(
        (elemento) => {
            if (elemento.type == "hidden" || elemento.type == "checkbox" || elemento.type == "file") { return true; }
            if (elemento.value == "") {
                if (id.value == 0 || id.value == '') {
                    //Só manda salvar depois de ter dado foco em TODOS os inputs caso seja um objeto novo
                    if (flag_foi_alterado.includes(elemento)) {
                        muda_cor_input(elemento, "erro");
                        elemento.title = "NENHUM CAMPO PODE SER DEIXADO EM BRANCO!";
                        if (document.activeElement == elemento) { mostra_caixa_mensagem("NENHUM CAMPO PODE SER DEIXADO EM BRANCO!","w3-red"); }
                        form_validou = false;
                        return false;
                    }
                    if(!flag_todos_inputs_alterados) {
                        form_validou = false;
                        return false;
                    }
                } else {
                    muda_cor_input(elemento, "erro");
                    elemento.title = "NENHUM CAMPO PODE SER DEIXADO EM BRANCO!";
                    if (document.activeElement == elemento) { mostra_caixa_mensagem("NENHUM CAMPO PODE SER DEIXADO EM BRANCO!","w3-red"); }
                    form_validou = false;
                    return false;   
                }
            } 
            muda_cor_input(elemento, "OK");
            elemento.title = "";
            return true;
        }
    );

    //Realiza as validações adicionais, caso seja necessário e caso eles já tenham sido editados
    objeto_valida_especial.forEach(
        (elemento, chave) => {
            if (!flag_foi_alterado.includes(elemento) && (id.value == 0 || id.value == '')) {
                form_validou = false
                return false;
            }
            
            let resposta_funcao_valida_especial = funcao_valida_especial[chave](event, elemento);
            if (!resposta_funcao_valida_especial) {
                form_validou = false;
                return false;
            } else if (form_validou) {
                form_validou = resposta_funcao_valida_especial;
                return form_validou;
            } else {
                resposta_funcao_valida_especial;
                form_validou = false;
                return false;
            }
        }
    );
    
    return form_validou;
}

function valida_imagem_consumo(event, elemento) {
    if (elemento.value != "") {
        return true;
    }
    
    mostra_caixa_mensagem("É NECESSÁRIO TER UMA IMAGEM ANEXA À LEITURA!","w3-red");
    return false;
}

function valida_email(event, elemento) {
    let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    if(elemento.value.match(mailformat))
    {
        muda_cor_input(elemento, "OK");
        elemento.title = "";
        return true;
    }

    muda_cor_input(elemento, "erro");
    elemento.title = "E-MAIL INVÁLIDO!";
    if (document.activeElement == elemento) { mostra_caixa_mensagem("E-MAIL INVÁLIDO!","w3-red"); }
    return false;
}

function valida_CPF(event, elemento) {
    if (elemento.value.length == 11) {
        muda_cor_input(elemento, "OK");
        elemento.title = "";
        return true;
    }
    
    muda_cor_input(elemento, "erro");
    if (document.activeElement == elemento) { mostra_caixa_mensagem("CPF INVÁLIDO!","w3-red"); }
    return false;
}

function valida_CEP(event, elemento) {
    if (elemento.value.length == 8) {
        muda_cor_input(elemento, "OK");
        elemento.title = "";
        let url = 'https://viacep.com.br/ws/'+elemento.value+'/json/';
        var erro = false;

        let retorno_dados_enviados = envia_dados(url, (json) => { erro = altera_endereco(json); }, elemento);
        if (!retorno_dados_enviados) {
            erro = false;
        } else {
            erro = retorno_dados_enviados;
        }
        return erro;
    }

    muda_cor_input(elemento, "erro");
    elemento.title = "CEP INVÁLIDO!";
    if (document.activeElement == elemento) { mostra_caixa_mensagem("CEP INVÁLIDO!","w3-red"); }
    return false;  
}

function valida_form_contato(evento, elemento) {
    let inputs = document.getElementsByTagName("input");
    let form_submit = document.getElementById("form_submit");
    form_submit.disabled = true;

    let elementos_com_valor = 0;
    Array.from(inputs).forEach(
        (elemento) => {
            if (elemento.value != "") {
                elementos_com_valor++;
            }
        }
    );
    
    if (elementos_com_valor == inputs.length) {
        form_submit.disabled = false;
    }
}