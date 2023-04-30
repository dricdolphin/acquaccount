function htmlToCSV(tabela, filename) {
	let data = [];
	//data.push("sep=,");
	let rows = document.getElementById(tabela).getElementsByTagName('tr');
	for (let i = 0; i < rows.length; i++) {
		let row = [], cols = rows[i].querySelectorAll("td, th");		
		for (let j = 0; j < cols.length; j++) {
		        row.push(cols[j].innerText.replaceAll('.',','));
        }        
		data.push(row.join(";")); 		
	}

	downloadCSVFile(data.join("\n"), filename);
}

function downloadCSVFile(csvContent, filename) {
	let csv_file, download_link;
	console.log(csvContent);
	
	csv_file = new Blob(["\ufeff",csvContent], {type: 'text/csv;charset=UTF-8'});
	download_link = document.createElement("a");
	download_link.download = filename;
	download_link.href = window.URL.createObjectURL(csv_file);
	download_link.style.display = "none";
	document.body.appendChild(download_link);
	download_link.click();
}