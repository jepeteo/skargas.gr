<?php

function v_getUrl() {
  $url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
  $url .= '://' . $_SERVER['SERVER_NAME'];
  $url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
  $url .= $_SERVER['REQUEST_URI'];
  return $url;
}
function v_forcelogin() {
  if( !is_user_logged_in() ) {
    $url = v_getUrl();
    $whitelist = apply_filters('v_forcelogin_whitelist', array());
    $redirect_url = apply_filters('v_forcelogin_redirect', $url);
    if( preg_replace('/\?.*/', '', $url) != preg_replace('/\?.*/', '', wp_login_url()) && !in_array($url, $whitelist) ) {
      wp_safe_redirect( wp_login_url( $redirect_url ), 302 ); exit();
    }
  }
}
add_action('init', 'v_forcelogin');


// Redirect to homepage after login
function redirect_admin( $redirect_to, $request, $user ){
    //is there a user to check?
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        //check for admins
        if ( in_array( 'administrator', $user->roles ) ) {
            $redirect_to = "/"; // Your redirect URL
        }
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'redirect_admin', 10, 3 );

/* EVENTS */
/*******************************/

// Show Rantevou
function showRantevou($atts) {
	ob_start();
	
// Find current date time.
$date_now = date('Y-m-d H:i:s');
$time_now = strtotime($date_now);

// Find date time in 7 days.
$time_next_week = strtotime('+7 day', $time_now);
$date_next_week = date('Y-m-d H:i:s', $time_next_week);

	/*
	$ev_date_now = date_i18n('l, d F Y', $time_now);
	$ev_date_nextweek = date_i18n('l, d F Y', $time_next_week);
	
	$ev_date_nowa = strtotime($_GET['evstartdate'] ?? $time_now);
	$ev_date_nowb = date_i18n('l, d F Y', $ev_date_nowa);

	$ev_date_nexa = strtotime($_GET['evenddate'] ?? $time_next_week);
	$ev_date_nexb = date_i18n('l, d F Y', $ev_date_nexa);
	*/

	//Print form
	if ($_GET['evstartdate']) {
		$form_ev_from = date_i18n("Y-m-d", strtotime($_GET['evstartdate']));	
	} else {
		$form_ev_from = date_i18n("Y-m-d", $time_now);
	}
	if ($_GET['evenddate']) {
		$form_ev_to   = date_i18n("Y-m-d", strtotime($_GET['evenddate']));
	} else {
		$form_ev_to = date_i18n("Y-m-d", $time_next_week);
	}
	echo "<div class='ev-filter'><span class='ev-filter-label'>ΦΙΛΤΡΑ</span>
	<form class='ev-filter-form' action='/events'>
		<label for='evstartdate'>Εμφάνιση προγραμματισμένων ραντεβού από:</label>
  		<input type='date' id='evstartdate' name='evstartdate' value='". $form_ev_from ."'>
		<label for='evenddate'>έως:</label>
  		<input type='date' id='evenddate' name='evenddate' value='". $form_ev_to ."'>
		<input id='ev-filter-button' type='submit'>
	</form></div> <br>" ;	
	


	$ev_date_start = date('Y-m-d H:i:s', strtotime($form_ev_from));
	$ev_date_end   = date('Y-m-d H:i:s', strtotime($form_ev_to));

	
	/* Debugging time
	echo $time_now. " - " . $time_next_week . "<br>";
	echo $form_ev_from . " - " . $form_ev_to . "<br>";
	echo strtotime($form_ev_from) . " - " . strtotime($form_ev_to) . "<br>";
	echo $ev_date_start . " - " . $ev_date_end;
	*/
	
// // // Query events.
// 
// 
$posts = get_posts(array(
    'posts_per_page' => -1,
    'post_type'      => 'events',
    'meta_query'     => array(
        array(
            'key'         => 'ev_datetime',
            'compare'     => 'BETWEEN',
            'value'       => array( $ev_date_start, $ev_date_end ),
//            'value'       => array( $date_now, $date_next_week ),
            'type'        => 'DATETIME'
        )
    ),
    'order'          => 'ASC',
    'orderby'        => 'meta_value',
    'meta_key'       => 'ev_datetime',
    'meta_type'      => 'DATETIME'
));

if( $posts ) {
	foreach( $posts as $post ) {
		echo "<a href='" . get_permalink($post->ID) ."'> <div class='event-card'>";
		echo "<div class='ev-card-title'>" . get_the_title($post->ID) . "</div>";
//		echo date_i18n('l d F Y, @ G:i', get_field('ev_datetime',$post->ID));
		echo "<div class='ev-card-date'>" . get_field('ev_datetime',$post->ID) . "</div>";

		
		$post_id = get_field('ev_client', $post->ID);
	
	    $city = get_field("city", $post_id);
        $address = get_field("address", $post_id);
		$client_name = get_field("name", $post_id);
		$client_lname = get_field("lastname", $post_id);
		$client_tel = "";
		$client_mob = "";
		if( get_field("phone-home", $post_id) ): $client_tel = "<span class='ev-tel'></span>" . get_field('phone-home', $post_id); endif;
		if( get_field("mobile-personal", $post_id) ): $client_mob = "<span class='ev-mob'></span>" . get_field('mobile-personal', $post_id); endif;
		
		$fulladdress 	= "<div class='ev-card-addr'>" . $address . ", " . $city . "</div>";
		$fullname 		= "<div class='ev-card-name'>" . $client_name . " " . $client_lname  . "</div>";
		$fullcontact 	= "<div class='ev-card-cont'>" . $client_tel . " ". $client_mob  . "</div>" ;
        $title = $fulladdress . $fullname . $fullcontact  ;
		echo $title;
		echo "</div></a>";

	}
}
	// l d F Y, @ h:i a (για 12h με πμ,μμ)
	// date_i18n( "l d F Y, @ G:i", $unixtimestamp);
	
    return ob_get_clean();
}
add_shortcode( 'Show-Rantevou', 'showRantevou' );



