<?php class ControllerCustomuploadhistory extends Controller{ 
    public function index(){
              
        $this->load->language('custom/uploadhistory');
        $template="custom/uploadhistory.tpl"; // .tpl location and file
        $this->template = ''.$template.'';
     
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('custom/cron');
        $this->getList();
    }
    protected function getList(){
        if (isset($this->request->get['sort'])) {
                $sort = $this->request->get['sort'];
        } else {
                $sort = 'fg.description';
        }

        if (isset($this->request->get['order'])) {
                $order = $this->request->get['order'];
        } else {
                $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
                $page = $this->request->get['page'];
        } else {
                $page = 1;
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
        }
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
               'text' => $this->language->get('text_home'),
               'href' => $this->url->link('custom/cron', 'token=' . $this->session->data['token'], true)
       );
        $data['breadcrumbs'][] = array(
                        'text' => $this->language->get('heading_title'),
                        'href' => $this->url->link('custom/cron', 'token=' . $this->session->data['token'], true)
        );
       // $data['add'] = $this->url->link('custom/uploadhistory/add', 'token=' . $this->session->data['token'] . $url, true);
        $data['delete'] = $this->url->link('custom/uploadhistory/delete', 'token=' . $this->session->data['token'] . $url, true);

        $data['filters'] = array();

        $filter_data = array(
                'sort'  => $sort,
                'order' => $order,
                'start' => ($page - 1) * $this->config->get('config_limit_admin'),
                'limit' => $this->config->get('config_limit_admin')
        );
        $filter_total = $this->model_custom_cron->getTotalUploadHistory();
        $results = $this->model_custom_cron->getUploadHistory($filter_data);
        foreach ($results as $result) {
            $data['filters'][] = array(
                    'uploadid'       => $result['uploadid'],
                    'inserted'   => $result['inserted'],
                    'updated'   => $result['updated'],
                    'date_added'   => date("d/m/Y", strtotime( $result['date_added'])),
                );
        }
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
       
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['column_schedule_date'] = $this->language->get('column_schedule_date');
        $data['column_total_inserted'] = $this->language->get('column_total_inserted');
        $data['column_total_updated'] = $this->language->get('column_total_updated');
        $data['column_action'] = $this->language->get('column_action');
        $data['button_delete'] = $this->language->get('button_delete');
       
        $data['error_name'] = $this->language->get('error_name');
        $data['token'] = $this->session->data['token'];

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

        if (isset($this->request->post['selected'])) {
                $data['selected'] = (array)$this->request->post['selected'];
        } else {
                $data['selected'] = array();
        }

        $url = '';

        if ($order == 'ASC') {
                $url .= '&order=DESC';
        } else {
                $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
        }
        $data['sort_name'] = $this->url->link('custom/uploadhistory', 'token=' . $this->session->data['token'] . '&sort=fg.date_added' . $url, true);
        $url = '';

        if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
        }
        if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $filter_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('custom/uploadhistory', 'token=' . $this->session->data['token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($filter_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($filter_total - $this->config->get('config_limit_admin'))) ? $filter_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $filter_total, ceil($filter_total / $this->config->get('config_limit_admin')));

        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('custom/uploadhistory', $data));
    }
 
    public function delete() {
        $this->load->language('custom/uploadhistory');
        $url='';
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('custom/cron');
        $upload_id = $this->request->get['id'];
        $this->model_custom_cron->deleteuploadhistory($upload_id);               
        $this->response->redirect($this->url->link('custom/uploadhistory', 'token=' . $this->session->data['token'] . $url, true));
    }
}