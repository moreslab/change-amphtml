<?php
/**
 *
 * Plugin Name: Change AMPHTML
 * Description: AMP sayfaları için amphtml etiketi domainini değiştirir. Ücretsiz bir yazılımdır. Güncel sürümünü github sayfamdan edinebilirsiniz.
 * Version:     2.0
 * Author:      moreslab
 * Plugin URI:  https://github.com/moreslab/change-amphtml
 * Author URI:  https://github.com/moreslab/
 * License: GNU General Public License v3.0
 *
 */


//Bu alan bunny.net kullanmak isteyenler için opsiyonel olarak eklenmiştir.
//Bunny.net sistemini AMP'de kullanmak için 16 ve satırları silin. 20. satırı kendinize göre düzenleyin.
/*
function buffer_bunny($buffer){

$domain = str_replace(['http://', 'https://', 'www.'], null, get_site_url());
$bunny_domain = 'subamp.b-cdn.net';


return preg_replace(
    '/<amp-img(.*?)src="https?:\/\/'.$domain.'/',
    '<amp-img$1src="https://'.$bunny_domain, 
    $buffer);

}

function buffer_bunny_start()
    {
        if (function_exists('buffer_bunny')) {
            ob_start('buffer_bunny');
        }
    }

    function buffer_bunny_end()
    {
        if (function_exists('buffer_bunny') && ob_start('buffer_bunny') === true) {
            ob_end_flush();
        }
    }

add_action('after_setup_theme', 'buffer_bunny_start');
add_action('shutdown', 'buffer_bunny_end');
*/


//Bu alan resimlerde cdn.ampproject.org kullanmak isteyenler için opsiyonel olarak eklenmiştir.

function buffer_bunny( $finder ) {
	
	$site = str_replace(['http://', 'https://'], '', rtrim(get_site_url(), '/'));
	$http_version = is_ssl() ? 'https://' : 'http://';
	$addon = !empty(get_option('moreslab__amphtml')) ? get_option('moreslab__amphtml') : str_replace(['https://', 'http://'], null, get_site_url());
	
	if ( strpos( $finder, 'https://cdn.ampproject.org' ) == false && $_SERVER['HTTP_HOST']==$addon) {
		
		header("HTTP/1.1 403 Forbidden" );
		exit;
		
		} else {
	
	if ( strpos( $finder, 'https://cdn.ampproject.org' ) !== false && ! is_admin() ) {
	
	$aPattern = '@<a(.*?)href="https?://'.$site.'@si';
    $aReplace = '<a$1href="https://'.str_replace(['.', ' '], '-', $addon).'.cdn.ampproject.org'.'/c/s/'.$addon;
    $finder = preg_replace( $aPattern, $aReplace, $finder );

    $imgPattern = '@<amp-img(.*?)src="https?://'.$site.'@si';
    $imgReplace = '<amp-img$1src="https://'.str_replace(['.', ' '], '-', $site).'.cdn.ampproject.org'.'/i/s/'.$site;
    $finder = preg_replace( $imgPattern, $imgReplace, $finder );
		
		$icoPattern = '@<link(.*?)icon" href="https?://'.$site.'@si';
        $icoReplace = '<link$1icon" href="https://'.str_replace(['.', ' '], '-', $site).'.cdn.ampproject.org'.'/i/s/'.$site;
        $finder = preg_replace( $icoPattern, $icoReplace, $finder );


        $bgPattern = "@background-image:(.*?)url\('?https?://" . $site.'@si';
        $bgReplace = 'background-image:$1url(http$2://'.str_replace(['.', ' '], '-', $site) . '.cdn.ampproject.org/i/s/'.$site;

        $finder = preg_replace($bgPattern, $bgReplace, $finder);
	
    }
	
	$finder = str_replace(
        '<link rel="amphtml" href="'.get_site_url(),
        '<link rel="amphtml" href="'.$http_version . $addon,
        $finder);
	
    return $finder;
	}
}

function buffer_bunny_start()
{
    if (function_exists('buffer_bunny')) {
        ob_start('buffer_bunny');
    }
}

function buffer_bunny_end()
{
    if (function_exists('buffer_bunny') && ob_start('buffer_bunny') === true) {
        ob_end_flush();
    }
}

add_action('after_setup_theme', 'buffer_bunny_start');
add_action('shutdown', 'buffer_bunny_end');


add_filter( 'change-amphtml/change-amphtml.php', function ($links_array){
    array_unshift( $links_array, '<a href="'.get_admin_url().'options-general.php?page=moreslab_amphtml_group">Ayarlar</a>' );
    return $links_array;
} );


add_action( 'admin_init', function () {
    register_setting( 'moreslab_amphtml_group', 'moreslab__amphtml' );
} );

add_action('admin_menu', function(){

    add_options_page(
        'AMPHTML',
        'AMPHTML',
        'manage_options',
        'moreslab_amphtml_group',
        function(){
            ?>
            <style>
                .submit{
                    padding: 0;
                }
                p.submit{
                    margin-top: 0 !important;
                }
            </style>
            <div style="width: fit-content; background: white; padding:15px; margin:10% auto 0; border-radius:7px;box-shadow: 1px 0 25px rgba(0, 0, 0, .1)">
                <div>
                    <form method="post" action="options.php">
                        <?php settings_fields( 'moreslab_amphtml_group' ); ?>
                        <?php do_settings_sections( 'moreslab_amphtml_group' ); ?>
                        <label>
                            <div style="margin-bottom:10px">
                                Kutuya <strong>sadece domain</strong> yazın. <br> Örneğin: sub.domain.com veya moreslab.com gibi.
                            </div>
                            <input type="text" name="moreslab__amphtml" placeholder="AMPHTML içeriği" value="<?php echo get_option('moreslab__amphtml') ?>">
                        </label>
                        <?php submit_button(); ?>
                    </form>
                </div>
            </div>
            <?php
        }
    );
});
