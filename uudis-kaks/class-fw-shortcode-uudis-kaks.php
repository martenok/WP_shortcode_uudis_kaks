<?php if (!defined('FW')) die('Forbidden');

class FW_Shortcode_Uudis_Kaks extends FW_Shortcode
{

	static $m = 0; //Muutuja modaal akende identifitseerimiseks
	static $uudis = array();

	public function _init()
	{
		$this->register_ajax();
	}

	private function register_ajax()
	{
		add_action( 'wp_ajax_uudis_kaks_create', array($this, 'uudis_kaks_create'));
		add_action( 'wp_ajax_nopriv_uudis_kaks_create', array($this, 'uudis_kaks_create'));
		add_action( 'wp_ajax_uudis_kaks_kustuta', array($this, 'uudis_kaks_kustuta'));
		add_action( 'wp_ajax_nopriv_uudis_kaks_kustuta', array($this, 'uudis_kaks_kustuta'));
		add_action( 'wp_ajax_uudis_kaks_mercy', array($this, 'uudis_kaks_mercy'));
		add_action( 'wp_ajax_nopriv_uudis_kaks_mercy', array($this, 'uudis_kaks_mercy'));
	}

	protected function _render($atts, $content = null, $tag = '')
	{
		if (!isset($atts['view'])) {
			return $this->get_error_msg();
		}

		if (!isset($atts['link'])) {
			return $this->get_error_msg();
		}

		if (isset($atts['mercury_key']) and $atts['mercury_key'] != "" ) {
			$tulem =  $this->get_mercury_voog($atts);
		} else {
			// $content =  $this->getVoog($atts);
		}

		$u2_height = ($atts['news_height'] === 'full') ? '416px' : '200px';

		// $tag = get_tag();

		if ($atts['view'] !== 'rand') {
			$view_path = $this->locate_path('/views/' . $atts['view'] . '.php');
			$content = $tulem['content'];
			// var_dump($content);

			$data = $tulem['data'];
			return fw_render_view($view_path, compact( 'content', 'tag', 'data', 'u2_height') );
		} else {
			$views = array('a', 'b', 'c');
			$random_index = mt_rand(0, count($views) - 1);
			$random_view = $views[$random_index];
			$random_view_path = $this->locate_path('/views/' . $random_view . '.php');
			return fw_render_view($random_view_path);
		}
	}


	private function get_error_msg()
	{
		return '<b>Something went wrong :(</b>';
	}

	public function get_mercury_voog($atts){
	/**
	*Võtab RSS voost lingi artiklile
	*Saadab saadud lingi läbi Mercury API
	*Näitab iga lingi taga olnud sisust u. esimesed 350 tähemärki
	*/
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');

	if (!isset($atts['lk']))
	{
		global $m; //Muutuja modaal akende identifitseerimiseks
		global $uudis;
	}

	$id =  $atts['id'];
	$m_id = "modal_" . $id;

	//Leiab kõik postituste nimed
	$posts = get_posts (
		array(
	  'numberposts' => -1,
	));

	foreach ($posts as $obj ) {
		$post_names[strtolower ($obj->{'post_title'})] = $obj->{'ID'};
	}
	//Lõpp Leiab kõik postituste nimed

	$feed_url = $atts['link'];

	if (!isset($feed_url)) {
		return $this->get_error_msg();
	}
	$xml = simplexml_load_file($feed_url);

	$uudis[$feed_url]['count'] = count($xml->channel->item);

	// Kontroll kas päring tuli kasutaja nupu (valge, sinine) vajutusest
		if (!isset($atts['lk']))
			{
				if ($uudis[$feed_url]['id'] < $uudis[$feed_url]['count'])
				{
					$uudis[$feed_url]['id']++;
				} else
				{
					$uudis[$feed_url]['id'] = $uudis[$feed_url]['count'];
				}
		} else {
			$uudis[$feed_url]['id'] = $atts['lk'];
		}


		$api_key = $atts['mercury_key'];

		// foreach($xml->channel->item as $entry) {
		//Käib läbi kõik RSS voost saadud uudised

		$entry = $xml->channel->item[($uudis[$feed_url]['id'] - 1 )];

			$link = $entry->link; //Võtab artiklile viitava lingi

			// $html = "";

			//Võlukood, mis saadud GitHUBist ja mis saadab lingi läbi mercury API kasutades cURL-i
			/**
			 * Created by PhpStorm.
			 * User: Rees Clissold
			 * Date: 13/11/2016
			 * Time: 20:46
			 */
			// Proof of concept
			// TODO: Rewrite this in JavaScript using an AJAX HTTP Request

			$ch = curl_init();
			// $api_key = file_get_contents('api.key');
			$request_headers = array();
			$request_headers[] = 'x-api-key: ' . $api_key;

			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_URL, 'https://mercury.postlight.com/parser?url=' . $link);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

