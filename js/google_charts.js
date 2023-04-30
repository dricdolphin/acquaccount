var dados_chart = [];
var options_chart = [];

function pega_dados_charts(elemento) {
    let url = "dados_chart.php";
    let dados = {'objeto': elemento.id
    };
    envia_dados(url, (json) => { carrega_dados(elemento, json); }, elemento, dados);
}

function carrega_dados(elemento, dados) {
    dados_chart[elemento.id] = JSON.parse(dados.dados_chart);
    options_chart[elemento.id] = JSON.parse(dados.options_chart);
    google.charts.setOnLoadCallback(draw_chart(elemento));
}

function draw_chart(elemento) {
    let data = new google.visualization.DataTable(dados_chart[elemento.id]);
    let options = options_chart[elemento.id];
    let chart = {};
    if (String(elemento.id).includes("consumo_unidade_") || String(elemento.id).includes("consumo_condominio_")) {
        chart = new google.visualization.ComboChart(elemento);
    } else {
        chart = new google.visualization.PieChart(elemento);
    }
    
    chart.draw(data, options);
  }