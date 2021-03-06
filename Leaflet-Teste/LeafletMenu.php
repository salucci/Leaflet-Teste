<?php
session_start();
//Calula se a ultima ativiade na sessão tem mais de x segundos e atualiza a ultima atividade
$_SESSION['TIMEOUT'] = ini_get("session.gc_maxlifetime");

if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > $_SESSION['TIMEOUT']) {
    // session started more than 30 minutes ago
    session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
}
$_SESSION['TargetTable'] = "default";

if ( !empty( $_COOKIE['TargetTable'] ) ) {
	$_SESSION['TargetTable'] = $_COOKIE['TargetTable']; //
}

//echo $_SESSION['TargetTable'];

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF8">

  	<script
  src="https://code.jquery.com/jquery-3.1.1.min.js"
  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
  crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  

	<script type="text/javascript" src="leaflet.js"></script>
	<script src="bundle.js"></script>
<script type="text/javascript" src="leaflet.ajax.min.js"></script>
<script type="text/javascript" src="d3.js"></script>
<link rel="stylesheet" type="text/css" href="leaflet.css">
  <script src="spin.min.js"></script>
<script type="text/javascript" src="leaflet.spin.min.js"></script>
<script src="leaflet.browser.print.js"></script>
<script src="leaflet.browser.print.utils.js"></script>
<script src="leaflet.browser.print.sizes.js"></script>




	
	<style>
	@page { size: landscape; }
	
		#map {
		  width: 100%;
    height: 500px;
    position: absolute;
    top: 0;
    left: 0;

    transition: height 0.5s ease-in-out;
		}
		.info {
    padding: 6px 8px;
    font: 14px/16px Arial, Helvetica, sans-serif;
    background: white;
    background: rgba(255,255,255,0.8);
    box-shadow: 0 0 15px rgba(0,0,0,0.2);
    border-radius: 5px;
}
.info h4 {
    margin: 0 0 5px;
    color: #777;
}

.input {
  padding-right: 0;  
  padding-left: 1%;
}
.inputlast {
  padding-right: 1%;  
  padding-left: 1%;
}

.panel-body {
  padding-right: 1%;  
  padding-left: 1%;
}

	</style>

    <title>Test Menu</title>
    </head>
    <body>
    <div class="container easyPrint">
	
	<!--
	<form class="form-inline" action="../ope.php" method="POST">
  <div class="form-group">
    <label for="email">Login:</label>
    <input type="login" class="form-control" name="login">
  </div>
  <div class="form-group">
    <label for="pwd">Senha:</label>
    <input type="password" class="form-control" name="senha">
  </div>
  <button type="submit" class="btn btn-default">Entrar</button>
</form>
-->
<nav class="navbar navbar-default">
<div class="container-fluid">
<ul class="nav navbar-nav navbar-left">
        <li><a href="./Leafletmenu.php">Home</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Tabelas Customizadas<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="../uploadfile.php">Enviar Tabela</a></li>
            <li><a href="../usermenu.php">Selecionar Tabela </a></li>
          </ul>
        </li>
      </ul>
</div>
</nav>
	
