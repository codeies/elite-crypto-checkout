<?php
/**
 * Elite crypto checkout
 *
 * @package       WOOCRYPTOC
 * @author        Codeies
 * @license       gplv2-or-later
 * @version       1.0
 *
 * @wordpress-plugin
 * Plugin Name:   Elite crypto checkout
 * Plugin URI:    https://codeies.com/woocrypto-checkout
 * Description:   Woocommerce Crypto currency checkout
 * Version:       1.0
 * Author:        Codeies
 * Author URI:    https://codeies.com/muhammad-junaid
 * Text Domain:   woocrypto-checkout
 * Domain Path:   /languages
 * License:       GPLv2 or later
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Elite crypto checkout. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'WOOCRYPTOC_NAME',			'Elite crypto checkout' );

// Plugin version
define( 'WOOCRYPTOC_VERSION',		'1.0' );

// Plugin Root File
define( 'WOOCRYPTOC_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'WOOCRYPTOC_PLUGIN_BASE',	plugin_basename( WOOCRYPTOC_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'WOOCRYPTOC_PLUGIN_DIR',	plugin_dir_path( WOOCRYPTOC_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'WOOCRYPTOC_PLUGIN_URL',	plugin_dir_url( WOOCRYPTOC_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once WOOCRYPTOC_PLUGIN_DIR . 'core/class-woocrypto-checkout.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Codeies
 * @since   1.0
 * @return  object|Woocrypto_Checkout
 */
function WOOCRYPTOC() {
	return Woocrypto_Checkout::instance();
}

WOOCRYPTOC();
