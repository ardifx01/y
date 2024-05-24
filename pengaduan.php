<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

date_default_timezone_set('Asia/Jakarta');

class pengaduan extends CI_Controller {

	public function __construct() 
        {
            parent::__construct();
			
			if($this->session->userdata('id_level')!=('1' OR '2' OR '3') ){
			redirect('login');
			}
			
			//$this->load->model('user_model', 'user');
			$this->load->library('session');
			$this->load->model('pengaduan_model','pengaduan');
			$this->load->model('pengaduan_via_model','pengaduan_via');
			$this->load->model('pengaduan_kategori_model','pengaduan_kategori');
			$this->load->model('pengaduan_status_model','pengaduan_status');
			$this->load->model('pekerjaan_satuan_model','pekerjaan_satuan');
			$this->load->model('unit_kerja_model','unit_kerja');
			//$this->load->model('user_level_model','user_level');
			$this->load->model('pengaduan_komentar_model','pengaduan_komentar');
			
		}
	
	public function index()
	{
		$user_id = $this->session->userdata('user_id');
		//print_r($user_id);
		
		$cek_unitkerja = $this->db->select ('id_unit_kerja')
									 ->from('user')
									 ->where('user.user_id', $user_id)
									 ->get()
									 ->row();
						
		$idna_unit_kerja = $cek_unitkerja->id_unit_kerja;
		
		$cek_levelna = $this->db->select ('id_level')
									 ->from('user')
									 ->where('user.user_id', $user_id)
									 ->get()
									 ->row();
						
		$ceklevel = $cek_levelna->id_level;
		//print_r($ceklevel);
		
		if($ceklevel == '1' || $ceklevel == '4'){
			$id_unit_kerja='';
			$user_id='';
		}else if($ceklevel == '2'){
			$id_unit_kerja=$idna_unit_kerja;
			$user_id='';
		}else{
			$id_unit_kerja='';
			$user_id=$user_id;
		}
		
		$data['data_pengaduan'] = $this->pengaduan->select_pengaduan($id_unit_kerja,$user_id);
		$data['data_pengaduan_via'] = $this->pengaduan_via->select_pengaduan_via($ceklevel);
		$data['data_pengaduan_kategori'] = $this->pengaduan_kategori->select_pengaduan_kategori();
		
		$this->load->view('menu/header_all');
		$this->load->view('pengaduan/view_pengaduan',$data);
		//print_r($data);
		$this->load->view('menu/footer');
	}
	
