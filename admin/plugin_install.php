<?php
class Oreka_Search_Activator {
	public static function activate() {
    if (! get_option('orekasearch_main')) { update_option('orekasearch_main', ''); }
    if (! get_option('orekasearch_server')) { update_option('orekasearch_server', ''); }
    if (! get_option('orekasearch_filters')) { update_option('orekasearch_filters', ''); }
    if (! get_option('orekasearch_cache')) { update_option('orekasearch_cache', ''); }
	}
}
?>