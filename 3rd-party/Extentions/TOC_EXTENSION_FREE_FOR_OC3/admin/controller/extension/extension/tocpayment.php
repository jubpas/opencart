<?php
class ControllerExtensionExtensionTocPayment extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/extension/payment');
		
		$this->load->model('setting/extension');

		$this->getList();
	}

	 public function install() {
		$this->load->language('extension/extension/payment');

		$this->load->model('setting/extension');

		if ($this->validate()) {
			$this->model_setting_extension->install('payment', $this->request->get['extension']);

			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/payment/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/payment/' . $this->request->get['extension']);

			// Call install method if it exsits
			$this->load->controller('extension/payment/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	public function uninstall() {
		$this->load->language('extension/extension/payment');
		$this->load->model('setting/extension');
		if ($this->validate()) {
			$this->model_setting_extension->uninstall('payment', $this->request->get['extension']);
			// Call uninstall method if it exsits
			$this->load->controller('extension/payment/' . $this->request->get['extension'] . '/uninstall');
			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}
	
	protected function getList() {
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_action'] = $this->language->get('column_action');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_install'] = $this->language->get('button_install');
		$data['button_uninstall'] = $this->language->get('button_uninstall');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$this->load->model('setting/extension');

		$extensions = $this->model_setting_extension->getInstalled('payment');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_APPLICATION . 'controller/extension/payment/' . $value . '.php') && !is_file(DIR_APPLICATION . 'controller/payment/' . $value . '.php')) {
				$this->model_setting_extension->uninstall('payment', $value); 
				unset($extensions[$key]);
			}
		}

		$data['extensions'] = array();

		// Compatibility code for old extension folders
		$files = glob(DIR_APPLICATION . 'controller/{extension/payment,payment}/*.php', GLOB_BRACE);

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
              if  (    (substr( $extension , 0, 3) =='toc') ||
			          (substr( $extension , 0, 5) =='bank_') ||
                    (preg_match('/bank_transfer/',$extension ))  ||
				    (preg_match('/pp_standard/',$extension ))    || 
                    (preg_match('/free_checkout/',$extension )) ||
					 (preg_match('/paysbuy/',$extension )) ||
                       (preg_match('/cod/',$extension )) 					) 
			  {
				$this->load->language('extension/payment/' . $extension);

				$text_link = $this->language->get('text_' . $extension);

				if ($text_link != 'text_' . $extension) {
					$link = $this->language->get('text_' . $extension);
				} else {
					$link = '';
				}

				$data['extensions'][] = array(
					'name'       => $this->language->get('heading_title'),
					'link'       => $link,
					'status'     => $this->config->get('payment_' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'sort_order' => $this->config->get('payment_' . $extension . '_sort_order'),
					'install'   => $this->url->link('extension/extension/tocpayment/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'uninstall' => $this->url->link('extension/extension/tocpayment/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'installed' => in_array($extension, $extensions),
					'edit'      => $this->url->link('extension/payment/' . $extension, 'user_token=' . $this->session->data['user_token'], true)
				);
			}
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('extension/extension/tocpayment', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/extension/payment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}