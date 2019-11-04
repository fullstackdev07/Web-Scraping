<?php

include "dom-class.php";

class Deals{

	private $scrapeObj, $domain;
	public function __construct($scrapeObj , $domain)
	{
		$this->scrapeObj = $scrapeObj;
		$this->doamin = $domain;
	}


	public function init($html = '')
	{
		// echo $html; die;
		$domObj = new domClass($html);

		$classByresp = $domObj->getDataByClass('tdc-row');
		// print_r($classByresp); die;

		if($classByresp && count($classByresp)  > 0){
			foreach ($classByresp as $key => $cData) {
				$domnewObj = new domClass($cData);

				$data =  $domnewObj->getDataByTag('b');

				$dealsLink = [];

				if($data && count($data)){
					if(in_array("Topdeals", $data) ){
						$links = $domnewObj->getAttrDataByTag('a','href');
						// print_r($links);
						$dealsLink[] = (object)['dealstype'=>"TopsDelas", "linkList" =>$links];
						
					}
					else if(in_array("De nieuwste deals", $data)){
						$links = $domnewObj->getAttrDataByTag('a','href');
						$dealsLink[] = (object)['dealstype'=>"NewDelas", "linkList" =>$links];
					}
					
				}

				// print_r($dealsLink);

				$this->setDealsData($dealsLink);

				// print_r($data); 
				// echo  '<br>';
			}
		}
	}




	public function setDealsData($dealsLinkList)
	{
		if($dealsLinkList && count($dealsLinkList) > 0){

			$dealsresponseList = [];

			foreach ($dealsLinkList as $key => $deallD) {
				$linkList = $deallD->linkList;
				$dealstype = $deallD->dealstype;
				// echo '<h2>'.$dealstype.'</h2>';
				// echo '<pre>';
				foreach ($linkList as $key => $dlink) {
					// echo $dlink.'<br>';
					if($dlink){
						$pageData = $this->scrapeObj->init($dlink , $this->domain);
						$respdata = $this->getAllInformationOfDeals($pageData);
						$respdata['gettingLink'] = $dlink;
						$dealsresponseList [] = (object) $respdata; 
						 // die;
					}
					
				}
			}

			// print_r($dealsresponseList);

			$this->createxmlfiles($dealsresponseList);
		}		
	}


	public function getAllInformationOfDeals($pageData)
	{
		// print_r($pageData['headers']); die;
		$domObj = new domClass($pageData['body']);
		$linkTagList =  $domObj->getAttrDataByTag('link' , 'rel' , 'shortlink', 'href');

		$product_link = ''; $productName = ''; $productImg = ''; $productPrice = '';
		if($linkTagList && count($linkTagList) > 0){
			$product_link = $linkTagList[0];
		}

		$classByresp = $domObj->getDataByClass('td-page-content');
		// print_r($classByresp); die;

		if($classByresp && count($classByresp)  > 0){
			$dealsPData = $classByresp[0];

			$domnewObj = new domClass($dealsPData);

			$pnameArr =  $domnewObj->getDataByTag('span', 'itemprop', 'name');
			// print_r($pnameArr);
			if(isset($pnameArr[0]) && $pnameArr[0]!=="Shedeals") $productName = $pnameArr[0];

			$imageArr =  $domnewObj->getAttrDataByTag('img', 'src');
			if(isset($imageArr[0]) && $imageArr[0]!=="") $productImg = $imageArr[0];

			$ppriceArr =  $domnewObj->getDataByTag('span', 'itemprop', 'price');
			if(isset($ppriceArr[0]) && $ppriceArr[0]!=="") $productPrice = $ppriceArr[0];

			// print_r($ppriceArr);



			  $str = $productName;
			  $arr = explode('â¬',$str);			  
			  $productNameNew = str_replace('â¬', '€', $str);			  
			  $dealingPrice = (isset($arr[1])) ?  str_replace(',', '.', $arr[1]) :0;

			  $discount = (float)($productPrice - $dealingPrice );

			$finalArr = ['product_link'=>$product_link, 'productName'=>$productNameNew , 'productImg'=>$productImg , 'productPrice'=>$productPrice , 'dealingPrice' =>$dealingPrice , 'discount'=> $discount ];

			// print_r($finalArr);

			return $finalArr;

		}

		# code...
	}


	public function createxmlfiles($dealList=[])
	{
		if($dealList && count($dealList) > 0){

			$dom = new DOMDocument();

			$dom->encoding = 'utf-8';

			$dom->xmlVersion = '1.0';

			$dom->formatOutput = true;

			$current_datetime = date("Y-m-d H:i");
			if (!file_exists('dealsxml')) {
			    mkdir('dealsxml', 0777, true);
			}
			$xml_file_name = 'dealsxml/deal_list_'.$current_datetime.'.xml';
			$root = $dom->createElement('Deals');
			foreach ($dealList as $key => $dealData) {
				
				if($dealData->productName && $dealData->productName!==""){

					$movie_node = $dom->createElement('deals');

						$attr_movie_id = new DOMAttr('deal_no', $key);

						$movie_node->setAttributeNode($attr_movie_id);

							$child_node_title = $dom->createElement('Title', $dealData->productName);

							$movie_node->appendChild($child_node_title);

							$child_node_link = $dom->createElement('productLink', $dealData->product_link);

							$movie_node->appendChild($child_node_link);

							$child_node_img = $dom->createElement('Img', $dealData->productImg);

							$movie_node->appendChild($child_node_img);

							$child_node_price = $dom->createElement('Price', $dealData->productPrice);

							$movie_node->appendChild($child_node_price);

							$child_node_dealprice = $dom->createElement('dealingPrice', $dealData->dealingPrice);

							$movie_node->appendChild($child_node_dealprice);

							$child_node_discount = $dom->createElement('discount', $dealData->discount);

							$movie_node->appendChild($child_node_discount);



					$root->appendChild($movie_node);

				}				

			}
			$dom->appendChild($root);

			$dom->save($xml_file_name);


		}

		echo "xml has been successfully created";
	}


}

?>