<div class="row">
<div class="col-md-8">
<div id="map"></div>
</div>

	  
<div class="col-md-4">	  <!-- coluna menu -->
 <div class="panel panel-primary">
                <div class="panel-heading">
                     <h3 class="panel-title">Consulta de Variáveis</h3>
                </div>
                <div class="panel-body">
                    <form role="form" method="post" id="myform">
					
						<div class="form-group">
                            							<label class="control-label">Alvo da consulta:</label>
						<div class="container-fluid">

                                <div class="btn-group input-group btn-group-justified" id="radiotarget" data-toggle="buttons">
                                    <label class="btn btn-success active">
                                        <input type="radio" name="options" value="municipios" id="option1" checked />Municípios</label>
                                    <label class="btn btn-primary">
                                        <input type="radio" name="options" value="estados" id="option2" />Estados</label>
                                    <label class="btn btn-danger">
                                        <input type="radio" name="options" value="regioes" id="option3" />Regiões</label>
                                </div>
								
								</div>
							
                        </div>
						
	                      <div class="form-group">
						  <label class="control-label">Variáveis:</label>
							<div class="container-fluid">
								<div class="row">
									<div class="col-xs-6 input">
                                    <select id="var1" name="var1" class="form-control varfield">
										<option value="" disabled selected hidden>Variável</option>
                                        <option>PIB</option>
                                        <option>População</option>
                                        <option>IDH</option>
                                        <option>Densidade</option>
                                    </select>
									</div>
									<div class="col-xs-3 input">
									
                                    <select id="val1" name="val1" class="form-control">
										<option value="" selected disabled hidden>Fuzzy</option>
                                        <option>Baixo</option>
                                        <option>Médio Baixo</option>
                                        <option>Médio</option>
                                        <option>Médio Alto</option>
                                        <option>Alto</option>
                                    </select>
									
									</div>
									<div class="col-xs-3 inputlast">
									<input type="text" class="input-small form-control" name="peso1" id="peso1" placeholder="Peso" />
									</div>
								</div> <!-- /row -->
							</div>										
                        </div>
						
					<div class="form-group">
						<div class="container-fluid">
								<div class="row">
									<div class="col-xs-6 input">
                                    <select id="var2" name="var2" class="form-control varfield">
										<option value="" selected disabled hidden>Variável</option>
                                        <option>PIB</option>
                                        <option>População</option>
                                        <option>IDH</option>
                                        <option>Densidade</option>
                                    </select> 
									</div>

									<div class="col-xs-3 input">
                                    <select id="val2" name="val2" class="form-control">
									<option value="" selected disabled hidden>Fuzzy</option>
                                        <option>Baixo</option>
                                        <option>Médio Baixo</option>
                                        <option>Médio</option>
                                        <option>Médio Alto</option>
                                        <option>Alto</option>
                                    </select>
									</div>
									<div class="col-xs-3 inputlast">
									<input type="text" class="input-small form-control" name="peso2" id="peso2" placeholder="Peso" />
									</div>
								</div> <!-- /row -->
                        </div>	
                    </div>
						
						<div class="form-group">
								<div class="container-fluid">
								<div class="row">
									<div class="col-xs-6 input">
                                    <select id="var3" name="var3" class="form-control varfield">
										<option value="" selected disabled hidden>Variável</option>
                                        <option>PIB</option>
                                        <option>População</option>
                                        <option>IDH</option>
                                        <option>Densidade</option>
                                    </select> 
									</div>
									
									<div class="col-xs-3 input">
                                    <select id="val3" name="val3" class="form-control">
									<option value="" selected disabled hidden>Fuzzy</option>
                                        <option>Baixo</option>
                                        <option>Médio Baixo</option>
                                        <option>Médio</option>
                                        <option>Médio Alto</option>
                                        <option>Alto</option>
                                    </select>
									</div>
									<div class="col-xs-3 inputlast">
									<input type="text" class="input-small form-control" name="peso3" id="peso3" placeholder="Peso" />
									</div>
								</div> <!-- /row -->
                            </div>					
                        </div>
						
						<div class="form-group">
                            <div class="container-fluid">
								<div class="row">
									<div class="col-xs-6 input">
                                    <select id="var4" name="var4" class="form-control varfield">
										<option value="" selected disabled hidden>Variável</option>
                                        <option>PIB</option>
                                        <option>População</option>
                                        <option>IDH</option>
                                        <option>Densidade</option>
                                    </select> 
									</div>
									
									<div class="col-xs-3 input">
                                    <select id="val4" name="val4" class="form-control">
									<option value="" selected disabled hidden>Fuzzy</option>
                                        <option>Baixo</option>
                                        <option>Médio Baixo</option>
                                        <option>Médio</option>
                                        <option>Médio Alto</option>
                                        <option>Alto</option>
                                    </select>
									</div>
									<div class="col-xs-3 inputlast">
									<input type="text" class="input-small form-control" name="peso4" id="peso4" placeholder="Peso" />
									</div>
								</div> <!-- /row -->
                            </div>					
                        </div>
						
						<div class="form-group">
                            <div class="container-fluid">
								<div class="row">
									<div class="col-xs-6 input">
                                    <select id="var5" name="var5" class="form-control varfield">
										<option value="" selected disabled hidden>Variável</option>
                                        <option>PIB</option>
                                        <option>População</option>
                                        <option>IDH</option>
                                        <option>Densidade</option>
                                    </select> 
									</div>
									
									<div class="col-xs-3 input">
                                    <select id="val5" name="val5" class="form-control">
									<option value="" selected disabled hidden>Fuzzy</option>
                                        <option>Baixo</option>
                                        <option>Médio Baixo</option>
                                        <option>Médio</option>
                                        <option>Médio Alto</option>
                                        <option>Alto</option>
                                    </select>
									</div>
									<div class="col-xs-3 inputlast">
									<input type="text" class="input-small form-control" name="peso5" id="peso5" placeholder="Peso" />
									</div>
								</div> <!-- /row -->
                            </div>					
                        </div>
						
						<div class="form-group">
							<div class="container-fluid">
								<button type="submit" class="btn btn-primary mb-2">Gerar Mapa</button>		
							</div>							
                        </div>

				</div> <!-- /form -->
			</div> <!-- /panel body -->
	</div>		
	</div><!-- /row -->
