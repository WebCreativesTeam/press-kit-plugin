<?php
/**
 * Register all actions and filters for the plugin
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Press_Kit
 * @subpackage Press_Kit/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Press_Kit
 * @subpackage Press_Kit/includes
 * @author     Your Name <email@example.com>
 */
class Press_Kit_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * The array of shortcode registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $shortcodes    The shortcode registered with WordPress to fire when the plugin loads.
	 */
	protected $shortcodes;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions    = array();
		$this->filters    = array();
		$this->shortcodes = array();

	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string $hook             The name of the WordPress action that is being registered.
	 * @param    object $component        A reference to the instance of the object on which the action is defined.
	 * @param    string $callback         The name of the function definition on the $component.
	 * @param    int    $priority         Optional. Priority at which the function should be fired. Default 10.
	 * @param    int    $accepted_args    Optional. Number of arguments that should be passed to the $callback. Default 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string $hook             The name of the WordPress filter that is being registered.
	 * @param    object $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string $callback         The name of the function definition on the $component.
	 * @param    int    $priority         Optional. Priority at which the function should be fired. Default 10.
	 * @param    int    $accepted_args    Optional. Number of arguments that should be passed to the $callback. Default 1.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Remove a filter from the collection registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string $tag              The filter hook to which the function to be removed is hooked.
	 * @param    string $class_name       Class name registering the filter callback.
	 * @param    string $method_to_remove Method name for the filter's callback.
	 * @param    int    $priority         The priority of the method (default 10).
	 *
	 * @return   $removed bool Whether the function is removed.
	 */
	public function remove_filter( $tag, $class_name = '', $method_to_remove = '', $priority = 10 ) {

		global $wp_filter;
		$removed = false;

		foreach ( $wp_filter[ $tag ]->callbacks as $filter_priority => $filters ) {

			if ( $filter_priority === $priority ) {

				foreach ( $filters as $filter ) {

					if ( $filter['function'][1] === $method_to_remove
						// only WP 4.7 and above. This plugin is requiring at least WP 4.9.
						&& is_object( $filter['function'][0] )
						&& $filter['function'][0] instanceof $class_name ) {
						$removed = $wp_filter[ $tag ]->remove_filter(
							$tag,
							array(
								$filter['function'][0],
								$method_to_remove,
							),
							$priority
						);
					}
				}
			}
		}

		return $removed;

	}

	/**
	 * Remove an action from the collection registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string $tag              The filter hook to which the function to be removed is hooked.
	 * @param    string $class_name       Class name registering the filter callback.
	 * @param    string $method_to_remove Method name for the filter's callback.
	 * @param    int    $priority         The priority of the method (default 10).
	 *
	 * @return   $removed bool Whether the function is removed.
	 */
	public function remove_action( $tag, $class_name = '', $method_to_remove = '', $priority = 10 ) {
		return $this->remove_filter( $tag, $class_name, $method_to_remove, $priority );
	}

	/**
	 * Add a new shortcode to the collection to be registered with WordPress
	 *
	 * @since     1.0.0
	 * @param     string $tag           The name of the new shortcode.
	 * @param     object $component      A reference to the instance of the object on which the shortcode is defined.
	 * @param     string $callback       The name of the function that defines the shortcode.
	 * @param    int    $priority         Optional. Priority at which the function should be fired. Default 10.
	 * @param    int    $accepted_args    Optional. Number of arguments that should be passed to the $callback. Default 1.
	 */
	public function add_shortcode( $tag, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->shortcodes = $this->add( $this->shortcodes, $tag, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array  $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string $hook             The name of the WordPress filter that is being registered.
	 * @param    object $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string $callback         The name of the function definition on the $component.
	 * @param    int    $priority         The priority at which the function should be fired.
	 * @param    int    $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array(
					$hook['component'],
					$hook['callback'],
				),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array(
					$hook['component'],
					$hook['callback'],
				),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->shortcodes as $hook ) {
			add_shortcode(
				$hook['hook'],
				array(
					$hook['component'],
					$hook['callback'],
				),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

	}

}
