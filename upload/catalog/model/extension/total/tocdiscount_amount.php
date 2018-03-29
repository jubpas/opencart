<?php
class ModelExtensionTotalTocDiscountAmount extends Model {
	public function getTotal($total ) {
		 
	 	if ($this->cart->getSubTotal()) {
			$this->language->load('extension/total/tocdiscount_amount');
		 	  	    $discount = 0;
					$percent =  0;
					$order_total   = $this->cart->getSubTotal();
					$rates = explode(',', $this->config->get('total_tocdiscount_amount_percent'));

					foreach ($rates as $rate) {
  						$data = explode(':', $rate);
                       
						if ($order_total <= $data[0]) {
							 
							if (isset($data[1])) {
    							$discount = $order_total * -1*(   $data[1] /100 ) ;
								$percent = $data[1];
								
							} else  {
								$discount = 0;
								$percent = 0;
							}
					
   							break;
  						}
					}
			$total['totals'][] = array(
				'code'       => 'tocdiscount_amount' ,
        		'title'      =>  $this->language->get('text_discount_amount')   . '(' . $percent . '%)' , 
        		'text'       => $discount ,
        		'value'      => $discount,
				'sort_order' => $this->config->get('total_tocdiscount_amount_sort_order')
			);
 
			$total['total'] += $discount; 
		} 
	}
}