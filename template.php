<?php

/**
 * Implements hook_preprocess().
 */
function islandora_camp_preprocess_islandora_large_image(&$variables) {
  dsm($variables, "a vars::");
  $variables['large_image_preprocess_function_variable'] = "TESTING LARGE IMAGE PREPROCESS FUNCTION IN THEME";
}

/**
 * Implements hook_preprocess().
 */
function islandora_camp_preprocess_islandora_basic_collection_wrapper(&$variables) {

  // Grab our pid for use as we work in this preprocess function
  $pid = $variables['islandora_object']->{'id'};

  // Add theme hook suggestions.
  // Theme hook suggestions are not provided in the islandora_basic_collection solution pack, so we
  // can add our own in the template. See the preprocessing function below
  // 'islandora_camp_preprocess_islandora_basic_collection_wrapper__islandora_sp_basic_image_collection'
  // to add custom variables to this template.
  $variables['theme_hook_suggestions'][] = 'islandora_basic_collection_wrapper__' . str_replace(':', '_', $pid);

  foreach ($variables['theme_hook_suggestions'] as $theme_hook_preprocess) {
    // Construct preprocess functions individually per theme hook suggestion,
    // As we may want to add more in the future.
    $function = 'islandora_camp_preprocess_' . $theme_hook_preprocess;
    if (function_exists($function)) {
      $function($variables);
    }
  }

  // Retrieve our theme settings
  $advanced_collection_pids = theme_get_setting('advanced_collection_view', 'islandora_camp');
  $collection_pids = str_getcsv($advanced_collection_pids, ",");

  // Apply our intended 'Advanced collection' view to the selected pids, or to
  // All collections depending on the theme settings.
  if (in_array($pid, $collection_pids) || in_array("all", $collection_pids)) {
    module_load_include('inc', 'islandora', 'includes/metadata');

    // Prepare advanced collection view template
    $embeded = views_embed_view('frontpage_owl_carousel', 'block');
    $metadata = islandora_retrieve_metadata_markup($variables['islandora_object']);

    // Be sure to add the required Drupal libraries for the metadata form.
    drupal_add_js('misc/form.js');
    drupal_add_js('misc/collapse.js');

    //First create a view object for the given view
    $view = views_get_view('frontpage_owl_carousel');

    // Then set display
    $view->set_display('block');

    // Before applying filter we have got its structure and apply values on it.
    $filter1 = $view->get_item('block', 'filter', 'RELS_EXT_isMemberOfCollection_uri_mt');
    $filter1['value'] = $pid;

    // Now apply the filters to the view
    $view->set_item('block', 'filter', 'RELS_EXT_isMemberOfCollection_uri_mt', $filter1);

    // Then execute to prepare it
    $view->pre_execute();
    $view->execute();

    // Rendering will return the HTML of the the view
    $output = $view->render();
    $variables['advanced_collection_view']  = array(
      'TN' => "/islandora/object/$pid/datastream/TN/view",
      'metadata' => $metadata,
      'carousel' => $output
    );
  }
  $show_search = theme_get_setting('advanced_collection_search_view', 'islandora_camp');
  if (isset($show_search)) {
    module_load_include('inc', 'islandora_solr', 'includes/blocks');
    $simple_search_block = islandora_solr_block_view('simple');
    $variables['islandora_custom_simple_search'] = render($simple_search_block['content']);
  }
}

/**
 * Theme suggestion preprocess for islandora:sp_basic_image_collection
 */
function islandora_camp_preprocess_islandora_basic_collection_wrapper__islandora_sp_basic_image_collection(&$variables) {
  // From here, we can do something entirely different for this particular collection, based on PID
  // Editing or adding to the variables array here will make it available in the overridden template,
  // In this case, 'islandora-basic-collection-wrapper--islandora-sp-basic-image-collection.tpl.php'
  $variables['template_preprocess_function_variable'] = "TESTING THE TEMPLATE PREPROCESS FUNCTION, UNIQUE TO BASIC IMAGE";

}
