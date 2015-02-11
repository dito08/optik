
<?php
	// Output the enabled services and change link/button if the user is authenticated.
	$this->load->helper('url');
	if(count($providers)>0){
		foreach($providers as $provider=>$connected){
			echo anchor(site_url('main/hauth/login/'.$provider), 
				'<img src="'.base_url('modules/main/assets/third_party/'.$provider.'.png' ).'" style="width:200px;"/>&nbsp');
		}		
	}else{
		echo '<p>No third party authentication enabled</p>';
	}
	
?>