	public function save_pengaduan()
	{
		
		$user_id = $this->session->userdata('user_id');
		$lokasi = $this->input->post('lokasi');
		$id_pengaduan_kategori = $this->input->post('id_pengaduan_kategori');
		$nama_user = $this->input->post('nama_user');
		$nomor_pengaduan = "P".time();
		$email = 'el.2083@proton.me';
		$emails = 'el.2083@proton.me';
		
		if(!empty($_FILES['userfile'])) {
	
		$nmfile = "file_".time(); //nama file saya beri nama langsung dan diikuti fungsi time
		$config['upload_path'] = './uploads/pengaduan/orig/'; //folder file original hasil upload
		//$config['allowed_types'] = '*'; //semua type file
		$config['allowed_types']        = 'jpeg|jpg|png|pdf';
		$config['file_name'] = $nmfile; //nama yang terupload nantinya
		$config['max_size'] = 2048; //max ukuran 2 MB yang dapat di upload

		$this->load->library('upload', $config);
		
		if ( !$this->upload->do_upload('userfile'))
		{
			 $data = array('error' => $this->upload->display_errors());
		
			
		} else
		{
		$file = $this->upload->data();
		
		// to re-size for thumbnail images 
		$config = array(
			'source_image' => $file['full_path'],
			'new_image' => './uploads/pengaduan/thumb/', //folder file thumb hasil upload
			'maintain_ration' => FALSE,
			'width' => 299,
			'height' => 239
		);

			$this->load->library('image_lib', $config);
			$this->image_lib->resize();
		
			}
		}
			
		if(!empty($file)){
			$gbr = $file['file_name'];
			$type = $file['file_type'];
		}else{
			$gbr = '';
			$type = '';
		}
		
		$data_pengaduan = array(
			'nomor_pengaduan' 		=> $nomor_pengaduan,
			'tgl_pengaduan' 		=> $this->input->post('tgl_pengaduan'),
			'nama_pelapor'	  		=> $this->input->post('nama_pelapor'),
			'nomor_telepon'	  		=> $this->input->post('nomor_telepon'),
			'id_pengaduan_kategori'	=> $id_pengaduan_kategori,
			'detail_pengaduan'      => $this->input->post('detail_pengaduan'),
			'lokasi'	  			=> $lokasi,
			'id_pengaduan_via'      => $this->input->post('id_pengaduan_via'),
			'id_pengaduan_status'  	=> 1,
			'nama_file' 			=> $gbr,
            'tipe_file' 			=> $type,
			'keterangan_pengaduan'  => $this->input->post('keterangan_pengaduan'),
			'id_user'	  			=> $user_id,
			'tgl_input'	  			=> date('Y-m-d H:i:s')
		);
			$this->db->insert('pengaduan', $data_pengaduan);
			
			$cek_id_anu_terakhir = $this->db->select_max ('id_pengaduan')
									 ->from('pengaduan')
									 ->get()
									 ->row();
						
			$id_pengaduan = $cek_id_anu_terakhir->id_pengaduan;
			
			$data_rekap_volume = array(
					'id_pengaduan' 			=> $id_pengaduan,
					'id_pengaduan_kategori' => $id_pengaduan_kategori,
					'id_status'             => 1,
					'nama_lokasi'           => $lokasi,
					'id_user'	  			=> $user_id,
					'tgl_input'	  			=> date('Y-m-d H:i:s')
					);
			
			$this->db->insert('rekap_volume', $data_rekap_volume);
			
		/*$config = array();
		$config['charset'] = 'utf-8';
		$config['useragent'] = 'Codeigniter';
		$config['protocol']= "smtp";
		$config['mailtype']= "html";
		$config['smtp_host']= "ssl://smtp.gmail.com";//pengaturan smtp
		$config['smtp_port']= "465";
		$config['smtp_timeout']= "400";
		$config['smtp_user']= "dpukotabandung@gmail.com"; // isi dengan email kamu
		$config['smtp_pass']= "dpu123456"; // isi dengan password kamu
		$config['crlf']="\r\n"; 
		$config['newline']="\r\n"; 
		$config['wordwrap'] = TRUE;
		//memanggil library email dan set konfigurasi untuk pengiriman email
			
		$this->email->initialize($config);*/
		
		$config = Array(
		 'protocol' => 'ssmtp',
		 'smtp_host' => 'mta.bandung.go.id',
		 'smtp_port' => 465,
		 'smtp_user' => 'admindpu12@bandung.go.id', // change it to yours
		 'smtp_pass' => '123456dpu', // change it to yours
		 'mailtype'  => 'html',
		 'wordwrap' => TRUE,
		  'crlf' => "\r\n",
		  'newline' => "\r\n" );
			
		$this->email->initialize($config);
		//konfigurasi pengiriman
		$this->email->from($config['smtp_user']);
		$this->email->to($email);
		$this->email->cc($emails);
		$this->email->subject("Pengaduan Infrastruktur");
		$this->email->message(
			"Dear admin, anda mendapatkan 1 pengaduan baru yang masuk melalui website dsdabm:<br><br>
			1. Nomor Pengaduan : $nomor_pengaduan<br><br>
			silahkan klik link tautan dibawah ini untuk membuka detail pengaduan<br><br>".
			site_url("pengaduan/edit_pengaduan/$id_pengaduan")
			
		);
		
		if($this->email->send()){
			
				echo '<script>alert("Data Pengaduan Berhasil Disimpan. Terima kasih atas laporannya, akan segera kami sampaikan kepada unit kerja terkait :)"); setTimeout("location.href=\''.site_url('pengaduan').'\'");</script>';
	
			}else{
				
				echo '<script>alert("Data Pengaduan Berhasil Disimpan. Terima kasih atas laporannya, akan segera kami sampaikan kepada unit kerja terkait ::"); setTimeout("location.href=\''.site_url('pengaduan').'\'");</script>';
			}
	}
	
	public function edit_pengaduan($id_pengaduan)
	{
		$user_id = $this->session->userdata('user_id');
		$cek_unitkerja = $this->db->select ('id_unit_kerja')
									 ->from('user')
									 ->where('user.user_id', $user_id)
									 ->get()
									 ->row();
						
		$idna_unit_kerja = $cek_unitkerja->id_unit_kerja;
		
		$cek_levelna = $this->db->select ('id_level')
									 ->from('user')
									 ->where('user.user_id', $user_id)
									 ->get()
									 ->row();
						
		$ceklevel = $cek_levelna->id_level;
		
		if($ceklevel == '1'){
			$id_unit_kerja='';
		}else if($ceklevel == '2'){
			$id_unit_kerja=$idna_unit_kerja;
		}else{
			$id_unit_kerja='';
		}
		$data['data_unit_kerja'] = $this->unit_kerja->select_unit_kerja($id_unit_kerja);
		$data['data_pengaduan'] = $this->pengaduan->select_pengaduan_edit($id_pengaduan);
		$data['data_pengaduan_via'] = $this->pengaduan_via->select_pengaduan_via($ceklevel);
		$data['data_pengaduan_status'] = $this->pengaduan_status->select_pengaduan_status();
		$data['data_pengaduan_kategori'] = $this->pengaduan_kategori->select_pengaduan_kategori();
				
		$this->load->view('menu/header_all');
		$this->load->view('pengaduan/edit_pengaduan',$data);$this->load->view('menu/footer');
	}
	