</div> <!-- /container -->
    </body>
	
	<script type="text/javascript">
	
	var LastForm; //last form saved when submit
	var GeoJsonFlag, ServerDataFlag;
	
const isValidValue = element => {
	console.log(element);
  return (!['checkbox', 'radio'].includes(element.type) || element.checked);
};

const isValidElement = element => {
  return element.name && element.value;
};

//radiotarget $('#radiotarget label.active input').val()

	const formToJSON_deconstructed = elements => {
  
  // This is the function that is called on each element of the array.
  const reducerFunction = (data, element) => {
    
    // Add the current field to the object.
	 if (isValidElement(element) && isValidValue(element)) {
    data[element.name] = element.value;
  }
    
    // For the demo only: show each step in the reducer’s progress.
    console.log(JSON.stringify(data));

    return data;
  };
  
  // This is used as the initial value of `data` in `reducerFunction()`.
  const reducerInitialValue = {};
  
  // To help visualize what happens, log the inital value, which we know is `{}`.
  console.log('Initial `data` value:', JSON.stringify(reducerInitialValue));
  
  // Now we reduce by `call`-ing `Array.prototype.reduce()` on `elements`.
  const formData = [].reduce.call(elements, reducerFunction, reducerInitialValue);
  
  // The result is then returned for use elsewhere.
  return formData;
};

const formToJSON = elements => [].reduce.call(elements, (data, element) => {
  
  	 if (isValidElement(element) && isValidValue(element)) {
    data[element.name] = element.value;
  }
  
  
  return data;

}, {});



var form = document.getElementById('myform');
var lastFormData = formToJSON(form.elements);

	
  

const handleFormSubmit = event => {
  
  // Stop the form from submitting since we’re handling that with AJAX.
  event.preventDefault();

  // Call our function to get the form data.
  lastFormData = formToJSON(form.elements);
  console.log("kk "+lastFormData.value);
  
  // Use `JSON.stringify()` to make the output valid, human-readable JSON.
  console.log(JSON.stringify(lastFormData, null, "  "));
  
  GeoJsonFlag = 0;
  ServerDataFlag = 0;
  GetGeoData(lastFormData);
  GetGeoJson();
  
  // ...this is where we’d actually do something with the form data...
};
	   

form.addEventListener('submit', handleFormSubmit);
  



// -------------------Leaflet Section------------------------------Leaflet Section-------------

	var obj = {};
obj['0AC'] = '10';
var geojsonLayer;
var map;
var IncomingData;
var varTableType = "municipios";// switch municipios,estados,regioes
var MunicipiosJson,EstadosJson,RegioesJson;



var info = L.control(); 
var scale = d3.scale.linear() 
.domain([0.0,1.0])
 .range(["#ffffff","#0000ff"]); 