// Edit Randevou
function editrandevouslink($atts) {
	ob_start();
	echo "<a href='" . get_edit_post_link() . "'>ΕΠΕΞΕΡΓΑΣΙΑ ΡΑΝΤΕΒΟΥ</a>";
    return ob_get_clean();
}
add_shortcode( 'EditRandevou', 'editrandevouslink' );

// Show Randevou Client information
function showrandevousclientinformation($atts) {
	ob_start();
	$post_id = get_field('ev_client', $post->ID);
	echo "<a href='" . get_permalink($post_id) . "'>ΠΡΟΒΟΛΗ ΠΕΛΑΤΗ</a>";
    return ob_get_clean();
}
add_shortcode( 'ShowRandevousClientInformation', 'showrandevousclientinformation' );

// Show Randevou Client
function showrandevousclient($atts) {
	ob_start();

		echo "<div class='rc-card'>";
		echo "<div class='rc-card-date'> Ημερομηνία Ραντεβού: " . get_field('ev_datetime',$post->ID) . "</div>";

		
		$post_id = get_field('ev_client', $post->ID);
	
	    $city = get_field("city", $post_id);
        $address = get_field("address", $post_id);
		$client_name = get_field("name", $post_id);
		$client_lname = get_field("lastname", $post_id);
		$client_tel = "";
		$client_mob = "";
		if( get_field("phone-home", $post_id) ): $client_tel = "<a href='tel:" . get_field('phone-home', $post_id) . "'><span class='rc-tel'></span>" . get_field('phone-home', $post_id) . "</a>" ; endif;
		if( get_field("mobile-personal", $post_id) ): $client_mob = "<a href='tel:" . get_field('mobile-personal', $post_id) . "'><span class='rc-mob'></span>" . get_field('mobile-personal', $post_id) . "</a>" ; endif;
		
		$fulladdress 	= "<div class='rc-card-addr'> Διεύθυνση: " . $address . ", " . $city . "</div>";
		$fullname 		= "<div class='rc-card-name'> Όνομαεπώνυμο: " . $client_name . " " . $client_lname  . "</div>";
		$fullcontact 	= "<div class='rc-card-cont'> Τηλέφωνα Επικοινωνίας: <br> " . $client_tel . " ". $client_mob  . "</div>" ;
        $title = $fulladdress . $fullname . $fullcontact  ;
		echo $title;
		echo "</div>";
	
	
	return ob_get_clean();
}
add_shortcode( 'ShowRandevousClient', 'showrandevousclient' );