	public function ubah_pengaduan()
	{
		
		$id_pengaduan = $this->input->post('id_pengaduan');
		$id_pengaduan_kategori = $this->input->post('id_pengaduan_kategori');
		$lokasi = $this->input->post('lokasi');
		$user_id = $this->session->userdata('user_id');
		$unit_kerja = $this->input->post('id_unit_kerja');
		$nomor_pengaduan = $this->input->post('nomor_pengaduan');
		
		$idlevel = $this->db->select('*')
							 ->from('user')
							 ->where('user.user_id', $user_id)
							 ->get()
							 ->row();
		$id_level = $idlevel->id_level;
		
		$path = './uploads/pengaduan/';
		$foto_lama = $this->input->post('foto_lama');
		$tipe_lama = $this->input->post('tipe_lama');
		
		if ((!empty ($_FILES['nama_file']))&&($foto_lama == '') || ($foto_lama != '')){ // file baru ada dan foto lama kosong
		
		$nmfile = "file_".time(); //nama file saya beri nama langsung dan diikuti fungsi time
		$config['upload_path'] = './uploads/pengaduan/orig/'; //folder file original hasil upload
		$config['allowed_types'] = '*'; //semua type file
		$config['file_name'] = $nmfile; //nama yang terupload nantinya

		$this->load->library('upload', $config);
		
		if ( !$this->upload->do_upload('nama_file'))
		{
			$data = array('error' => $this->upload->display_errors());
			
		} else {
		
		$file = $this->upload->data();
		
		// to re-size for thumbnail images 
		$config = array(
			'source_image' => $file['full_path'],
			'new_image' => './uploads/pengaduan/thumb/', //folder file thumb hasil upload
			'maintain_ration' => FALSE,
			'width' => 299,
			'height' => 239
		);

			$this->load->library('image_lib', $config);
			$this->image_lib->resize();
		
			}
		}
		
		if(!empty($file)){
		
			$gbr = $file['file_name'];
			$type = $file['file_type'];
			
			if(!empty($foto_lama)){
				if(file_exists ($path.'orig/'.$foto_lama)){
				unlink($path.'orig/'.$foto_lama);
				}
				if(file_exists ($path.'thumb/'.$foto_lama)){
				unlink($path.'thumb/'.$foto_lama);
				}
			}
			
		}  else if ((empty ($file))&&($foto_lama != '')){ // file baru kosong dan foto lama ada
		
			$gbr = $foto_lama;
			$type = $tipe_lama;
			
		}else{
		
			$gbr = '';
			$type = '';
		}
		
		$data_pengaduan = array(
			'tgl_pengaduan' 		=> $this->input->post('tgl_pengaduan'),
			'nama_pelapor'	  		=> $this->input->post('nama_pelapor'),
			'nomor_telepon'	  		=> $this->input->post('nomor_telepon'),
			'id_pengaduan_kategori' => $id_pengaduan_kategori,
			'detail_pengaduan'      => $this->input->post('detail_pengaduan'),
			'lokasi'	  			=> $lokasi,
			'id_pengaduan_via'      => $this->input->post('id_pengaduan_via'),
			'id_pengaduan_status'  	=> $this->input->post('id_pengaduan_status'),
			'nama_file' 			=> $gbr,
            'tipe_file' 			=> $type,
			'keterangan_pengaduan'  => $this->input->post('keterangan_pengaduan'),
			'id_unit_kerja' 		=> $this->input->post('id_unit_kerja'),
			//'id_user'	  			=> $user_id,
			'tgl_input'	  			=> date('Y-m-d H:i:s')
			
		);
		
		$this->db->where('id_pengaduan', $id_pengaduan);
		$this->db->update('pengaduan', $data_pengaduan);
		
		$cek_ah_idpegaduan = $this->db->select('id_pengaduan, count(*)')
						  ->from('rekap_volume')
						  ->where('rekap_volume.id_pengaduan', $id_pengaduan)
						  ->get()
						  ->row();
						
		$idpengaduans = $cek_ah_idpegaduan->id_pengaduan;
		
		$cek_ah_idpolumena = $this->db->select('id_rekap_volume, count(*)')
						  ->from('rekap_volume')
						  ->where('rekap_volume.id_pengaduan', $id_pengaduan)
						  ->get()
						  ->row();
						
		$idrekapvolume = $cek_ah_idpolumena->id_rekap_volume;
		
		$data_rekap_volume = array(
					'id_pengaduan' 			=> $id_pengaduan,
					'id_unit_kerja' 		=> $unit_kerja,
					'id_pengaduan_kategori' => $id_pengaduan_kategori,
					'id_status'             => $this->input->post('id_pengaduan_status'),
					'nama_lokasi'           => $lokasi,
					//'id_user'	  			=> $user_id,
					'tgl_input'	  			=> date('Y-m-d H:i:s')
					);
					
			if ( $idpengaduans > 0 ) 
			   {
				   
				  $this->db->where('id_rekap_volume', $idrekapvolume);
				  $this->db->update('rekap_volume', $data_rekap_volume);
				  
			   } else {
				   
				  $this->db->insert('rekap_volume', $data_rekap_volume);
			   }
			   
			   $cek_email = $this->db->select_min('email')
						  ->from('user')
						  ->where('user.id_unit_kerja', $unit_kerja)
						  ->get()
						  ->row();
						
			   $email = $cek_email->email;
			   	//print_r($email);
			   
			   $cek_emails = $this->db->select_max('email')
						  ->from('user')
						  ->where('user.id_unit_kerja', $unit_kerja)
						  ->get()
						  ->row();
						
			   $emails = $cek_emails->email;
			   
			   if(!empty($unit_kerja)){
				   
				   $config = Array(
					 'protocol' => 'ssmtp',
					 'smtp_host' => 'mta.bandung.go.id',
					 'smtp_port' => 465,
					 'smtp_user' => 'admindpu1@bandung.go.id', // change it to yours
					 'smtp_pass' => '123456dpu2', // change it to yours
					 'mailtype'  => 'html',
					 'wordwrap' => TRUE,
					  'crlf' => "\r\n",
					  'newline' => "\r\n" );
						
					$this->email->initialize($config);
					//konfigurasi pengiriman
					$this->email->from($config['smtp_user']);
					$this->email->to($email);
					$this->email->cc($emails);
					$this->email->subject("Pengaduan Infrastruktur");
					$this->email->message(
						"Dear Operator, anda mendapatkan 1 pengaduan baru:<br><br>
						1. Nomor Pengaduan : $nomor_pengaduan<br><br>
						silahkan klik link tautan dibawah ini untuk membuka detail pengaduan<br><br>".
						site_url("pengaduan/edit_pengaduan/$id_pengaduan")
						
					);
					
					
			   }
			   
			   if($id_level == '1'){
				   
				   if($this->email->send()){	
					   
						echo '<script>alert("Data berhasil disimpan dan email berhasil dikirim"); setTimeout("location.href=\''.site_url('pengaduan/edit_pengaduan/'.$id_pengaduan).'\'");</script>';
					}else{
					echo '<script>alert("Data berhasil disimpan tapi email gagal dikirim"); setTimeout("location.href=\''.site_url('pengaduan/edit_pengaduan/'.$id_pengaduan).'\'");</script>';
					}
			   }else{
				echo '<script>alert("Data Berhasil Diubah"); setTimeout("location.href=\''.site_url('pengaduan').'\'");</script>';
			}
	}
	