var maxBounds = L.bounds([[-50, -90],[5, -25]]);
 var southWest = L.latLng(10, -10),
	northEast = L.latLng(-40, -110),
     mybounds = L.latLngBounds(southWest, northEast);
 
 info.onAdd = function (map) {
	if(typeof this._div === 'undefined')
	this._div = L.DomUtil.create('div', 'info'); // create a div with a class "info"
    this.update();
    return this._div;
};


info.update = function (props) {
	var creatingInnerHTML = '<h4>Variáveis da Região:</h4>';
	if (!props) {this._div.innerHTML = creatingInnerHTML+' Passe o mouse';}
	else{
	for (var key in props) {
	creatingInnerHTML = creatingInnerHTML + '<b>'+key+' : ' + props[key] + '</b><br/>';
			}
			this._div.innerHTML = creatingInnerHTML;
	}
};


var customActionToPrint = function(context) {
				return function() {
					window.alert("We are printing the MAP. Let's do Custom print here!");
					context._printCustom();
				}
			}

//[-15.5, -49.3]
function SetupMap() { 
	map = new L.Map("map", {center: mybounds.getCenter(),maxBounds: mybounds,maxBoundsViscosity: 0, zoom: 4,minZoom: 4,maxZoom: 10})
		.addLayer(new L.TileLayer("http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"));
	// L.easyPrint({
	// title: 'My awesome print button',
	// position: 'bottomright',
	// sizeModes: ['A4Portrait', 'A4Landscape']
	// }).addTo(map);
	
	 // L.control.browserPrint({
		 // title: 'Just print me!',
	// documentTitle: 'Map printed using leaflet.browser.print plugin',
	// printLayer: L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.{ext}', {
                            	// attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                            	// subdomains: 'abcd',
                            	// minZoom: 1,
                            	// maxZoom: 16,
                            	// ext: 'png'
                            // }),
	// closePopupsOnPrint: false,
	 
	 // printModes: [
					// L.control.browserPrint.mode.landscape()
				// ]
	 // }).addTo(map);
	 
	 			// L.Control.BrowserPrint.Utils.registerLayer(L.TileLayer.WMS, 'L.TileLayer.WMS', function(layer) {
				// console.info("Printing WMS layer.");
				// return L.tileLayer.wms(layer._url, layer.options);
			// });

            // L.popup({minWidth: 500}).setLatLng(L.latLng(39.73, -104.99)).setContent("Leaflet browser print plugin with custom print Layer and content").openOn(map);



}


			

var customActionToPrint = function(context) {
return function()
{
    context._printCustom();
}
}


function GetGeoJson(){
		map.spin(true); //start spin while GeoJson download
		
		if (typeof geojsonLayer != 'undefined')
		{
		map.removeLayer(geojsonLayer);
		}
		if (lastFormData.options == "municipios"){	
		MunicipiosJson = new L.GeoJSON.AJAX("https://raw.githubusercontent.com/salucci/Leaflet-Teste/master/municipios_fixed.json", {style: style, onEachFeature: onEachFeature});
		geojsonLayer = MunicipiosJson;
		}
		if (lastFormData.options == "estados"){	
		EstadosJson = new L.GeoJSON.AJAX("https://raw.githubusercontent.com/salucci/Leaflet-Teste/master/estados_fixed.json", {style: style, onEachFeature: onEachFeature});
		geojsonLayer = EstadosJson;
		}
		if (lastFormData.options == "regioes"){	
		RegioesJson = new L.GeoJSON.AJAX("https://raw.githubusercontent.com/salucci/Leaflet-Teste/master/regioes_fixed.json", {style: style, onEachFeature: onEachFeature});
		geojsonLayer = RegioesJson;
		}
		
		geojsonLayer.on('data:loaded',function(e){
				map.spin(false);
				//flag the GeoJson is ready
				GeoJsonFlag = 1;
				TryMergeData();
		});
}


// It's a async request so I need to store the data and trigger later
function GetGeoData(UpcomingData){
//console.log("Going to post "+UpcomingData);
$.ajax({
    url: "../phpController.php",
    type: "POST",
    dataType: "json",
    data: UpcomingData,
    success: function(data){
        console.log(data);
		IncomingData = data;
		ServerDataFlag = 1;
		TryMergeData();
    },
    error: function(error){
         console.log("AJAX Error:");
         console.log(error);
    }
});
}

