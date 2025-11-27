<?php
/**
 * DataKit Integration Helper
 *
 * Provides integration between frs-lrg and DataKit SDK for unified data management.
 * This helper class creates DataViews for various data types (Leads, Partnerships, etc.)
 * and wraps existing REST API endpoints as DataKit DataSources.
 *
 * @package LendingResourceHub\Core
 * @since   1.0.0
 */

namespace LendingResourceHub\Core;

use DataKit\DataViews\Data\ArrayDataSource;
use DataKit\DataViews\DataView\DataView;
use DataKit\DataViews\Field\TextField;
use DataKit\DataViews\Field\DateTimeField;
use DataKit\DataViews\Field\EnumField;
use DataKit\DataViews\DataView\Sort;
use LendingResourceHub\Traits\Base;

defined( 'ABSPATH' ) || exit;

/**
 * DataKit Integration Helper Class
 *
 * Provides methods to create DataViews for various data types in the plugin.
 *
 * Usage:
 * ```php
 * // Get instance
 * $datakit = DataKit::get_instance();
 *
 * // Create a DataView for leads
 * $leads_dataview = $datakit->create_leads_dataview();
 *
 * // Render the DataView
 * echo $datakit->render_dataview( $leads_dataview );
 * ```
 *
 * @since 1.0.0
 */
class DataKit {
	use Base;

	/**
	 * Cached DataView instances
	 *
	 * @var array<string, DataView>
	 */
	private array $dataviews = [];

	/**
	 * Initialize the DataKit integration
	 *
	 * @return void
	 */
	public function init(): void {
		// Hook into WordPress to register REST routes for DataViews
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Register REST API routes for DataKit DataViews
	 *
	 * DataKit requires REST endpoints to handle AJAX requests for pagination,
	 * filtering, sorting, and actions like delete.
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		// DataKit REST routes will be registered here
		// Format: /wp-json/datakit/v1/views/{view_id}/data
	}

	/**
	 * Create a DataView for Calculator Leads
	 *
	 * This creates a table-based DataView showing all calculator leads with
	 * ability to filter, sort, and paginate.
	 *
	 * @return DataView
	 */
	public function create_leads_dataview(): DataView {
		// Cache the DataView to avoid recreating it
		if ( isset( $this->dataviews['leads'] ) ) {
			return $this->dataviews['leads'];
		}

		// Fetch leads data from the database
		// In production, this would use a custom DataSource that fetches from REST API
		// For now, we'll use ArrayDataSource with mock data as an example
		$leads_data = $this->get_leads_data();

		// Create ArrayDataSource with the data
		$data_source = new ArrayDataSource( 'calculator-leads', $leads_data );

		// Define fields for the DataView
		$fields = array(
			TextField::create( 'name', __( 'Name', 'lending-resource-hub' ) ),
			TextField::create( 'email', __( 'Email', 'lending-resource-hub' ) ),
			TextField::create( 'phone', __( 'Phone', 'lending-resource-hub' ) ),
			EnumField::create(
				'calculator_type',
				__( 'Calculator', 'lending-resource-hub' ),
				array(
					'affordability' => __( 'Affordability', 'lending-resource-hub' ),
					'conventional' => __( 'Conventional', 'lending-resource-hub' ),
					'refinance' => __( 'Refinance', 'lending-resource-hub' ),
					'rent-vs-buy' => __( 'Rent vs Buy', 'lending-resource-hub' ),
					'dscr' => __( 'DSCR', 'lending-resource-hub' ),
					'buydown' => __( 'Buydown', 'lending-resource-hub' ),
					'net-proceeds' => __( 'Net Proceeds', 'lending-resource-hub' ),
				)
			),
			EnumField::create(
				'status',
				__( 'Status', 'lending-resource-hub' ),
				array(
					'new' => __( 'New', 'lending-resource-hub' ),
					'contacted' => __( 'Contacted', 'lending-resource-hub' ),
					'qualified' => __( 'Qualified', 'lending-resource-hub' ),
					'closed' => __( 'Closed', 'lending-resource-hub' ),
				)
			),
			DateTimeField::create( 'created_at', __( 'Submitted', 'lending-resource-hub' ) ),
		);

		// Create the DataView with table layout
		$dataview = DataView::table( 'calculator-leads', $data_source, $fields )
			->sort( Sort::desc( 'created_at' ) ) // Default sort by newest first
			->paginate( 25 ) // Show 25 leads per page
			->search( '' ); // Enable search

		// Store in cache
		$this->dataviews['leads'] = $dataview;

		return $dataview;
	}