	public function delete_pengaduan_action($id_pengaduan,$id_rekap_volume,$nama_file)
	{
	
		$cek_ah_potona = $this->db->select('id_tindaklanjut, count(*)')
						  ->from('pengaduan_tindaklanjut')
						  ->where('pengaduan_tindaklanjut.id_pengaduan', $id_pengaduan)
						  ->get()
						  ->row();
						
		$poto = $cek_ah_potona->id_tindaklanjut;
	
		if($poto > 0){
		
		echo '<script>alert("Data pengaduan tidak dapat dihapus. Karena terdapat data dokumentasi tindak lanjut pengaduan ..!!"); setTimeout("location.href=\''.site_url('pengaduan').'\'");</script>';
		
		}else{
			
			$path = './uploads/pengaduan/';
			if(file_exists ($path.'orig/'.$nama_file)){
			unlink($path.'orig/'.$nama_file);
			}
			if(file_exists ($path.'thumb/'.$nama_file)){
			unlink($path.'thumb/'.$nama_file);
			}
				
			$this->db->where('id_rekap_volume', $id_rekap_volume);
			$this->db->delete('rekap_volume');
									
			$this->db->where('id_pengaduan', $id_pengaduan);
			$this->db->delete('pengaduan');
					
				echo '<script>alert("Data Berhasil Dihapus"); setTimeout("location.href=\''.site_url('pengaduan').'\'");</script>';
		
		}
		
		
	}
	
	public function update_pengaduan($id_pengaduan)
	{
		$user_id = $this->session->userdata('user_id');
		$cek_unitkerja = $this->db->select ('id_unit_kerja')
									 ->from('user')
									 ->where('user.user_id', $user_id)
									 ->get()
									 ->row();
						
		$idna_unit_kerja = $cek_unitkerja->id_unit_kerja;
		
		$cek_levelna = $this->db->select ('id_level')
									 ->from('user')
									 ->where('user.user_id', $user_id)
									 ->get()
									 ->row();
						
		$ceklevel = $cek_levelna->id_level;
		
		if($ceklevel == '1'){
			$id_unit_kerja='';
		}else if($ceklevel == '2'){
			$id_unit_kerja=$idna_unit_kerja;
		}else{
			$id_unit_kerja='';
		}
		$data['data_unit_kerja'] = $this->unit_kerja->select_unit_kerja($id_unit_kerja);
		$id_tindaklanjut = $this->input->post('id_tindaklanjut');
		$data['data_tindaklanjut'] = $this->pengaduan->select_tindaklanjut($id_pengaduan);
		//$data['data_dokumentasi'] = $this->pengaduan->select_dokumentasi($id_tindaklanjut);
		$data['data_pengaduan'] = $this->pengaduan->select_pengaduan_edit($id_pengaduan);
		$data['data_pengaduan_via'] = $this->pengaduan_via->select_pengaduan_via($ceklevel);
		$data['data_pengaduan_kategori'] = $this->pengaduan_kategori->select_pengaduan_kategori();
		$data['data_pengaduan_status'] = $this->pengaduan_status->select_pengaduan_status();
		$data['data_pengaduan_status_pelapor'] = $this->pengaduan_status->select_pengaduan_status_pelapor();
		$data['data_pekerjaan_satuan'] = $this->pekerjaan_satuan->select_pekerjaan_satuan();
		$data['data_komentar'] = $this->pengaduan_komentar->select_pengaduan_komentar($id_pengaduan);
		
		/*$cek_tgl_tindaklanjut_selesai = $this->db->select ('tgl_tindaklanjut_selesai')
									 ->from('pengaduan')
									 ->where('pengaduan.id_pengaduan', $id_pengaduan)
									 ->get()
									 ->row();
						
		$tgl_tindaklanjut_selesai = $cek_tgl_tindaklanjut_selesai->tgl_tindaklanjut_selesai;
		
		$tgl_mulai=$tgl_tindaklanjut_selesai;// tanggal launching aplikasi
		$jangka_waktu = strtotime('+2 days', strtotime($tgl_mulai));// jangka waktu + 365 hari
		$tgl_exp=date("Y-m-d",$jangka_waktu);//tanggal expired
		$tgl_sekarang=date("Y-m-d");//tanggal sekarang
		
		if ($tgl_sekarang >=$tgl_exp )
		{
		$data_pengaduan = array(
		
			'id_pengaduan_status'  	   => 3
				);
				
		$this->db->where('id_pengaduan', $id_pengaduan);
		$this->db->update('pengaduan', $data_pengaduan);
		}*/
						
		$this->load->view('menu/header_all');
		$this->load->view('pengaduan/update_pengaduan',$data);
		$this->load->view('menu/footer');
	}
	
