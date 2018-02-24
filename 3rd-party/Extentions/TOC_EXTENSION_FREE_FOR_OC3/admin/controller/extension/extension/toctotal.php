<?php
class ControllerExtensionExtensionToctotal extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/extension/toctotal');
		$this->load->model('setting/extension');
		$this->getList();
	}

	public function install() {
		$this->load->language('extension/extension/total');

		$this->load->model('setting/extension');

		if ($this->validate()) {
			$this->model_setting_extension->install('total', $this->request->get['extension']);

			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/total/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/total/' . $this->request->get['extension']);

			$this->load->controller('extension/toctotal/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->language->get('text_success');
			 $this->response->redirect($this->url->link('extension/extension/toctotal', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->getList();
	}

	public function uninstall() {
		$this->load->language('extension/extension/total');

		$this->load->model('setting/extension');

		if ($this->validate()) {
			$this->model_setting_extension->uninstall('total', $this->request->get['extension']);

				$this->load->controller('extension/toctotal/' . $this->request->get['extension'] . '/uninstall');

			$this->session->data['success'] = $this->language->get('text_success');
			 $this->response->redirect($this->url->link('extension/extension/toctotal', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->getList();
	}

	protected function getList() {
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

		$extensions = $this->model_setting_extension->getInstalled('total');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_APPLICATION . 'controller/extension/total/' . $value . '.php') && !is_file(DIR_APPLICATION . 'controller/total/' . $value . '.php')) {
				$this->model_setting_extension->uninstall('total', $value);

				unset($extensions[$key]);
			}
		}

		$data['extensions'] = array();
		
		// Compatibility code for old extension folders
		$files = glob(DIR_APPLICATION . 'controller/extension/total/toc*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				$this->load->language('extension/total/' . $extension, 'extension');

				$data['extensions'][] = array(
					'name'       => $this->language->get('extension')->get('heading_title'),
					'status'     => $this->config->get('total_' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'sort_order' => $this->config->get('total_' . $extension . '_sort_order'),
					'install'    => $this->url->link('extension/extension/toctotal/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'uninstall'  => $this->url->link('extension/extension/toctotal/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'installed'  => in_array($extension, $extensions),
					'edit'       => $this->url->link('extension/total/' . $extension, 'user_token=' . $this->session->data['user_token'], true)
				);
			}
		}
 
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer'); 
		
		$this->response->setOutput($this->load->view('extension/extension/toctotal', $data));
		
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/extension/toctotal')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}