	/**
	 * Get leads data from the database
	 *
	 * In production, this would fetch real data from wp_frs_calculator_leads table.
	 * For now, returns mock data for demonstration.
	 *
	 * @return array<string, array<string, string>>
	 */
	private function get_leads_data(): array {
		// TODO: Replace with actual database query using Eloquent
		// Example: return Lead::all()->toArray();

		// Mock data for demonstration
		return array(
			'1' => array(
				'id' => '1',
				'name' => 'John Doe',
				'email' => 'john@example.com',
				'phone' => '555-1234',
				'calculator_type' => 'affordability',
				'status' => 'new',
				'created_at' => '2025-11-22 10:30:00',
			),
			'2' => array(
				'id' => '2',
				'name' => 'Jane Smith',
				'email' => 'jane@example.com',
				'phone' => '555-5678',
				'calculator_type' => 'conventional',
				'status' => 'contacted',
				'created_at' => '2025-11-22 09:15:00',
			),
		);
	}

	/**
	 * Render a DataView
	 *
	 * This method outputs the HTML and JavaScript needed to display a DataView.
	 * It enqueues the necessary DataKit assets and renders the DataView container.
	 *
	 * @param DataView $dataview The DataView instance to render.
	 * @return string HTML markup for the DataView
	 */
	public function render_dataview( DataView $dataview ): string {
		// Enqueue DataKit assets
		$this->enqueue_datakit_assets();

		// Get the DataView configuration as JSON
		$dataview_config = $dataview->to_js( true );

		// Generate unique ID for this DataView instance
		$container_id = 'datakit-' . $dataview->id();

		// Output the container and initialization script
		ob_start();
		?>
		<div id="<?php echo esc_attr( $container_id ); ?>" class="datakit-container"></div>
		<script type="text/javascript">
		( function() {
			if ( typeof datakit !== 'undefined' ) {
				datakit.init( '<?php echo esc_js( $container_id ); ?>', <?php echo $dataview_config; ?> );
			}
		} )();
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Enqueue DataKit assets
	 *
	 * Loads the necessary JavaScript and CSS files for DataKit.
	 * DataKit SDK includes pre-built assets in the assets/ directory.
	 *
	 * @return void
	 */
	private function enqueue_datakit_assets(): void {
		$datakit_path = plugin_dir_path( __DIR__ ) . '../libs/datakit/';
		$datakit_url = plugin_dir_url( __DIR__ ) . '../libs/datakit/';

		// Check if assets exist
		if ( ! file_exists( $datakit_path . 'assets/datakit.js' ) ) {
			return;
		}

		// Enqueue DataKit JavaScript
		wp_enqueue_script(
			'datakit',
			$datakit_url . 'assets/datakit.js',
			array( 'wp-element', 'wp-components', 'wp-dataviews' ),
			'1.0.0',
			true
		);

		// Enqueue DataKit CSS
		wp_enqueue_style(
			'datakit',
			$datakit_url . 'assets/datakit.css',
			array( 'wp-components' ),
			'1.0.0'
		);
	}

	/**
	 * Create a shortcode for rendering DataViews
	 *
	 * Usage: [datakit_leads]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered DataView HTML
	 */
	public function shortcode_leads( array $atts ): string {
		$dataview = $this->create_leads_dataview();
		return $this->render_dataview( $dataview );
	}
}
