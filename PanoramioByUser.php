<?php
/*
	Plugin Name: Panoramio by User
	Plugin URI: http://www.irrwisch.net
	Description: Panoramio by User - Displays some, or all, images owned by a certain user ID
	Version: 1.1
	Author: Nico Hochberger
	Author URI: http://www.irrwisch.net
	License: GPL2
*/
/* 
    Copyright 2009  Nico Hochberger  (email : nico@irrwisch.net)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
	$SIZES = array('medium', 'small', 'thumbnail', 'square', 'mini_square');
	$SORTING_CRITERIA = array('popularity', 'upload_date');
	$GALLERY_IMAGES = NULL;
	$RANDOM_IMAGES = NULL;
	
	// If the form was submitted, 
	// store the new values to the database
	if ('alterSettings' == $HTTP_POST_VARS['PbU_action']) {
	    update_option("PbU_UserID",$HTTP_POST_VARS['PbU_UserID']);
		update_option("PbU_Copyright",$HTTP_POST_VARS['PbU_Copyright']);
		update_option("PbU_Size",$HTTP_POST_VARS['PbU_Size']);
		update_option("PbU_Order",$HTTP_POST_VARS['PbU_Order']);
		update_option("PbU_Gallery_size",$HTTP_POST_VARS['PbU_Gallery_size']);
		update_option("PbU_Short_copyright",$HTTP_POST_VARS['PbU_Short_copyright']);
		if(0 < $HTTP_POST_VARS['PbU_Pics_per_row'])
		{
			update_option("PbU_Pics_per_row", $HTTP_POST_VARS['PbU_Pics_per_row']);
		}		
	}
	
	
	// Registering WordPress-Hooks
	add_action('admin_menu', 'PbU_registerMenu');
	add_action('wp_head', 'PbU_registerStylesheet');
	
	// Registering WordPress-Filters
	add_filter('the_content', 'PbU_displayGalleryIn');
	add_filter('the_content', 'PbU_displayRandomPictureIn');
	
	function PbU_registerStylesheet() {
		echo '<link rel="stylesheet" href="'.trailingslashit(get_option('siteurl')).'wp-content/plugins/panoramio-by-user/PbU_style.css" type="text/css" media="screen" />';
	}
	
	function PbU_RegisterMenu() {
		add_option("PbU_UserID");
		add_option("PbU_Copyright", "Panoramio pictures by ##user##");
		add_option("PbU_Short_copyright", "Panoramio picture by ##user##");
		add_option("PbU_Pics_per_row", "3");
		add_option("PbU_Order", "0");
		add_option("PbU_Size", "2");
		add_option("PbU_Gallery_size", "3");
		add_options_page('Panoramio by User', 'Panoramio by User', 9, __FILE__, 'PbU_createOptionsPage');
	}
	
	function PbU_createOptionsPage() 
	{
		?>
		<div class="wrap">
			<h2>Panoramio by User Settings</h2>
			<form method="post" action="<?php echo $location ?>">
				<?php wp_nonce_field('PbU_alterSettings', 'PbU_settingsNonce'); ?>
				<input type="hidden" name="PbU_action" value="alterSettings" />
				<table class="form-table">
					<tr>
						<td colspan="3"><h3>General Settings</h3></td>
					</tr>
					<tr>
						<td>Panoramio user-ID:</td>
						<td><input name="PbU_UserID" value="<?php echo get_option('PbU_UserID') ?>" type="text" /></td>
						<td>Note that this is the numerical user-ID, not the user's name. This ID can be retrieved from the URL of the user's gallery on Panoramio.</td>
					</tr>
					<tr>
						<td colspan="3"><h3>Random Picture Settings</h3></td>
					</tr>
					<tr>
						<td>Picture size:</td>
						<td><?php echo PbU_createSizeSelectionFor('PbU_Size')?></td>
						<td></td>
					</tr>
					<tr>
						<td>Copyright text:</td>
						<td><input name="PbU_Short_copyright" value="<?php echo stripslashes(get_option('PbU_Short_copyright')) ?>" type="text" size="60" /></td>
						<td>Use <code>##user##</code> to insert username and link to the user's site on Panoramio.</td>
					</tr>
					<tr>
						<td colspan="3"><h3>Gallery Settings</h3></td>
					</tr>
					<tr>
						<td>Picture Size:</td>
						<td><?php echo PbU_createSizeSelectionFor('PbU_Gallery_size')?></td>
						<td>Recommended: Square</td>
					</tr>
					<tr>
						<td>Order pictures by:</td>
						<td><?php echo PbU_createOrderSelectionFor('PbU_Order')?></td>
						<td></td>
					</tr>
					<tr>
						<td>Pictures per row:</td>
						<td><input name="PbU_Pics_per_row" value="<?php echo get_option('PbU_Pics_per_row')?>" type="text" /></td>
						<td></td>
					</tr>
					<tr>
						<td>Copyright text:</td>
						<td><input name="PbU_Copyright" value="<?php echo stripslashes(get_option('PbU_Copyright')) ?>" type="text" size="60" /></td>
						<td>Use <code>##user##</code> to insert username and link to the user´s site on Panoramio.</td>
					</tr>
					<tr>
						<td></td>
						<td><input value="Save" type="submit" /></td>
						<td></td>
					</tr>
				</table>
			</form>
			<p>
				Note: Please see Panoramio API terms of use for information about required copyright information.
			</p>
			<p>
			To display a single, random picture submitted by the given user, use either <code>&lt;?php PbU_show_random_image() ?&gt;</code> in source code or <code>##PbU_Random##</code> in artciles or pages. To display all images submitted by the given user, use either <code>&lt;?php PbU_show_gallery() ?&gt;</code> in source code or <code>##PbU_Gallery##</code> in artciles or pages.
			</p>
		</div>
		<?php
	}
	
	function PbU_createSizeSelectionFor($property) {
		global $SIZES;
		$selectBox = '<select name="' . $property . '">';
		$selectedSize = get_option($property);
		foreach ($SIZES as $key => $size) {
			$selectBox .= '<option value="'.$key.'"';
			if ($key == $selectedSize) {
				$selectBox .= 'selected';
			}
			$selectBox .= '>' . $size . '</option>';
		}
		$selectBox .= '</select>';
		return $selectBox;
	}
	
	function PbU_createOrderSelectionFor($property) {
		global $SORTING_CRITERIA;
		$selectBox = '<select name="' . $property . '">';
		$selectedCriterion = get_option($property);
		foreach ($SORTING_CRITERIA as $key => $criterion) {
			$selectBox .= '<option value="'.$key.'"';
			if ($key == $selectedCriterion) {
				$selectBox .= 'selected';
			}
			$selectBox .= '>' . $criterion . '</option>';
		}
		$selectBox .= '</select>';
		return $selectBox;
	}
	
	
	// Filter for replacing ##xxx## by the desired content
	function PbU_displayGalleryIn($theContent) {
		$gallery = PbU_generateGallery();
		return str_ireplace('##PbU_Gallery##', $gallery, $theContent);
	}
	
	function PbU_displayRandomPictureIn($theContent) {
		$picture = PbU_generateRandomPictureView();
		return str_ireplace('##PbU_Random##', $picture, $theContent);
	}
	
	/**
	 * @deprecated use <code>PbU_showRandomPicture</code> instead
	 */
	function PbU_show_random_image() {
		PbU_showRandomPicture();
	}
	
	function PbU_showRandomPicture() {
		echo PbU_generateRandomPictureView();
	}
	
	function PbU_generateRandomPictureView() {
		$images = PbU_getImagesForRandomImage();
		$amountOfImages = $images->count;
		$randomlyChosenIndex = mt_rand() % $amountOfImages;
		$randomPicture = $images->photos[$randomlyChosenIndex];
		$picture = '';
		$picture .= '<div class="PbU_randomPictureBox">';
		$picture .= '<a href="' . $randomPicture->photo_url . '" class="PbU_randomPicture">';
		$picture .= '<img src="' . $randomPicture->photo_file_url . '" alt="' . $randomPicture->photo_title . '" title="' . $randomPicture->photo_title . '">';
		$picture .= '</a>';
		$copyrightSceleton = '<a href="' .$images->photos[0]->owner_url. '">'.$images->photos[0]->owner_name.'</a>';
		$copyrightText = str_replace('##user##', $copyrightSceleton, stripslashes(get_option('PbU_Short_copyright')));
		$picture .= ' <p class="PbU_randomPictureCopyright">' . $copyrightText .'</p>';
		$picture .= '</div>';
		return $picture;
	}
	
	function PbU_getImagesForRandomImage() {
		global $RANDOM_IMAGES;
		if (NULL == $RANDOM_IMAGES) {
			$RANDOM_IMAGES = PbU_loadImagesFromPanoramioForRandomImage();
		}
		return $RANDOM_IMAGES;
	}
	
	function PbU_loadImagesFromPanoramioForRandomImage() {
		require_once 'load.php';
		global $SIZES;
		global $SORTING_CRITERIA;
		$size = $SIZES[get_option('PbU_Size')];
		$order = $SORTING_CRITERIA[get_option('PbU_Order')];
		$images = PbU_LoadImagesFromPanoramioWith($size, $order);
		return $images;
	}
	
	function PbU_LoadImagesFromPanoramioWith($size, $order) {
		$json_content = load('http://www.panoramio.com/map/get_panoramas.php?order='.$order.'&set=' . get_option('PbU_UserID') . '&from=0&to=20&minx=-180&miny=-90&maxx=180&maxy=90&size=' . $size);
		return json_decode($json_content); 
	}

	/**
	 * @deprecated use <code>PbU_showGallery</code> instead
	 */
	function PbU_show_gallery() {
		PbU_showGallery();
	}
	
	function PbU_showGallery() {
		echo PbU_generateGallery();
	}
	
	function PbU_getImagesForGallery() {
		global $GALLERY_IMAGES;
		if (NULL == $GALLERY_IMAGES) {
			$GALLERY_IMAGES = PbU_loadImagesFromPanoramioForGallery();
		}
		return $GALLERY_IMAGES;
	}
	
	function PbU_loadImagesFromPanoramioForGallery() {
		require_once 'load.php';
		global $SIZES;
		global $SORTING_CRITERIA;
		$size = $SIZES[get_option('PbU_Gallery_size')];
		$order = $SORTING_CRITERIA[get_option('PbU_Order')];
		$images = PbU_LoadImagesFromPanoramioWith($size, $order);
		return $images;
	}
	
	function PbU_generateGallery() {
		$images = PbU_getImagesForGallery();
		$copyrightSceleton = '<a href="' .$images->photos[0]->owner_url. '">'.$images->photos[0]->owner_name.'</a>';
		$copyrightText = str_replace('##user##', $copyrightSceleton, stripslashes(get_option('PbU_Copyright')));
		$widthOfImageContainer = intval(99/get_option('PbU_Pics_per_row'));
		$gallery = '';
		$gallery .= '<div class="PbU_gallery">';
		foreach ($images->photos as $key => $image) {
			$gallery .= '<p class="PbU_galleryImageBox" style="width: ' . $widthOfImageContainer . '%;">';
			$gallery .= '<a href="'. $image->photo_url .'" target="_blank" class="PbU_galleryImage">';
			$gallery .= '<img src="' . $image->photo_file_url . '" alt="'.$image->photo_title.'" title="'.$image->photo_title.'" />';
			$gallery .= '</a>';
			$gallery .= '</p>';
		}
		$gallery .= '<p class="PbU_galleryCopyright">' . $copyrightText . '</p>';
		$gallery .= '</div>';
		return $gallery;
	}
?>