/* EVENTS END */
/*******************************/
/*******************************/
/*******************************/
/*******************************/
/*******************************/




// Shortcode to custom loop
function show_clients( $atts ){
	ob_start();
	$posts = get_posts(array(
	'posts_per_page'	=> -1,
	'post_type'			=> 'post'
));

if( $posts ) :
	echo "<div><ol>";
		foreach( $posts as $post ): 
		
			setup_postdata( $post );
			echo "<li><a href='";
			the_permalink();
			echo "'>";
			the_title();
			echo "</a></li>";

		endforeach;	
	echo "</ol></div>";
	wp_reset_postdata();
	endif;
}
add_shortcode( 'showclients', 'show_clients', 10,3 );

// Edit Client
function editclientlink($atts) {
	ob_start();
	echo "<a href='" . get_edit_post_link() . "'>ΕΠΕΞΕΡΓΑΣΙΑ ΠΕΛΑΤΗ</a>";
    return ob_get_clean();
}
add_shortcode( 'EditClient', 'editclientlink' );

// Repeater Field - Αρχεία
function show_files( $atts ){
	ob_start();
//ACF fields here
// Check rows exists.
if( have_rows('documents') ):

    // Loop through rows.
    while( have_rows('documents') ) : the_row();
        // Load sub field value.
        $docutitle = get_sub_field('document_title');
        $docufile = get_sub_field('document_file');
        echo "<a class='client-docu' href='" . $docufile . "' target='_blank'>" . $docutitle . "</a>";
    // End loop.
    endwhile;

// No value.
else :
    // Do something...
endif;
    return ob_get_clean();
}
add_shortcode( 'allfiles', 'show_files' );




// Repeater Field - Πληρωμές
function show_maintenance_history( $atts ){
	ob_start();
//ACF fields here
// Check rows exists.
if( have_rows('maintenance') ):

    echo "<ol>";
    // Loop through rows.
    while( have_rows('maintenance') ) : the_row();

        // Load sub field value.
        $lastmaint = get_sub_field('last-time-date-maintanance');
        $costmaint = get_sub_field('cost-maintanance');
        echo "<li>" . $lastmaint . " - " . $costmaint . " € </li>";
    // End loop.
    endwhile;
    echo "</ol>";

// No value.
else :
    // Do something...
endif;
    return ob_get_clean();
}
add_shortcode( 'maintenancehistory', 'show_maintenance_history' );

// Φωτογραφίες Gallery
function show_gimages( $atts ){
	ob_start();
	    $images = get_field('crm-gallery');
        $size = 'thumbnail'; // (thumbnail, medium, large, full or custom size)
        	if( $images ):
            	echo "<ul class='crm-gal'>";
                $new_img_title = get_the_title();
                $new_img_title = strip_tags($new_img_title); 
                foreach( $images as $image ):
				echo "<li><a data-fancybox='gallery' data-infobar='true' data-smallBtn='true' data-animationEffect='fade' data-toolbar='auto' data-fancybox-zoom='false' data-caption='" . $new_img_title . "' href='" . $image['url'] ."' title='" .  $new_img_title . "' alt='" . $new_img_title . "' itemprop='image'>" . wp_get_attachment_image( $image['ID'], $size ) . "</a></li>";
				endforeach;
                echo "</ul>";
                endif;
	return ob_get_clean();
}
add_shortcode( 'image-gallery', 'show_gimages' );



// Τηλέφωνο Οικίας
function show_phone_home( $atts ){
	ob_start();
	if( get_field('phone-home') ){
		$client_tel = get_field('phone-home'); $client_tel = "<a href='tel:" . $client_tel . "'>Τηλ. Οικίας <br>" . $client_tel . "</a>";
	} else {
    	$client_tel = "<span>Τηλ. Οικίας</span>";
	}
	echo $client_tel;
    return ob_get_clean();
}
add_shortcode( 'phone_home', 'show_phone_home' );