	public function tindaklanjut_pengaduan()
    {
        $id_pengaduan = $this->input->post('id_pengaduan');
		//$id_pengaduan = $this->uri->segment('3');
		$user_id = $this->session->userdata('user_id');
		$tgl_tindaklanjut = $this->input->post('tgl_tindaklanjut');
		$keterangan_tindaklanjut = $this->input->post('keterangan_tindaklanjut');
		//$email = 'el.2083@proton.me';
		
        if(isset($_FILES['userfile']) && $_FILES['userfile']['error'] != '4') {
            $files = $_FILES;
            $count = count($_FILES['userfile']['name']);
            for($i=0; $i<$count; $i++){
				
				$ext = end((explode(".", $files['userfile']['name'][$i])));
				//$fileName = 'file_' . rand(100000, 999999)."_".($i+1).".".$ext;
				$fileName = 'file_' . time()."_".($i+1).".".$ext;
				$_FILES['userfile']['name'] = $fileName;
                $_FILES['userfile']['type']= $files['userfile']['type'][$i];
                $_FILES['userfile']['tmp_name']= $files['userfile']['tmp_name'][$i];
                $_FILES['userfile']['error']= $files['userfile']['error'][$i];
                $_FILES['userfile']['size']= $files['userfile']['size'][$i];
                $config['upload_path'] = './uploads/tindaklanjut/orig/';
                $target_path = './uploads/tindaklanjut/thumb/';
				
                $config['allowed_types'] = '*';
                $config['max_size'] = '2048';
                $config['remove_spaces'] = true;
                $config['overwrite'] = true;
                //$config['max_width'] = '1024';
                //$config['max_height'] = '768';
				$config['encrypt_name'] = true;
				
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                $this->upload->do_upload('userfile');
				
                $data = array('upload_data' => $this->upload->data());
				
				 $path=$data['upload_data']['full_path'];
				 $configi['name']=$data['upload_data']['file_name'];
				 $configi['image_library'] = 'gd2';
				 $configi['source_image']   = $path;
				 $configi['new_image']   = $target_path;
				 $configi['maintain_ratio'] = TRUE;
				 $configi['width']  = 250;
				 $configi['height'] = 250;
				 
                $this->load->library('image_lib');
                $this->image_lib->initialize($configi);    
                $this->image_lib->resize();
                $images[] = $fileName;
				
				if($this->upload->do_upload('userfile'))
                {
					$data_tindaklanjut = array(
					'id_pengaduan' 			   => $id_pengaduan,
					'tgl_tindaklanjut_selesai' => $this->input->post('tgl_tindaklanjut_selesai'),
					'images' 				   => $fileName,
					'id_user'	  			   => $user_id,
					'tgl_input'	  			   => date('Y-m-d H:i:s')
					);
					
                   $this->db->insert('pengaduan_tindaklanjut', $data_tindaklanjut);

                }
				
				$cek_ah_idpegaduan = $this->db->select('id_pengaduan, count(*)')
						  ->from('rekap_volume')
						  ->where('rekap_volume.id_pengaduan', $id_pengaduan)
						  ->get()
						  ->row();
						
				$idpengaduans = $cek_ah_idpegaduan->id_pengaduan;
				
				$cek_ah_idpolumena = $this->db->select('id_rekap_volume, count(*)')
						  ->from('rekap_volume')
						  ->where('rekap_volume.id_pengaduan', $id_pengaduan)
						  ->get()
						  ->row();
						
				$idrekapvolume = $cek_ah_idpolumena->id_rekap_volume;
				
				$tgl_selesai = $this->input->post('tgl_tindaklanjut_selesai');
				$tgl_mulai = $this->input->post('tgl_tindaklanjut');
						
				$jumlah_hari = $this->select_jumlah_hari($tgl_selesai,$tgl_mulai);
				
				$data_rekap_volume = array(
					'id_pengaduan' 			=> $id_pengaduan,
					'id_pengaduan_kategori' => $this->input->post('id_pengaduan_kategori'),
					'id_status'             => $this->input->post('id_pengaduan_status'),
					'rekap_volume'          => $this->input->post('volume_tindaklanjut'),
					'id_satuan'             => $this->input->post('id_satuan'),
					'tgl_mulai'             => $tgl_mulai,
					'tgl_selesai'           => $tgl_selesai,
					'jumlah_hari'           => $jumlah_hari,
					'nama_lokasi'           => $this->input->post('lokasi'),
					'keterangan_selesai'    => $this->input->post('keterangan_tindaklanjut'),
					'id_unit_kerja' 		=> $this->input->post('id_unit_kerja'),
					'id_user'	  			=> $user_id,
					'tgl_input'	  			=> date('Y-m-d H:i:s')
					);

			   if ( $idpengaduans > 0 ) 
			   {
				   
				  $this->db->where('id_rekap_volume', $idrekapvolume);
				  $this->db->update('rekap_volume', $data_rekap_volume);
				  
			   } else {
				   
				  $this->db->insert('rekap_volume', $data_rekap_volume);
			   }
                

				$data_pengaduan = array(
				'id_pengaduan_status'  	   => $this->input->post('id_pengaduan_status'),
				'tgl_tindaklanjut' 		   => $tgl_tindaklanjut,
				'tgl_tindaklanjut_selesai' => $this->input->post('tgl_tindaklanjut_selesai'),
				'keterangan_tindaklanjut'  => $keterangan_tindaklanjut,
				'id_unit_kerja' 		   => $this->input->post('id_unit_kerja'),
				'volume_tindaklanjut'  	   => $this->input->post('volume_tindaklanjut'),
				'id_satuan'                => $this->input->post('id_satuan')
					);
				$this->db->where('id_pengaduan', $id_pengaduan);
				$this->db->update('pengaduan', $data_pengaduan);
                         
            }
        }
		
		//$email = $this->user->select_user_email($id_pengaduan);
		//$nama_user = $this->user->select_user_nama($id_pengaduan);
		$cek_nama_user = $this->db->select ('nama_user')
									 ->from('user')
									 ->join('pengaduan', 'pengaduan.id_user = user.user_id')
									 ->where('pengaduan.id_pengaduan', $id_pengaduan)
									 ->get()
									 ->row();
						
		$nama_user = $cek_nama_user->nama_user;
		$cek_email = $this->db->select ('email')
									 ->from('user')
									 ->join('pengaduan', 'pengaduan.id_user = user.user_id')
									 ->where('pengaduan.id_pengaduan', $id_pengaduan)
									 ->get()
									 ->row();
						
		$email = $cek_email->email;
		
		/*$config = Array(
		 'protocol' => 'smtp',
		 'smtp_host' => 'ssl://mail.dpu.bandung.go.id',
		 'smtp_port' => 465,
		 'smtp_user' => 'admin@dpu.bandung.go.id', // change it to yours
		 'smtp_pass' => 'dpu123456oyeh', // change it to yours
		 'mailtype'  => 'html',
		 'wordwrap' => TRUE,
		  'crlf' => "\r\n",
		  'newline' => "\r\n" );
			
		$this->email->initialize($config);*/
		
		$config = Array(
		 'protocol' => 'ssmtp',
		 'smtp_host' => 'mta.bandung.go.id',
		 'smtp_port' => 465,
		 'smtp_user' => 'admindpu1@bandung.go.id', // change it to yours
		 'smtp_pass' => '1234516dpu', // change it to yours
		 'mailtype'  => 'html',
		 'wordwrap' => TRUE,
		  'crlf' => "\r\n",
		  'newline' => "\r\n" );
			
		$this->email->initialize($config);
		
		//konfigurasi pengiriman
		$this->email->from($config['smtp_user']);
		$this->email->to($email);
		$this->email->subject("Tindak Lanjut Pengaduan");
		$this->email->message(
			"<html>
			<head>
			<title>Tindak Lanjut Pengaduan</title>
			</head>
			<body>
			<p>Dear $nama_user, berikut tindak lanjut dari pengaduan yang anda sampaikan:</p>
			<table border=1>
			<tr>
			<th>Tanggal Tindak Lanjut</th><th>Keterangan Tindak Lanjut</th>
			</tr>
			<tr>
			<td>$tgl_tindaklanjut</td><td>$keterangan_tindaklanjut</td>
			</tr>
			</table>
			<p>silahkan klik link tautan dibawah ini untuk membuka detail tindak lanjut pengaduan:</p>
			</body>
			</html>".
			site_url("pengaduan/update_pengaduan/$id_pengaduan")
			
		);
			
			if($this->email->send()){
			echo '<script>alert("Data tindak lanjut pengaduan berhasil disimpan dan email berhasil dikirimkan ke pelapor"); setTimeout("location.href=\''.site_url('pengaduan/update_pengaduan/'.$id_pengaduan).'\'");</script>';
		} else {
			echo '<script>alert("Data tindak lanjut pengaduan berhasil disimpan dan email gagal dikirimkan ke pelapor"); setTimeout("location.href=\''.site_url('pengaduan/update_pengaduan/'.$id_pengaduan).'\'");</script>';
		}
        
    }
	
