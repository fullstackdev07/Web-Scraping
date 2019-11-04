<?php


Class domClass{

	private $doc, $html_content;

	#iniciate the class (requires url)
    public function __construct($html_content) {
        $this->html_content     = ($html_content && $html_content!=="") ? trim($html_content) : "";
        $this->setDom();
    }

   	public function setDom()
   	{
   		$this->doc = new DOMDocument();
  		libxml_use_internal_errors( true );
  		@$this->doc->loadHTML( $this->html_content);
   	}

   	public function getDataById($id='')
   	{
      // print_r($this->doc); die;

      $content_node = '';
   		if($id && $id!==''){
   			$content_node=$this->doc->getElementById($id);
   		}

      return $content_node;
   	}

   	public function getDataByClass($class_nm ='')
   	{
   		
   		if($class_nm=="" || $class_nm ==null) return [];

   		$classdata = [];
   		$finder = new DomXPath($this->doc);
  		$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $class_nm ')]");

  		foreach ($nodes as $node) 
	    {	
	    	$tmp_dom = new DOMDocument(); 

	    	$tmp_dom->appendChild($tmp_dom->importNode($node,true));
	    	$classdata[] = trim($tmp_dom->saveHTML());
	    }

	    return $classdata;

   	}

   	public function getDataByTag($tag_nm='' , $attr_nm='' , $attr_val ='')
   	{

   		if($tag_nm=="" || $tag_nm ==null) return [];

   		$items = $this->doc->getElementsByTagName($tag_nm); 
		$resultData= [];
		if($items && count($items) > 0){
			foreach($items as $value) 
			{ 
			  
          if($attr_nm && $attr_val && $attr_val!==""){
            $attrs = $value->attributes;
              if($attrs && $attrs->getNamedItem($attr_nm) && $attrs->getNamedItem($attr_nm)->nodeValue ==$attr_val)$resultData[]=$value->nodeValue;
          }
          else{
            $resultData[] = $value->nodeValue; 
          }
			}
		}

		return $resultData;
		 
   	}

   	public function getAttrDataByTag($tag_nm='', $attr_nm='' , $attr_val ='' , $attr_nm_2 ='')
   	{
   		if($tag_nm=="" || $tag_nm ==null || $attr_nm=="" || $attr_nm ==null) return [];

   		$items = $this->doc->getElementsByTagName($tag_nm); 
		$resultData= [];

		if($items && count($items) > 0){
			foreach($items as $value) 
			{ 
				$attrs = $value->attributes;

          if($attr_nm_2 && $attr_nm_2!==""){
              if($attrs && $attrs->getNamedItem($attr_nm) && $attrs->getNamedItem($attr_nm_2) && $attrs->getNamedItem($attr_nm)->nodeValue ==$attr_val)$resultData[]=$attrs->getNamedItem($attr_nm_2)->nodeValue;
          }else{
              if($attrs && $attrs->getNamedItem($attr_nm))$resultData[]=$attrs->getNamedItem($attr_nm)->nodeValue;
          }

  				
			}
		}

		return $resultData;

   	}

}



?>