// Τηλέφωνο Εργασίας
function show_phone_work( $atts ){
	ob_start();
	if( get_field('phone-work') ){
		$client_telw = get_field('phone-work'); $client_telw = "<a href='tel:" . $client_telw . "'>Τηλ. Εργασίας <br>" . $client_telw . "</a>";
	} else {
    	$client_telw = "<span>Τηλ. Εργασίας</span>";
	}
	echo $client_telw;
    return ob_get_clean();
}
add_shortcode( 'phone_work', 'show_phone_work' );

// Τηλέφωνο Άλλο
function show_phone_other( $atts ){
	ob_start();
	if( get_field('phone-other') ){
		$client_telo = get_field('phone-other'); $client_telo = "<a href='tel:" . $client_telo . "'>Τηλ. Άλλο <br>" . $client_telo . "</a>";
	} else {
    	$client_telo = "<span>Τηλ. Άλλο</span>";
	}
	echo $client_telo;
    return ob_get_clean();
}
add_shortcode( 'phone_other', 'show_phone_other' );

// Κινητό Προσωπικό
function show_mobile_personal( $atts ){
	ob_start();
	if( get_field('mobile-personal') ){
		$client_mob = get_field('mobile-personal'); $client_mob = "<a href='tel:" . $client_mob . "'>Κινητό <br>" . $client_mob . "</a>";
	} else {
    	$client_mob = "<span>Κινητό</span>";
	}
	echo $client_mob;
    return ob_get_clean();
}
add_shortcode( 'mobile_personal', 'show_mobile_personal' );

// Τηλέφωνο Εργασίας
function show_mobile_work( $atts ){
	ob_start();
	if( get_field('mobile-work') ){
		$client_mobw = get_field('mobile-work'); $client_mobw = "<a href='tel:" . $client_mobw . "'>Κιν. Εργασίας <br>" . $client_mobw . "</a>";
	} else {
    	$client_mobw = "<span>Κιν. Εργασίας</span>";
	}
	echo $client_mobw;
    return ob_get_clean();
}
add_shortcode( 'mobile_work', 'show_mobile_work' );


// Κινητό Άλλο
function show_mobile_other( $atts ){
	ob_start();
	if( get_field('mobile-other') ){
		$client_mobo = get_field('mobile-other'); $client_mobo = "<a href='tel:" . $client_mobo . "'>Κιν. Άλλο <br>" . $client_mobo . "</a>";
	} else {
    	$client_mobo = "<span>Κιν. Άλλο</span>";
	}
	echo $client_mobo;
    return ob_get_clean();
}
add_shortcode( 'mobile_other', 'show_mobile_other' );

