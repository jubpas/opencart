<?php
class ModelExtensionPaymentBankTmb extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/bank_tmb');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_bank_tmb_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('payment_bank_tmb_total') > 0 && $this->config->get('payment_bank_tmb_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_bank_tmb_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'bank_tmb',
				'title'      => '<img src= "' . $this->config->get('config_url') . 'image/data/payment/bank_tmb.png" /> ' . $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_bank_tmb_sort_order')
			);
		}

		return $method_data;
	}
}