// var myVar = setInterval(GetCurrentPrice,3000);

// function myStopFunction() {
    // clearInterval(myVar);
// }

// function GetCurrentPrice()
// {
	// lastFormData = formToJSON(form.elements);
	// console.log("going to post get att: "+lastFormData.options);
// }

$('input[type="radio"][name="options"]').on('click change', function(e) {
    GetAtributes();
	
});


function GetAtributes(){
	//lastFormData = formToJSON(form.elements);
	console.log("going to post get att: "+document.querySelector('input[type="radio"][name="options"]:checked').value);//document.querySelector('input[name="options"]:checked').value
$.ajax({
    url: "../listAtributes.php",
    type: "POST",
    dataType: "json",
    data: {"TableType": document.querySelector('input[type="radio"][name="options"]:checked').value ,"operation": "listAtributes","TargetTable":"<?php echo $_SESSION['TargetTable'] ?>"},
    success: function(data){
		console.log("fillAtt");
        console.log(data);
		fillAtributes(data);
		
    },
    error: function(error){
         console.log("Error:");
         console.log(error);
    }
});
}

function fillAtributes(data){
$( ".varfield" ).html('<option value="" selected disabled hidden>Variável</option>');
for(var i = 0; i < data.length; i++) {	
	$( ".varfield" ).append('<option value="'+data[i].Codigo+'">'+data[i].AttAlias+'</option>');

}
}

function TryMergeData(){

// test if both data are ready
console.log("GeoJSON Flag: "+GeoJsonFlag+" ServerDataFlag: "+ServerDataFlag);
	if (GeoJsonFlag == 1 && ServerDataFlag == 1){
	var count = 0;
geojsonLayer.eachLayer(function (layer) {

	var IndexedIncomingLayer = IncomingData[layer.feature.properties.COD_IBGE];

	if (IndexedIncomingLayer != null) { //teste com digito verificador
			for (var key in IndexedIncomingLayer) {
			layer.feature.properties[key] = IndexedIncomingLayer[key];
			}
			count++;
		} 		
		else{ layer.feature.properties.status = 'Data not found'; layer.feature.properties.FinalValue = '0'; 
		console.log(layer.feature.properties.NOME_MUNI);}
	//console.log (IndexedIncomingLayer);
	IndexedIncomingLayer = null;  

			geojsonLayer.addTo(map);
				info.addTo(map);
	
  layer.setStyle({
        fillColor:  scale(parseFloat(layer.feature.properties.FinalValue)),
        weight: 0,
        opacity: 1,
        color: 'white',
        dashArray: '3',
        fillOpacity: 0.7
        });
  
});
//alert("Foram encontradas e adicionadas ao mapa "+count+" regiões ");

	}
}
 

function style(feature) {
//console.log(feature.properties.FinalValue + "  "+ scale(parseFloat(feature.properties.FinalValue)));
    return {
        fillColor: scale(parseFloat(feature.properties.FinalValue)),
        weight: 0,
        opacity: 1,
        color: 'white',
        dashArray: '3',
        fillOpacity: 0.7
    };
}

function highlightFeature(e) {
    var layer = e.target;
    layer.setStyle({
        weight: 5,
        color: '#666',
        dashArray: '',
        fillOpacity: 0.7
    });

    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
        layer.bringToFront();
    }
	info.update(layer.feature.properties);
}

function resetHighlight(e) {
    geojsonLayer.resetStyle(e.target);
	info.update();
}

function zoomToFeature(e) {
    map.fitBounds(e.target.getBounds());
}

function onEachFeature(feature, layer) {
    layer.on({
        mouseover: highlightFeature,
        mouseout: resetHighlight,
        click: zoomToFeature
    });
}

L.Map.include({
  'clearLayers': function () {
    this.eachLayer(function (layer) {
      this.removeLayer(layer);
    }, this);
  }
});
	 
	 
   $(document).ready(function () {
        SetupMap(); 
		GetAtributes();
		setTimeout(function(){ map.invalidateSize()}, 400);
    });
	 
//window.onload= function (){

//}
	</script>
</html>
