<?php

class obtainFaqAnswer {
	
	const FAQ_PATH = "preguntas-frecuentes/resources/faq.html";
	public $xpath;
	
	public function __construct() {
		$dom = new DOMDocument();
		$dom->encoding = 'utf-8';
		@$dom->loadHTML(file_get_contents(self::FAQ_PATH));
		$this->xpath = new DOMXpath($dom);
	}
	
	public function getAnswer($id) {
		$nodes = $this->xpath->query("//div[@id='".$id."']/div[@class='panel-body']");
		if($nodes->length == 1) {
			$answerHtml = $nodes->item(0)->ownerDocument->saveHTML($nodes->item(0));
			return $answerHtml;
		} else return "";
	}
	
}

?>