function show_client_info( $atts ){
	
	ob_start();
	if($_GET['s'] && !empty($_GET['s'])) { $stext = $_GET['s']; }
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	
	$args= array(
		'post_type' => 'clients',
		'post_per_page' => -1,
        'paged' => $paged,
		's' => $stext
	);
	$query = new WP_Query($args);

	echo "<table class='listclient'>";
	echo "<thead><tr><td>ΔΙΕΥΘΥΝΣΗ</td>";
	echo "<td>ΠΟΛΗ</td>";
	echo "<td>ΝΟΜΟΣ</td>";
	echo "<td>ΤΚ.</td>";
	echo "<td>ΟΝΟΜΑ</td>";
	echo "<td>ΕΠΩΝΥΜΟ</td>";
	echo "<td>ΤΗΛ. ΟΙΚΙΑΣ</td>";
//	echo "<td>ΤΗΛ. ΕΡΓΑΣΙΑΣ</td>";
//	echo "<td>ΤΗΛ. ΑΛΛΟ</td>";
	echo "<td>ΚΙΝ. ΠΡΟΣΩΠΙΚΟ</td>";
//	echo "<td>ΚΙΝ. ΕΡΓΑΣΙΑΣ</td>";
//	echo "<td>ΚΙΝ. ΑΛΛΟ</td>";
	echo "<td>ΤΕΛ.<br>ΧΡΕΩΣΗ</td>";
	echo "<td></td></tr></thead>";
	
		while ($query -> have_posts()) : $query -> the_post();
			
			$client_permalink = get_the_permalink();
			$client_address = get_field("address");
			$client_city = get_field("city");
			$client_state = get_field("state");
			$client_pc = get_field("postal-code");
			$client_name = get_field('name');
			$client_lname = get_field('lastname');
			$client_telh = get_field('phone-home');
//			$client_telw = get_field('phone-work');
//			$client_telo = get_field('phone-other');
			$client_mobp = get_field('mobile-personal');
//			$client_mobw = get_field('mobile-work');
//			$client_mobo = get_field('mobile-other');
			$client_maint = get_field('maintenance');
				$client_lastmaintcost = $client_maint[0]['cost-maintanance'];


			echo "<tr><td class='claddr'>" . $client_address ." </td>";
			echo "<td class='clcity'>" . $client_city ." </td>";
			echo "<td class='clstat'>" . $client_state ." </td>";
			echo "<td class='clpost'>" . $client_pc ." </td>";
			echo "<td class='clname'>" . $client_name ." </td>";
			echo "<td class='cllnam'>" . $client_lname ." </td>";
			echo "<td class='cltelh'>" . $client_telh ." </td>";
//			echo "<td class='cltelw'>" . $client_telw ." </td>";
//			echo "<td class='cltelo'>" . $client_telo ." </td>";
			echo "<td class='clmobp'>" . $client_mobp ." </td>";
//			echo "<td class='clmobw'>" . $client_mobw ." </td>";
//			echo "<td class='clmobo'>" . $client_mobo ." </td>";
			echo "<td class='cllastmaincost'>" .$client_lastmaintcost ." €</td>";
			echo "<td class='viewclient' style='padding: 0 4px;'><a href='" . $client_permalink . "' title='Προβολή Πελάτη'><i class='fa-solid fa-eye'></i></a></td></tr>";
		endwhile; 
	
	echo "</table>";
	wp_reset_query();

	$big=99999;
	$args1 = array(
	'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
	'format'       => '?paged=%#%',
	'total'        => $query->max_num_pages,
	'current'      => $paged,
	'prev_next'    => True,
    'prev_text'    => __('Previous'),
    'next_text'    => __('Next')	);

	echo "<div class='crm-pagin'>" . paginate_links( $args1 ) . "</div>";
	
/*
	if( get_field('phone-home') ): $client_tel = " | Τηλ." . get_field('phone-home'); endif;
	if( get_field('mobile-personal') ): $client_mob = " | Κιν." . get_field('mobile-personal'); endif;
*/	
	
	return ob_get_clean();
}
add_shortcode( 'client-info', 'show_client_info' );








/* Add custom functions - Th.M */
/* Remove Divi Project Type */
add_action( 'init', 'teo_remove_divi_project_post_type' );
if ( ! function_exists( 'teo_remove_divi_project_post_type' ) ) {
 function teo_remove_divi_project_post_type(){
 unregister_post_type( 'project' );
 unregister_taxonomy( 'project_category' );
 unregister_taxonomy( 'project_tag' );
 }
}

// Removes from admin menu
add_action( 'admin_menu', 'teo_remove_admin_menus' );
function teo_remove_admin_menus() {
    remove_menu_page( 'edit-comments.php' );
}

// Removes from post and pages
add_action('init', 'teo_remove_comment_support', 100);
function teo_remove_comment_support() {
    remove_post_type_support( 'post', 'comments' );
    remove_post_type_support( 'page', 'comments' );
}

// Removes from admin bar
add_action( 'wp_before_admin_bar_render', 'teo_remove_comments_admin_bar' );
function teo_remove_comments_admin_bar() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}



/* Custom Post Type Πελάτης Title */
function teo_filter_title( $title, $post_id )
{
    if (get_post_type($post_id) == 'clients' ) {
        $city = get_field("city", $post_id);
        $address = get_field("address", $post_id);
		$client_name = get_field('name');
		$client_lname = get_field('lastname');
		$client_tel = "";
		$client_mob = "";
		if( get_field('phone-home') ): $client_tel = " | Τηλ." . get_field('phone-home'); endif;
		if( get_field('mobile-personal') ): $client_mob = " | Κιν." . get_field('mobile-personal'); endif;
        $title = $address . " - " . $city . " | " . $client_name . " " . $client_lname . $client_tel . $client_mob ;
        return $title;
    } else {
        return $title;
    }
}
add_filter( 'the_title','teo_filter_title',10,2 );
?>