	public function select_jumlah_hari($tgl_selesai,$tgl_mulai)
	{
		
	  $query = $this->db->query("SELECT DATEDIFF('$tgl_selesai', '$tgl_mulai') +1 AS jumlah_hari");
	  $result = $query->row();
	  return $result->jumlah_hari;
	}
	
	public function delete_tindaklanjut_action($id_tindaklanjut, $id_pengaduan, $images)
	{
		$user_id = $this->session->userdata('user_id');
		$idlevel = $this->db->select('*')
							 ->from('user')
							 ->where('user.user_id', $user_id)
							 ->get()
							 ->row();
		$id_level = $idlevel->id_level;
					 
		if ($id_level == '3'){
			echo '<script>alert("Hanya admin yang dapat menghapus data ini..!!"); setTimeout("location.href=\''.site_url('pengaduan/update_pengaduan/'.$id_pengaduan).'\'");</script>';
		}else{
			
		$path = './uploads/tindaklanjut/';
			if(file_exists ($path.'orig/'.$images)){
			unlink($path.'orig/'.$images);
			}
			if(file_exists ($path.'thumb/'.$images)){
			unlink($path.'thumb/'.$images);
			}
		$this->db->where('id_tindaklanjut', $id_tindaklanjut);
		$this->db->delete('pengaduan_tindaklanjut');
		
			
			echo '<script>alert("Data Berhasil Dihapus"); setTimeout("location.href=\''.site_url('pengaduan/update_pengaduan/'.$id_pengaduan).'\'");</script>';
		
		}
	}
	
