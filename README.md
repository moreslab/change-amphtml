# change-amphtml

add_action('amp_post_template_head', function(){
  echo '<link rel="canonical" href="' . get_site_url() . str_replace('/amp', null, $_SERVER['REQUEST_URI']) . '" />';
});
