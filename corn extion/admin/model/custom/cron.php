<?php
class ModelCustomCron extends Model {
    public function getCronJobs($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "cron_setting` fg";

		$sort_data = array(
			'fg.description',
			'fg.sheduledate'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY fg.description";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
       
	}  
        
        public function getTotalCron($data = array()) {
            $sql = "SELECT COUNT(cron_id) AS total FROM " . DB_PREFIX . "cron_setting";
            $query = $this->db->query($sql);
            return $query->row['total'];
	}
        
         public function getUploadHistory($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "uploadhistory` fg";

		$sort_data = array(
			'fg.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY fg.date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
       
	}  
        
         public function getTotalUploadHistory($data = array()) {
            $sql = "SELECT COUNT(uploadid) AS total FROM " . DB_PREFIX . "uploadhistory";
            $query = $this->db->query($sql);
            return $query->row['total'];
	}
         public function deleteuploadhistory($data) {
            $sql="DELETE FROM " . DB_PREFIX . "uploadhistory WHERE uploadid = " .$data;
           $this->db->query($sql);
            $this->cache->delete('cron_setting');
	}
        
        public function deleteCronJob($data) {
            $sql="DELETE FROM " . DB_PREFIX . "cron_setting WHERE cron_id = " .$data;
           $this->db->query($sql);
            $this->cache->delete('cron_setting');
	}
        
        
        public function addCron($desc,$sdate) {
            $sql="INSERT INTO `" . DB_PREFIX . "cron_setting` SET  sheduledate ='".$sdate."' ,description = '".$desc."'";
            $this->db->query($sql);
        }
}

