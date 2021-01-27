
<?php
include "simple_html_dom.php";
$url = 'https://www.masterd.es/cursos-de-formacion-mantenimiento-industrial-g11';
$html=connectar($url); //funicon conectar que devuelve la el objeto html de la url
//echo $html;
//echo $html->find("listado-grupo");
$a=array(); //array de cursos
$hey=$html->find("div[id=listado-grupo]",0); //Buscamos el listado de cursos por id

foreach ($hey->find("ul",0)->find("li") as $link) { //Por cada parte de la lista de cursos
	$enlace=$link->find("a",0)->href;
	$titulo=$link->find("a",0)->title;
	//echo $titulo ." ";
	//echo $enlace;
	$html=connectar($enlace);
	$curs=new Curso();
	foreach($html ->find('img') as $item) { //borramos imagenes del html que tenemos
		$item->outertext = '';
		}
	$html->save();
	$descripcion=$html->find("#contenido-ficha",0);
	$descripcion=strip_tags($descripcion);
	//echo $descripcion;
	$curs->set_descripcion($descripcion);
	$curs->set_title($titulo);
	$curs->set_url($enlace);
	$a[]=$curs;	

	//echo $link;
	//echo "</br>";
}

$html->clear();
unset($html);

$csv='cursos_masterd.csv'; //ruta local del archivo csv
$fh=fopen($csv,'r'); //file handler el que se encarga de abrirlo

while (list($id,$titulo,$imparticion)=fgetcsv($fh,1024,',')) {
	printf("<p>%s ,%s,%s</p>",$id,$titulo,$imparticion);

	foreach($a as $curso){
		$titulo_del_curso=$curso->get_title();
		if(strcasecmp($titulo_del_curso,$titulo) == 0){
			$curso->set_id($id);
		}
	}

	
}

fclose($fh);

$dom = new DOMDocument();
$dom->encoding = 'utf-8';
$dom->xmlVersion = '1.0';
$dom->formatOutput = true;

$xml_file_name = 'listas_sin_id.xml';
$xml_file_names = 'listas_con_id.xml';

$root = $dom->createElement('courses');
foreach($a as $curso){
	if($curso->get_id()==-1){
		echo " " . $curso->get_title() . " ". $curso->get_id() . " <br>";
		$course = $dom->createElement('course');
		$child_node_title = $dom->createElement('title', $curso->get_title());
		$course->appendChild($child_node_title);
		$child_url = $dom->createElement('url',$curso->get_url());
		$course->appendChild($child_url);
		$child_node_description = $dom->createElement('description',$curso->get_descripcion());
		echo $curso->get_url() . " <br>";
		//echo $curso->get_descripcion();
		$child_node_id =  $dom->createElement('id',$curso->get_id());
		$course->appendChild($child_node_id);
		$course->appendChild($child_node_description);
		$root->appendChild($course);
		$dom->appendChild($root);
	}
	
}

$dom->save($xml_file_name);

unset($dom);
$dom = new DOMDocument();
$dom->encoding = 'utf-8';
$dom->xmlVersion = '1.0';
$dom->formatOutput = true;
//Falta de optimizacion
$inicio = $dom->createElement('courses');
foreach($a as $curso){
	if($curso->get_id() > -1){
		echo " " . $curso->get_title() . " ". $curso->get_id() . " <br>";
		$course = $dom->createElement('course');
		$child_node_title = $dom->createElement('title', $curso->get_title());
		$course->appendChild($child_node_title);
		$child_url = $dom->createElement('url',$curso->get_url());
		$course->appendChild($child_url);
		$child_node_description = $dom->createElement('description',$curso->get_descripcion());
		echo $curso->get_url() . " <br>";
		//echo $curso->get_descripcion();
		$child_node_id =  $dom->createElement('id',$curso->get_id());
		$course->appendChild($child_node_id);
		$course->appendChild($child_node_description);
		$inicio->appendChild($course);
		$dom->appendChild($inicio);
	}
	
}

$dom->save($xml_file_names);








function connectar($url){
	$handle = curl_init($url);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($handle);
	libxml_use_internal_errors(true); // Prevent HTML errors from displaying

	$html = new simple_html_dom();
	$html->load($response);
	curl_close($handle);
	return $html;
}

class Curso {
	// Properties
	public $url;
	public $title;
	public $descripcion;
	public $id=-1;
  
	// Methods
	function set_url($name) {
	  $this->url = $name;
	}
	function get_url() {
	  return $this->url;
	}

	function set_id($name) {
		$this->id = $name;
	  }
	  function get_id() {
		return $this->id;
	  }
	function set_title($name) {
		$this->title = $name;
	  }
	  function get_title() {
		return $this->title;
	  }
	  function set_descripcion($name) {
		$this->descripcion = $name;
	  }
	  function get_descripcion() {
		return $this->descripcion;
	  }
  }

?>