	public function update_tindaklanjut_pengaduan()
	{
		
		$id_tindaklanjut = $this->input->post('id_tindaklanjut');
		$id_pengaduan = $this->input->post('id_pengaduan');
		$id_pengaduan_kategori = $this->input->post('id_pengaduan_kategori');
		$lokasi = $this->input->post('lokasi');
		$user_id = $this->session->userdata('user_id');
		$idlevel = $this->db->select('*')
							 ->from('user')
							 ->where('user.user_id', $user_id)
							 ->get()
							 ->row();
		$id_level = $idlevel->id_level;
		
		$path = './uploads/tindaklanjut/';
		$foto_lama = $this->input->post('foto_lama');
		$tipe_lama = $this->input->post('tipe_lama');
		
		if ((!empty ($_FILES['nama_file']))&&($foto_lama == '') || ($foto_lama != '')){ // file baru ada dan foto lama kosong
		
		$nmfile = "file_".time(); //nama file saya beri nama langsung dan diikuti fungsi time
		$config['upload_path'] = './uploads/tindaklanjut/orig/'; //folder file original hasil upload
		$config['allowed_types'] = '*'; //semua type file
		$config['file_name'] = $nmfile; //nama yang terupload nantinya

		$this->load->library('upload', $config);
		
		if ( !$this->upload->do_upload('nama_file'))
		{
			$data = array('error' => $this->upload->display_errors());
			
		} else {
		
		$file = $this->upload->data();
		
		// to re-size for thumbnail images 
		$config = array(
			'source_image' => $file['full_path'],
			'new_image' => './uploads/tindaklanjut/thumb/', //folder file thumb hasil upload
			'maintain_ration' => FALSE,
			'width' => 299,
			'height' => 239
		);

			$this->load->library('image_lib', $config);
			$this->image_lib->resize();
		
			}
		}
		
		if(!empty($file)){
		
			$gbr = $file['file_name'];
			$type = $file['file_type'];
			
			if(!empty($foto_lama)){
				if(file_exists ($path.'orig/'.$foto_lama)){
				unlink($path.'orig/'.$foto_lama);
				}
				if(file_exists ($path.'thumb/'.$foto_lama)){
				unlink($path.'thumb/'.$foto_lama);
				}
			}
			
		}  else if ((empty ($file))&&($foto_lama != '')){ // file baru kosong dan foto lama ada
		
			$gbr = $foto_lama;
			$type = $tipe_lama;
			
		}else{
		
			$gbr = '';
			$type = '';
		}
		
		$data_dokumentasi = array(
			'tgl_tindaklanjut_selesai'	=> $this->input->post('tgl_tindaklanjut_selesai2'),
			'images'					=> $gbr,
			'id_user'	  				=> $user_id,
			'tgl_input'	  				=> date('Y-m-d H:i:s')
			
		);
		
		$this->db->where('id_tindaklanjut', $id_tindaklanjut);
		$this->db->update('pengaduan_tindaklanjut', $data_dokumentasi);
			
		
			echo '<script>alert("Data Berhasil Diubah"); setTimeout("location.href=\''.site_url('pengaduan/update_pengaduan/'.$id_pengaduan).'\'");</script>';
		
	}
	
