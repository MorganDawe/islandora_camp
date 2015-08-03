<?php

function islandora_camp_preprocess_islandora_large_image(&$variables) {
  dsm($variables, "a vars::");
}

function islandora_camp_preprocess_islandora_basic_collection_wrapper(&$variables) {

  $advanced_collection_pids = theme_get_setting('advanced_collection_view', 'islandora_camp');
  $collection_pids = str_getcsv($advanced_collection_pids, ",");
  $pid = $variables['islandora_object']->{'id'};

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
}
