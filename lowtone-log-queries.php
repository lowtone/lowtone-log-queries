<?php
/*
 * Plugin Name: Log queries
 * Plugin URI: http://wordpress.lowtone.nl
 * Description: 
 * Version: 1.0
 * Author: Lowtone <info@lowtone.nl>
 * Author URI: http://lowtone.nl
 * License: http://wordpress.lowtone.nl/license
 * Requires: lowtone-lib
 */
/**
 * @author Paul van der Meijs <code@lowtone.nl>
 * @copyright Copyright (c) 2011-2012, Paul van der Meijs
 * @license http://wordpress.lowtone.nl/license/
 * @version 1.0
 * @package wordpress\plugins\lowtone\log\queries
 */

namespace lowtone\log\queries {

	use lowtone\content\packages\Package;

	// Includes
	
	if (!include_once WP_PLUGIN_DIR . "/lowtone-content/lowtone-content.php") 
		return trigger_error("Lowtone Content plugin is required", E_USER_ERROR);

	Package::init(array(
			Package::INIT_PACKAGES => array("lowtone"),
			Package::INIT_MERGED_PATH => __NAMESPACE__,
			Package::INIT_SUCCESS => function() {

				if (!defined("SAVEQUERIES"))
					define("SAVEQUERIES", 1);

				add_action("shutdown", function() {
					$url = \lowtone\net\URL::fromCurrent();

					$file = LOG_DIR . DIRECTORY_SEPARATOR . sprintf("query_log-%s.sql", md5(serialize($url)));

					$lines = array();

					$lines[] = "/**";
					$lines[] = " * QUERY LOG";
					$lines[] = " * ";
					$lines[] = " * Created: " . date("Y-m-d H:i:s");
					$lines[] = " * Queries: " . count($GLOBALS["wpdb"]->queries);
					$lines[] = " * URL: " . $url;
					$lines[] = " * Total time: " . array_sum(array_map(function($query) {return $query[1];}, $GLOBALS["wpdb"]->queries)) . "s";
					$lines[] = " */";
					$lines[] = "";
					$lines[] = "";

					foreach ($GLOBALS["wpdb"]->queries as $i => $query) {
						$lines[] = "/**";
						$lines[] = " * #" . $i;
						$lines[] = " * Time: " . $query[1];
						$lines[] = " * Caller: ";

						foreach (explode(",", $query[2]) as $caller)
							$lines[] = " *   -->  " . trim($caller);

						$lines[] = " */";

						$lines[] = preg_replace('/\s+/', ' ', $query[0]) . ";";

						$lines[] = "";
					}

					file_put_contents($file,  implode("\r\n", $lines));
				}, 9999);

			}
		));

}