			$output = json_decode(curl_exec($ch));
			curl_close($ch);
			//Võlukoodi lõpp


			$title = $output->title;
			$content = $output->content;

			$kategooria = get_terms( array(
												'taxonomy' => 'category',
												'hide_empty' => false,
												'include' => $atts['news_category'],
											) );


 		// 	var_dump($atts['news_category']);

			// if ($output->date_published == ""){
			// 	if ($entry->pubDate == ""){
			// 		$kuup = "Väga vana";
			// 	}else $kuup = date('Y-m-d h:i', strtotime($entry->pubDate));
			// }else {
			// 	$kuup = date('Y-m-d h:i', strtotime($output->date_published));
			// }

			if ($output->date_published == ""){
				if ($entry->pubDate == ""){
					$kuup = "Väga vana";
				}else $kuup = $entry->pubDate;
			}else {
				$kuup = $output->date_published;
			}

			$image = $output->lead_image_url;

			/**
			*Kontroll, kas artiklil on mingi sisu
			*strip_tags() puhastab stringi HTML ja PHP tagidest
			*/
			if (strlen(strip_tags($content)) > 0) {
				$result['data'] = $atts;
				//Lehitseja aknale sisu loomine

				$html = "<a class='u2' href= $output->url>'$output->domain'</a>";

				$html .= $uudis[$feed_url]['id'] . "/" . $uudis[$feed_url]['count'];
				if (!array_key_exists (strtolower($title) , $post_names )){
					$html .= $kategooria[0]->name . " " ."<button name='nupp' type='submit' onclick = korja() class='btn btn-success btn-sm'></button>" ;
				}	else{
					$post_id = $post_names[strtolower($title)];
					$html .= $kategooria[0]->name . " " ."<button name='nupp' type='submit' onclick = kustuta('$post_id') class='btn btn-danger btn-sm'></button>" ;

					// $image = media_sideload_image( $image, $post_id, $output->domain);
					// var_dump($image);
				}
				$html .=  "<button name='eelmine' type='button' onclick=tulevane() class='btn btn-default btn-sm'>" . "" . "</button>";
				$html .=  "<button name='tulevane' type='button' onclick=tulevane() class='btn btn-primary btn-sm'>" . "" . "</button>";
				// $html .= "<div class='col-sm-4'> ";
				$html .= "<div class='u2-container'>";

				// $m++; //Modaalakna unikaalne id

				//link modaalaknale
				$html .= "<a class='u2' href= '#' data-target=\"#$m_id\" data-toggle=\"modal\"> <h2 class='u2'>$title</h2>";

				$html .=  "$kuup" ;

				// var_dump($atts['news_height']);

				//Pilt lehitsejale ainult siis, kui pildi link terve
				if ( $this->url_exists($image)){
					if ($atts['news_height'] === 'full')
					{
						$html .= "<img src='$image' class='img-thumbnail' alt='$title' 	style='float:right;width:50%;border:0;'>";
					} else {
						$html .= "<img src='$image' class='img-thumbnail' alt='$title' 	style='float:right;width:50%;height:100%;border:0;'>";
					}
					$result['data']['lead_image'] = $image;
				}

				if ($atts['show_preview'] and $atts['news_height'] === 'full')
				{
					//Lühendatud eelvaate sisu tegemine
					$n = 550; //eelvaate sisu pikkus tähtedes

					$eelVaade = strtok(strip_tags($content), "."); //strtok() annab stringi kuni eraldajani
					for ($x = 0; $x <= 2; $x++) { //Võtan 3 lauset, eeldusel, et lause lõppeb punktiga
						$eelVaade .= strtok(".") . ".";
					}

					if (strlen($eelVaade) > $n) { //Kui sisu pikem kui vaja, siis tee viimase tühiku pealt lühemaks
						$description = substr($eelVaade, 0, strripos(substr($eelVaade, 0, $n), " ")) . "...";
					}else {
						$description = $eelVaade;
					}

					$html .= $description;
				}

				$html .= "</a>";

				// $html .= $output->date_published .'<br>';
				// $html .= $output->lead_image_url .'<br>';
				// $html .= $output->dek .'<br>';
				// $html .= $output->url .'<br>';
				// $html .= $output->domain .'<br>';
				// $html .= $output->excerpt .'<br>';
				// $html .= $output->word_count .'<br>';
				// $html .= $output->direction .'<br>';
				// $html .= $output->total_pages .'<br>';
				// $html .= $output->rendered_pages .'<br>';
				// $html .= $output->next_page_url .'<br>';

				//modaalakna sisu

				$html .= "
					<div id='$m_id' class='modal fade'>
						<div class='modal-dialog'>
							<div class='modal-content'>
								<div class='modal-header'>
									<button type='button' class='close' data-dismiss='modal' >&times;</button>
									<h2>'$title'</h2>
								</div>
								<div class='modal-body'>
								   $content
								</div>

							</div>
						</div>
					</div>";

				$html .= "</div>";


				$result['data']['lk'] = $uudis[$feed_url]['id'];
				$result['data']['lk_kokku'] = $uudis[$feed_url]['count'];
				// $result['data']['title'] = $title;
				// $result['data']['kuup'] = $kuup;
				$jura = array("http", ":", ".", "/", "_", "?", "=", "<", ">");
				$result['data']['voo_nimi'] = str_replace($jura, "",strip_tags($feed_url));

				$html .= "<div class='u2-andmed' data-u2=" . json_encode($result['data']) . "> </div>";

				$result['content'] = $html;

				return $result; //saadab browserile sisu

			}

