<?php

/**
 * Implements hook_preprocess().
 */
function islandora_camp_preprocess_islandora_large_image(&$variables) {
  $variables['large_image_preprocess_function_variable'] = "TESTING LARGE IMAGE PREPROCESS FUNCTION IN THEME";
}

/**
 * Implements hook_form_alter().
 */
function islandora_camp_form_islandora_solr_simple_search_form_alter(&$form, &$form_state, $form_id) {
  $form['simple']['islandora_simple_search_query']['#attributes']['placeholder'] = t("Search Repository");
}

/**
 * Implements hook_preprocess().
 */
function islandora_camp_preprocess_islandora_basic_collection_wrapper(&$variables) {

  // In the collection wrapper preprocess, we will add the collection thumb,
  // islandora simple search block, collection metadata and a view rendered
  // dynamically.

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

    // Prepare advanced collection view template
    $embeded = views_embed_view('frontpage_owl_carousel', 'block');

    // Set out as catch all, in-case our view is not around.
    $output = "";
    if (module_exists('owlcarousel')) {
      //First create a view object for the given view
      // TODO: Could move this to the theme settings, specifying what view shows on the
      // collection level page why not?
      $view = views_get_view('frontpage_owl_carousel');
      if (isset($view)) {
        // If our view exists, then set the display.
        $view->set_display('block');

        // Before applying filter we have got its structure and apply values on it.
        $filter1 = $view->get_item('block', 'filter', 'RELS_EXT_isMemberOfCollection_uri_mt');

        // Only looking for members of this collection object. Do not need to check it's
        // CModels as we are already in the islandora_basic_collection_wrapper preprocess
        // function.
        $filter1['value'] = $pid;

        // Apply our dynamic filter to our view
        $view->set_item('block', 'filter', 'RELS_EXT_isMemberOfCollection_uri_mt', $filter1);

        // Execute and prepare the view.
        $view->pre_execute();
        $view->execute();

        // Rendering will return the HTML of the the view
        $output = $view->render();

        // Passing this as array, perhaps add more, like custom title or the like?
        $variables['advanced_collection_view']  = array(
          'carousel' => $output
        );
      }
    }
  }

  // Show collection metadata if selected in the theme settings.
  $metadata = "";
  $show_metadata = theme_get_setting('show_collection_metadata', 'islandora_camp');
  if (isset($show_metadata) && $show_metadata > 0) {
    // Include the required metadata functionality.
    module_load_include('inc', 'islandora', 'includes/metadata');

    // Be sure to add the required Drupal libraries for the metadata form.
    drupal_add_js('misc/form.js');
    drupal_add_js('misc/collapse.js');

    // Set our metadata variable to be printed in the template.
    $variables['collection_metadata']  = islandora_retrieve_metadata_markup($variables['islandora_object']);
  }

  // Show islandora simple search if selected in theme settings.
  $show_search = theme_get_setting('advanced_collection_search_view', 'islandora_camp');
  if (isset($show_search) && $show_search > 0) {
    module_load_include('inc', 'islandora_solr', 'includes/blocks');

    // Use native solr functionality in includes/blocks to render
    // 'islandora_solr_search' provided block CONTENT
    $simple_search_block = islandora_solr_block_view('simple');

    // Render any old block with this theme's 'block render' helper function.
    $simple_search_block = islandora_camp_block_render('islandora_solr', 'simple');
    $variables['islandora_custom_simple_search'] = $simple_search_block;//render($simple_search_block['content']);
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

/**
 * Implements hook_preprocess_block().
 */
function islandora_camp_preprocess_block(&$variables) {
  // Preprocess block based on delta.
  if ($variables['block']->{"delta"} == "simple") {
    // Could do something fancy here i suppose.
    $variables['classes_array'][] = 'fun-class';
    $variables['title_attributes_array']['class'] = array(
      'fun-title-attributes',
      'class-two-here',
    );
    // Add additional attributes as required.
  }
}

function islandora_camp_preprocess_region(&$variables) {
  // Region preprocessing, switch on region name in var's, $variables['region']  <-name
}

function islandora_camp_js_alter(&$javascript) {
}

function islandora_camp_css_alter(&$css) {
}

/**
 * Render a block unique to this themes layouts.
 *
 * @param string $module
 *   The module providing the block.
 * @param string $delta
 *   The delta of the block
 *
 * @return string
 *   The rendered block's HTML content.
 */
function islandora_camp_block_render($module, $delta, $as_renderable = FALSE) {
  $block = block_load($module, $delta);
  $block_content = _block_render_blocks(array($block));
  $build = _block_get_renderable_array($block_content);
  if ($as_renderable) {
    return $build;
  }
  $block_rendered = drupal_render($build);
  return $block_rendered;
}