	public function tindaklanjut_komentar()
    {
        $id_pengaduan = $this->input->post('id_pengaduan');
		$user_id = $this->session->userdata('user_id');
		$komentar = $this->input->post('komentar');
		$tgl_komentar = date('Y-m-d');
		
		
		
		$path = './uploads/komentar/';
		$foto_lama = $this->input->post('foto_lama');
		$tipe_lama = $this->input->post('tipe_lama');
		
		if ((!empty ($_FILES['nama_file']))&&($foto_lama == '') || ($foto_lama != '')){ // file baru ada dan foto lama kosong
		
		$nmfile = "file_".time(); //nama file saya beri nama langsung dan diikuti fungsi time
		$config['upload_path'] = './uploads/komentar/orig/'; //folder file original hasil upload
		$config['allowed_types'] = '*'; //semua type file
		$config['file_name'] = $nmfile; //nama yang terupload nantinya

		$this->load->library('upload', $config);
		
		if ( !$this->upload->do_upload('nama_file'))
		{
			$data = array('error' => $this->upload->display_errors());
			
		} else {
		
		$file = $this->upload->data();
		
		// to re-size for thumbnail images 
		$config = array(
			'source_image' => $file['full_path'],
			'new_image' => './uploads/komentar/thumb/', //folder file thumb hasil upload
			'maintain_ration' => FALSE,
			'width' => 299,
			'height' => 239
		);

			$this->load->library('image_lib', $config);
			$this->image_lib->resize();
		
			}
		}
		
		if(!empty($file)){
		
			$gbr = $file['file_name'];
			$type = $file['file_type'];
			
			if(!empty($foto_lama)){
				if(file_exists ($path.'orig/'.$foto_lama)){
				unlink($path.'orig/'.$foto_lama);
				}
				if(file_exists ($path.'thumb/'.$foto_lama)){
				unlink($path.'thumb/'.$foto_lama);
				}
			}
			
		}  else if ((empty ($file))&&($foto_lama != '')){ // file baru kosong dan foto lama ada
		
			$gbr = $foto_lama;
			$type = $tipe_lama;
			
		}else{
		
			$gbr = '';
			$type = '';
		}
					
				
				$cek_ah_idpegaduan = $this->db->select('id_pengaduan, count(*)')
						  ->from('pengaduan_komentar')
						  ->where('pengaduan_komentar.id_pengaduan', $id_pengaduan)
						  ->get()
						  ->row();
						
				$idpengaduans = $cek_ah_idpegaduan->id_pengaduan;
				
				$cek_ah_idkomentar = $this->db->select('id_komentar, count(*)')
						  ->from('pengaduan_komentar')
						  ->where('pengaduan_komentar.id_pengaduan', $id_pengaduan)
						  ->get()
						  ->row();
						
				$idkomentar = $cek_ah_idkomentar->id_komentar;
				
				$data_pengaduan_komentar = array(
					'id_pengaduan' 			=> $id_pengaduan,
					'komentar' 				=> $komentar,
					'tgl_komentar'          => $tgl_komentar,
					'nama_file' 			=> $gbr,
					'tipe_file' 			=> $type,
					'user_id'	  			=> $user_id,
					'tgl_input'	  			=> date('Y-m-d H:i:s')
					);

			   if ( $idpengaduans >0 ) 
			   {
				   
				  $this->db->where('id_komentar', $idkomentar);
				  $this->db->update('pengaduan_komentar', $data_pengaduan_komentar);
				  
			   } else {
				   
				  $this->db->insert('pengaduan_komentar', $data_pengaduan_komentar);
			   }
                

				/*$data_pengaduan = array(
				'id_pengaduan_status'  	   => $this->input->post('id_pengaduan_status')
					);
				$this->db->where('id_pengaduan', $id_pengaduan);
				$this->db->update('pengaduan', $data_pengaduan);*/
                         
            
		
		//$email = $this->user->select_user_email($id_pengaduan);
		//$nama_user = $this->user->select_user_nama($id_pengaduan);
		
		
						
		$email = 'el.2083@proton.me';
		$emails = 'el.2083@proton.me';
		
		/*$config = Array(
		 'protocol' => 'smtp',
		 'smtp_host' => 'ssl://mail.dpu.bandung.go.id',
		 'smtp_port' => 465,
		 'smtp_user' => 'admin@dpu.bandung.go.id', // change it to yours
		 'smtp_pass' => 'dpu123456oyeh', // change it to yours
		 'mailtype'  => 'html',
		 'wordwrap' => TRUE,
		  'crlf' => "\r\n",
		  'newline' => "\r\n" );
			
		$this->email->initialize($config);*/
		
		$config = Array(
		 'protocol' => 'ssmtp',
		 'smtp_host' => 'mta.bandung.go.id',
		 'smtp_port' => 465,
		 'smtp_user' => 'admindpu11@bandung.go.id', // change it to yours
		 'smtp_pass' => '123456dpu', // change it to yours
		 'mailtype'  => 'html',
		 'wordwrap' => TRUE,
		  'crlf' => "\r\n",
		  'newline' => "\r\n" );
			
		$this->email->initialize($config);
		
		//konfigurasi pengiriman
		$this->email->from($config['smtp_user']);
		$this->email->to($email);
		$this->email->cc($emails);
		$this->email->subject("Komentar Pengaduan");
		$this->email->message(
			"<html>
			<head>
			<title>Tindak Lanjut Pengaduan</title>
			</head>
			<body>
			<p>Dear admin dsdabm, berikut komentar dari pelapor atas tindak lanjut dari pengaduan yang telah anda sampaikan:</p>
			<table border=1>
			<tr>
			<th>Tanggal Komentar</th><th>Komentar dari Pelapor</th>
			</tr>
			<tr>
			<td>$tgl_komentar</td><td>$komentar</td>
			</tr>
			</table>
			<p>silahkan klik link tautan dibawah ini untuk membuka detail komentar dari pelapor:</p>
			</body>
			</html>".
			site_url("pengaduan/update_pengaduan/$id_pengaduan")
			
		);
			
			if($this->email->send()){
			echo '<script>alert("Data berhasil disimpan"); setTimeout("location.href=\''.site_url('pengaduan/update_pengaduan/'.$id_pengaduan).'\'");</script>';
		}
        
    }
	
}