		// }

	}

	public function _get_category_dropdown_choices()	{
	// Funktsioon annab kategooriate valiku nimekirja

		$kategooriad = (get_terms( array(
											'taxonomy' => 'category',
											'hide_empty' => false,
										) )
									);

		$result = array();

		foreach($kategooriad as $kat){
			$v6ti = $kat->term_id;
			$result[$v6ti] = $kat->name ;
			}

		return $result;
	}

	private function url_exists($url) {
		//Funktsioon mis kontrollib, kas pildi lingilt tuleb pilt
	    $hdrs = @get_headers($url);
	    return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$hdrs[0]) : false;
	}


	function uudis_kaks_create()
	// Salvestab uue uudise WP-i
	{
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		$query_post = $_POST;
    $post_title = ($query_post['post_title']) ? $query_post['post_title'] : false;

		$content = stripslashes($query_post['post_content']);
    // Create post object
    $new_u2_post = array(
        'post_type'     => 'post',
        'post_title'    => wp_strip_all_tags($post_title ),
				'post_name' => wp_strip_all_tags($post_title ),
				'post_content' => $content,
        'post_status'   => 'publish',
        'post_author'   => 1,
				'post_category' => array($query_post['post_category']),
    );

    // Insert the post into the database
    $uus_post_id = wp_insert_post( $new_u2_post );

		$alt = "lead_image";

		$image = media_sideload_image( $query_post['lead_image'], $uus_post_id, $alt);
		if(!empty($image) && !is_wp_error($image)){
			$args = array(
				'post_type' => 'attachment',
				'posts_per_page' => 1,
				'post_status' => 'any',
				'post_parent' => $uus_post_id
			);

			// reference new image to set as featured
			$attachments = get_posts($args);

			if($attachments){
				foreach($attachments as $attachment){
					set_post_thumbnail($uus_post_id, $attachment->ID);
					// only want one image
					break;
				}
			}
		}

		$dom = new domDocument;
		/*** load the html into the object ***/
		libxml_use_internal_errors(true);
		$dom->loadHTML($content);
		// libxml_clear_errors();
		libxml_use_internal_errors(false);
		/*** discard white space ***/
		$dom->preserveWhiteSpace = false;
		$images = $dom->getElementsByTagName('img');

		// $content = "*";
		$content2 = $content;
		// var_dump ($content2);

		foreach($images as $img)
				{
					$url = $img->getAttribute('src');
					// var_dump ($url);
					// $url = stripslashes($url);
					$url = trim($url, '"');

					$alt = $img->getAttribute('alt');
					// $alt = stripslashes($alt);
					$alt = trim($alt, '"');

					// $start = strripos  ($url, "/");
					// $end = strripos  ($url, ".");

					// $alt = substr ( $url , $start + 1 , ($end - $start) );
					// var_dump ($url);
					// var_dump ($alt);
					// die;

					$image = media_sideload_image($url, $uus_post_id, $alt,  'src');
					// var_dump (is_wp_error($image));
					if(!empty($image) && !is_wp_error($image)) {
						// $content .= "Image not empty and ...";
						$content2 = str_replace($url, $image, $content2);

					} else {
						$content .= $image;
					}

				}

			// var_dump ($content2);
			// die;

		$new_u2_post['post_content'] = $content2;
		$new_u2_post['ID'] = $uus_post_id;

		wp_update_post( $new_u2_post );

		// $uus_post_id2 = wp_insert_post( $new_u2_post );

		wp_send_json_success( $uus_post_id );

		// die();
	}

	function uudis_kaks_kustuta()
	{
		$postid = ($_POST['post_id']) ? $_POST['post_id'] : false;

		$korras = wp_delete_post( $postid );

		wp_send_json_success( $korras );

	}

	function uudis_kaks_mercy()
	{
		$atts = $_POST;
		unset($atts[action]);

		$korras = $this->get_mercury_voog($atts);

		wp_send_json_success( $korras );
	}

}
