	<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Apkcode extends CI_Controller {
		public function __construct(){

			parent::__construct();
			date_default_timezone_set('Asia/Kolkata');
		$this->load->model('Validate_model');
		$this->load->model('Applogin_model');

   
		}


		public function CategoryList()  // done
		{	

			$wheredata = array('param_status' => 1);
		$category_data  = $this->Crud_model->get_all_data_sp('CALL `GetCategoryList_SP`(?)',$wheredata)->result_array();
		$jsondata=[];
		foreach ($category_data as $key => $value) {
			$jsondata['category_id']=$value['category_id'];
			$jsondata['category_name']=$value['category_name'];
			if(!empty($value['category_image'])){
				$jsondata['category_image']="http://ceylon.pinnalsoft.com/images/categories/".$value['category_image'];
			}else{
				$jsondata['category_image']="http://ceylon.pinnalsoft.com/images/empty.png";
			}
		}

		if(!empty($jsondata))
		{
			$arr = array('status' => array('status' => 'OK','message' => 'Categories Available.'), 'details' => $jsondata );
		}
		else 
		{
			$arr = array('status' => array('status' => 'KO','message' => 'No-Categories Available.'), 'details' => new ArrayObject());
		}
		echo json_encode($arr,JSON_PRETTY_PRINT);

	}

	public function SubCategoryList2()
	{	
		$wheredata = array('param_status' => 1);
	$sub_cat_data  = $this->Crud_model->get_records("sub_categories");
	$list=[];
	foreach($sub_cat_data as $sub_data){
		$temp=[];
		$all_cat_id=json_decode($sub_data['category_id'],true);
		$sub_data['category_name']='';
		foreach($all_cat_id as $catid){
			$wheredata=array('category_id'=>$catid);
			$cat_data=$this->Crud_model->get_records_where('categories',$wheredata);
			if($cat_data[0]['category_name']!=''){
			$sub_data['category_name'].=$cat_data[0]['category_name'].',';
		}
		}
		$list[]=$sub_data;
	}
		$jsondata=[];
		$data=[];
		foreach ($list as $key => $value) {
				$data['sub_cat_id']=$value['sub_cat_id'];
				$data['category_name']=$value['category_name'];
				$data['sub_cat_name']=$value['sub_cat_name'];
			if(!empty($value['sub_cat_image'])){
				$data['sub_cat_image']="http://ceylon.pinnalsoft.com/images/sub_categories/".$value['sub_cat_image'];
			}else{
				$data['sub_cat_image']="http://ceylon.pinnalsoft.com/images/empty.png";
			}
			$jsondata[]=$data;
		}


	echo json_encode($jsondata,JSON_PRETTY_PRINT);
}


	public function SubCategoryList()   // done
	{	 
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
			return; 

		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		$valid_array = array('category_id');

		$baseUrl = base_url();
		
		if($this->Validate_model->CheckParams($decoded, $valid_array))  return;

	$cat_id  = $decoded['category_id'];
	$sub_cat_data  = $this->Crud_model->get_records("sub_categories");
	$list=[];
	foreach($sub_cat_data as $sub_data){
		$temp=[];
		$all_cat_id=json_decode($sub_data['category_id'],true);

		if(in_array($cat_id, $all_cat_id)) $list[]=$sub_data;
	}
		$jsondata=[];
		$data=[];
		foreach ($list as $key => $value) {
				$data['sub_cat_id']=$value['sub_cat_id'];
				//$data['category_name']=$value['category_name'];
				$data['sub_cat_name']=$value['sub_cat_name'];
			if(!empty($value['sub_cat_image'])){
				$data['sub_cat_image']= $baseUrl ."images/sub_categories/".$value['sub_cat_image'];
			}else{
				$data['sub_cat_image']= $baseUrl ."images/empty.png";
			}
			$jsondata[]=$data;
		}

		if(!empty($jsondata))
		{
			$arr = array('status' => array('status' => 'OK','message' => 'Sub-Categories Available'), 'details' => $jsondata );
		}
		else 
		{
			$arr = array('status' => array('status' => 'KO','message' => 'No Sub-Categories Available'), 'details' => new ArrayObject());
		}
		echo json_encode($arr,JSON_PRETTY_PRINT);
}

public function availableLocations($lat1,$lng1){
	$loc=$this->Crud_model->get_records_where("delivery_location",array('status' => 1));
	$temp=[];
	foreach ($loc as $key => $loc_data) {
		$distance=$this->Crud_model->getDistance($lat1,$lat2);
		if ($distance<=$loc_data['distance']) {
			$temp[]=$loc_data['del_loc_id'];

		}

	}
	$data['available_locations']=$temp;
	return $data;
}


public function ProductList()
{	
			header("Content-Type: application/json");
			$cust_data 	= 		json_decode(file_get_contents('php://input')); //For decode the JSON string
			$cust_lat	= 		$cust_data->latitude;
			$cust_lng	= 		$cust_data->longitude;
			$page_no	= 		$cust_data->page_no;
			
			
			
			$loc=$this->Crud_model->get_records_where("delivery_location",array('status' => 1));
			$sup=$this->Crud_model->get_records_where("suppliers",array('status' => 1));
			$temp1=[];


		//	print_r($loc);
		//	print_r($sup); 
			
			foreach ($loc as $key => $loc_data) {
				$customer_distance=$this->Crud_model->getDistance($cust_lat,$cust_lng,$loc_data['del_lat'],$loc_data['del_lng']);		
					//$temp1[]=$loc_data['location'].'------->'.$customer_distance;
				if ($customer_distance<=$loc_data['distance']) {
					$temp1[]=$loc_data['del_loc_id'];

				}

			}
			$data['available_delivery_location_for_customer']=$temp1;
			//print_r($data['available_delivery_location_for_customer']); exit;

			/*foreach ($sup as $key => $sup_data) {
				$temp2=[];

				foreach ($loc as $key => $loc_data) {
					$supplier_distance=$this->Crud_model->getDistance($sup_data['latitude'],$sup_data['longitude'],$loc_data['del_lat'],$loc_data['del_lng']);		
						$temp2[]=$sup_data['location'].'------->'.$loc_data['location'].'------->'.$supplier_distance;
					if ($supplier_distance<=$loc_data['distance']) {
						$temp2[]=$loc_data['del_loc_id'].'-----'.$supplier_distance;

					}
				}
				$data['available_delivery_location_for_supplier']=$temp2;
			}*/
			foreach ($data['available_delivery_location_for_customer'] as $key => $value) {
				$temp3=[];
				$temp4=[];
				$ldata=$this->Crud_model->get_records_where('delivery_location',array('del_loc_id'=>$value,'status'=>1));
				foreach ($sup as $key => $sup_data) {
					$supplier_distance=$this->Crud_model->getDistance($sup_data['latitude'],$sup_data['longitude'],$ldata[0]['del_lat'],$ldata[0]['del_lng']);		
					if ($supplier_distance<=$ldata[0]['distance']) {
						$pdata=$this->Crud_model->get_by_sql("SELECT DISTINCT product_id FROM supplier_products WHERE status=1 AND supplier_id='".$sup_data['supplier_id']."'");
						$temp3[]=array('supplier_id'=>$sup_data['supplier_id'],'distance'=>$supplier_distance);
						$temp4[]= $pdata;

					}
				}
			}     

			//print_r($temp4);

			array_multisort( array_column($temp3, "distance"), SORT_ASC, $temp3 );
			$data['available_suppliers'] = $temp3;

			//print_r($data['available_suppliers']);

			$available_products=$temp4;
			$oneDimensionalArray = call_user_func_array('array_merge', $available_products);
			$final = array();
			foreach ($oneDimensionalArray as $array) {
				if(!in_array($array, $final)){
					$final[] = $array;
				}
			}
			
			//exit;  
			$pro=[];	
			$wheredata = array('status' => 1, );
			$items_per_page=2;
			//$total_rows=$this->Crud_model->count_all_where('products',array('status'=>1));
			//$total_pages = ceil($total_rows / $items_per_page);
			if(!empty($page_no)){
				$page_no	= 		$cust_data->page_no;
			}else{
				$page_no	=1;
			}
			$offset = ($page_no-1) * $items_per_page;

			$productlist=$this->Crud_model->get_by_sql("SELECT products.*,supplier_products.* FROM products JOIN supplier_products ON supplier_products.product_id=products.product_id WHERE supplier_products.status=1 AND products.status=1 GROUP BY  supplier_products.product_id LIMIT $items_per_page OFFSET $offset");
			
			for($i=0;$i<count($productlist);$i++) {
				$cat_name=[];
				$sub_cat_name=[];
				foreach($final as $key=>$arrval){
					if($arrval['product_id']==$productlist[$i]['product_id']){
						$pro[]=$productlist[$i];
						$cat=json_decode($productlist[$i]['category_id'],true);
						$sub_cat=json_decode($productlist[$i]['sub_cat_id'],true);
						$temp1=[];
						$temp2=[];
						if(is_array($cat)){
							foreach ($cat as $key => $main_cat) {
								$wheredata=array('category_id' => $main_cat);
								$cat_data=$this->Crud_model->get_records_where('categories',$wheredata);
								$temp1[]=$cat_data[0]['category_name'];
							}
							$cat_name[]=$temp1;
							foreach ($sub_cat as $key => $subcat) {
								$wheredata=array('sub_cat_id' => $subcat);
								$sub_data=$this->Crud_model->get_records_where('sub_categories',$wheredata);
								$temp2[]=$sub_data[0]['sub_cat_name'];
							}
							$sub_cat_name[]=$temp2;
						}

					}
				}
				$pro[$i]['category_name']=$cat_name;
				$pro[$i]['sub_category_name']=$sub_cat_name;
			}
			$data['pro_data']=$pro;
			$jsondata=[];
			$filtered_data=[];
			foreach ($data['pro_data'] as $key => $value) {
			if(!empty($value['product_image'])){
				$value['product_image']="http://ceylon.pinnalsoft.com/images/products/".$value['product_image'];
			}else{
				$value['product_image']="http://ceylon.pinnalsoft.com/images/empty.png";
			}
				$filtered_data['product_id']=$value['product_id'];
				$filtered_data['category_name']=$value['category_name'];
				$filtered_data['sub_category_name']=$value['sub_category_name'];
				$filtered_data['product_code']=$value['product_code'];
				$filtered_data['product_name']=$value['product_name'];
				$filtered_data['product_image']=$value['product_image'];
				$filtered_data['product_description']=$value['product_description'];
				$filtered_data['product_units']=$value['product_units'];
				$filtered_data['product_volume']=$value['product_volume'];
				$filtered_data['product_price']=$value['admin_price'];


			$jsondata[]=$filtered_data;
			}
		echo json_encode($jsondata,JSON_PRETTY_PRINT);
		}


		public function searchProduct()
		{	
			header("Content-Type: application/json");
			$cust_data 	= 		json_decode(file_get_contents('php://input')); //For decode the JSON string
			$cust_lat	= 		$cust_data->latitude;
			$cust_lng	= 		$cust_data->longitude;
			$product_name	= 		$cust_data->product_name;
			$loc=$this->Crud_model->get_records_where("delivery_location",array('status' => 1));
			$sup=$this->Crud_model->get_records_where("suppliers",array('status' => 1));
			$temp1=[];
			
			foreach ($loc as $key => $loc_data) {
				$customer_distance=$this->Crud_model->getDistance($cust_lat,$cust_lng,$loc_data['del_lat'],$loc_data['del_lng']);		
					//$temp1[]=$loc_data['location'].'------->'.$customer_distance;
				if ($customer_distance<=$loc_data['distance']) {
					$temp1[]=$loc_data['del_loc_id'];

				}

			}
			$data['available_delivery_location_for_customer']=$temp1;

			/*foreach ($sup as $key => $sup_data) {
				$temp2=[];

				foreach ($loc as $key => $loc_data) {
					$supplier_distance=$this->Crud_model->getDistance($sup_data['latitude'],$sup_data['longitude'],$loc_data['del_lat'],$loc_data['del_lng']);		
						$temp2[]=$sup_data['location'].'------->'.$loc_data['location'].'------->'.$supplier_distance;
					if ($supplier_distance<=$loc_data['distance']) {
						$temp2[]=$loc_data['del_loc_id'].'-----'.$supplier_distance;

					}
				}
				$data['available_delivery_location_for_supplier']=$temp2;
			}*/
			foreach ($data['available_delivery_location_for_customer'] as $key => $value) {
				$temp3=[];
				$temp4=[];
				$ldata=$this->Crud_model->get_records_where('delivery_location',array('del_loc_id'=>$value,'status'=>1));
				foreach ($sup as $key => $sup_data) {
					$supplier_distance=$this->Crud_model->getDistance($sup_data['latitude'],$sup_data['longitude'],$ldata[0]['del_lat'],$ldata[0]['del_lng']);		
					if ($supplier_distance<=$ldata[0]['distance']) {
						$pdata=$this->Crud_model->get_by_sql("SELECT DISTINCT product_id FROM supplier_products WHERE status=1 AND supplier_id='".$sup_data['supplier_id']."'");
						$temp3[]=array('supplier_id'=>$sup_data['supplier_id'],'distance'=>$supplier_distance);
						$temp4[]= $pdata;

					}
				}
			}
			array_multisort( array_column($temp3, "distance"), SORT_ASC, $temp3 );
			$data['available_suppliers']=$temp3;
			$available_products=$temp4;
			$oneDimensionalArray = call_user_func_array('array_merge', $available_products);
			$final = array();
			foreach ($oneDimensionalArray as $array) {
				if(!in_array($array, $final)){
					$final[] = $array;
				}
			}
			$pro=[];	
			$wheredata = array('status' => 1, );
			$items_per_page=2;
			//$total_rows=$this->Crud_model->count_all_where('products',array('status'=>1));
			//$total_pages = ceil($total_rows / $items_per_page);
			if(!empty($page_no)){
				$page_no	= 		$cust_data->page_no;
			}else{
				$page_no	=1;
			}
			$offset = ($page_no-1) * $items_per_page;

			$productlist=$this->Crud_model->get_by_sql("SELECT products.*,supplier_products.* FROM products JOIN supplier_products ON supplier_products.product_id=products.product_id WHERE supplier_products.status=1 AND products.status=1 AND products.product_name LIKE '%$product_name' GROUP BY  supplier_products.product_id LIMIT $items_per_page OFFSET $offset");
			for($i=0;$i<count($productlist);$i++) {
				$cat_name=[];
				$sub_cat_name=[];
				foreach($final as $key=>$arrval){
					if($arrval['product_id']==$productlist[$i]['product_id']){
						$pro[]=$productlist[$i];
						$cat=json_decode($productlist[$i]['category_id'],true);
						$sub_cat=json_decode($productlist[$i]['sub_cat_id'],true);
						$temp1=[];
						$temp2=[];
						if(is_array($cat)){
							foreach ($cat as $key => $main_cat) {
								$wheredata=array('category_id' => $main_cat);
								$cat_data=$this->Crud_model->get_records_where('categories',$wheredata);
								$temp1[]=$cat_data[0]['category_name'];
							}
							$cat_name[]=$temp1;
							foreach ($sub_cat as $key => $subcat) {
								$wheredata=array('sub_cat_id' => $subcat);
								$sub_data=$this->Crud_model->get_records_where('sub_categories',$wheredata);
								$temp2[]=$sub_data[0]['sub_cat_name'];
							}
							$sub_cat_name[]=$temp2;
						}

					}
				}
				$pro[$i]['category_name']=$cat_name;
				$pro[$i]['sub_category_name']=$sub_cat_name;
			}
			$data['pro_data']=$pro;
			$jsondata=[];
			$filtered_data=[];
			foreach ($data['pro_data'] as $key => $value) {
			if(!empty($value['product_image'])){
				$value['product_image']="http://ceylon.pinnalsoft.com/images/products/".$value['product_image'];
			}else{
				$value['product_image']="http://ceylon.pinnalsoft.com/images/empty.png";
			}
				$filtered_data['product_id']=$value['product_id'];
				$filtered_data['category_name']=$value['category_name'];
				$filtered_data['sub_category_name']=$value['sub_category_name'];
				$filtered_data['product_code']=$value['product_code'];
				$filtered_data['product_name']=$value['product_name'];
				$filtered_data['product_image']=$value['product_image'];
				$filtered_data['product_description']=$value['product_description'];
				$filtered_data['product_units']=$value['product_units'];
				$filtered_data['product_volume']=$value['product_volume'];
				$filtered_data['product_price']=$value['admin_price'];
				


			$jsondata[]=$filtered_data;
			}
		echo json_encode($jsondata,JSON_PRETTY_PRINT);

		}



		public function OffersList()
		{	
			$wheredata = array('param_status' => 1);
		//	$data  = $this->Crud_model->get_all_data_sp('CALL `GetOffersList_SP`(?)',$wheredata)->result_array();
			$data = $this->Crud_model->get_records_where("offers", array( "status" => 1) );
			$jsondata=[];
			foreach ($data as $key => $value) {
				if(!empty($value['offer_image'])){
					$value['offer_image']="http://ceylon.pinnalsoft.com/images/offers/".$value['offer_image'];
				}else{
					$value['offer_image']="http://ceylon.pinnalsoft.com/images/empty.png";
				}
				$jsondata[]=$value;
			}

if(!empty($jsondata))
			{
				$arr = array('status' => array('status' => 'OK','message' => "Offers Available"), 'details' => $jsondata );
			}
			else
			{
				$arr = array('status' => array('status' => 'KO','message' => 'No Offers Available'), 'details' => new ArrayObject());
			}
			echo json_encode($arr,JSON_PRETTY_PRINT);
		}


	public function BuySaveOffersList()
	{	
		$wheredata = array('param_status' => 1);
		$off_data  = $this->Crud_model->get_records('buy_save_offer');
		$data=[];
		foreach ($off_data as $key => $value) {
			$all_pro_id=json_decode($value['product_id']);
			$d=0;
			foreach ($all_pro_id as $key => $proid) {
				$wheredata=array('product_id' => $proid);
				$pro_data=$this->Crud_model->get_records_where('products',$wheredata);
				$value['product_name'][$d]=$pro_data[0]['product_name'];
				
			$d++; }
			$data[]=$value;
		}
		$jsondata=[];
			foreach ($data as $key => $value) {
				if(!empty($value['offer_image'])){
					$value['offer_image']="http://ceylon.pinnalsoft.com/images/offers/".$value['offer_image'];
				}else{
					$value['offer_image']="http://ceylon.pinnalsoft.com/images/empty.png";
				}
				$jsondata[]=$value;
			}
			echo json_encode($jsondata,JSON_PRETTY_PRINT);
	}


	public function SubscriptionList()
	{	
		$wheredata = array('param_status' => 1);
		$sub_data  = $this->Crud_model->get_records('subscribe_save');
		$data=[];
		foreach ($sub_data as $key => $value) {
		$all_pro_id=json_decode($value['product_id']);
		$d=0;
		foreach ($all_pro_id as $key => $proid) {
			$wheredata=array('product_id' => $proid);
			$pro_data=$this->Crud_model->get_records_where('products',$wheredata);
			$value['product_name'][$d]=$pro_data[0]['product_name'];
			$d++; 
		}
		$data[]=$value;
	}
	$jsondata=[];
			foreach ($data as $key => $value) {
				if(!empty($value['subscribe_img'])){
					$value['subscribe_img']="http://ceylon.pinnalsoft.com/images/subscribes/".$value['subscribe_img'];
				}else{
					$value['subscribe_img']="http://ceylon.pinnalsoft.com/images/empty.png";
				}
				$jsondata[]=$value;
			}
			echo json_encode($jsondata,JSON_PRETTY_PRINT);
	}


public function CreateOrder()
{	
	header("Content-Type: application/json");
			$order_data 	= json_decode(file_get_contents('php://input')); //For decode the JSON string
			$post_data = array(
				'order_no' 				=> 'ORD'.date('m').rand(00000,99999).date('d'),
				'order_type' 			=> $order_data->orderDetails->order_type,
				'shedule_time' 			=> $order_data->orderDetails->shedule_time,
				'customer_id' 			=> $order_data->orderDetails->customer_id,
				'order_cost'      		=> $order_data->orderDetails->order_cost,
				'offer_value'      		=> $order_data->orderDetails->offer_value,
				'delivery_charge' 		=> $order_data->orderDetails->delivery_charge,
				'total_cost'  			=> $order_data->orderDetails->total_cost,
				'address_id' 			=> $order_data->orderDetails->address_id,
				'latitude' 				=> $order_data->orderDetails->latitude,
				'longitude'				=> $order_data->orderDetails->longitude,
				'distance'				=> $order_data->orderDetails->distance,
				'payment_type' 			=> $order_data->orderDetails->payment_type,
				'order_status'			=>	'1',
				'date_created' 			=> date('Y-m-d'),
				'status'				=> '1'
			);
			$insert=$this->Crud_model->insert_data('orders',$post_data);

			foreach ($order_data->productDetails as $key => $value) {
				$product_data = array('order_id' 			=> 	$insert, 
					'product_id'			=>	$value->product_id,
					'quantity'			=>	$value->quantity,
					'cost'				=>	$value->cost,
					'status'				=> 	'1',
					'date_created' 		=> 	date('Y-m-d')
				);
				$insert=$this->Crud_model->insert_data('order_products',$product_data);
			}
			if(!empty($insert))
			{
				$arr = array('status' => array('status' => 'OK','message' => "Order created successfully.."), 'details' => array('order_id' => $insert));
			}
			else
			{
				$arr = array('status' => array('status' => 'KO','message' => 'Something wrong to order...'), 'details' => new ArrayObject());
			}
			echo json_encode($arr,JSON_PRETTY_PRINT);
		}

		public function CreateSubscribeSaveOrder()
		{	

			header("Content-Type: application/json");
			$order_data 	= json_decode(file_get_contents('php://input')); //For decode the JSON string
			$post_data = array(
				'order_no' 				=> rand(00000,99999),
				'order_type' 			=> $order_data->orderDetails->order_type,
				'subscribe_id' 			=> $order_data->orderDetails->subscribe_id,
				'shedule_time' 			=> $order_data->orderDetails->shedule_time,
				'customer_id' 			=> $order_data->orderDetails->customer_id,
				'order_cost'      		=> $order_data->orderDetails->order_cost,
				'offer_value'      		=> $order_data->orderDetails->offer_value,
				'delivery_charge' 		=> $order_data->orderDetails->delivery_charge,
				'total_cost'  			=> $order_data->orderDetails->total_cost,
				'address_id' 			=> $order_data->orderDetails->address_id,
				'latitude' 				=> $order_data->orderDetails->latitude,
				'longitude'				=> $order_data->orderDetails->longitude,
				'distance'				=> $order_data->orderDetails->distance,
				'payment_type' 			=> $order_data->orderDetails->payment_type,
				'date_created' 			=> date('Y-m-d'),
				'status'				=> '1'
			);
			$insert=$this->Crud_model->insert_data('orders',$post_data);

			foreach ($order_data->productDetails as $key => $value) {
				$product_data = array('order_id' 			=> 	$insert, 
					'product_id'			=>	$value->product_id,
					'quantity'			=>	$value->quantity,
					'cost'				=>	$value->cost,
					'status'				=> 	'1',
					'date_created' 		=> 	date('Y-m-d')
				);
				$insert_p=$this->Crud_model->insert_data('order_products',$product_data);
			}
			if(!empty($insert_p))
			{
				$arr = array('status' => array('status' => 'OK','message' => "Order created successfully.."), 'details' => array('order_id' => $insert));
			}
			else
			{
				$arr = array('status' => array('status' => 'KO','message' => 'Something wrong to order...'), 'details' => new ArrayObject());
			}
			echo json_encode($arr,JSON_PRETTY_PRINT);
		}


	public function OrderRequestNotificationList()
		{	$wheredata = array('status' => 0);
		$req_data  = $this->Crud_model->get_records_where('order_request',$wheredata);
		echo json_encode($req_data,JSON_PRETTY_PRINT);
	}

	public function AcceptOrderRequest()
	{	
		header("Content-Type: application/json");
				$accept_data 	= json_decode(file_get_contents('php://input')); //For decode the JSON string
				$order_id 					= $accept_data->order_id;
				$request_id 			= $accept_data->request_id;
				$driver_id 					= $accept_data->driver_id;
				$status 					= $accept_data->status;
				$wheredata 			= 	array('order_id' => $order_id );
				$update_data 		= 	array(	
					'driver_id' 			=> $driver_id,
					'status' 	=> $status);
				$update_data2 		= 	array(	
					'driver_id' 			=> $driver_id,
					'order_status' 			=> 3
				);
				$update=$this->Crud_model->update_data('order_request',$update_data,$wheredata);
				$update2=$this->Crud_model->update_data('orders',$update_data2,$wheredata);
				$req_data  = $this->Crud_model->get_records_where('order_request',$wheredata);
				if($update && $update2)
				{
					$arr = array('status' => array('status' => 'OK','message' => "Order accepted successfully.."), 'details' => array('request_id' => $request_id));
				}
				else
				{
					$arr = array('status' => array('status' => 'KO','message' => 'Something wrong to order...'), 'details' => new ArrayObject());
				}
				echo json_encode($arr,JSON_PRETTY_PRINT);
			}



	public function addtoFav() //done
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
		return; 
  
		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		$valid_array = array('customer_id','product_id');
		
		if($this->Validate_model->CheckParams($decoded, $valid_array))
			return;																						
			
		$post_data = array(
			'customer_id' 			=> $decoded['customer_id'],
			'product_id' 			=> $decoded['product_id'],
		);

		$check = $this->Crud_model->get_records_where("wish_list", $post_data);

		if(is_array($check))
		{
				$arr = array('status' => array('status' => 3,'message' => 'Item Already In WishList'), 'details' => new ArrayObject());
		}
		else
		{

		$post_data = array(
			'customer_id' 			=> $decoded['customer_id'],
			'product_id' 			=> $decoded['product_id'],
			'date_created' 			=> date('Y-m-d')
		);

			 //For get the user_id for respective mobile number..
			$cartId=$this->Crud_model->insert_data('wish_list',$post_data);
			if(empty($cartId))
			{
			$arr = array('status' => array('status' => 0,'message' => 'Not Added To WishList'), 'details' => new ArrayObject());
			}
			else 
			{
					$arr = array('status' => array('status' => 1,'message' => 'Added To WishList'), 'details' => array('wish_list_id' => $cartId));
			}
		}
		
		echo json_encode($arr,JSON_PRETTY_PRINT);		
	}

	public function removeFav()  //done
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
		return; 
  
		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		$valid_array = array('wish_list_id');
		
		if($this->Validate_model->CheckParams($decoded, $valid_array))
			return;
			
		// $post_data = array(
		// 	'user_id' 			=> $decoded['user_id'],
		// 	'product_id' 			=> $decoded['product_id']	);

		$post_data = array(	'wish_list_id' => $decoded['wish_list_id']  );

		$update = $this->Crud_model->delete_data("wish_list", $post_data);
		$arr = array('status' => array('status' => 1,'message' => 'Item Removed WishList'), 'details' => new ArrayObject());
		echo json_encode($arr,JSON_PRETTY_PRINT);		
	}	

	public function favList()  //done
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
		return; 
  
		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		$valid_array = array('customer_id');
		
		if($this->Validate_model->CheckParams($decoded, $valid_array))
			return;
			
		$post_data = array(
			'customer_id' 			=> $decoded['customer_id'],
		);
		
		$getVal  = $this->Applogin_model->getall_datas_sp('CALL `FavList_sp`(?)',$post_data)->result_array();

		//print_r($getVal);
	
		if(empty($getVal))
		{
		$arr = array('status' => array('status' => 0,'message' => 'No Items in Your WishList'), 'details' => new ArrayObject());
		}
		else 
		{
				$arr = array('status' => array('status' => 1,'message' => 'List Of WishList Items'), 'details' => $getVal );
		}
		
		echo json_encode($arr,JSON_PRETTY_PRINT);				
	}



	public function addtoCart()  //done
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
		return; 

		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		$valid_array = array('customer_id','product_id','product_count');
		
		if($this->Validate_model->CheckParams($decoded, $valid_array))
			return;

		$post_data = array(
			'user_id' 			=> $decoded['customer_id'],
			'product_id' 	    => $decoded['product_id'],
			'status'   => 1	);

		$get = $this->Crud_model->get_records_where("cart", $post_data);

		if(is_array($get))
		{
	$arr = array('status' => array('status' => 3,'message' => 'Item Already In Cart'), 'details' => $get[0]['cart_id'] );
		}
		else
		{	
			$post_data = array(
				'user_id' 			=> $decoded['customer_id'],
				'product_id' 	    => $decoded['product_id'],
				'product_count'     => $decoded['product_count']	);

			$cartId=$this->Applogin_model->insert_data('cart',$post_data);

			if(empty($cartId))
			{
			$arr = array('status' => array('status' => 0,'message' => 'Not Added To Cart'), 'details' => new ArrayObject());
			}
			else 
			{
					$arr = array('status' => array('status' => 1,'message' => 'Added To Cart'), 'details' => array('cart_id' => $cartId));
			}
		}
		
		echo json_encode($arr,JSON_PRETTY_PRINT);		
	}

	public function removeCart()  //done
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
		return; 
  
		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		$valid_array = array('cart_id');
		if($this->Validate_model->CheckParams($decoded, $valid_array))
			return;
			
		$post_data = array(  'cart_id' 			=> $decoded['cart_id']  );
		$del = $this->Crud_model->delete_data("cart",  $post_data);

		if(1)
		{
				$arr = array('status' => array('status' => 1,'message' => 'Item Removed in Cart'), 'details' => new ArrayObject());
		}
		
		echo json_encode($arr,JSON_PRETTY_PRINT);		
	}		

	public function cartList()  //done
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
		return; 
  
		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		$valid_array = array('customer_id');
		
		if($this->Validate_model->CheckParams($decoded, $valid_array))
			return;
			
		$post_data = array(
			'user_id' 			=> $decoded['customer_id'],    
		);
		
		 //For get the user_id for respective mobile number..     
		$getVal  = $this->Applogin_model->getall_datas_sp('CALL `CartList_sp`(?)',$post_data)->result_array();

		$amount = 0;
		foreach($getVal as $val)
		{
			$amount  += $val['price']; 
		}
		
		if(empty($getVal))
		{
		$arr = array('status' => array('status' => 0,'message' => 'No Items in Your Cart'), 'details' => new ArrayObject());
		}
		else 
		{
				$arr = array('status' => array('status' => 1,'message' => 'List Of Cart Items'), 'details' => $getVal, 'totalAmount' => $amount  );
		}
		
		echo json_encode($arr,JSON_PRETTY_PRINT);				
	}	
	

	public function driversList() 
	{
		header("Content-Type: application/json");
		if($this->Validate_model->CheckRequestType())
		return; 	

		$getVal = $this->Crud_model->get("drivers", array( "status" => 1) );

		if(empty($getVal))
		{
		$arr = array('status' => array('status' => 0,'message' => 'No drivers Available'), 'details' => new ArrayObject());
		}
		else 
		{
				$arr = array('status' => array('status' => 1,'message' => 'Drivers Available'), 'details' => $getVal  );
		}
		
		echo json_encode($arr,JSON_PRETTY_PRINT);				
	}	















}